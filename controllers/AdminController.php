<?php
require_once __DIR__ . '/../models/Page.php';
require_once __DIR__ . '/../models/Settings.php';
require_once __DIR__ . '/../models/Analytics.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/ExternalLink.php';

class AdminController
{
    private function requireAdmin()
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: /login');
            exit;
        }
    }

    // === DASHBOARD (prosty) ===
    public function dashboard()
    {
        $this->requireAdmin();

        $pageModel = new Page();
        $settings  = new Settings();

        // Proste statystyki
        $stats = [
            'total_pages' => method_exists($pageModel, 'getTotalCount')
                ? $pageModel->getTotalCount()
                : 0,
        ];

        // Jeśli masz Analytics, możesz dodać więcej:
        $analyticsModel = new Analytics();
        if (method_exists($analyticsModel, 'getTotalViews')) {
            $stats['total_views'] = $analyticsModel->getTotalViews();
        }

        // Ostatnie strony (jeśli masz taką metodę)
        $recentPages = method_exists($pageModel, 'getRecent')
            ? $pageModel->getRecent(10)
            : [];

        require __DIR__ . '/../views/admin/dashboard.php';
    }

    // === CUSTOMIZACJA / USTAWIENIA ===
    public function customize()
    {
        $this->requireAdmin();

        $settings = new Settings();

        require __DIR__ . '/../views/admin/customize.php';
    }

    public function saveCustomize()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/customize');
            exit;
        }

        $settings     = new Settings();
        $logoUploaded = false;

        // === UPLOAD LOGO (opcjonalny plik) ===
        if (isset($_FILES['site_logo_file']) && $_FILES['site_logo_file']['error'] === UPLOAD_ERR_OK) {
            $file         = $_FILES['site_logo_file'];
            $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml', 'image/webp'];
            $maxSize      = 2 * 1024 * 1024; // 2MB

            // Typ pliku
            if (!in_array($file['type'], $allowedTypes, true)) {
                $_SESSION['error'] = 'Nieprawidłowy typ pliku! Dozwolone: PNG, JPG, SVG, WEBP';
                header('Location: /admin/customize');
                exit;
            }

            // Rozmiar
            if ($file['size'] > $maxSize) {
                $_SESSION['error'] = 'Plik jest za duży! Maksymalny rozmiar to 2MB.';
                header('Location: /admin/customize');
                exit;
            }

            $extension  = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename   = 'logo_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $uploadPath = __DIR__ . '/../public/uploads/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
                // Usuń stare logo jeśli było w /uploads
                $oldLogo = $settings->get('site_logo');
                if ($oldLogo && strpos($oldLogo, '/uploads/') === 0) {
                    $oldPath = __DIR__ . '/../public' . $oldLogo;
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                $settings->set('site_logo', '/uploads/' . $filename);
                $logoUploaded = true;
            } else {
                $_SESSION['error'] = 'Błąd podczas przesyłania pliku.';
                header('Location: /admin/customize');
                exit;
            }
        }

        // === POZOSTAŁE USTAWIENIA Z POST ===
        foreach ($_POST as $key => $value) {
            // Pomiń input plikowy
            if ($key === 'site_logo_file') {
                continue;
            }

            // Obsługujemy tylko klucze w formacie setting_xxx
            if (strpos($key, 'setting_') === 0) {
                $settingKey = substr($key, 8);

                // Jeśli właśnie uploadowaliśmy logo, ignoruj URL
                if ($settingKey === 'site_logo' && $logoUploaded) {
                    continue;
                }

                $settings->set($settingKey, $value);
            }
        }

        // === CHECKBOXY (boolean) ===
        $booleanSettings = [
            'maintenance_mode',
            'allow_registration',
            'require_email_verification',
            'enable_comments',
        ];

        foreach ($booleanSettings as $boolSetting) {
            if (!isset($_POST['setting_' . $boolSetting])) {
                $settings->set($boolSetting, '0');
            }
        }

        $_SESSION['success'] = 'Ustawienia zostały zapisane!';
        header('Location: /admin/customize');
        exit;
    }

    // === USUWANIE LOGO (AJAX) ===
    public function removeLogo()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            $settings    = new Settings();
            $currentLogo = $settings->get('site_logo');

            if ($currentLogo && strpos($currentLogo, '/uploads/') === 0) {
                $filePath = __DIR__ . '/../public' . $currentLogo;
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            $settings->set('site_logo', '');

            echo json_encode([
                'success' => true,
                'message' => 'Logo zostało usunięte',
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Błąd podczas usuwania logo: ' . $e->getMessage(),
            ]);
        }

        exit;
    }

    // ========================================
    // === ZEWNĘTRZNE LINKI ===
    // ========================================

    /**
     * Lista wszystkich zewnętrznych linków
     */
    public function links()
    {
        $this->requireAdmin();
        
        $linkModel = new ExternalLink();
        $links = $linkModel->getAll();
        
        require __DIR__ . '/../views/admin/links.php';
    }

    /**
     * Dodaj nowy zewnętrzny link
     */
    public function addLink()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/links');
            exit;
        }

        // === DEBUG START ===
        error_log("=== AdminController::addLink() START ===");
        error_log("POST: " . print_r($_POST, true));
        error_log("FILES: " . print_r($_FILES, true));
        // === DEBUG END ===

        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $source = trim($_POST['source'] ?? '');
        $icon = $_POST['icon'] ?? '🔗';
        $description = trim($_POST['description'] ?? '');
        $thumbnailType = $_POST['thumbnail_type'] ?? 'upload';
        $thumbnail = null;

        error_log("Thumbnail type: " . $thumbnailType);

        if (empty($title) || empty($url)) {
            error_log("ERROR: Title or URL is empty");
            header('Location: /admin/links?error=1');
            exit;
        }

        $linkModel = new ExternalLink();

        // Obsługa miniatury
        if ($thumbnailType === 'url') {
            $thumbnailUrl = trim($_POST['thumbnail_url'] ?? '');
            error_log("Thumbnail URL from POST: " . $thumbnailUrl);
            
            if (!empty($thumbnailUrl)) {
                $thumbnail = $linkModel->validateThumbnailUrl($thumbnailUrl);
                error_log("After validateThumbnailUrl: " . ($thumbnail ?? 'NULL'));
            }
        } elseif ($thumbnailType === 'upload') {
            error_log("Attempting file upload...");
            
            if (isset($_FILES['thumbnail_file'])) {
                error_log("File error code: " . $_FILES['thumbnail_file']['error']);
                error_log("File name: " . ($_FILES['thumbnail_file']['name'] ?? 'NO NAME'));
                error_log("File size: " . ($_FILES['thumbnail_file']['size'] ?? 0));
                
                if ($_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
                    $thumbnail = $linkModel->handleThumbnailUpload($_FILES['thumbnail_file']);
                    error_log("After handleThumbnailUpload: " . ($thumbnail ?? 'NULL'));
                } else {
                    error_log("Upload error code: " . $_FILES['thumbnail_file']['error']);
                }
            } else {
                error_log("No 'thumbnail_file' in FILES array");
            }
        }

        // Jeśli nie udało się ustawić miniatury, spróbuj auto-wykryć
        if (empty($thumbnail)) {
            error_log("Thumbnail is empty, trying auto-detect for URL: " . $url);
            $thumbnail = $linkModel->getAutoThumbnail($url);
            error_log("After getAutoThumbnail: " . ($thumbnail ?? 'NULL'));
        }

        $userId = $_SESSION['user_id'] ?? null;
        
        error_log("FINAL THUMBNAIL VALUE BEFORE CREATE: " . ($thumbnail ?? 'NULL'));
        error_log("User ID: " . ($userId ?? 'NULL'));
        
        $result = $linkModel->create($title, $url, $description, $source, $icon, $userId, $thumbnail);
        
        error_log("Create result: " . ($result ? 'SUCCESS' : 'FAILED'));
        error_log("=== AdminController::addLink() END ===");

        header('Location: /admin/links?success=added');
        exit;
    }

    /**
     * Usuń zewnętrzny link
     */
    public function deleteLink($linkId)
    {
        $this->requireAdmin();
        
        $linkModel = new ExternalLink();
        $linkModel->delete($linkId);
        
        header('Location: /admin/links?success=deleted');
        exit;
    }

    /**
     * Przełącz widoczność linku
     */
    public function toggleLink($linkId)
    {
        $this->requireAdmin();
        
        $linkModel = new ExternalLink();
        $linkModel->toggleVisibility($linkId);
        
        header('Location: /admin/links');
        exit;
    }

    /**
     * Przesuń link w górę lub w dół
     */
    public function moveLink($linkId, $direction)
    {
        $this->requireAdmin();
        
        if (!in_array($direction, ['up', 'down'])) {
            header('Location: /admin/links');
            exit;
        }
        
        $linkModel = new ExternalLink();
        $linkModel->move($linkId, $direction);
        
        header('Location: /admin/links?success=moved');
        exit;
    }
    
    // ========================================
// === SZABLONY ===
// ========================================

/**
 * Lista szablonów
 */
public function templates()
{
    $this->requireAdmin();
    
    require_once __DIR__ . '/../models/Template.php';
    $templateModel = new Template();
    $templates = $templateModel->getAll();
    
    require __DIR__ . '/../views/admin/templates.php';
}

/**
 * Zapisz szablon (dodaj nowy lub edytuj istniejący)
 */
public function saveTemplate()
{
    $this->requireAdmin();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /admin/templates');
        exit;
    }
    
    $templateId = isset($_POST['template_id']) ? (int)$_POST['template_id'] : null;
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $content = $_POST['content'] ?? '';
    
    if (empty($name) || empty($slug)) {
        header('Location: /admin/templates?error=' . urlencode('Nazwa i klucz są wymagane'));
        exit;
    }
    
    require_once __DIR__ . '/../models/Template.php';
    $templateModel = new Template();
    
    if ($templateId) {
        // Edycja istniejącego
        $templateModel->update($templateId, $name, $slug, $content);
    } else {
        // Dodaj nowy
        $templateModel->create($name, $slug, $content);
    }
    
    header('Location: /admin/templates?success=1');
    exit;
}

/**
 * Usuń szablon
 */
public function deleteTemplate($templateId)
{
    $this->requireAdmin();
    
    require_once __DIR__ . '/../models/Template.php';
    $templateModel = new Template();
    $templateModel->delete($templateId);
    
    header('Location: /admin/templates?success=1');
    exit;
}

    
}
