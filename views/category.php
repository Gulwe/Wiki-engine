<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategoria: <?= htmlspecialchars($category['name']) ?> - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <div class="container">
        <h1>📁 <?= htmlspecialchars($category['name']) ?></h1>
        
        <?php if ($category['description']): ?>
            <p class="category-description"><?= htmlspecialchars($category['description']) ?></p>
        <?php endif; ?>
        
        <div class="category-stats">
            📄 <?= count($pages) ?> stron w tej kategorii
        </div>
        
        <?php if (empty($pages)): ?>
            <p class="info">Brak stron w tej kategorii.</p>
        <?php else: ?>
            <ul class="page-list">
                <?php foreach ($pages as $page): ?>
                    <li>
                        <a href="/page/<?= htmlspecialchars($page['slug']) ?>">
                            <?= htmlspecialchars($page['title']) ?>
                        </a>
                        <span class="meta">
                            Autor: <?= htmlspecialchars($page['author'] ?? 'Nieznany') ?> | 
                            <?= date('d.m.Y', strtotime($page['updated_at'])) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <div style="margin-top: 30px;">
            <a href="/" class="btn">🏠 Strona główna</a>
        </div>
    </div>
</body>
</html>
