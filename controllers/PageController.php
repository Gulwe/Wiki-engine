<?php
class PageController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
public function save(string $slug): void {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $comment = $_POST['comment'] ?? '';
    $categories = $_POST['categories'] ?? [];
    $userId = $_SESSION['user_id'];
    
    if (empty($title) || empty($content)) {
        header('Location: /page/' . $slug . '/edit?error=empty');
        exit;
    }
    
    // Sprawdź czy strona istnieje
    $pageModel = new Page();
    $existingPage = $pageModel->findBySlug($slug);
    
    if ($existingPage) {
        // Aktualizuj istniejącą stronę
        $pageId = $existingPage['page_id'];
        
        // Zaktualizuj tytuł
        $stmt = $this->db->prepare("UPDATE pages SET title = :title WHERE page_id = :page_id");
        $stmt->execute(['title' => $title, 'page_id' => $pageId]);
        
    } else {
        // Utwórz nową stronę
        $pageId = $pageModel->create($slug, $title, $userId);
    }
    
    // Dodaj nową rewizję
    $stmt = $this->db->prepare("
        INSERT INTO revisions (page_id, content, author_id, revision_comment)
        VALUES (:page_id, :content, :author_id, :comment)
    ");
    
    $stmt->execute([
        'page_id' => $pageId,
        'content' => $content,
        'author_id' => $userId,
        'comment' => $comment
    ]);
    
    $revisionId = $this->db->lastInsertId();
    
    // Ustaw jako aktualną rewizję
    $stmt = $this->db->prepare("
        UPDATE pages SET current_revision_id = :revision_id WHERE page_id = :page_id
    ");
    
    $stmt->execute([
        'revision_id' => $revisionId,
        'page_id' => $pageId
    ]);
    
    // Zaktualizuj kategorie
    $this->updateCategories($pageId, $categories);
    
    header('Location: /page/' . $slug);
}
public function create(): void
{
    Auth::requireLogin();
    Auth::requireRole(['admin', 'editor']);

    require_once __DIR__ . '/../models/Template.php';

    $templateModel = new Template();
    $templates = $templateModel->getAll();

    $page = [
        'title' => '',
        'slug' => '',
        'content' => '',
        'page_id' => null
    ];

    View::render('pages/edit', [
        'page' => $page,
        'templates' => $templates
    ]);
}


public function store()
{
    Auth::requireLogin();
    Auth::requireRole(['admin', 'editor']);

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    $categories = $_POST['categories'] ?? [];

    // Walidacja
    if (empty($title) || empty($content)) {
        $_SESSION['error'] = 'Tytuł i treść są wymagane.';
        header('Location: /pages/new');
        exit;
    }

    // Jeśli slug jest pusty, wygeneruj z tytułu
    if (empty($slug)) {
        $slug = $this->generateSlug($title);
    } else {
        // Waliduj i sanitizuj slug
        $slug = $this->sanitizeSlug($slug);
    }

    // Sprawdź czy slug już istnieje
    if ($this->pageModel->getBySlug($slug)) {
        $_SESSION['error'] = 'Strona o tym URL już istnieje. Wybierz inny slug.';
        header('Location: /pages/new');
        exit;
    }

    $author = $_SESSION['username'] ?? 'Nieznany';

    // Utwórz stronę
    $pageId = $this->pageModel->create($title, $slug, $content, $author);

    if ($pageId) {
        // Przypisz kategorie
        if (!empty($categories)) {
            $this->assignCategories($pageId, $categories);
        }

        // Zapisz w historii
        $this->saveHistory($pageId, $content, $comment);

        $_SESSION['success'] = 'Strona została utworzona!';
        header('Location: /pages/' . $slug);
    } else {
        $_SESSION['error'] = 'Błąd podczas tworzenia strony.';
        header('Location: /pages/new');
    }
    exit;
}

private function generateSlug($title)
{
    // Zamiana polskich znaków
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

    // Jeśli slug jest pusty, użyj timestampu
    if (empty($slug)) {
        $slug = 'page-' . time();
    }

    return $slug;
}

private function sanitizeSlug($slug)
{
    // Zamiana polskich znaków
    $polishChars = [
        'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n',
        'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z',
        'Ą' => 'a', 'Ć' => 'c', 'Ę' => 'e', 'Ł' => 'l', 'Ń' => 'n',
        'Ó' => 'o', 'Ś' => 's', 'Ź' => 'z', 'Ż' => 'z'
    ];

    $slug = strtr($slug, $polishChars);
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');

    // Jeśli slug jest pusty po sanitizacji
    if (empty($slug)) {
        $slug = 'page-' . time();
    }

    return $slug;
}

private function assignCategories($pageId, $categories)
{
    $db = Database::getInstance()->getConnection();
    
    // Usuń stare przypisania
    $stmt = $db->prepare("DELETE FROM page_categories WHERE page_id = :page_id");
    $stmt->execute(['page_id' => $pageId]);
    
    // Dodaj nowe
    $stmt = $db->prepare("INSERT INTO page_categories (page_id, category_id) VALUES (:page_id, :category_id)");
    
    foreach ($categories as $categoryId) {
        $stmt->execute([
            'page_id' => $pageId,
            'category_id' => $categoryId
        ]);
    }
}

private function saveHistory($pageId, $content, $comment)
{
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        INSERT INTO page_history (page_id, content, edited_by, comment, edited_at)
        VALUES (:page_id, :content, :edited_by, :comment, NOW())
    ");
    
    $stmt->execute([
        'page_id' => $pageId,
        'content' => $content,
        'edited_by' => $_SESSION['username'] ?? 'Nieznany',
        'comment' => $comment
    ]);
}


private function updateCategories(int $pageId, array $categories): void {
    // Usuń stare przypisania
    $stmt = $this->db->prepare("DELETE FROM page_categories WHERE page_id = :page_id");
    $stmt->execute(['page_id' => $pageId]);
    
    // Dodaj nowe
    if (!empty($categories)) {
        $stmt = $this->db->prepare("
            INSERT INTO page_categories (page_id, category_id)
            VALUES (:page_id, :category_id)
        ");
        
        foreach ($categories as $categoryId) {
            $stmt->execute([
                'page_id' => $pageId,
                'category_id' => (int)$categoryId
            ]);
        }
    }
}

public function show(string $slug): void
{
    require_once __DIR__ . '/../core/View.php';
    require_once __DIR__ . '/../models/Page.php';
    require_once __DIR__ . '/../models/Analytics.php';

    $pageModel = new Page();
    $page = $pageModel->findBySlug($slug);

    if (!$page) {
        http_response_code(404);
        View::render('404', [], null);
        return;
    }

    // Analytics
    $analytics = new Analytics();
    $analytics->trackPageView(
        $page['page_id'],
        $_SESSION['user_id'] ?? null
    );

    // jeśli licznik jest w DB
    $page = $pageModel->findBySlug($slug);

    View::render('pages/view', [
        'page' => $page
    ]);
}


}
