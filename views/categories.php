<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wszystkie Kategorie - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <div class="container">
        <h1>📁 Wszystkie Kategorie</h1>
        
        <?php if (empty($categories)): ?>
            <p class="info">Brak kategorii. <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?><a href="/admin/categories">Dodaj pierwszą kategorię</a><?php endif; ?></p>
        <?php else: ?>
            <div class="categories-grid">
                <?php foreach ($categories as $cat): ?>
                    <a href="/category/<?= $cat['category_id'] ?>" class="category-card">
                        <div class="category-icon">📁</div>
                        <div class="category-name"><?= htmlspecialchars($cat['name']) ?></div>
                        <?php if ($cat['description']): ?>
                            <div class="category-desc"><?= htmlspecialchars($cat['description']) ?></div>
                        <?php endif; ?>
                        <div class="category-count"><?= $cat['pages_count'] ?> stron</div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
