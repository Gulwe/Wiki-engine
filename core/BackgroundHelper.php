<?php

class BackgroundHelper
{
    /**
     * Pobierz losowe tło dla danego motywu
     * 
     * @param string $themeName Nazwa motywu (np. 'default', 'am', 'ru', 'zsi')
     * @return string URL do obrazka tła
     */
    public static function getThemeBackground($themeName = 'default')
    {
        // Mapa: motyw → folder z tłami
        $themeBackgrounds = [
            'default' => 'default',
            'am'      => 'am',
            'ru'      => 'ru',
            'zsi'     => 'zsi',
        ];

        // Sprawdź czy motyw ma przypisany folder
        $folder = isset($themeBackgrounds[$themeName]) 
            ? $themeBackgrounds[$themeName] 
            : 'default';

        // Ścieżka do folderu z tłami dla motywu
        $backgroundDir = __DIR__ . '/../public/backgrounds/' . $folder . '/';

        // Debug
        error_log("BackgroundHelper: Looking for theme '{$themeName}' in folder '{$folder}'");
        error_log("BackgroundHelper: Directory path: " . $backgroundDir);

        // Jeśli folder nie istnieje, użyj default
        if (!is_dir($backgroundDir)) {
            error_log("BackgroundHelper: Folder '{$backgroundDir}' does not exist, falling back to default");
            $backgroundDir = __DIR__ . '/../public/backgrounds/default/';
            $folder = 'default';
        }

        // Pobierz listę obrazków
        $backgroundImages = [];
        if (is_dir($backgroundDir)) {
            $files = scandir($backgroundDir);
            foreach ($files as $file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $backgroundImages[] = $file;
                }
            }
        }

        // Debug - pokaż znalezione obrazki
        error_log("BackgroundHelper: Found " . count($backgroundImages) . " background images: " . implode(', ', $backgroundImages));

        // Jeśli brak obrazków, zwróć pusty string
        if (empty($backgroundImages)) {
            error_log("BackgroundHelper: No background images found!");
            return '';
        }

        // Wybierz losowy obrazek
        $randomImage = $backgroundImages[array_rand($backgroundImages)];
        
        error_log("BackgroundHelper: Selected image: " . $randomImage);

        // Zwróć URL (bez timestamp - przeglądarka i tak przeładowuje stronę)
        return '/backgrounds/' . $folder . '/' . $randomImage;
    }


    /**
     * Pobierz losową ikonę frakcji na podstawie ID motywu/nacji
     * (am, ru, zsi, default itd.)
     *
     * Struktura katalogów:
     * public/icons/id_nacja/{id}/plik.png
     */
public static function getNationIconForTheme(string $themeName, string $iconName): string
{
    $themeToNation = [
        'default' => 'default',
        'am'      => 'am',
        'ru'      => 'ru',
        'zsi'     => 'zsi',
    ];

    $nationId = $themeToNation[$themeName] ?? 'default';

    // baza: /public/uploads/icons/id_nacja/{nacja}/
    $baseDir = __DIR__ . '/../public/uploads/icons/' . $nationId . '/';
    $baseUrl = '/uploads/icons/' . $nationId . '/';

    // obsługujemy kilka rozszerzeń
    $possibleExt = ['png', 'webp', 'jpg', 'jpeg', 'gif'];

    foreach ($possibleExt as $ext) {
        $path = $baseDir . $iconName . '.' . $ext;
        if (is_file($path)) {
            return $baseUrl . $iconName . '.' . $ext;
        }
    }

    // fallback: default theme
    if ($nationId !== 'default') {
        $baseDir = __DIR__ . '/../public/uploads/icons/default/';
        $baseUrl = '/uploads/icons/default/';

        foreach ($possibleExt as $ext) {
            $path = $baseDir . $iconName . '.' . $ext;
            if (is_file($path)) {
                return $baseUrl . $iconName . '.' . $ext;
            }
        }
    }

    error_log("BackgroundHelper: Icon {$iconName} not found for theme {$themeName}");
    return '';
}



    /**
     * Pobierz aktualny motyw użytkownika
     */
    public static function getCurrentTheme()
    {
        // Sprawdź czy jest cookie (ustawiane przez JavaScript)
        if (isset($_COOKIE['theme'])) {
            error_log("BackgroundHelper: Theme from cookie: " . $_COOKIE['theme']);
            return $_COOKIE['theme'];
        }

        // Sprawdź GET (zmiana motywu w locie)
        if (isset($_GET['theme'])) {
            error_log("BackgroundHelper: Theme from GET: " . $_GET['theme']);
            return $_GET['theme'];
        }

        // Sprawdź sesję
        if (isset($_SESSION['theme'])) {
            error_log("BackgroundHelper: Theme from session: " . $_SESSION['theme']);
            return $_SESSION['theme'];
        }

        // Domyślny motyw z ustawień
        if (class_exists('Settings')) {
            $settings = new Settings();
            $defaultTheme = $settings->get('default_theme', 'default');
            error_log("BackgroundHelper: Theme from settings (default): " . $defaultTheme);
            return $defaultTheme;
        }

        error_log("BackgroundHelper: Using hardcoded default theme");
        return 'default';
    }

    /**
     * Pobierz tło dla aktualnego motywu
     */
    public static function getBackgroundForCurrentTheme()
    {
        $currentTheme = self::getCurrentTheme();
        error_log("BackgroundHelper: Getting background for current theme: " . $currentTheme);
        return self::getThemeBackground($currentTheme);
    }

    /**
     * Sprawdź czy folder dla motywu istnieje
     */
    public static function themeHasBackground($themeName)
    {
        $backgroundDir = __DIR__ . '/../public/backgrounds/' . $themeName . '/';
        return is_dir($backgroundDir);
    }

    /**
     * Pobierz listę dostępnych motywów z tłami
     */
    public static function getAvailableThemes()
    {
        $backgroundsDir = __DIR__ . '/../public/backgrounds/';
        $themes = [];

        if (is_dir($backgroundsDir)) {
            $folders = scandir($backgroundsDir);
            foreach ($folders as $folder) {
                if ($folder === '.' || $folder === '..') {
                    continue;
                }

                $folderPath = $backgroundsDir . $folder;
                if (is_dir($folderPath)) {
                    // Sprawdź czy są jakieś obrazki w folderze
                    $files = scandir($folderPath);
                    $hasImages = false;
                    
                    foreach ($files as $file) {
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $hasImages = true;
                            break;
                        }
                    }

                    if ($hasImages) {
                        $themes[] = $folder;
                    }
                }
            }
        }

        return $themes;
    }
}
