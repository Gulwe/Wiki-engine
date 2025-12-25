<?php
class Middleware {
    /**
     * Wymaga zalogowania
     */
    public static function requireAuth(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }
    
    /**
     * Wymaga roli admin
     */
    public static function requireAdmin(): void {
        self::requireAuth();
        
        if ($_SESSION['role'] !== 'admin') {
            View::render('403', ['pageTitle' => 'Brak dostępu']);
            exit;
        }
    }
    
    /**
     * Wymaga roli editor lub admin
     */
    public static function requireEditor(): void {
        self::requireAuth();
        
        if (!in_array($_SESSION['role'], ['editor', 'admin'])) {
            View::render('403', ['pageTitle' => 'Brak dostępu']);
            exit;
        }
    }
    
    /**
     * Wymaga konkretnej roli lub wyższej
     * Hierarchia: viewer < editor < admin
     */
    public static function requireRole(string $minRole): void {
        self::requireAuth();
        
        $roles = ['viewer' => 1, 'editor' => 2, 'admin' => 3];
        $userRole = $_SESSION['role'] ?? 'viewer';
        
        if ($roles[$userRole] < $roles[$minRole]) {
            View::render('403', ['pageTitle' => 'Brak dostępu']);
            exit;
        }
    }
    
    /**
     * Blokuje dostęp dla zalogowanych (np. /login, /register)
     */
    public static function requireGuest(): void {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
    }
}
