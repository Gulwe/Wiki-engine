<?php
require_once __DIR__ . '/../core/Database.php';

class Settings {
    private $db;
    private static $cache = [];
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM site_settings ORDER BY category, setting_key");
        return $stmt->fetchAll();
    }
    
    public function getByCategory(string $category): array {
        $stmt = $this->db->prepare("SELECT * FROM site_settings WHERE category = :category");
        $stmt->execute(['category' => $category]);
        return $stmt->fetchAll();
    }
    
    public function get(string $key, $default = null) {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        
        $stmt = $this->db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = :key");
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch();
        
        $value = $result ? $result['setting_value'] : $default;
        self::$cache[$key] = $value;
        
        return $value;
    }
    
    // MYSQL - ON DUPLICATE KEY UPDATE
    public function set(string $key, $value): bool {
        $stmt = $this->db->prepare("
            INSERT INTO site_settings (setting_key, setting_value)
            VALUES (:key, :value)
            ON DUPLICATE KEY UPDATE setting_value = :value2
        ");
        
        $result = $stmt->execute([
            'key' => $key,
            'value' => $value,
            'value2' => $value
        ]);
        
        unset(self::$cache[$key]);
        return $result;
    }
    
    public function setMultiple(array $settings): bool {
        $this->db->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $this->set($key, $value);
            }
            
            $this->db->commit();
            self::$cache = [];
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
