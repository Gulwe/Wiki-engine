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

}
