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
        
        // Kolory z panelu admina – używane jako wartości zmiennych,
        // ale nie wstrzykiwane „na sztywno” w selektory typu a:hover
        $primaryColor   = $settings['primary_color']   ?? '#8b5cf6';
        $secondaryColor = $settings['secondary_color'] ?? '#2563eb';
        $backgroundColor= $settings['background_color']?? '#0a0014';
        $headerColor    = $settings['header_color']    ?? '#140028';
        
        $css = "
<style id='dynamic-theme'>
:root {
    /* Bridge między ustawieniami w panelu a systemem motywów */
    /* --accent-primary:  {$primaryColor};
    --accent-secondary:{$secondaryColor};
    --bg-primary:      {$backgroundColor};
    --header-bg:       {$headerColor}; */
}

/* Tło strony – reszta gradientu może być nadal z motywu */
body {
    background: linear-gradient(135deg, var(--bg-primary), var(--bg-secondary, #1a0033));
}

/* Nagłówek */
.modern-header {
    background: var(--header-bg);
}

/* Przyciski główne opieramy na zmiennych akcentu */
.btn-primary,
.fab-button,
.wiki-button-primary {
    background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
}

/* NIE tykamy a:hover / .nav-link:hover – robią to motywy */

/* Karty / wiki-card – tylko border korzysta z akcentu */
.stat-card:hover,
.wiki-card:hover {
    border-color: var(--accent-primary);
}

/* Pasek postępu */
.progress-fill {
    background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
}

/* Badge primary */
.badge-primary {
    background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
}

/* Oś czasu */
.timeline::before {
    background: linear-gradient(180deg, var(--accent-primary), var(--accent-secondary));
}

.timeline-marker {
    background: var(--accent-primary);
}

/* Focus pól formularza */
input:focus,
textarea:focus,
select:focus {
    border-color: var(--accent-primary);
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
}

/* Delikatny cień przy hoverze, bez zmiany koloru */
.wiki-tag:hover,
.btn:hover {
    box-shadow: 0 4px 12px color-mix(in srgb, var(--accent-primary) 27%, transparent);
}
</style>
";
        
        // Custom CSS z panelu
        if (!empty($settings['custom_css'])) {
            $css .= "<style id='custom-css'>\n{$settings['custom_css']}\n</style>";
        }
        
        return $css;
    }
}
