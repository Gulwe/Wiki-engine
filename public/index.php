<?php
// public/index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/ThemeLoader.php';

// Sprawd藕 tryb maintenance
$maintenanceMode = ThemeLoader::get('maintenance_mode', '0');
if ($maintenanceMode == '1' && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
    http_response_code(503);
    ?>
    <!DOCTYPE html>
    <html lang="pl">
    <head>
        <meta charset="UTF-8">
        <title>Konserwacja - <?= ThemeLoader::get('site_name', 'Wiki Engine') ?></title>
        <link rel="stylesheet" href="/css/style.css">
        <?= ThemeLoader::generateCSS() ?>
    </head>
    <body>
        <div class="container" style="text-align: center; padding: 100px 20px;">
            <h1 style="font-size: 72px;">馃敡</h1>
            <h1>Strona w Trybie Konserwacji</h1>
            <p style="font-size: 18px; color: #a78bfa;">
                Wracamy wkr贸tce! Prowadzimy prace konserwacyjne.
            </p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Obs艂uga plik贸w statycznych (CSS, JS, obrazki)
$requestUri = $_SERVER['REQUEST_URI'];
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|webp)$/', $requestUri)) {
    return false; // Pozw贸l serwerowi obs艂u偶y膰 plik
}

// Obs艂uga folder贸w js, css, uploads
if (preg_match('#^/(js|css|uploads|misc)/#', parse_url($requestUri, PHP_URL_PATH))) {
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

// Inicjalizacja po艂膮czenia z baz膮
Database::getInstance();

// Parser URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// === ROUTING (KOLEJNO艢膯 MA ZNACZENIE!) ===

// Strona g艂贸wna
if ($uri === '/' || $uri === '') {
    $pageModel = new Page();
    $pages = $pageModel->getAll();
    require __DIR__ . '/../views/home.php';
    exit;
}

// === DIAGNOSTIC (DODAJ TO) ===
if ($uri === '/diagnostic') {
    require __DIR__ . '/../views/diagnostic.php';
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

// API: Podgl膮d
if ($uri === '/api/preview' && $method === 'POST') {
    require_once __DIR__ . '/../core/WikiParser.php';
    $parser = new WikiParser();
    $content = $_POST['content'] ?? '';
    echo $parser->parse($content);
    exit;
}

// NOWA STRONA - MUSI BY膯 PRZED /page/{slug}!
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

// Wy艣wietl konkretn膮 rewizj臋
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
    
    $page['content'] = $revision['content'];
    $page['revision_comment'] = $revision['revision_comment'];
    $page['revision_date'] = $revision['created_at'];
    $page['revision_author'] = $revision['author'];
    $page['is_old_revision'] = true;
    $page['current_revision_id_display'] = $revisionId;
    
    require __DIR__ . '/../views/pages/view.php';
    exit;
}

// Przywr贸膰 rewizj臋 (dla admin贸w)
if (preg_match('#^/page/([a-z0-9-]+)/restore/(\d+)$#', $uri, $matches)) {
    $slug = $matches[1];
    $revisionId = (int)$matches[2];
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('403 - Tylko admin mo偶e przywraca膰 rewizje');
    }
    
    $pageModel = new Page();
    $page = $pageModel->findBySlug($slug);
    
    if (!$page) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        exit;
    }
    
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
    
    $stmt = $db->prepare("
        INSERT INTO revisions (page_id, content, author_id, revision_comment)
        VALUES (:page_id, :content, :author_id, :comment)
    ");
    
    $stmt->execute([
        'page_id' => $page['page_id'],
        'content' => $oldRevision['content'],
        'author_id' => $_SESSION['user_id'],
        'comment' => 'Przywr贸cono rewizj臋 #' . $revisionId
    ]);
    
    $newRevisionId = $db->lastInsertId();
    
    $stmt = $db->prepare("UPDATE pages SET current_revision_id = :revision_id WHERE page_id = :page_id");
    $stmt->execute([
        'revision_id' => $newRevisionId,
        'page_id' => $page['page_id']
    ]);
    
    header('Location: /page/' . $slug . '?restored=1');
    exit;
}

// Historia strony
if (preg_match('#^/page/([a-z0-9-]+)/history$#', $uri, $matches)) {
    $slug = $matches[1];
    $pageModel = new Page();
    $page = $pageModel->findBySlug($slug);
    
    if (!$page) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        exit;
    }
    
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

// Zapisz stron臋
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

// Edytuj stron臋
if (preg_match('#^/page/([a-z0-9-]+)/edit$#', $uri, $matches)) {
    $slug = $matches[1];
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    
    $pageModel = new Page();
    $page = $pageModel->findBySlug($slug);
    
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

// Upload obrazk贸w
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

// Galeria obrazk贸w
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
        die('<h1 style="color:#ff0000;">403 - Brak dost臋pu</h1><p>Tylko administratorzy maj膮 dost臋p do tego panelu.</p>');
    }
    
    $db = Database::getInstance()->getConnection();
    
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
        SELECT 
            u.*,
            COALESCE(p.pages_created, 0)   AS pages_created,
            COALESCE(r.total_edits, 0)     AS total_edits,
            COALESCE(c.total_comments, 0)  AS total_comments
        FROM users u
        LEFT JOIN (
            SELECT created_by AS user_id, COUNT(*) AS pages_created
            FROM pages
            GROUP BY created_by
        ) p ON u.user_id = p.user_id
        LEFT JOIN (
            SELECT author_id AS user_id, COUNT(*) AS total_edits
            FROM revisions
            GROUP BY author_id
        ) r ON u.user_id = r.user_id
        LEFT JOIN (
            SELECT user_id, COUNT(*) AS total_comments
            FROM comments
            GROUP BY user_id
        ) c ON u.user_id = c.user_id
        ORDER BY u.created_at DESC
    ")->fetchAll();
    
    require __DIR__ . '/../views/admin/users.php';
    exit;
}


// Admin - Dodaj u偶ytkownika
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
    
    // Nie można usunąć samego siebie
    if ($userId === $_SESSION['user_id']) {
        header('Location: /admin/users?error=self');
        exit;
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Usuń komentarze użytkownika
    $stmt = $db->prepare("DELETE FROM comments WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    
    // Usuń rewizje użytkownika
    $stmt = $db->prepare("DELETE FROM revisions WHERE author_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    
    // Usuń strony użytkownika
    $stmt = $db->prepare("DELETE FROM pages WHERE created_by = :user_id");
    $stmt->execute(['user_id' => $userId]);
    
    // Usuń użytkownika
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

// Admin - Dodaj kategori臋
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
    
    $customCSS = $db->query("SELECT content FROM customizations WHERE type = 'css' AND is_active = 1 ORDER BY custom_id DESC LIMIT 1")->fetchColumn();
    $customJS = $db->query("SELECT content FROM customizations WHERE type = 'js' AND is_active = 1 ORDER BY custom_id DESC LIMIT 1")->fetchColumn();
    
    require __DIR__ . '/../views/admin/customization.php';
    exit;
}

// Admin - Zapisz customizacj臋 CSS/JS
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
    
    $db->prepare("UPDATE customizations SET is_active = 0 WHERE type = :type")->execute(['type' => $type]);
    
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
    
    $stmt = $db->prepare("SELECT * FROM categories WHERE category_id = :id");
    $stmt->execute(['id' => $categoryId]);
    $category = $stmt->fetch();
    
    if (!$category) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        exit;
    }
    
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

// Pomoc sk艂adni
if ($uri === '/syntax-help') {
    require __DIR__ . '/../views/syntax-help.php';
    exit;
}

// === KOMENTARZE ===

// Dodaj komentarz
if ($uri === '/comment/add' && $method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Musisz by膰 zalogowany']);
        exit;
    }
    
    $pageId = (int)($_POST['page_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    
    if (empty($content)) {
        http_response_code(400);
        echo json_encode(['error' => 'Tre艣膰 komentarza nie mo偶e by膰 pusta']);
        exit;
    }
    
    $commentModel = new Comment();
    if ($commentModel->create($pageId, $_SESSION['user_id'], $content, $parentId)) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'B艂膮d dodawania komentarza']);
    }
    exit;
}

// Usu艅 komentarz
if (preg_match('#^/comment/(\d+)/delete$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Brak autoryzacji']);
        exit;
    }
    
    $commentId = (int)$matches[1];
    $commentModel = new Comment();
    
    if ($commentModel->delete($commentId, $_SESSION['user_id'])) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'B艂膮d usuwania komentarza']);
    }
    exit;
}

// === ANALYTICS DASHBOARD ===
if ($uri === '/analytics') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: /login');
        exit;
    }
    
    require_once __DIR__ . '/../models/Analytics.php';
    $analytics = new Analytics();
    
    require __DIR__ . '/../views/analytics/dashboard.php';
    exit;
}

// API endpoint dla wykres贸w (JSON)
if ($uri === '/api/analytics/views') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(401);
        exit;
    }
    
    require_once __DIR__ . '/../models/Analytics.php';
    $analytics = new Analytics();
    
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
    $data = $analytics->getViewsLastDays($days);
    
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// === PANEL CUSTOMIZACJI ===
if ($uri === '/admin/customize') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: /login');
        exit;
    }
    
    require_once __DIR__ . '/../models/Settings.php';
    $settings = new Settings();
    
    require __DIR__ . '/../views/admin/customize.php';
    exit;
}

// Zapisz ustawienia
if ($uri === '/admin/customize/save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(401);
        exit;
    }
    
    require_once __DIR__ . '/../controllers/AdminController.php';
    $controller = new AdminController();
    $controller->saveCustomize();
    exit;
}




// Wy艣wietl stron臋 - MUSI BY膯 NA KO艃CU!
if (preg_match('#^/page/([a-z0-9-]+)$#', $uri, $matches)) {
    $slug = $matches[1];
    $pageModel = new Page();
    $page = $pageModel->findBySlug($slug);
    
    if (!$page) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        exit;
    }
    
    // Track page view z Analytics
    require_once __DIR__ . '/../models/Analytics.php';
    $analytics = new Analytics();
    $analytics->trackPageView($page['page_id'], $_SESSION['user_id'] ?? null);
    
    // Pobierz zaktualizowane views
    $page = $pageModel->findBySlug($slug);
    
    require __DIR__ . '/../views/pages/view.php';
    exit;
}


// 404 - Strona nie znaleziona
http_response_code(404);
require __DIR__ . '/../views/404.php';
