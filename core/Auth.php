// core/Auth.php
<?php
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        
        // Konfiguracja sesji dla bezpieczeñstwa
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 1); // Wymaga HTTPS
        ini_set('session.cookie_samesite', 'Strict');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function register(string $username, string $email, string $password): bool {
        // Walidacja si³y has³a
        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters');
        }
        
        // Hash has³a (bcrypt z PHP 7.4)
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password_hash, role)
                VALUES (:username, :email, :password_hash, 'viewer')
            ");
            
            return $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                throw new Exception('Username or email already exists');
            }
            throw $e;
        }
    }
    
    public function login(string $username, string $password): bool {
        $stmt = $this->db->prepare("
            SELECT user_id, username, password_hash, email, role
            FROM users
            WHERE username = :username
            LIMIT 1
        ");
        
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        // Weryfikacja has³a
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }
        
        // Regeneruj session ID aby zapobiec session fixation
        session_regenerate_id(true);
        
        // Ustaw zmienne sesyjne
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    public function logout(): void {
        // Wyczyœæ sesjê
        $_SESSION = [];
        
        // Zniszcz cookie sesji
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Zniszcz sesjê
        session_destroy();
    }
    
    public function isLoggedIn(): bool {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }
        
        // Timeout sesji po 30 minutach nieaktywnoœci
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            $this->logout();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    public function hasRole(string $role): bool {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    public function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }
    
    public function requireRole(string $role): void {
        $this->requireLogin();
        
        if (!$this->hasRole($role)) {
            http_response_code(403);
            die('Access denied');
        }
    }
}
