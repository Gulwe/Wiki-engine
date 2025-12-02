<?php
// D:\xampp\htdocs\test-password.php
echo "<pre style='color:#00ff00;background:#1a1a1a;padding:20px;font-family:monospace;'>";

echo "=== TEST PHP PASSWORD FUNCTIONS ===\n\n";

// Sprawdź wersję PHP
echo "PHP Version: " . phpversion() . "\n\n";

// Sprawdź czy funkcje istnieją
echo "password_hash exists: " . (function_exists('password_hash') ? 'YES ✓' : 'NO ✗') . "\n";
echo "password_verify exists: " . (function_exists('password_verify') ? 'YES ✓' : 'NO ✗') . "\n\n";

// Sprawdź czy PASSWORD_BCRYPT jest zdefiniowane
echo "PASSWORD_BCRYPT defined: " . (defined('PASSWORD_BCRYPT') ? 'YES ✓' : 'NO ✗') . "\n";
echo "PASSWORD_DEFAULT defined: " . (defined('PASSWORD_DEFAULT') ? 'YES ✓' : 'NO ✗') . "\n\n";

// Test hashowania i weryfikacji
if (function_exists('password_hash') && function_exists('password_verify')) {
    echo "--- TESTING HASH & VERIFY ---\n";
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    echo "Test Password: $password\n";
    echo "Generated Hash: $hash\n";
    echo "Hash Length: " . strlen($hash) . "\n";
    echo "Verification: " . (password_verify($password, $hash) ? 'SUCCESS ✓' : 'FAIL ✗') . "\n\n";
    
    // Test z istniejącym hashem
    $existingHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    echo "--- TESTING EXISTING HASH ---\n";
    echo "Hash: $existingHash\n";
    echo "Password 'admin123' verify: " . (password_verify('admin123', $existingHash) ? 'SUCCESS ✓' : 'FAIL ✗') . "\n\n";
    
    echo "SQL DO WYKONANIA:\n";
    echo "DELETE FROM users WHERE username = 'admin';\n";
    echo "INSERT INTO users (username, email, password_hash, role)\n";
    echo "VALUES ('admin', 'admin@wiki.local', '$hash', 'admin');\n";
} else {
    echo "❌ ERROR: Password functions are NOT available!\n";
    echo "This is very strange for PHP 7.4.4\n\n";
    
    echo "Loaded extensions:\n";
    print_r(get_loaded_extensions());
}

echo "</pre>";
?>
