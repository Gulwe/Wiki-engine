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
            SELECT user_id, username, password_hash, email, role
            FROM users
            WHERE username = :username
            LIMIT 1
        ");
        
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            header('Location: /login?error=invalid');
            exit;
        }
        
        // Zaloguj użytkownika
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        header('Location: /');
        exit;
    }
}
