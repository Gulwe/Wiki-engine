<?php
class Page {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username as author,
                   r.content, r.created_at as last_modified
            FROM pages p
            LEFT JOIN users u ON p.created_by = u.user_id
            LEFT JOIN revisions r ON p.current_revision_id = r.revision_id
            WHERE p.slug = :slug
        ");
        
        $stmt->execute(['slug' => $slug]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
    
    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT p.slug, p.title, p.updated_at, u.username as author
            FROM pages p
            LEFT JOIN users u ON p.created_by = u.user_id
            ORDER BY p.updated_at DESC
            LIMIT 50
        ");
        
        return $stmt->fetchAll();
    }
    
public function create($title, $slug, $content, $author)
{
    $db = Database::getInstance()->getConnection();
    
    try {
        $db->beginTransaction();
        
        // 1. Utwórz stronę (bez contentu)
        $stmt = $db->prepare("
            INSERT INTO pages (title, slug, created_by, created_at, updated_at)
            VALUES (:title, :slug, :author, NOW(), NOW())
        ");
        
        $stmt->execute([
            'title' => $title,
            'slug' => $slug,
            'author' => $_SESSION['user_id'] ?? null
        ]);
        
        $pageId = $db->lastInsertId();
        
        // 2. Utwórz pierwszą rewizję z contentem
        $stmt = $db->prepare("
            INSERT INTO revisions (page_id, content, author_id, revision_comment, created_at)
            VALUES (:page_id, :content, :author_id, :comment, NOW())
        ");
        
        $stmt->execute([
            'page_id' => $pageId,
            'content' => $content,
            'author_id' => $_SESSION['user_id'] ?? null,
            'comment' => 'Utworzono stronę'
        ]);
        
        $revisionId = $db->lastInsertId();
        
        // 3. Zaktualizuj pages.current_revision_id
        $stmt = $db->prepare("
            UPDATE pages 
            SET current_revision_id = :revision_id 
            WHERE page_id = :page_id
        ");
        
        $stmt->execute([
            'revision_id' => $revisionId,
            'page_id' => $pageId
        ]);
        
        $db->commit();
        
        return $pageId;
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Error creating page: " . $e->getMessage());
        return false;
    }
}


    
    // Pobierz N ostatnich stron
public function getRecent($limit = 5) {
    $stmt = $this->db->prepare("
        SELECT p.*, u.username as author
        FROM pages p
        LEFT JOIN users u ON p.created_by = u.user_id
        ORDER BY p.updated_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

public function getRecentlyUpdated($limit = 5) {
    $stmt = $this->db->prepare("
        SELECT 
            p.slug,
            p.title,
            u.username AS author,
            r.created_at AS last_modified
        FROM pages p
        LEFT JOIN users u ON p.created_by = u.user_id
        LEFT JOIN revisions r ON p.current_revision_id = r.revision_id
        WHERE p.current_revision_id IS NOT NULL
        ORDER BY r.created_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




    
}
