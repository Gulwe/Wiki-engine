<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wiki Engine - Strona Główna</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <div class="container">
        <h1>🏠 Witaj w Wiki Engine</h1>
        
        <div class="stats">
            <span>📚 Wszystkich stron: <?= count($pages) ?></span>
            <?php if (isset($_SESSION['username'])): ?>
                <span>👤 Zalogowany jako: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
            <?php endif; ?>
        </div>
        
        <h2>Ostatnio zaktualizowane strony</h2>
        
        <?php if (empty($pages)): ?>
            <p class="info">Brak stron. <a href="/page/home/edit">Stwórz pierwszą stronę!</a></p>
        <?php else: ?>
            <ul class="page-list">
                <?php foreach ($pages as $page): ?>
                    <li>
                        <a href="/page/<?= htmlspecialchars($page['slug']) ?>">
                            <?= htmlspecialchars($page['title']) ?>
                        </a>
                        <span class="meta">
                            Autor: <?= htmlspecialchars($page['author'] ?? 'Nieznany') ?> | 
                            <?= date('d.m.Y H:i', strtotime($page['updated_at'])) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
