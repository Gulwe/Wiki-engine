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
    
    public function upload(): array {
        if (!isset($_FILES['image'])) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }
        
        $file = $_FILES['image'];
        
        // SprawdŸ b³êdy
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
        }
        
        // SprawdŸ rozmiar
        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'error' => 'File too large (max 5MB)'];
        }
        
        // SprawdŸ typ MIME
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type'];
        }
        
        // Walidacja obrazu
        if (!getimagesize($file['tmp_name'])) {
            return ['success' => false, 'error' => 'Invalid image'];
        }
        
        // Generuj bezpieczn¹ nazwê
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeFilename = bin2hex(random_bytes(16)) . '_' . time() . '.' . strtolower($extension);
        $targetPath = $this->uploadDir . $safeFilename;
        
        // Przenieœ plik
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'error' => 'Failed to save file'];
        }
        
        chmod($targetPath, 0644);
        
        // Zapisz do bazy
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
