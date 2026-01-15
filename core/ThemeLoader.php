<?php
require_once __DIR__ . '/../models/Settings.php';

class ThemeLoader {
    private static $settings;
    
    public static function loadSettings() {
        if (self::$settings === null) {
            $settingsModel = new Settings();
            self::$settings = [];
            
            $allSettings = $settingsModel->getAll();
            foreach ($allSettings as $setting) {
                self::$settings[$setting['setting_key']] = $setting['setting_value'];
            }
        }
        
        return self::$settings;
    }
    
    public static function get(string $key, $default = null) {
        $settings = self::loadSettings();
        return $settings[$key] ?? $default;
    }
    
public static function generateCSS(): string {
    // TYMCZASOWO WYŁĄCZONE
    return '';
}

}
