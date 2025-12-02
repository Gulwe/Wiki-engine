<?php
class Comment {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Pobierz wszystkie komentarze dla strony
    public function getByPageId(int $pageId): array {
        $stmt = $this->db->prepare("
            SELECT c.*, u.username, u.email
            FROM comments c
            JOIN users u ON c.user_id = u.user_id
            WHERE c.page_id = :page_id 
            AND c.is_deleted = 0
            ORDER BY c.created_at ASC
        ");
        
        $stmt->execute(['page_id' => $pageId]);
        return $stmt->fetchAll();
    }
    
    // Dodaj komentarz
    public function create(int $pageId, int $userId, string $content, ?int $parentId = null): bool {
        $stmt = $this->db->prepare("
            INSERT INTO comments (page_id, user_id, content, parent_id)
            VALUES (:page_id, :user_id, :content, :parent_id)
        ");
        
        return $stmt->execute([
            'page_id' => $pageId,
            'user_id' => $userId,
            'content' => $content,
            'parent_id' => $parentId
        ]);
    }
    
    // Edytuj komentarz
    public function update(int $commentId, int $userId, string $content): bool {
        $stmt = $this->db->prepare("
            UPDATE comments 
            SET content = :content, updated_at = NOW()
            WHERE comment_id = :comment_id 
            AND user_id = :user_id
        ");
        
        return $stmt->execute([
            'comment_id' => $commentId,
            'user_id' => $userId,
            'content' => $content
        ]);
    }
    
    // Usuń komentarz (soft delete)
    public function delete(int $commentId, int $userId): bool {
        $stmt = $this->db->prepare("
            UPDATE comments 
            SET is_deleted = 1 
            WHERE comment_id = :comment_id 
            AND user_id = :user_id
        ");
        
        return $stmt->execute([
            'comment_id' => $commentId,
            'user_id' => $userId
        ]);
    }
    
    // Zlicz komentarze dla strony
    public function countByPageId(int $pageId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM comments 
            WHERE page_id = :page_id 
            AND is_deleted = 0
        ");
        
        $stmt->execute(['page_id' => $pageId]);
        $result = $stmt->fetch();
        
        return (int)$result['count'];
    }
}
