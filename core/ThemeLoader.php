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
    // TYMCZASOWO WYŁĄCZONE - powrócimy do tego później
    return '';
    
    /* STARY KOD - zakomentowany
    $settings = self::loadSettings();
    
    $primaryColor   = $settings['primary_color']   ?? '#8b5cf6';
    $secondaryColor = $settings['secondary_color'] ?? '#2563eb';
    $backgroundColor= $settings['background_color']?? '#0a0014';
    $headerColor    = $settings['header_color']    ?? '#140028';
    
    $css = "
<style id='dynamic-theme'>
...
</style>
";
    
    if (!empty($settings['custom_css'])) {
        $css .= "<style id='custom-css'>\n{$settings['custom_css']}\n</style>";
    }
    
    return $css;
    */
}

}
