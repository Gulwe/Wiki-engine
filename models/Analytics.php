<?php
class Analytics {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Zapisz wyświetlenie strony
    public function trackPageView(int $pageId, ?int $userId = null): void {
        $stmt = $this->db->prepare("
            INSERT INTO page_views (page_id, user_id, ip_address, user_agent)
            VALUES (:page_id, :user_id, :ip_address, :user_agent)
        ");
        
        $stmt->execute([
            'page_id' => $pageId,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        // Zwiększ licznik
        $stmt = $this->db->prepare("UPDATE pages SET views = views + 1 WHERE page_id = :page_id");
        $stmt->execute(['page_id' => $pageId]);
    }
    
    // Ogólne statystyki
    public function getGeneralStats(): array {
        $stats = [];
        
        // Łączna liczba stron
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM pages");
        $stats['total_pages'] = $stmt->fetch()['count'];
        
        // Łączna liczba komentarzy
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM comments WHERE is_deleted = 0");
        $stats['total_comments'] = $stmt->fetch()['count'];
        
        // Łączna liczba użytkowników
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch()['count'];
        
        // Łączna liczba wyświetleń
        $stmt = $this->db->query("SELECT SUM(views) as total FROM pages");
        $stats['total_views'] = $stmt->fetch()['total'] ?? 0;
        
        // Wyświetlenia dzisiaj
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM page_views 
            WHERE DATE(viewed_at) = CURDATE()
        ");
        $stats['views_today'] = $stmt->fetch()['count'];
        
        // Wyświetlenia w tym tygodniu
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM page_views 
            WHERE YEARWEEK(viewed_at) = YEARWEEK(NOW())
        ");
        $stats['views_week'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    // Najpopularniejsze strony
    public function getTopPages(int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT p.page_id, p.title, p.slug, p.views, p.created_at,
                   u.username as author
            FROM pages p
            LEFT JOIN users u ON p.created_by = u.user_id
            ORDER BY p.views DESC
            LIMIT :limit
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Wyświetlenia w ostatnich dniach (dla wykresów)
    public function getViewsLastDays(int $days = 30): array {
        $stmt = $this->db->prepare("
            SELECT DATE(viewed_at) as date, COUNT(*) as views
            FROM page_views
            WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
            GROUP BY DATE(viewed_at)
            ORDER BY date ASC
        ");
        
        $stmt->execute(['days' => $days]);
        return $stmt->fetchAll();
    }
    
    // Najaktywniejsze kategorie
    public function getTopCategories(int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT c.category_id, c.name, 
                   COUNT(DISTINCT pc.page_id) as page_count,
                   SUM(p.views) as total_views
            FROM categories c
            LEFT JOIN page_categories pc ON c.category_id = pc.category_id
            LEFT JOIN pages p ON pc.page_id = p.page_id
            GROUP BY c.category_id
            ORDER BY total_views DESC
            LIMIT :limit
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Najaktywniejsze użytkownicy (komentarze)
    public function getTopCommenters(int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT u.user_id, u.username, COUNT(*) as comment_count
            FROM users u
            JOIN comments c ON u.user_id = c.user_id
            WHERE c.is_deleted = 0
            GROUP BY u.user_id
            ORDER BY comment_count DESC
            LIMIT :limit
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Ostatnia aktywność
    public function getRecentActivity(int $limit = 20): array {
        $stmt = $this->db->prepare("
            SELECT 'page_view' as type, 
                   pv.viewed_at as timestamp,
                   p.title as page_title,
                   p.slug as page_slug,
                   u.username
            FROM page_views pv
            JOIN pages p ON pv.page_id = p.page_id
            LEFT JOIN users u ON pv.user_id = u.user_id
            
            UNION ALL
            
            SELECT 'comment' as type,
                   c.created_at as timestamp,
                   p.title as page_title,
                   p.slug as page_slug,
                   u.username
            FROM comments c
            JOIN pages p ON c.page_id = p.page_id
            JOIN users u ON c.user_id = u.user_id
            WHERE c.is_deleted = 0
            
            ORDER BY timestamp DESC
            LIMIT :limit
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
