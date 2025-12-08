<?php
class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function login(): void {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            header('Location: /login?error=empty');
            exit;
        }
        
        $stmt = $this->db->prepare("
            SELECT user_id, username, password_hash, email, role, is_banned
            FROM users
            WHERE username = :username
            LIMIT 1
        ");
        
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            header('Location: /login?error=invalid');
            exit;
        }

        // Zbanowany – nie logujemy
        if (!empty($user['is_banned'])) {
            header('Location: /login?error=banned');
            exit;
        }
        
        // Zaloguj użytkownika
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['role']      = $user['role'];
        
        header('Location: /');
        exit;
    }
}
