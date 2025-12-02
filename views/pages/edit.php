<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj: <?= htmlspecialchars($page['title'] ?? 'Nowa strona') ?> - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="container">
        <h1>✏️ <?= isset($page['page_id']) && $page['page_id'] ? 'Edytuj' : 'Utwórz' ?> Stronę</h1>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'empty'): ?>
            <div class="error">Tytuł i treść nie mogą być puste!</div>
        <?php endif; ?>
        
        <form method="POST" action="/page/<?= htmlspecialchars($page['slug']) ?>/save">
            <div class="form-group">
                <label>Tytuł strony:</label>
                <input type="text" name="title" value="<?= htmlspecialchars($page['title'] ?? '') ?>" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Treść (Markdown):</label>
                <textarea name="content" id="content" rows="20" required><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
                <small style="color: #a78bfa; display: block; margin-top: 5px;">
                    💡 Możesz używać składni Markdown: **pogrubienie**, *kursywa*, ## nagłówki, [linki](url), {{image:nazwa.jpg|Alt}}
                </small>
            </div>
            
            <div class="form-group">
                <label>Komentarz do zmian:</label>
                <input type="text" name="comment" placeholder="Opcjonalny opis zmian (np. 'Poprawiono błędy', 'Dodano sekcję X')">
            </div>
            
            <div class="form-group">
                <label>Kategorie:</label>
                <?php
                $db = Database::getInstance()->getConnection();
                $allCategories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
                
                // Pobierz przypisane kategorie dla tej strony
                $assignedCategories = [];
                if (isset($page['page_id']) && $page['page_id']) {
                    $stmt = $db->prepare("SELECT category_id FROM page_categories WHERE page_id = :page_id");
                    $stmt->execute(['page_id' => $page['page_id']]);
                    $assignedCategories = array_column($stmt->fetchAll(), 'category_id');
                }
                ?>
                
                <?php if (empty($allCategories)): ?>
                    <p style="color:#a78bfa; font-size:14px;">
                        Brak kategorii. 
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="/admin/categories" style="color:#818cf8;">Dodaj kategorię w panelu admina</a>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <div class="categories-checkboxes">
                        <?php foreach ($allCategories as $cat): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="categories[]" value="<?= $cat['category_id'] ?>" 
                                       <?= in_array($cat['category_id'], $assignedCategories) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="editor-actions">
                <button type="submit" class="btn">💾 Zapisz</button>
                
                <?php if (isset($page['page_id']) && $page['page_id']): ?>
                    <!-- Strona istnieje - wróć do niej -->
                    <a href="/page/<?= htmlspecialchars($page['slug']) ?>" class="btn" style="background: rgba(239, 68, 68, 0.2); border-color: #ef4444;">
                        ❌ Anuluj
                    </a>
                <?php else: ?>
                    <!-- Nowa strona - wróć do strony głównej -->
                    <a href="/" class="btn" style="background: rgba(239, 68, 68, 0.2); border-color: #ef4444;">
                        ❌ Anuluj
                    </a>
                <?php endif; ?>
                
                <button type="button" id="preview-btn" class="btn" style="background: rgba(59, 130, 246, 0.2); border-color: #3b82f6;">
                    👁️ Podgląd
                </button>
            </div>
        </form>
        
        <div id="preview-container" style="display:none; margin-top: 30px;">
            <h2>👁️ Podgląd</h2>
            <div id="preview-content" style="padding: 20px; background: rgba(30, 0, 60, 0.4); border-radius: 12px; border: 2px solid rgba(139, 92, 246, 0.3);"></div>
        </div>
    </div>
    
    <script>
    $(document).ready(function() {
        $('#preview-btn').on('click', function() {
            const content = $('#content').val();
            
            if (!content.trim()) {
                alert('Treść jest pusta!');
                return;
            }
            
            $.ajax({
                type: 'POST',
                url: '/api/preview',
                data: { content: content },
                success: function(html) {
                    $('#preview-content').html(html);
                    $('#preview-container').slideDown();
                    
                    // Scroll do podglądu
                    $('html, body').animate({
                        scrollTop: $('#preview-container').offset().top - 100
                    }, 500);
                },
                error: function() {
                    alert('Błąd podczas generowania podglądu');
                }
            });
        });
        
        // Ctrl+S do zapisu
        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 83) {
                e.preventDefault();
                $('form').submit();
            }
        });
    });
    </script>
</body>
</html>
