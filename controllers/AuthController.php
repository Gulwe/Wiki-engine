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
    
    public function register(): array {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Walidacja
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        return ['success' => false, 'error' => 'Wypełnij wszystkie pola'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Nieprawidłowy adres email'];
    }
    
    if (strlen($username) < 3) {
        return ['success' => false, 'error' => 'Nazwa użytkownika musi mieć minimum 3 znaki'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'error' => 'Hasło musi mieć minimum 6 znaków'];
    }
    
    if ($password !== $confirmPassword) {
        return ['success' => false, 'error' => 'Hasła nie są identyczne'];
    }
    
    // Sprawdź czy username już istnieje
    $stmt = $this->db->prepare("SELECT user_id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Nazwa użytkownika jest już zajęta'];
    }
    
    // Sprawdź czy email już istnieje
    $stmt = $this->db->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Email jest już zarejestrowany'];
    }
    
    // Utwórz użytkownika
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $this->db->prepare("
        INSERT INTO users (username, email, password_hash, role, created_at)
        VALUES (:username, :email, :password, 'viewer', NOW())
    ");
    
    try {
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword
        ]);
        
        return ['success' => true, 'message' => 'Konto zostało utworzone! Możesz się teraz zalogować.'];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Błąd podczas tworzenia konta'];
    }
}

    public function changePassword(): array {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Walidacja
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return ['success' => false, 'error' => 'Wypełnij wszystkie pola'];
        }
        
        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'error' => 'Nowe hasła nie są identyczne'];
        }
        
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'error' => 'Nowe hasło musi mieć minimum 6 znaków'];
        }
        
        if ($currentPassword === $newPassword) {
            return ['success' => false, 'error' => 'Nowe hasło musi być inne niż obecne'];
        }
        
        // Pobierz obecne hasło z bazy
        $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE user_id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'error' => 'Użytkownik nie istnieje'];
        }
        
        // Sprawdź obecne hasło
        if (!password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Obecne hasło jest nieprawidłowe'];
        }
        
        // Zaktualizuj hasło
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $stmt = $this->db->prepare("UPDATE users SET password_hash = :password WHERE user_id = :id");
        $stmt->execute([
            'password' => $hashedPassword,
            'id' => $_SESSION['user_id']
        ]);
        
        return ['success' => true, 'message' => 'Hasło zostało zmienione'];
    }
}
