<?php
// views/pages-list.php
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wszystkie strony | <?= htmlspecialchars(ThemeLoader::get('site_name', 'Wiki Engine')) ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <?= ThemeLoader::generateCSS() ?>
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <div class="container">
        <div class="home-section-header" style="margin-bottom: 24px;">
            <h1 class="home-section-title">📚 Wszystkie strony</h1>
            <span class="home-meta-small">
                Znaleziono: <?= (int)$totalPages ?> stron
            </span>
        </div>
        
        <!-- Filtry i sortowanie -->
        <section class="home-sidebar-section home-sidebar-section-main" style="margin-bottom: 24px;">
            <div class="home-filters">
                <!-- Filtr kategorii -->
                <div class="home-filter-block">
                    <label class="home-filter-label">Kategoria</label>
                    <select
                        onchange="window.location.href='?category=' + this.value + '&sort=<?= htmlspecialchars($sortBy) ?>'"
                        class="home-filter-select"
                    >
                        <option value="">Wszystkie kategorie</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>" <?= $categoryFilter == $cat['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?> (<?= (int)$cat['pages_count'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Sortowanie -->
                <div class="home-filter-block">
                    <label class="home-filter-label">Sortuj według</label>
                    <select
                        onchange="window.location.href='?sort=' + this.value + '<?= $categoryFilter ? '&category=' . $categoryFilter : '' ?>'"
                        class="home-filter-select"
                    >
                        <option value="updated" <?= $sortBy == 'updated' ? 'selected' : '' ?>>Ostatnio zaktualizowane</option>
                        <option value="created" <?= $sortBy == 'created' ? 'selected' : '' ?>>Ostatnio utworzone</option>
                        <option value="title" <?= $sortBy == 'title' ? 'selected' : '' ?>>Alfabetycznie (A-Z)</option>
                        <option value="views" <?= $sortBy == 'views' ? 'selected' : '' ?>>Najpopularniejsze</option>
                    </select>
                </div>

                <?php if ($categoryFilter || $sortBy != 'updated'): ?>
                    <div class="home-filter-actions">
                        <a href="/pages" class="btn-outline">
                            🔄 Resetuj filtry
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- Lista stron -->
        <?php if (empty($pages)): ?>
            <p class="info">Nie znaleziono żadnych stron.</p>
        <?php else: ?>
            <ul class="page-list">
                <?php foreach ($pages as $page): ?>
                    <li class="page-list-item">
                        <a href="/page/<?= htmlspecialchars($page['slug']) ?>" class="page-list-item-link">
                            <div class="page-list-item-title">
                                <?= htmlspecialchars($page['title']) ?>
                            </div>

                            <span class="page-list-item-meta">
                                <?php if (!empty($page['category_name'])): ?>
                                    <span class="page-category-pill">
                                        <?= htmlspecialchars($page['category_name']) ?>
                                    </span>
                                <?php endif; ?>
                                Autor: <?= htmlspecialchars($page['author'] ?? 'Nieznany') ?> • 
                                <?= date('d.m.Y H:i', strtotime($page['updated_at'])) ?>
                                <?php if (!empty($page['views']) && $page['views'] > 0): ?>
                                    • 👁️ <?= (int)$page['views'] ?>
                                <?php endif; ?>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <!-- Paginacja -->
        <?php if ($totalPagesCount > 1): ?>
            <div class="pagination-bar">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?><?= $sortBy != 'updated' ? '&sort=' . $sortBy : '' ?>" 
                       class="btn-outline">
                        ← Poprzednia
                    </a>
                <?php endif; ?>
                
                <span class="home-meta-small">
                    Strona <?= $currentPage ?> z <?= $totalPagesCount ?>
                </span>
                
                <?php if ($currentPage < $totalPagesCount): ?>
                    <a href="?page=<?= $currentPage + 1 ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?><?= $sortBy != 'updated' ? '&sort=' . $sortBy : '' ?>" 
                       class="btn-outline">
                        Następna →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
