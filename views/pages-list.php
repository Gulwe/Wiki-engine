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
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
            <h1>📚 Wszystkie strony</h1>
            <span style="color:#a78bfa;font-size:14px;">Znaleziono: <?= $totalPages ?> stron</span>
        </div>
        
        <!-- Filtry i sortowanie -->
        <div style="display:flex;gap:15px;flex-wrap:wrap;margin-bottom:30px;padding:20px;background:rgba(15,23,42,0.6);border-radius:12px;border:1px solid rgba(139,92,246,0.3);">
            <!-- Filtr kategorii -->
            <div style="flex:1;min-width:200px;">
                <label style="display:block;color:#a78bfa;font-size:12px;margin-bottom:8px;font-weight:600;">Kategoria</label>
                <select onchange="window.location.href='?category=' + this.value + '&sort=<?= htmlspecialchars($sortBy) ?>'" style="width:100%;padding:10px;background:rgba(30,0,60,0.5);border:2px solid rgba(139,92,246,0.3);color:#e0e0ff;border-radius:8px;font-family:inherit;">
                    <option value="">Wszystkie kategorie</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>" <?= $categoryFilter == $cat['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?> (<?= $cat['pages_count'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Sortowanie -->
            <div style="flex:1;min-width:200px;">
                <label style="display:block;color:#a78bfa;font-size:12px;margin-bottom:8px;font-weight:600;">Sortuj według</label>
                <select onchange="window.location.href='?sort=' + this.value + '<?= $categoryFilter ? '&category=' . $categoryFilter : '' ?>'" style="width:100%;padding:10px;background:rgba(30,0,60,0.5);border:2px solid rgba(139,92,246,0.3);color:#e0e0ff;border-radius:8px;font-family:inherit;">
                    <option value="updated" <?= $sortBy == 'updated' ? 'selected' : '' ?>>Ostatnio zaktualizowane</option>
                    <option value="created" <?= $sortBy == 'created' ? 'selected' : '' ?>>Ostatnio utworzone</option>
                    <option value="title" <?= $sortBy == 'title' ? 'selected' : '' ?>>Alfabetycznie (A-Z)</option>
                    <option value="views" <?= $sortBy == 'views' ? 'selected' : '' ?>>Najpopularniejsze</option>
                </select>
            </div>
            
            <?php if ($categoryFilter || $sortBy != 'updated'): ?>
                <div style="display:flex;align-items:flex-end;">
                    <a href="/pages" style="padding:10px 20px;background:rgba(139,92,246,0.2);color:#e0e0ff;text-decoration:none;border-radius:8px;border:1px solid rgba(139,92,246,0.4);">
                        🔄 Resetuj filtry
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Lista stron -->
        <?php if (empty($pages)): ?>
            <p class="info">Nie znaleziono żadnych stron.</p>
        <?php else: ?>
            <ul class="page-list">
                <?php foreach ($pages as $page): ?>
                    <li>
                        <a href="/page/<?= htmlspecialchars($page['slug']) ?>" class="page-list-item-link">
                            <div class="page-list-item">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:15px;">
                                    <div style="flex:1;">
                                        <div class="page-list-item-title">
                                            <?= htmlspecialchars($page['title']) ?>
                                        </div>
                                        <span class="page-list-item-meta">
                                            <?php if (!empty($page['category_name'])): ?>
                                                <span style="background:rgba(139,92,246,0.2);padding:2px 10px;border-radius:12px;font-size:11px;margin-right:8px;">
                                                    <?= htmlspecialchars($page['category_name']) ?>
                                                </span>
                                            <?php endif; ?>
                                            Autor: <?= htmlspecialchars($page['author'] ?? 'Nieznany') ?> • 
                                            <?= date('d.m.Y H:i', strtotime($page['updated_at'])) ?>
                                            <?php if ($page['views'] > 0): ?>
                                                • 👁️ <?= (int)$page['views'] ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <!-- Paginacja -->
        <?php if ($totalPagesCount > 1): ?>
            <div style="display:flex;justify-content:center;gap:10px;margin-top:40px;flex-wrap:wrap;">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?><?= $sortBy != 'updated' ? '&sort=' . $sortBy : '' ?>" 
                       style="padding:10px 16px;background:rgba(139,92,246,0.2);color:#e0e0ff;text-decoration:none;border-radius:8px;border:1px solid rgba(139,92,246,0.4);">
                        ← Poprzednia
                    </a>
                <?php endif; ?>
                
                <span style="padding:10px 16px;color:#a78bfa;">
                    Strona <?= $currentPage ?> z <?= $totalPagesCount ?>
                </span>
                
                <?php if ($currentPage < $totalPagesCount): ?>
                    <a href="?page=<?= $currentPage + 1 ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?><?= $sortBy != 'updated' ? '&sort=' . $sortBy : '' ?>" 
                       style="padding:10px 16px;background:rgba(139,92,246,0.2);color:#e0e0ff;text-decoration:none;border-radius:8px;border:1px solid rgba(139,92,246,0.4);">
                        Następna →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
