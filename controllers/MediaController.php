<?php
class MediaController {
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxFileSize = 5242880; // 5MB
    private $uploadDir;
    
    public function __construct() {
        $this->uploadDir = __DIR__ . '/../public/uploads/';
        
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    // ✅ NOWA FUNKCJA: Slugify z polskimi znakami
    private function slugifyFilename(string $filename): string {
        $info = pathinfo($filename);
        $name = $info['filename'];
        $ext = isset($info['extension']) ? $info['extension'] : '';
        
        // Zamień polskie znaki na ASCII
        $name = str_replace(
            ['ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż', 'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż', ' '],
            ['a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z', 'A', 'C', 'E', 'L', 'N', 'O', 'S', 'Z', 'Z', '_'],
            $name
        );
        
        // Usuń wszystko oprócz liter, cyfr, myślnika i podkreślenia
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        
        // Usuń wielokrotne podkreślenia
        $name = preg_replace('/_+/', '_', $name);
        
        // Usuń podkreślenia z początku i końca
        $name = trim($name, '_');
        
        return $name . '.' . strtolower($ext);
    }
    
    public function upload(): array {
        // Sprawdź czy to masowy upload (images[])
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            return $this->uploadMultiple();
        }
        
        // Pojedynczy upload (image)
        if (!isset($_FILES['image'])) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }
        
        return $this->uploadSingle($_FILES['image']);
    }
    
    // MASOWY UPLOAD
    private function uploadMultiple(): array {
        $uploadedCount = 0;
        $errors = [];
        $uploadedFiles = [];
        
        $fileCount = count($_FILES['images']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = $_FILES['images']['name'][$i] . ': Upload error';
                continue;
            }
            
            $file = [
                'name' => $_FILES['images']['name'][$i],
                'type' => $_FILES['images']['type'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error' => $_FILES['images']['error'][$i],
                'size' => $_FILES['images']['size'][$i]
            ];
            
            if ($file['size'] > $this->maxFileSize) {
                $errors[] = $file['name'] . ': File too large';
                continue;
            }
            
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            
            if (!in_array($mimeType, $this->allowedTypes)) {
                $errors[] = $file['name'] . ': Invalid file type';
                continue;
            }
            
            if (!getimagesize($file['tmp_name'])) {
                $errors[] = $file['name'] . ': Invalid image';
                continue;
            }
            
            // ✅ Użyj slugify
            $safeFilename = $this->slugifyFilename($file['name']);
            
            // Sprawdź duplikaty
            $extension = pathinfo($safeFilename, PATHINFO_EXTENSION);
            $basename = pathinfo($safeFilename, PATHINFO_FILENAME);
            $counter = 1;
            while (file_exists($this->uploadDir . $safeFilename)) {
                $safeFilename = $basename . '_' . $counter . '.' . $extension;
                $counter++;
            }
            $targetPath = $this->uploadDir . $safeFilename;
            
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                $errors[] = $file['name'] . ': Failed to save file';
                continue;
            }
            
            chmod($targetPath, 0644);
            
            try {
                $this->saveToDatabase($safeFilename, $file['name'], $mimeType, $file['size'], $_SESSION['user_id']);
                $uploadedCount++;
                $uploadedFiles[] = [
                    'filename' => $safeFilename,
                    'url' => '/uploads/' . $safeFilename
                ];
            } catch (Exception $e) {
                $errors[] = $file['name'] . ': Database error';
                unlink($targetPath);
            }
        }
        
        if ($uploadedCount > 0) {
            return [
                'success' => true,
                'uploaded' => $uploadedCount,
                'files' => $uploadedFiles,
                'errors' => count($errors) > 0 ? $errors : null
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to upload any files',
                'details' => $errors
            ];
        }
    }
    
    // POJEDYNCZY UPLOAD
    private function uploadSingle(array $file): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
        }
        
        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'error' => 'File too large (max 5MB)'];
        }
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type'];
        }
        
        if (!getimagesize($file['tmp_name'])) {
            return ['success' => false, 'error' => 'Invalid image'];
        }
        
        // ✅ Użyj slugify
        $safeFilename = $this->slugifyFilename($file['name']);
        
        // Sprawdź duplikaty
        $extension = pathinfo($safeFilename, PATHINFO_EXTENSION);
        $basename = pathinfo($safeFilename, PATHINFO_FILENAME);
        $counter = 1;
        while (file_exists($this->uploadDir . $safeFilename)) {
            $safeFilename = $basename . '_' . $counter . '.' . $extension;
            $counter++;
        }
        $targetPath = $this->uploadDir . $safeFilename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'error' => 'Failed to save file'];
        }
        
        chmod($targetPath, 0644);
        
        $this->saveToDatabase($safeFilename, $file['name'], $mimeType, $file['size'], $_SESSION['user_id']);
        
        return [
            'success' => true,
            'filename' => $safeFilename,
            'url' => '/uploads/' . $safeFilename
        ];
    }
    
    private function saveToDatabase(string $filename, string $originalName, string $mimeType, int $size, int $userId): void {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO media (filename, original_name, file_path, mime_type, file_size, uploaded_by)
            VALUES (:filename, :original_name, :file_path, :mime_type, :file_size, :uploaded_by)
        ");
        
        $stmt->execute([
            'filename' => $filename,
            'original_name' => $originalName,
            'file_path' => '/uploads/' . $filename,
            'mime_type' => $mimeType,
            'file_size' => $size,
            'uploaded_by' => $userId
        ]);
    }
}
