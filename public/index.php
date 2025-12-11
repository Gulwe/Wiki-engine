<?php
// public/index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/ThemeLoader.php';

// Auto‑wylogowanie zbanowanego użytkownika
if (!empty($_SESSION['user_id'])) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT is_banned FROM users WHERE user_id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['is_banned'])) {
        session_unset();
        session_destroy();
        header('Location: /login?error=banned');
        exit;
    }
}

// Sprawdź tryb maintenance
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
            <h1 style="font-size: 72px;">🔧</h1>
            <h1>Strona w Trybie Konserwacji</h1>
            <p style="font-size: 18px; color: #a78bfa;">
                Wracamy wkrótce! Prowadzimy prace konserwacyjne.
            </p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Obsługa plików statycznych (CSS, JS, obrazki)
$requestUri = $_SERVER['REQUEST_URI'];
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|webp)$/', $requestUri)) {
    return false;
}

// Obsługa folderów js, css, uploads
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

// Inicjalizacja połączenia z bazą
Database::getInstance();

// Parser URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// === ROUTING (KOLEJNOŚĆ MA ZNACZENIE!) ===

// === DIAGNOSTIC ===
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
    header('Content-Type: application/json; charset=utf-8');
    
    $query = $_GET['q'] ?? '';
    
    if (strlen($query) < 2) {
        echo json_encode([]);
        exit;
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        $searchTerm = '%' . $query . '%';
        
        $stmt = $db->prepare("
            SELECT 
                p.page_id,
                p.slug,
                p.title,
                SUBSTRING(COALESCE(r.content, ''), 1, 150) AS excerpt,
                c.name AS category
            FROM pages p
            LEFT JOIN revisions r       ON p.current_revision_id = r.revision_id
            LEFT JOIN page_categories pc ON pc.page_id = p.page_id
            LEFT JOIN categories c       ON c.category_id = pc.category_id
            WHERE 
                CONCAT(p.title, ' ', COALESCE(r.content, ''), ' ', COALESCE(c.name, '')) LIKE ?
            GROUP BY 
                p.page_id, p.slug, p.title, excerpt, category
            LIMIT 10
        ");
        
        $stmt->execute([$searchTerm]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $results = [];
        foreach ($rows as $row) {
            $category = isset($row['category']) ? trim($row['category']) : '';
            if ($category === '') {
                $category = 'Bez kategorii';
            }
            
            $results[] = [
                'page_id'  => $row['page_id'],
                'slug'     => $row['slug'],
                'title'    => $row['title'],
                'excerpt'  => $row['excerpt'],
                'category' => $category,
            ];
        }
        
        echo json_encode($results, JSON_UNESCAPED_UNICODE);
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

    if ($_SESSION['role'] === 'viewer') {
        header('Location: /?error=forbidden');
        exit;
    }

    // Pobierz szablony
    $db = Database::getInstance()->getConnection();
    $templates = $db->query("SELECT machine_key, name, content FROM templates WHERE is_active = 1 ORDER BY name")->fetchAll();

    // Pusta strona bez slug
    $page = [
        'slug'    => '',  // PUSTY - użytkownik ustawi w formularzu
        'title'   => '',
        'content' => '',
        'page_id' => null,
    ];

    require __DIR__ . '/../views/pages/edit.php';
    exit;
}

// ZAPISYWANIE NOWEJ STRONY
if ($uri === '/page/store' && $method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    if ($_SESSION['role'] === 'viewer') {
        header('Location: /?error=forbidden');
        exit;
    }

    require_once __DIR__ . '/../models/Page.php';
    $pageModel = new Page();

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    $categories = $_POST['categories'] ?? [];

    // Walidacja
    if (empty($title) || empty($content)) {
        $_SESSION['error'] = 'Tytuł i treść są wymagane.';
        header('Location: /page/new');
        exit;
    }

    // Funkcja generowania slug
    function generateSlug($title) {
        $polishChars = [
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n',
            'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z',
            'Ą' => 'a', 'Ć' => 'c', 'Ę' => 'e', 'Ł' => 'l', 'Ń' => 'n',
            'Ó' => 'o', 'Ś' => 's', 'Ź' => 'z', 'Ż' => 'z'
        ];
        
        $slug = strtr($title, $polishChars);
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return empty($slug) ? 'page-' . time() : $slug;
    }

    // Jeśli slug jest pusty, wygeneruj z tytułu
    if (empty($slug)) {
        $slug = generateSlug($title);
    } else {
        // Sanitizuj slug
        $slug = generateSlug($slug);
    }

    // Sprawdź czy slug już istnieje
    if ($pageModel->findBySlug($slug)) {
        $_SESSION['error'] = 'Strona o tym URL już istnieje. Wybierz inny slug.';
        header('Location: /page/new');
        exit;
    }

    $author = $_SESSION['username'] ?? 'Nieznany';

    // Utwórz stronę (metoda create() już tworzy rewizję)
    $pageId = $pageModel->create($title, $slug, $content, $author);

    if ($pageId) {
        // Przypisz kategorie
        if (!empty($categories)) {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("DELETE FROM page_categories WHERE page_id = :page_id");
            $stmt->execute(['page_id' => $pageId]);
            
            $stmt = $db->prepare("INSERT INTO page_categories (page_id, category_id) VALUES (:page_id, :category_id)");
            foreach ($categories as $categoryId) {
                $stmt->execute([
                    'page_id' => $pageId,
                    'category_id' => $categoryId
                ]);
            }
        }

        $_SESSION['success'] = 'Strona została utworzona!';
        header('Location: /page/' . $slug);
    } else {
        $_SESSION['error'] = 'Błąd podczas tworzenia strony.';
        header('Location: /page/new');
    }
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
        'comment' => 'Przywrócono rewizję #' . $revisionId
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

// Zapisz stronę
if (preg_match('#^/page/([a-z0-9\-]+)/save$#', $uri, $matches) && $method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    if ($_SESSION['role'] === 'viewer') {
        header('Location: /?error=forbidden');
        exit;
    }

    require_once __DIR__ . '/../controllers/PageController.php';
    $pageController = new PageController();
    $pageController->save($matches[1]);
    exit;
}

// Edytuj stronę
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
    
    $db = Database::getInstance()->getConnection();
    $templates = $db->query("SELECT machine_key, name, content FROM templates WHERE is_active = 1 ORDER BY name")->fetchAll();

    require __DIR__ . '/../views/pages/edit.php';
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
            u.user_id,
            u.username,
            u.email,
            u.role,
            u.created_at,
            u.is_banned,
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

// Admin - Podgląd szablonu
if (preg_match('#^/admin/templates/preview/(\d+)$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit('403 - Brak dostępu');
    }

    $templateId = (int)$matches[1];

    $templateModel = new Templates();
    $template = $templateModel->findById($templateId);

    if (!$template) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        exit;
    }

    require __DIR__ . '/../views/admin/template-preview.php';
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

// Admin - Zbanuj użytkownika
if (preg_match('#^/admin/users/ban/(\d+)$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { http_response_code(403); exit; }
    $userId = (int)$matches[1];
    if ($userId === $_SESSION['user_id']) { header('Location: /admin/users?error=self'); exit; }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE users SET is_banned = 1 WHERE user_id = :id");
    $stmt->execute(['id' => $userId]);
    header('Location: /admin/users?success=banned');
    exit;
}

// Admin - Odbanuj użytkownika
if (preg_match('#^/admin/users/unban/(\d+)$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { http_response_code(403); exit; }
    $userId = (int)$matches[1];

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE users SET is_banned = 0 WHERE user_id = :id");
    $stmt->execute(['id' => $userId]);
    header('Location: /admin/users?success=unbanned');
    exit;
}

// Admin - Szablony
if ($uri === '/admin/templates') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }

    $db = Database::getInstance()->getConnection();

    $stmt = $db->query("
        SELECT
            template_id,
            name,
            machine_key AS slug,
            content,
            updated_at
        FROM templates
        ORDER BY name ASC
    ");

    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require __DIR__ . '/../views/admin/templates.php';
    exit;
}

// Admin - Dodaj szablon
if ($uri === '/admin/templates/add' && $method === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { http_response_code(403); exit; }

    $name   = trim($_POST['name'] ?? '');
    $key    = trim($_POST['machine_key'] ?? '');
    $content = $_POST['content'] ?? '';

    if ($name === '' || $key === '' || $content === '') {
        header('Location: /admin/templates?error=empty');
        exit;
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO templates (name, machine_key, content) VALUES (:name, :key, :content)");
    try {
        $stmt->execute(['name' => $name, 'key' => $key, 'content' => $content]);
        header('Location: /admin/templates?success=added');
    } catch (PDOException $e) {
        header('Location: /admin/templates?error=exists');
    }
    exit;
}

// Admin - Zapisz szablon (dodaj nowy LUB edytuj)
if ($uri === '/admin/templates/save' && $method === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }

    $templateId = isset($_POST['template_id']) ? (int)$_POST['template_id'] : null;
    $name = trim($_POST['name'] ?? '');
    $machineKey = trim($_POST['slug'] ?? ''); // Formularz wysyła 'slug', ale zapisujemy jako 'machine_key'
    $content = $_POST['content'] ?? '';

    if ($name === '' || $machineKey === '') {
        header('Location: /admin/templates?error=' . urlencode('Nazwa i klucz są wymagane'));
        exit;
    }

    $db = Database::getInstance()->getConnection();
    
    if ($templateId) {
        // EDYCJA istniejącego szablonu
        $stmt = $db->prepare("
            UPDATE templates
            SET name = :name, machine_key = :key, content = :content, updated_at = NOW()
            WHERE template_id = :id
        ");
        $stmt->execute([
            'name' => $name,
            'key' => $machineKey,
            'content' => $content,
            'id' => $templateId
        ]);
    } else {
        // DODAWANIE nowego szablonu
        $stmt = $db->prepare("
            INSERT INTO templates (name, machine_key, content, created_at, updated_at)
            VALUES (:name, :key, :content, NOW(), NOW())
        ");
        $stmt->execute([
            'name' => $name,
            'key' => $machineKey,
            'content' => $content
        ]);
    }
    
    header('Location: /admin/templates?success=1');
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

// ========================================
// === ADMIN - ZEWNĘTRZNE LINKI ===
// ========================================

// Lista linków
if (preg_match('#^/admin/links/?$#', $requestUri)) {
    require_once __DIR__ . '/../controllers/AdminController.php';
    $controller = new AdminController();
    $controller->links();
    exit;
}

// Dodaj link (POST)
if ($requestUri === '/admin/links/add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../controllers/AdminController.php';
    $controller = new AdminController();
    $controller->addLink();
    exit;
}

// Usuń link
if (preg_match('#^/admin/links/delete/(\d+)$#', $requestUri, $matches)) {
    require_once __DIR__ . '/../controllers/AdminController.php';
    $controller = new AdminController();
    $controller->deleteLink($matches[1]);
    exit;
}

// Przełącz widoczność
if (preg_match('#^/admin/links/toggle/(\d+)$#', $requestUri, $matches)) {
    require_once __DIR__ . '/../controllers/AdminController.php';
    $controller = new AdminController();
    $controller->toggleLink($matches[1]);
    exit;
}

// Przesuń w górę/dół
if (preg_match('#^/admin/links/move/(up|down)/(\d+)$#', $requestUri, $matches)) {
    require_once __DIR__ . '/../controllers/AdminController.php';
    $controller = new AdminController();
    $controller->moveLink($matches[2], $matches[1]);
    exit;
}



// Profil użytkownika
if (preg_match('#^/user/(\d+)$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    $userId = (int)$matches[1];

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT 
            u.user_id,
            u.username,
            u.email,
            u.role,
            u.created_at,
            u.is_banned,
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
        WHERE u.user_id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $userId]);
    $profileUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profileUser) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        exit;
    }

    require __DIR__ . '/../views/user/profile.php';
    exit;
}


// Kategoria - lista stron
if (preg_match('~^/category/(\d+)$~', $uri, $matches)) {
    $categoryId = (int)$matches[1];

    $db = Database::getInstance()->getConnection();

    // Pobierz kategorię
    $stmt = $db->prepare("
        SELECT *
        FROM categories
        WHERE category_id = :id
    ");
    $stmt->execute(['id' => $categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        exit;
    }

    // Pobierz strony w kategorii z treścią bieżącej rewizji
    $stmt = $db->prepare("
        SELECT 
            p.page_id,
            p.slug,
            p.title,
            p.created_by,
            p.updated_at,
            u.username AS author,
            r.content
        FROM pages p
        JOIN page_categories pc ON p.page_id = pc.page_id
        JOIN revisions r        ON p.current_revision_id = r.revision_id
        LEFT JOIN users u       ON p.created_by = u.user_id
        WHERE pc.category_id = :category_id
        ORDER BY p.title ASC
    ");
    $stmt->execute(['category_id' => $categoryId]);
    $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Parser meta (opis + flagi + symbole kampanii) ---
    foreach ($pages as &$p) {
        $content = $p['content'] ?? '';

        // OPIS MODA z sekcji "### Opis moda"
        $p['mod_description'] = '';

        if (preg_match('/^###\s*Opis moda\s*\R(.+?)(?:\R#{1,6}\s|\Z)/usm', $content, $m)) {
            $text = trim($m[1]);

            // wywal szablony, linki wiki, markdown
            $text = preg_replace('/\{\{.*?\}\}/s', '', $text);        // {{ ... }}
            $text = preg_replace('/\[\[(.*?)\]\]/', '$1', $text);     // [[Link]]
            $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);     // **bold**
            $text = preg_replace('/\*(.*?)\*/', '$1', $text);         // *italic*
            $text = strip_tags($text);
            $text = trim($text);

            if ($text !== '') {
                if (mb_strlen($text) > 220) {
                    $text = mb_substr($text, 0, 220) . '…';
                }
                $p['mod_description'] = $text;
            }
        }

        // FLAGI {{flag:PL}} / {{flag:PL|Polski}}
        $langs = [];
        if (preg_match_all('/\{\{\s*flag:([A-Za-z]{2})(?:\|([^}]*))?\}\}/i', $content, $m2, PREG_SET_ORDER)) {
            foreach ($m2 as $match) {
                $code  = strtoupper(trim($match[1]));
                $label = isset($match[2]) && trim($match[2]) !== '' ? trim($match[2]) : $code;

                if ($code !== '') {
                    $langs[$code] = [
                        'code'  => $code,
                        'label' => $label,
                    ];
                }
            }
        }
        $p['languages'] = array_values($langs); // [ [code, label], ... ]

        // SYMBOLE KAMPANII {{symbol:am_small}} {{symbol:ru_small}}
        // dopasuj ścieżkę src do tego, czego używa Twój parser symboli
// --- SYMBOLE KAMPANII z wiersza "| Kampania || ..." ---
$symbols = [];

// znajdź wiersz tabeli zaczynający się od "| Kampania"
if (preg_match('/^\|\s*Kampania\s*\|\|\s*(.+)$/mi', $content, $rowMatch)) {
    $cell = trim($rowMatch[1]); // np. "{{symbol:am_small}} {{symbol:ru_small}}"

    // teraz wyciągnij wszystkie {{symbol:...}} z tej komórki
    if (preg_match_all('/\{\{\s*symbol:([^\}\r\n]+)\}\}/i', $cell, $m3, PREG_SET_ORDER)) {
        foreach ($m3 as $match) {
            $name = trim($match[1]);   // np. am_small
            if ($name === '') {
                continue;
            }
            $key = strtolower($name);
            $src = "/symbols/{$key}.png"; // dopasuj do swojej ścieżki

            $symbols[$key] = [
                'name' => $name,
                'src'  => $src,
            ];
        }
    }
}

$p['campaign_symbols'] = array_values($symbols);

    }
    unset($p);

    require __DIR__ . '/../views/category.php';
    exit;
}



// Strona główna
if ($uri === '/' || $uri === '') {
    $pageModel = new Page();
    $pages = $pageModel->getRecent(5);

    $db = Database::getInstance()->getConnection();

    // liczba wszystkich stron
    $totalPagesCount = (int)$db->query("SELECT COUNT(*) FROM pages")->fetchColumn();

    $stmt = $db->query("
        SELECT c.category_id, c.name, c.description, COUNT(pc.page_id) AS pages_count
        FROM categories c
        LEFT JOIN page_categories pc ON c.category_id = pc.category_id
        GROUP BY c.category_id
        ORDER BY pages_count DESC, c.name ASC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    require __DIR__ . '/../views/home.php';
    exit;
}





// ====== EXTERNAL LINKS - ADMIN ======

// Admin - External Links
if ($uri == '/admin/links') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        http_response_code(403);
        die('403 - Brak dostępu');
    }

    require_once __DIR__ . '/../models/ExternalLink.php';
    $linkModel = new ExternalLink();
    $links = $linkModel->getAll();

    require __DIR__ . '/../views/admin/links.php';
    exit;
}

// Admin - Dodaj External Link
if ($uri == '/admin/links/add' && $method == 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        http_response_code(403);
        exit;
    }

    $title = $_POST['title'] ?? '';
    $url = $_POST['url'] ?? '';
    $description = $_POST['description'] ?? '';
    $source = $_POST['source'] ?? '';
    $icon = $_POST['icon'] ?? '🔗';

    if (empty($title) || empty($url)) {
        header('Location: /admin/links?error=empty');
        exit;
    }

    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    require_once __DIR__ . '/../models/ExternalLink.php';
    $linkModel = new ExternalLink();
    $linkModel->create($title, $url, $description, $source, $icon, $userId);

    header('Location: /admin/links?success=added');
    exit;
}

// Admin - Usuń External Link
if (preg_match('#^/admin/links/delete/(\d+)$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        http_response_code(403);
        exit;
    }

    $linkId = (int)$matches[1];

    require_once __DIR__ . '/../models/ExternalLink.php';
    $linkModel = new ExternalLink();
    $linkModel->delete($linkId);

    header('Location: /admin/links?success=deleted');
    exit;
}

// Admin - Toggle widoczność External Link
if (preg_match('#^/admin/links/toggle/(\d+)$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        http_response_code(403);
        exit;
    }

    $linkId = (int)$matches[1];

    require_once __DIR__ . '/../models/ExternalLink.php';
    $linkModel = new ExternalLink();
    $linkModel->toggleVisibility($linkId);

    header('Location: /admin/links');
    exit;
}

// Admin - Przesuń External Link (góra/dół)
if (preg_match('#^/admin/links/move/(up|down)/(\d+)$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        http_response_code(403);
        exit;
    }

    $direction = $matches[1]; // up / down
    $linkId    = (int)$matches[2];

    require_once __DIR__ . '/../models/ExternalLink.php';
    $linkModel = new ExternalLink();
    $linkModel->move($linkId, $direction);

    header('Location: /admin/links');
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

// Lista wszystkich stron
if ($uri === '/pages') {
    $pageModel = new Page();
    
    // Paginacja
    $perPage = 20;
    $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($currentPage - 1) * $perPage;
    
    // Filtrowanie po kategorii
    $categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : null;
    
    // Sortowanie
    $sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'updated'; // updated, created, title, views
    
    $db = Database::getInstance()->getConnection();
    
    // Query budowanie
    $whereClause = '';
    $params = [];
    
    if ($categoryFilter) {
        $whereClause = 'WHERE pc.category_id = :category_id';
        $params['category_id'] = $categoryFilter;
    }
    
switch ($sortBy) {
    case 'created':
        $orderClause = 'p.created_at DESC';
        break;
    case 'title':
        $orderClause = 'p.title ASC';
        break;
    case 'views':
        $orderClause = 'p.views DESC';
        break;
    default:
        $orderClause = 'p.updated_at DESC';
        break;
}

    
    // Pobierz strony
    $sql = "
        SELECT p.*, u.username as author, c.name as category_name
        FROM pages p
        LEFT JOIN users u ON p.created_by = u.user_id
        LEFT JOIN page_categories pc ON p.page_id = pc.page_id
        LEFT JOIN categories c ON pc.category_id = c.category_id
        $whereClause
        GROUP BY p.page_id
        ORDER BY $orderClause
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue(":$key", $val, PDO::PARAM_INT);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $pages = $stmt->fetchAll();
    
    // Policz total dla paginacji
    $countSql = "SELECT COUNT(DISTINCT p.page_id) FROM pages p LEFT JOIN page_categories pc ON p.page_id = pc.page_id $whereClause";
    $countStmt = $db->prepare($countSql);
    foreach ($params as $key => $val) {
        $countStmt->bindValue(":$key", $val, PDO::PARAM_INT);
    }
    $countStmt->execute();
    $totalPages = (int)$countStmt->fetchColumn();
    $totalPagesCount = ceil($totalPages / $perPage);
    
    // Pobierz kategorie dla filtra
    $categories = $db->query("
        SELECT c.*, COUNT(pc.page_id) as pages_count
        FROM categories c
        LEFT JOIN page_categories pc ON c.category_id = pc.category_id
        GROUP BY c.category_id
        ORDER BY c.name ASC
    ")->fetchAll();
    
    require __DIR__ . '/../views/pages-list.php';
    exit;
}


// 404 - Strona nie znaleziona
http_response_code(404);
require __DIR__ . '/../views/404.php';
