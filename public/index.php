<?php
session_start();

// Autoload i includes
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/ThemeLoader.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Page.php';
require_once __DIR__ . '/../core/autoload.php';

// === PARSOWANIE URI ===
$requestUri = $_SERVER['REQUEST_URI'];
$uri = parse_url($requestUri, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Usuń trailing slash (oprócz głównej /)
if ($uri !== '/' && substr($uri, -1) === '/') {
    $uri = rtrim($uri, '/');
}

// Obsługa plików statycznych
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|webp)$/', $uri)) {
    return false;
}

if (preg_match('#^/(js|css|uploads|misc|symbols|flags|backgrounds)/#', $uri)) {
    return false;
}

// === MIDDLEWARE: Auto-wylogowanie zbanowanych ===
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

// === MIDDLEWARE: Tryb konserwacji ===
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

// ========================================
// === ROUTING - AUTH ===
// ========================================

// Logowanie
if ($uri === '/login') {
    if ($method === 'POST') {
        require_once __DIR__ . '/../controllers/AuthController.php';
        $authController = new AuthController();
        $authController->login();
    } else {
        View::render('login', ['pageTitle' => 'Logowanie']);
    }
    exit;
}

// Wylogowanie
if ($uri === '/logout') {
    session_destroy();
    header('Location: /');
    exit;
}

// ========================================
// === ROUTING - STRONA GŁÓWNA ===
// ========================================

if ($uri === '/' || $uri === '') {
    $pageModel = new Page();
    $pages = $pageModel->getRecent(5);

    $db = Database::getInstance()->getConnection();
    $totalPagesCount = (int)$db->query("SELECT COUNT(*) FROM pages")->fetchColumn();

    $stmt = $db->query("
        SELECT c.category_id, c.name, c.description, COUNT(pc.page_id) AS pages_count
        FROM categories c
        LEFT JOIN page_categories pc ON c.category_id = pc.category_id
        GROUP BY c.category_id
        ORDER BY pages_count DESC, c.name ASC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats = [
        'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn()
    ];
    
    View::render('home', [
        'pages' => $pages,
        'categories' => $categories,
        'totalPagesCount' => $totalPagesCount,
        'stats' => $stats,
        'pageTitle' => ThemeLoader::get('site_name', 'Wiki Engine') . ' - Strona Główna'
    ]);
    exit;
}

// ========================================
// === ROUTING - STRONY (PAGES) ===
// ========================================

// NOWA STRONA - formularz (GET) i zapis (POST)
if ($uri === '/page/new') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    if ($_SESSION['role'] === 'viewer') {
        header('Location: /?error=forbidden');
        exit;
    }

    $db = Database::getInstance()->getConnection();

    // GET - pokaż formularz
    if ($method === 'GET') {
        $templates = $db->query("SELECT machine_key, name, content FROM templates WHERE is_active = 1 ORDER BY name")->fetchAll();
        $categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

        $page = [
            'slug'    => '',
            'title'   => '',
            'content' => '',
            'page_id' => null,
        ];

        View::render('pages/edit', [
            'page' => $page,
            'templates' => $templates,
            'categories' => $categories,
            'pageTitle' => 'Nowa Strona'
        ]);
        exit;
    }

    // POST - zapisz stronę
    if ($method === 'POST') {
        require_once __DIR__ . '/../models/Page.php';
        $pageModel = new Page();

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $categories = $_POST['categories'] ?? [];

        // Walidacja
        if (empty($title) || empty($content)) {
            $_SESSION['error'] = 'Tytuł i treść są wymagane.';
            header('Location: /page/new');
            exit;
        }

        // Generuj slug
        if (!function_exists('generateSlug')) {
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
        }

        $slug = empty($slug) ? generateSlug($title) : generateSlug($slug);

        // Sprawdź duplikaty
        if ($pageModel->findBySlug($slug)) {
            $_SESSION['error'] = 'Strona o tym URL już istnieje.';
            header('Location: /page/new');
            exit;
        }

        $author = $_SESSION['username'] ?? 'Nieznany';
        $pageId = $pageModel->create($title, $slug, $content, $author);

        if ($pageId && !empty($categories)) {
            $stmt = $db->prepare("INSERT INTO page_categories (page_id, category_id) VALUES (:page_id, :category_id)");
            foreach ($categories as $categoryId) {
                $stmt->execute(['page_id' => $pageId, 'category_id' => (int)$categoryId]);
            }
        }

        $_SESSION['success'] = 'Strona utworzona!';
        header('Location: /page/' . $slug);
        exit;
    }
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
    $categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

    View::render('pages/edit', [
        'page' => $page,
        'templates' => $templates,
        'categories' => $categories,
        'pageTitle' => 'Edycja: ' . htmlspecialchars($page['title'])
    ]);
    exit;
}

// Zapisz stronę (edycja istniejącej)
if (preg_match('#^/page/([a-z0-9-]+)/save$#', $uri, $matches) && $method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    if ($_SESSION['role'] === 'viewer') {
        http_response_code(403);
        die('403 - Brak uprawnień do edycji');
    }

    require_once __DIR__ . '/../controllers/PageController.php';
    $pageController = new PageController();
    $pageController->save($matches[1]);
    exit;
}

// Historia strony
if (preg_match('#^/page/([a-z0-9-]+)/history$#', $uri, $matches)) {
    $slug = $matches[1];
    $pageModel = new Page();
    $page = $pageModel->findBySlug($slug);
    
    if (!$page) {
        http_response_code(404);
        View::render('404', ['pageTitle' => '404 - Nie znaleziono']);
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

    View::render('pages/history', [
        'page' => $page,          
        'revisions' => $revisions,
        'pageTitle' => 'Historia: ' . htmlspecialchars($page['title'])
    ]);
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
        View::render('404', ['pageTitle' => '404 - Nie znaleziono']);
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
        View::render('404', ['pageTitle' => '404 - Nie znaleziono']);
        exit;
    }
    
    // Nadpisz dane strony danymi z rewizji
    $page['content'] = $revision['content'];
    $page['revision_comment'] = $revision['revision_comment'];
    $page['revision_date'] = $revision['created_at'];
    $page['revision_author'] = $revision['author'];
    $page['is_old_revision'] = true;
    $page['current_revision_id_display'] = $revisionId;
    
    View::render('pages/view', [
        'page' => $page,
        'pageTitle' => 'Rewizja #' . $revisionId . ': ' . htmlspecialchars($page['title'])
    ]);
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
        View::render('404', ['pageTitle' => '404 - Nie znaleziono']);
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

// Wyświetl stronę (MUSI BYĆ NA KOŃCU!)
if (preg_match('#^/page/([a-z0-9-]+)$#', $uri, $matches)) {
    require_once __DIR__ . '/../controllers/PageController.php';

    $controller = new PageController();
    $controller->show($matches[1]);
    exit;
}

// ========================================
// === ROUTING - KATEGORIE ===
// ========================================

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

    View::render('categories', [
        'categories' => $categories,
        'pageTitle' => 'Kategorie'
    ]);
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
        View::render('404', ['pageTitle' => '404 - Nie znaleziono']);
        exit;
    }

    // Pobierz strony w kategorii
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

    // Parser meta (opis + flagi + symbole)
    foreach ($pages as &$p) {
        $content = $p['content'] ?? '';

        // OPIS MODA
        $p['mod_description'] = '';
        if (preg_match('/^###\s*Opis moda\s*\R(.+?)(?:\R#{1,6}\s|\Z)/usm', $content, $m)) {
            $text = trim($m[1]);
            $text = preg_replace('/\{\{.*?\}\}/s', '', $text);
            $text = preg_replace('/\[\[(.*?)\]\]/', '$1', $text);
            $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
            $text = preg_replace('/\*(.*?)\*/', '$1', $text);
            $text = strip_tags($text);
            $text = trim($text);

            if ($text !== '' && mb_strlen($text) > 220) {
                $text = mb_substr($text, 0, 220) . '…';
            }
            $p['mod_description'] = $text;
        }

        // FLAGI
        $langs = [];
        if (preg_match_all('/\{\{\s*flag:([A-Za-z]{2})(?:\|([^}]*))?\}\}/i', $content, $m2, PREG_SET_ORDER)) {
            foreach ($m2 as $match) {
                $code  = strtoupper(trim($match[1]));
                $label = isset($match[2]) && trim($match[2]) !== '' ? trim($match[2]) : $code;

                if ($code !== '') {
                    $langs[$code] = ['code' => $code, 'label' => $label];
                }
            }
        }
        $p['languages'] = array_values($langs);

        // SYMBOLE KAMPANII
        $symbols = [];
        if (preg_match('/^\|\s*Kampania\s*\|\|\s*(.+)$/mi', $content, $rowMatch)) {
            $cell = trim($rowMatch[1]);

            if (preg_match_all('/\{\{\s*symbol:([^\}\r\n]+)\}\}/i', $cell, $m3, PREG_SET_ORDER)) {
                foreach ($m3 as $match) {
                    $name = trim($match[1]);
                    if ($name === '') continue;
                    
                    $key = strtolower($name);
                    $symbols[$key] = [
                        'name' => $name,
                        'src'  => "/symbols/{$key}.png",
                    ];
                }
            }
        }
        $p['campaign_symbols'] = array_values($symbols);
    }
    unset($p);

    View::render('category', [
        'categoryId' => $categoryId,
        'category' => $category,
        'pages' => $pages,
        'pageTitle' => htmlspecialchars($category['name'])
    ]);
    exit;
}

// ========================================
// === ROUTING - API ===
// ========================================

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

// ========================================
// === ROUTING - ANALYTICS API ===
// ========================================

// API: Wyświetlenia stron (ostatnie X dni)
if ($uri === '/api/analytics/views' && $method === 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Brak dostępu']);
        exit;
    }
    
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
    
    require_once __DIR__ . '/../models/Analytics.php';
    $analytics = new Analytics();
    $data = $analytics->getViewsLastDays($days);
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// API: Najpopularniejsze strony
if ($uri === '/api/analytics/popular' && $method === 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Brak dostępu']);
        exit;
    }
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    require_once __DIR__ . '/../models/Analytics.php';
    $analytics = new Analytics();
    $data = $analytics->getPopularPages($limit);
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// API: Aktywność użytkowników
if ($uri === '/api/analytics/users' && $method === 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Brak dostępu']);
        exit;
    }
    
    require_once __DIR__ . '/../models/Analytics.php';
    $analytics = new Analytics();
    $data = $analytics->getUserActivity();
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// API: Statystyki ogólne
if ($uri === '/api/analytics/stats' && $method === 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Brak dostępu']);
        exit;
    }
    
    require_once __DIR__ . '/../models/Analytics.php';
    $analytics = new Analytics();
    
    $data = [
        'total_pages' => $analytics->getTotalPages(),
        'total_users' => $analytics->getTotalUsers(),
        'total_views' => $analytics->getTotalViews(),
        'total_edits' => $analytics->getTotalEdits(),
    ];
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// API: Upload obrazków
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

// ========================================
// === ROUTING - MEDIA ===
// ========================================

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
    
    View::render('media', [
        'mediaFiles' => $mediaFiles,
        'pageTitle' => 'Galeria Obrazków',
        'customJS' => '/js/media.js'
    ]);
    exit;
}

// ========================================
// === ROUTING - ADMIN ===
// ========================================

// Panel Admina - Dashboard
if ($uri === '/admin') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('<h1 style="color:#ff0000;">403 - Brak dostępu</h1><p>Tylko administratorzy mają dostęp do tego panelu.</p>');
    }
    
    $db = Database::getInstance()->getConnection();
    
    $stats = [
        'pages' => $db->query("SELECT COUNT(*) FROM pages")->fetchColumn(),
        'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'revisions' => $db->query("SELECT COUNT(*) FROM revisions")->fetchColumn(),
        'media' => $db->query("SELECT COUNT(*) FROM media")->fetchColumn()
    ];
    
    View::render('admin/dashboard', [
        'stats' => $stats,
        'pageTitle' => 'Panel Admina'
    ]);
    exit;
}

// PANEL CUSTOMIZACJI
if ($uri === '/admin/customize') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: /login');
        exit;
    }
    
    require_once __DIR__ . '/../models/Settings.php';
    $settings = new Settings();
    
    View::render('admin/customize', [
        'settings' => $settings,
        'pageTitle' => 'Customizacja'
    ]);
    exit;
}

// Zapisz ustawienia customizacji
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
    
    View::render('admin/users', [
        'users' => $users,
        'pageTitle' => 'Zarządzanie Użytkownikami'
    ]);
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
    
    if ($userId === $_SESSION['user_id']) {
        header('Location: /admin/users?error=self');
        exit;
    }
    
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("DELETE FROM comments WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    
    $stmt = $db->prepare("DELETE FROM revisions WHERE author_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    
    $stmt = $db->prepare("DELETE FROM pages WHERE created_by = :user_id");
    $stmt->execute(['user_id' => $userId]);
    
    $stmt = $db->prepare("DELETE FROM users WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    
    header('Location: /admin/users?success=deleted');
    exit;
}

// Admin - Zbanuj użytkownika
if (preg_match('#^/admin/users/ban/(\d+)$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }
    
    $userId = (int)$matches[1];
    
    if ($userId === $_SESSION['user_id']) {
        header('Location: /admin/users?error=self');
        exit;
    }
    
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE users SET is_banned = 1 WHERE user_id = :id");
    $stmt->execute(['id' => $userId]);
    
    header('Location: /admin/users?success=banned');
    exit;
}

// Admin - Odbanuj użytkownika
if (preg_match('#^/admin/users/unban/(\d+)$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }
    
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

    View::render('admin/templates', [
        'templates' => $templates,
        'pageTitle' => 'Zarządzanie Szablonami'
    ]);
    exit;
}

// Admin - Zapisz szablon
if ($uri === '/admin/templates/save' && $method === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }

    $templateId = isset($_POST['template_id']) ? (int)$_POST['template_id'] : null;
    $name = trim($_POST['name'] ?? '');
    $machineKey = trim($_POST['slug'] ?? '');
    $content = $_POST['content'] ?? '';

    if ($name === '' || $machineKey === '') {
        header('Location: /admin/templates?error=' . urlencode('Nazwa i klucz są wymagane'));
        exit;
    }

    $db = Database::getInstance()->getConnection();
    
    if ($templateId) {
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

// Admin - Podgląd szablonu
if (preg_match('#^/admin/templates/preview/(\d+)$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit('403 - Brak dostępu');
    }

    $templateId = (int)$matches[1];

    require_once __DIR__ . '/../models/Templates.php';
    $templateModel = new Templates();
    $template = $templateModel->findById($templateId);

    if (!$template) {
        http_response_code(404);
        View::render('404', ['pageTitle' => '404 - Nie znaleziono']);
        exit;
    }

    View::render('admin/template-preview', [
        'template' => $template,
        'pageTitle' => 'Podgląd: ' . htmlspecialchars($template['name'])
    ]);
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
    
    View::render('admin/categories', [
        'categories' => $categories,
        'pageTitle' => 'Zarządzanie Kategoriami'
    ]);
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

// Admin - External Links (lista)
if ($uri === '/admin/links') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('403 - Brak dostępu');
    }

    require_once __DIR__ . '/../models/ExternalLink.php';
    $linkModel = new ExternalLink();
    $links = $linkModel->getAll();

    View::render('admin/links', [
        'links' => $links,
        'pageTitle' => 'Zarządzanie Linkami'
    ]);
    exit;
}

// Admin - Dodaj External Link
if ($uri === '/admin/links/add' && $method === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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

    $userId = (int)$_SESSION['user_id'];

    require_once __DIR__ . '/../models/ExternalLink.php';
    $linkModel = new ExternalLink();
    $linkModel->create($title, $url, $description, $source, $icon, $userId);

    header('Location: /admin/links?success=added');
    exit;
}

// Admin - Usuń External Link
if (preg_match('#^/admin/links/delete/(\d+)$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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

// Admin - Przesuń External Link
if (preg_match('#^/admin/links/move/(up|down)/(\d+)$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }

    $direction = $matches[1];
    $linkId = (int)$matches[2];

    require_once __DIR__ . '/../models/ExternalLink.php';
    $linkModel = new ExternalLink();
    $linkModel->move($linkId, $direction);

    header('Location: /admin/links');
    exit;
}

// ========================================
// === ROUTING - KOMENTARZE ===
// ========================================

// Dodaj komentarz
if ($uri === '/comment/add' && $method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Musisz być zalogowany']);
        exit;
    }
    
    $pageId = (int)($_POST['page_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    
    if (empty($content)) {
        http_response_code(400);
        echo json_encode(['error' => 'Treść komentarza nie może być pusta']);
        exit;
    }
    
    require_once __DIR__ . '/../models/Comment.php';
    $commentModel = new Comment();
    
    if ($commentModel->create($pageId, $_SESSION['user_id'], $content, $parentId)) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Błąd dodawania komentarza']);
    }
    exit;
}

// Usuń komentarz
if (preg_match('#^/comment/(\d+)/delete$#', $uri, $matches)) {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Brak autoryzacji']);
        exit;
    }
    
    $commentId = (int)$matches[1];
    
    require_once __DIR__ . '/../models/Comment.php';
    $commentModel = new Comment();
    
    if ($commentModel->delete($commentId, $_SESSION['user_id'])) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Błąd usuwania komentarza']);
    }
    exit;
}

// ========================================
// === ROUTING - ANALYTICS ===
// ========================================

if ($uri === '/analytics') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: /login');
        exit;
    }
    
    require_once __DIR__ . '/../models/Analytics.php';
    $analytics = new Analytics();
    
    View::render('analytics/dashboard', [
        'analytics' => $analytics,
        'pageTitle' => 'Analytics Dashboard'
    ]);
    exit;
}

// ========================================
// === ROUTING - INNE ===
// ========================================

// Pomoc składni
if ($uri === '/syntax-help') {
    View::render('syntax-help', [
        'pageTitle' => 'Pomoc - Składnia Wiki'
    ]);
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
        View::render('404', ['pageTitle' => '404 - Nie znaleziono']);
        exit;
    }

    View::render('user/profile', [
        'profileUser' => $profileUser,
        'pageTitle' => 'Profil: ' . htmlspecialchars($profileUser['username'])
    ]);
    exit;
}

// Diagnostic
if ($uri === '/diagnostic') {
    View::render('diagnostic', [
        'pageTitle' => 'Diagnostyka Systemu'
    ]);
    exit;
}

// ========================================
// === 404 - STRONA NIE ZNALEZIONA ===
// ========================================

http_response_code(404);
View::render('404', ['pageTitle' => '404 - Nie znaleziono']);
exit;
