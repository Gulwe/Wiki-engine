<?php
// views/admin/links.php
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zewnętrzne linki - Admin</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .thumbnail-preview {
            margin-top: 10px;
            max-width: 200px;
            border-radius: 8px;
            border: 2px solid var(--border-subtle);
        }
        .thumbnail-cell img {
            max-width: 80px;
            max-height: 60px;
            border-radius: 4px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container">
        <h1>🔗 Zewnętrzne linki</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">
                <?php if ($_GET['success'] == 'added'): ?>
                    ✅ Link dodany pomyślnie!
                <?php elseif ($_GET['success'] == 'deleted'): ?>
                    ✅ Link usunięty.
                <?php elseif ($_GET['success'] == 'moved'): ?>
                    ✅ Kolejność zaktualizowana.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert-error">
                ❌ Błąd: wypełnij wszystkie pola.
            </div>
        <?php endif; ?>

        <!-- Formularz dodawania -->
        <div class="admin-section">
            <h3>➕ Dodaj nowy link</h3>
            <form method="POST" action="/admin/links/add" class="admin-form" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label>Tytuł *</label>
                        <input type="text" name="title" required placeholder="np. Gameplay na YouTube">
                    </div>
                    <div class="form-group">
                        <label>URL *</label>
                        <input type="url" name="url" required placeholder="https://...">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Źródło</label>
                        <input type="text" name="source" placeholder="np. YouTube, ModDB, Forum">
                    </div>
                    <div class="form-group">
                        <label>Ikona</label>
                        <select name="icon">
                            <option value="🔗">🔗 Link ogólny</option>
                            <option value="🎥">🎥 YouTube</option>
                            <option value="🎮">🎮 ModDB / Gra</option>
                            <option value="💬">💬 Forum / Discord</option>
                            <option value="📰">📰 Artykuł / News</option>
                            <option value="🛠️">🛠️ Narzędzie</option>
                            <option value="📦">📦 Download</option>
                            <option value="🌐">🌐 Strona WWW</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Opis (opcjonalnie)</label>
                    <textarea name="description" rows="2" placeholder="Krótki opis..."></textarea>
                </div>
                
                <!-- NOWA SEKCJA: Miniatura -->
                <div class="form-group">
                    <label>Miniatura (opcjonalnie)</label>
                    <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">
                        Możesz wgrać plik graficzny lub podać URL do zewnętrznego obrazka
                    </p>
                    
                    <div style="display: flex; gap: 20px; margin-bottom: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="radio" name="thumbnail_type" value="upload" checked>
                            📁 Wgraj plik
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="radio" name="thumbnail_type" value="url">
                            🌐 Podaj URL
                        </label>
                    </div>
                    
                    <div id="upload-section">
                        <input type="file" 
                               name="thumbnail_file" 
                               accept="image/jpeg,image/png,image/gif,image/webp"
                               onchange="previewThumbnail(this)">
                        <small style="display: block; margin-top: 4px; color: var(--text-muted);">
                            Obsługiwane formaty: JPG, PNG, GIF, WebP (max 2MB)
                        </small>
                    </div>
                    
                    <div id="url-section" style="display: none;">
                        <input type="url" 
                               name="thumbnail_url" 
                               placeholder="https://example.com/image.jpg"
                               onchange="previewThumbnailUrl(this)">
                        <small style="display: block; margin-top: 4px; color: var(--text-muted);">
                            Link do obrazka (np. z YouTube, ModDB, itp.)
                        </small>
                    </div>
                    
                    <div id="thumbnail-preview" style="margin-top: 12px; display: none;">
                        <p style="font-size: 12px; font-weight: 600; margin-bottom: 6px;">Podgląd:</p>
                        <img id="preview-image" class="thumbnail-preview" alt="Podgląd miniatury">
                    </div>
                </div>
                
                <button type="submit" class="btn">➕ Dodaj link</button>
            </form>
        </div>

        <!-- Lista linków -->
        <div class="admin-section" style="margin-top: 40px;">
            <h2>📋 Wszystkie linki</h2>
            <?php if (empty($links)): ?>
                <p class="info">Brak linków. Dodaj pierwszy!</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Miniatura</th>
                            <th>Ikona</th>
                            <th>Tytuł</th>
                            <th>URL</th>
                            <th>Źródło</th>
                            <th>Widoczny</th>
                            <th>Dodał</th>
                            <th>Data</th>
                            <th>Kolejność</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($links as $link): ?>
                            <tr>
                                <td class="thumbnail-cell">
                                    <?php if (!empty($link['thumbnail'])): ?>
                                        <img src="<?= htmlspecialchars($link['thumbnail']) ?>" 
                                             alt="<?= htmlspecialchars($link['title']) ?>">
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 11px;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:24px;"><?= htmlspecialchars($link['icon'] ?? '🔗') ?></td>
                                <td><strong><?= htmlspecialchars($link['title']) ?></strong></td>
                                <td>
                                    <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank" style="font-size:12px;color:#818cf8;">
                                        <?= htmlspecialchars(substr($link['url'], 0, 50)) ?><?= strlen($link['url']) > 50 ? '...' : '' ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($link['source'] ?? '-') ?></td>
                                <td>
                                    <?php if ($link['is_visible']): ?>
                                        <span style="color:#4ade80;">✅ Tak</span>
                                    <?php else: ?>
                                        <span style="color:#fca5a5;">❌ Nie</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($link['author'] ?? 'Nieznany') ?></td>
                                <td><?= date('d.m.Y', strtotime($link['added_at'])) ?></td>
                                <td>
                                    <div style="display:flex;flex-direction:column;gap:4px;align-items:center;">
                                        <a href="/admin/links/move/up/<?= $link['link_id'] ?>" 
                                           class="btn-small" 
                                           title="Wyżej">▲</a>
                                        <a href="/admin/links/move/down/<?= $link['link_id'] ?>" 
                                           class="btn-small" 
                                           title="Niżej">▼</a>
                                    </div>
                                </td>
                                <td>
                                    <a href="/admin/links/toggle/<?= $link['link_id'] ?>" class="btn-small">
                                        <?= $link['is_visible'] ? '👁️ Ukryj' : '👁️‍🗨️ Pokaż' ?>
                                    </a>
                                    <a href="/admin/links/delete/<?= $link['link_id'] ?>" 
                                       class="btn-small btn-danger"
                                       onclick="return confirm('Usunąć ten link?');">
                                        🗑️ Usuń
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px;">
            <a href="/admin" class="btn">⬅️ Panel admina</a>
        </div>
    </div>

    <script>
        // Przełączanie między uploadem a URL
        document.querySelectorAll('input[name="thumbnail_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const uploadSection = document.getElementById('upload-section');
                const urlSection = document.getElementById('url-section');
                const preview = document.getElementById('thumbnail-preview');
                
                if (this.value === 'upload') {
                    uploadSection.style.display = 'block';
                    urlSection.style.display = 'none';
                } else {
                    uploadSection.style.display = 'none';
                    urlSection.style.display = 'block';
                }
                
                // Ukryj podgląd przy zmianie typu
                preview.style.display = 'none';
            });
        });

        // Podgląd wgranego pliku
        function previewThumbnail(input) {
            const preview = document.getElementById('thumbnail-preview');
            const previewImg = document.getElementById('preview-image');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        // Podgląd URL
        function previewThumbnailUrl(input) {
            const preview = document.getElementById('thumbnail-preview');
            const previewImg = document.getElementById('preview-image');
            
            if (input.value) {
                previewImg.src = input.value;
                preview.style.display = 'block';
                
                // Ukryj podgląd jeśli obrazek się nie załaduje
                previewImg.onerror = function() {
                    preview.style.display = 'none';
                };
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
