<?php
// views/media.php
require_once __DIR__ . '/../core/ThemeLoader.php';
?>   
    <div class="container">
        <div class="page-header">
            <h1>🖼️ Galeria Obrazków</h1>
        </div>
        
<div class="upload-section">
    <h2>📤 Upload obrazków</h2>
    <form id="upload-form" enctype="multipart/form-data">
        <input type="file" id="image-input" name="images[]" accept="image/*" multiple style="display:none;">
        
        <div class="upload-area" id="upload-area">
            <div class="upload-prompt">
                <p>🖼️ Kliknij lub przeciągnij obrazki tutaj</p>
                <small>Max 5MB każdy | JPG, PNG, GIF, WEBP</small>
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



<style>
/* Upload Section */
.upload-section {
    margin: 30px 0;
    padding: 30px;
    background: var(--card-bg);
    border-radius: 14px;
    border: 1px solid var(--border-subtle);
}

.upload-section h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: var(--accent-main);
}

.upload-area {
    border: 2px dashed var(--accent-main);
    border-radius: 12px;
    padding: 60px 20px;
    text-align: center;
    background: var(--bg-surface);
    cursor: pointer;
    transition: all 0.3s ease;
}

.upload-area:hover {
    background: var(--bg-surface-alt);
    border-color: var(--accent-secondary);
    transform: translateY(-2px);
}

.upload-area.dragover {
    background: rgba(139, 92, 246, 0.1);
    border-color: var(--accent-secondary);
}

.upload-prompt p {
    font-size: 1.2em;
    margin: 0 0 10px 0;
    color: var(--text-primary);
}

.upload-prompt small {
    color: var(--text-muted);
    font-size: 0.9em;
}

#upload-status {
    margin-top: 20px;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    font-weight: 600;
}

#upload-status.success {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #86efac;
}

#upload-status.error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
}

#upload-status.loading {
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: #93c5fd;
}

/* Media Grid */
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.media-item {
    background: var(--card-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.media-item:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.media-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}

.media-info {
    padding: 15px;
    background: var(--bg-surface);
}

.media-info strong {
    color: var(--text-primary);
    font-size: 0.95em;
    display: block;
    margin-bottom: 8px;
    word-break: break-word;
}

.media-info small {
    color: var(--text-muted);
    font-size: 0.85em;
    line-height: 1.6;
}

.media-actions {
    display: flex;
    gap: 8px;
    padding: 12px;
    border-top: 1px solid var(--border-subtle);
}

.btn-small {
    flex: 1;
    padding: 8px 12px;
    font-size: 0.85em;
    background: var(--accent-main);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn-small:hover {
    background: var(--accent-secondary);
    transform: translateY(-1px);
}

.info {
    text-align: center;
    padding: 40px;
    color: var(--text-muted);
    font-size: 1.1em;
}

@media (max-width: 768px) {
    .media-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
    }
    
    .media-item img {
        height: 150px;
    }
    
    .upload-area {
        padding: 40px 15px;
    }
    
    .media-actions {
        flex-direction: column;
    }
}
</style>
