<?php
// public/index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Obsługa plików statycznych (CSS, JS, obrazki)
$requestUri = $_SERVER['REQUEST_URI'];
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico)$/', $requestUri)) {
    return false; // Pozwól serwerowi obsłużyć plik
}

// Obsługa folderów js, css, uploads
if (preg_match('#^/(js|css|uploads)/#', parse_url($requestUri, PHP_URL_PATH))) {
    return false;
}


// Autoload
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../core/',
        __DIR__ . '/../controllers/',
        __DIR__ . '/../models/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Inicjalizacja połączenia z bazą
Database::getInstance();

// Parser URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// === ROUTING (KOLEJNOŚĆ MA ZNACZENIE!) ===

// Strona główna
if ($uri === '/' || $uri === '') {
    $pageModel = new Page();
    $pages = $pageModel->getAll();
    require __DIR__ . '/../views/home.php';
    exit;
}

// Logowanie
if ($uri === '/login') {
    if ($method === 'POST') {
        require __DIR__ . '/../controllers/AuthController.php';
        $authController = new AuthController();
        $authController->login();
    } else {
        require __DIR__ . '/../views/login.php';
    }
    exit;
}

// Wylogowanie
if ($uri === '/logout') {
    session_destroy();
    header('Location: /');
    exit;
}

// API: Wyszukiwanie
if ($uri === '/api/search' && $method === 'GET') {
    header('Content-Type: application/json');
    
    $query = $_GET['q'] ?? '';
    
    if (strlen($query) < 2) {
        echo json_encode([]);
        exit;
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        $searchTerm = '%' . $query . '%';
        
        // Prostsze zapytanie z CONCAT
        $stmt = $db->prepare("
            SELECT p.page_id, p.slug, p.title,
                   SUBSTRING(COALESCE(r.content, ''), 1, 150) as excerpt
            FROM pages p
            LEFT JOIN revisions r ON p.current_revision_id = r.revision_id
            WHERE CONCAT(p.title, ' ', COALESCE(r.content, '')) LIKE ?
            LIMIT 10
        ");
        
        $stmt->execute([$searchTerm]);
        $results = $stmt->fetchAll();
        
        echo json_encode($results);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}


// API: Podgląd
if ($uri === '/api/preview' && $method === 'POST') {
    require_once __DIR__ . '/../core/WikiParser.php';
    $parser = new WikiParser();
    $content = $_POST['content'] ?? '';
    echo $parser->parse($content);
    exit;
}

// NOWA STRONA - MUSI BYĆ PRZED /page/{slug}!
if ($uri === '/page/new') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    
    $slug = 'new-page-' . time();
    $page = [
        'slug' => $slug,
        'title' => '',
        'content' => '',
        'page_id' => null
    ];
    require __DIR__ . '/../views/pages/edit.php';
    exit;
}

// Wyświetl konkretną rewizję
if (preg_match('#^/page/([a-z0-9-]+)/revision/(\d+)$#', $uri, $matches)) {
    $slug = $matches[1];
    $revisionId = (int)$matches[2];
    
    $pageModel = new Page();
    $page = $pageModel->findBySlug($slug);
    
    if (!$page) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        exit;
    }
    
    // Pobierz konkretną rewizję
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT r.*, u.username as author
        FROM revisions r
        LEFT JOIN users u ON r.author_id = u.user_id
        WHERE r.revision_id = :revision_id AND r.page_id = :page_id
    ");
    
    $stmt->execute([
        'revision_id' => $revisionId,
        'page_id' => $page['page_id']
    ]);
    
    $revision = $stmt->fetch();
    
    if (!$revision) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        exit;
    }
    
    // Nadpisz zawartość strony rewizją
    $page['content'] = $revision['content'];
    $page['revision_comment'] = $revision['revision_comment'];
    $page['revision_date'] = $revision['created_at'];
    $page['revision_author'] = $revision['author'];
    $page['is_old_revision'] = true;
    $page['current_revision_id_display'] = $revisionId;
    
    require __DIR__ . '/../views/pages/view.php';
    exit;
}

// Przywróć rewizję (dla adminów)
if (preg_match('#^/page/([a-z0-9-]+)/restore/(\d+)$#', $uri, $matches)) {
    $slug = $matches[1];
    $revisionId = (int)$matches[2];
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('403 - Tylko admin może przywracać rewizje');
    }
    
    $pageModel = new Page();
    $page = $pageModel->findBySlug($slug);
    
    if (!$page) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        exit;
    }
    
    // Pobierz starą rewizję
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT content FROM revisions WHERE revision_id = :revision_id AND page_id = :page_id");
    $stmt->execute([
        'revision_id' => $revisionId,
        'page_id' => $page['page_id']
    ]);
    
    $oldRevision = $stmt->fetch();
    
    if (!$oldRevision) {
        http_response_code(404);
        die('404 - Rewizja nie znaleziona');
    }
    
    // Stwórz nową rewizję z zawartością starej
    $stmt = $db->prepare("
        INSERT INTO revisions (page_id, content, author_id, revision_comment)
        VALUES (:page_id, :content, :author_id, :comment)
    ");
    
    $stmt->execute([
        'page_id' => $page['page_id'],
        'content' => $oldRevision['content'],
        'author_id' => $_SESSION['user_id'],
        'comment' => 'Przywrócono rewizję #' . $revisionId
    ]);
    
    $newRevisionId = $db->lastInsertId();
    
    // Ustaw jako aktualną
    $stmt = $db->prepare("UPDATE pages SET current_revision_id = :revision_id WHERE page_id = :page_id");
    $stmt->execute([
        'revision_id' => $newRevisionId,
        'page_id' => $page['page_id']
    ]);
    
    header('Location: /page/' . $slug . '?restored=1');
    exit;
}

// Historia strony - PRZED /page/{slug}/save!
if (preg_match('#^/page/([a-z0-9-]+)/history$#', $uri, $matches)) {
    $slug = $matches[1];
    $pageModel = new Page();
    $page = $pageModel->findBySlug($slug);
    
    if (!$page) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        exit;
    }
    
    // Pobierz wszystkie rewizje
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT r.*, u.username as author
        FROM revisions r
        LEFT JOIN users u ON r.author_id = u.user_id
        WHERE r.page_id = :page_id
        ORDER BY r.created_at DESC
    ");
    $stmt->execute(['page_id' => $page['page_id']]);
    $revisions = $stmt->fetchAll();
    
    require __DIR__ . '/../views/pages/history.php';
    exit;
}



// Zapisz stronę - PRZED /page/{slug}!
if (preg_match('#^/page/([a-z0-9-]+)/save$#', $uri, $matches) && $method === 'POST') {
    $slug = $matches[1];
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    
    require_once __DIR__ . '/../controllers/PageController.php';
    $pageController = new PageController();
    $pageController->save($slug);
    exit;
}

// Edytuj stronę - PRZED /page/{slug}!
if (preg_match('#^/page/([a-z0-9-]+)/edit$#', $uri, $matches)) {
    $slug = $matches[1];
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    
    $pageModel = new Page();
    $page = $pageModel->findBySlug($slug);
    
    // Jeśli strona nie istnieje, pozwól ją stworzyć
    if (!$page) {
        $page = [
            'slug' => $slug,
            'title' => ucfirst(str_replace('-', ' ', $slug)),
            'content' => '',
            'page_id' => null
        ];
    }
    
    require __DIR__ . '/../views/pages/edit.php';
    exit;
}

// Wyświetl stronę - MUSI BYĆ NA KOŃCU!
if (preg_match('#^/page/([a-z0-9-]+)$#', $uri, $matches)) {
    $slug = $matches[1];
    $pageModel = new Page();
    $page = $pageModel->findBySlug($slug);
    
    if (!$page) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        exit;
    }
    
    require __DIR__ . '/../views/pages/view.php';
    exit;
}

// Upload obrazków
if ($uri === '/api/upload' && $method === 'POST') {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    
    require_once __DIR__ . '/../controllers/MediaController.php';
    $mediaController = new MediaController();
    $result = $mediaController->upload();
    
    echo json_encode($result);
    exit;
}

// Galeria obrazków
if ($uri === '/media') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("
        SELECT m.*, u.username as uploader
        FROM media m
        LEFT JOIN users u ON m.uploaded_by = u.user_id
        ORDER BY m.uploaded_at DESC
    ");
    $mediaFiles = $stmt->fetchAll();
    
    require __DIR__ . '/../views/media.php';
    exit;
}

// Panel Admina - Dashboard
if ($uri === '/admin') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('<h1 style="color:#ff0000;">403 - Brak dostępu</h1><p>Tylko administratorzy mają dostęp do tego panelu.</p>');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Statystyki
    $stats = [
        'pages' => $db->query("SELECT COUNT(*) FROM pages")->fetchColumn(),
        'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'revisions' => $db->query("SELECT COUNT(*) FROM revisions")->fetchColumn(),
        'media' => $db->query("SELECT COUNT(*) FROM media")->fetchColumn()
    ];
    
    require __DIR__ . '/../views/admin/dashboard.php';
    exit;
}

// Admin - Użytkownicy
if ($uri === '/admin/users') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('403 - Brak dostępu');
    }
    
    $db = Database::getInstance()->getConnection();
    $users = $db->query("
        SELECT u.*, 
               COUNT(DISTINCT p.page_id) as pages_created,
               COUNT(DISTINCT r.revision_id) as total_edits
        FROM users u
        LEFT JOIN pages p ON u.user_id = p.created_by
        LEFT JOIN revisions r ON u.user_id = r.author_id
        GROUP BY u.user_id
        ORDER BY u.created_at DESC
    ")->fetchAll();
    
    require __DIR__ . '/../views/admin/users.php';
    exit;
}

// Admin - Dodaj użytkownika
if ($uri === '/admin/users/add' && $method === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }
    
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'viewer';
    
    if (empty($username) || empty($email) || empty($password)) {
        header('Location: /admin/users?error=empty');
        exit;
    }
    
    $db = Database::getInstance()->getConnection();
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
    try {
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash, role)
            VALUES (:username, :email, :password_hash, :role)
        ");
        
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role
        ]);
        
        header('Location: /admin/users?success=added');
    } catch (PDOException $e) {
        header('Location: /admin/users?error=exists');
    }
    exit;
}

// Admin - Usuń użytkownika
if (preg_match('#^/admin/users/delete/(\d+)$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }
    
    $userId = (int)$matches[1];
    
    // Nie pozwól usunąć samego siebie
    if ($userId === $_SESSION['user_id']) {
        header('Location: /admin/users?error=self');
        exit;
    }
    
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("DELETE FROM users WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    
    header('Location: /admin/users?success=deleted');
    exit;
}

// Admin - Kategorie
if ($uri === '/admin/categories') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }
    
    $db = Database::getInstance()->getConnection();
    $categories = $db->query("
        SELECT c.*, 
               COUNT(pc.page_id) as pages_count,
               parent.name as parent_name
        FROM categories c
        LEFT JOIN page_categories pc ON c.category_id = pc.category_id
        LEFT JOIN categories parent ON c.parent_id = parent.category_id
        GROUP BY c.category_id
        ORDER BY c.name ASC
    ")->fetchAll();
    
    require __DIR__ . '/../views/admin/categories.php';
    exit;
}

// Admin - Dodaj kategorię
if ($uri === '/admin/categories/add' && $method === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }
    
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($name)) {
        header('Location: /admin/categories?error=empty');
        exit;
    }
    
    $db = Database::getInstance()->getConnection();
    
    try {
        $stmt = $db->prepare("
            INSERT INTO categories (name, description)
            VALUES (:name, :description)
        ");
        
        $stmt->execute([
            'name' => $name,
            'description' => $description
        ]);
        
        header('Location: /admin/categories?success=added');
    } catch (PDOException $e) {
        header('Location: /admin/categories?error=exists');
    }
    exit;
}

// Admin - Customizacja CSS/JS
if ($uri === '/admin/customization') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Pobierz obecne customizacje
    $customCSS = $db->query("SELECT content FROM customizations WHERE type = 'css' AND is_active = 1 ORDER BY custom_id DESC LIMIT 1")->fetchColumn();
    $customJS = $db->query("SELECT content FROM customizations WHERE type = 'js' AND is_active = 1 ORDER BY custom_id DESC LIMIT 1")->fetchColumn();
    
    require __DIR__ . '/../views/admin/customization.php';
    exit;
}

// Admin - Zapisz customizację
if ($uri === '/admin/customization/save' && $method === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }
    
    $type = $_POST['type'] ?? '';
    $content = $_POST['content'] ?? '';
    
    if (!in_array($type, ['css', 'js'])) {
        header('Location: /admin/customization?error=invalid');
        exit;
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Dezaktywuj stare
    $db->prepare("UPDATE customizations SET is_active = 0 WHERE type = :type")->execute(['type' => $type]);
    
    // Dodaj nowe
    $stmt = $db->prepare("
        INSERT INTO customizations (type, name, content, is_active)
        VALUES (:type, :name, :content, 1)
    ");
    
    $stmt->execute([
        'type' => $type,
        'name' => $type . '_' . date('Y-m-d_H-i-s'),
        'content' => $content
    ]);
    
    header('Location: /admin/customization?success=saved');
    exit;
}

// Kategoria - lista stron
if (preg_match('#^/category/(\d+)$#', $uri, $matches)) {
    $categoryId = (int)$matches[1];
    
    $db = Database::getInstance()->getConnection();
    
    // Pobierz kategorię
    $stmt = $db->prepare("SELECT * FROM categories WHERE category_id = :id");
    $stmt->execute(['id' => $categoryId]);
    $category = $stmt->fetch();
    
    if (!$category) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        exit;
    }
    
    // Pobierz strony w kategorii
    $stmt = $db->prepare("
        SELECT p.*, u.username as author
        FROM pages p
        JOIN page_categories pc ON p.page_id = pc.page_id
        LEFT JOIN users u ON p.created_by = u.user_id
        WHERE pc.category_id = :category_id
        ORDER BY p.title ASC
    ");
    $stmt->execute(['category_id' => $categoryId]);
    $pages = $stmt->fetchAll();
    
    require __DIR__ . '/../views/category.php';
    exit;
}

// Lista wszystkich kategorii
if ($uri === '/categories') {
    $db = Database::getInstance()->getConnection();
    $categories = $db->query("
        SELECT c.*, COUNT(pc.page_id) as pages_count
        FROM categories c
        LEFT JOIN page_categories pc ON c.category_id = pc.category_id
        GROUP BY c.category_id
        ORDER BY c.name ASC
    ")->fetchAll();
    
    require __DIR__ . '/../views/categories.php';
    exit;
}



// 404
http_response_code(404);
require __DIR__ . '/../views/404.php';
