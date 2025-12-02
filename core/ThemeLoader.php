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
        $settings = self::loadSettings();
        
        $primaryColor = $settings['primary_color'] ?? '#8b5cf6';
        $secondaryColor = $settings['secondary_color'] ?? '#2563eb';
        $backgroundColor = $settings['background_color'] ?? '#0a0014';
        $headerColor = $settings['header_color'] ?? '#140028';
        
        $css = "
        <style id='dynamic-theme'>
        :root {
            --primary-color: {$primaryColor};
            --secondary-color: {$secondaryColor};
            --background-color: {$backgroundColor};
            --header-color: {$headerColor};
        }
        
body {
    background: linear-gradient(135deg, {$backgroundColor} 0%, #1a0033 100%);
}

.modern-header {
    background: {$headerColor};
}

        
        .btn-primary,
        .fab-button,
        .wiki-button-primary {
            background: linear-gradient(135deg, {$primaryColor}, {$secondaryColor});
        }
        
        a:hover,
        .nav-link:hover {
            color: {$primaryColor};
        }
        
        .stat-card:hover,
        .wiki-card:hover {
            border-color: {$primaryColor};
        }
        
        .progress-fill {
            background: linear-gradient(90deg, {$primaryColor}, {$secondaryColor});
        }
        
        .badge-primary {
            background: linear-gradient(135deg, {$primaryColor}, {$secondaryColor});
        }
        
        .timeline::before {
            background: linear-gradient(180deg, {$primaryColor}, {$secondaryColor});
        }
        
        .timeline-marker {
            background: {$primaryColor};
        }
        
        input:focus,
        textarea:focus,
        select:focus {
            border-color: {$primaryColor};
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }
        
        .wiki-tag:hover,
        .btn:hover {
            box-shadow: 0 4px 12px {$primaryColor}44;
        }
        
        </style>
        ";
        
        // Dodaj custom CSS jeśli istnieje
        if (!empty($settings['custom_css'])) {
            $css .= "<style id='custom-css'>\n{$settings['custom_css']}\n</style>";
        }
        
        return $css;
    }
}
