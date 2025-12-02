<?php
require_once __DIR__ . '/../../core/WikiParser.php';
$parser = new WikiParser();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page['title']) ?> - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="container">
        <?php if (isset($_GET['restored'])): ?>
            <div class="alert-success">✓ Rewizja została przywrócona!</div>
        <?php endif; ?>
        
        <?php if (isset($page['is_old_revision']) && $page['is_old_revision']): ?>
            <div class="revision-warning">
                ⚠️ <strong>To jest starsza wersja tej strony</strong> z dnia <?= date('d.m.Y H:i', strtotime($page['revision_date'])) ?>
                (autor: <?= htmlspecialchars($page['revision_author']) ?>)
                <br>
                <a href="/page/<?= $page['slug'] ?>">← Wróć do aktualnej wersji</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    | <a href="/page/<?= $page['slug'] ?>/restore/<?= $page['current_revision_id_display'] ?>" onclick="return confirm('Przywrócić tę wersję jako aktualną?')">↩️ Przywróć tę wersję</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="page-header">
            <div>
                <h1><?= htmlspecialchars($page['title']) ?></h1>
                
                <?php
                // Pobierz kategorie tej strony
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    SELECT c.* FROM categories c
                    JOIN page_categories pc ON c.category_id = pc.category_id
                    WHERE pc.page_id = :page_id
                    ORDER BY c.name ASC
                ");
                $stmt->execute(['page_id' => $page['page_id']]);
                $pageCategories = $stmt->fetchAll();
                
                if (!empty($pageCategories)): ?>
                    <div class="page-categories">
                        <?php foreach ($pageCategories as $cat): ?>
                            <a href="/category/<?= $cat['category_id'] ?>" class="category-badge">
                                📁 <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="page-actions">
                <?php if (isset($_SESSION['user_id']) && (!isset($page['is_old_revision']) || !$page['is_old_revision'])): ?>
                    <a href="/page/<?= $page['slug'] ?>/edit" class="btn">✏️ Edytuj</a>
                <?php endif; ?>
                <a href="/page/<?= $page['slug'] ?>/history" class="btn">📜 Historia</a>
                <a href="/" class="btn">🏠 Strona główna</a>
            </div>
        </div>
        
        <div class="page-content">
            <?php if ($page['content']): ?>
                <?= $parser->parse($page['content']) ?>
            <?php else: ?>
                <p class="info">Ta strona jest pusta. <?php if (isset($_SESSION['user_id'])): ?><a href="/page/<?= $page['slug'] ?>/edit">Dodaj treść!</a><?php endif; ?></p>
            <?php endif; ?>
        </div>
        
        <div class="page-meta">
            <small>
                <?php if (isset($page['is_old_revision']) && $page['is_old_revision']): ?>
                    📝 Wersja z: <?= date('d.m.Y H:i', strtotime($page['revision_date'])) ?> | 
                    👤 Autor tej wersji: <?= htmlspecialchars($page['revision_author']) ?>
                    <?php if ($page['revision_comment']): ?>
                        | 💬 <?= htmlspecialchars($page['revision_comment']) ?>
                    <?php endif; ?>
                <?php else: ?>
                    📝 Autor: <?= htmlspecialchars($page['author'] ?? 'Nieznany') ?> | 
                    🕐 Ostatnia modyfikacja: <?= date('d.m.Y H:i', strtotime($page['last_modified'] ?? $page['created_at'])) ?>
                <?php endif; ?>
            </small>
        </div>
    </div>
</body>
</html>
