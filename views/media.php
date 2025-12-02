<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria Obrazków - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <div class="container">
        <h1>🖼️ Galeria Obrazków</h1>
        
        <div class="upload-section">
            <h2>📤 Upload nowego obrazka</h2>
            <form id="upload-form" enctype="multipart/form-data">
                <div class="upload-area" id="upload-area">
                    <input type="file" id="image-input" name="image" accept="image/*" style="display:none;">
                    <div class="upload-prompt">
                        <p>🖼️ Kliknij lub przeciągnij obrazek tutaj</p>
                        <small>Max 5MB | JPG, PNG, GIF, WEBP</small>
                    </div>
                </div>
                <div id="upload-status"></div>
            </form>
        </div>
        
        <div class="media-grid">
            <?php if (empty($mediaFiles)): ?>
                <p class="info">Brak uploadowanych obrazków.</p>
            <?php else: ?>
                <?php foreach ($mediaFiles as $media): ?>
                    <div class="media-item" data-filename="<?= htmlspecialchars($media['filename']) ?>">
                        <img src="<?= htmlspecialchars($media['file_path']) ?>" alt="<?= htmlspecialchars($media['original_name']) ?>">
                        <div class="media-info">
                            <strong><?= htmlspecialchars($media['original_name']) ?></strong><br>
                            <small>
                                👤 <?= htmlspecialchars($media['uploader'] ?? 'Nieznany') ?><br>
                                📅 <?= date('d.m.Y H:i', strtotime($media['uploaded_at'])) ?><br>
                                📦 <?= round($media['file_size'] / 1024, 1) ?> KB
                            </small>
                        </div>
                        <div class="media-actions">
                            <button class="btn-small copy-url" data-url="<?= htmlspecialchars($media['file_path']) ?>">📋 Kopiuj URL</button>
                            <button class="btn-small copy-markdown" data-filename="<?= htmlspecialchars($media['filename']) ?>">📝 Wiki Link</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="/js/media.js"></script>
</body>
</html>
