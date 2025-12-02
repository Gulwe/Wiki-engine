<?php
require_once __DIR__ . '/../models/Page.php';
require_once __DIR__ . '/../models/Settings.php';
// Jeśli masz model Analytics, odkomentuj:
 require_once __DIR__ . '/../models/Analytics.php';
// Jeśli chcesz użyć komentarzy w statystykach, możesz też mieć:
 require_once __DIR__ . '/../models/Comment.php';

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
        /*
        $analyticsModel = new Analytics();
        $stats['total_views'] = $analyticsModel->getTotalViews();
        */

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
}
