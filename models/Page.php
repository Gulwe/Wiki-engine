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
    
    public function create(string $slug, string $title, int $userId): int {
        $stmt = $this->db->prepare("
            INSERT INTO pages (slug, title, created_by)
            VALUES (:slug, :title, :created_by)
        ");
        
        $stmt->execute([
            'slug' => $slug,
            'title' => $title,
            'created_by' => $userId
        ]);
        
        return (int)$this->db->lastInsertId();
    }
}
