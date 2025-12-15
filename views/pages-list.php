<?php
// views/pages-list.php - TYLKO TREŚĆ
?>

<div class="pages-list-page">
    <header class="page-header">
        <h1>📄 Wszystkie strony</h1>
        
        <form method="GET" class="pages-filter">
            <input type="text" name="search" placeholder="Szukaj..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <select name="category">
                <option value="">Wszystkie kategorie</option>
                <?php
                $db = Database::getInstance()->getConnection();
                $cats = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
                foreach ($cats as $cat):
                    $selected = isset($_GET['category']) && $_GET['category'] == $cat['category_id'] ? 'selected' : '';
                ?>
                    <option value="<?= $cat['category_id'] ?>" <?= $selected ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">🔍 Szukaj</button>
        </form>
    </header>

    <?php if (empty($pages)): ?>
        <p class="info">Nie znaleziono żadnych stron.</p>
    <?php else: ?>
        <ul class="page-list">
            <?php foreach ($pages as $page): ?>
                <li class="page-list-item">
                    <a href="/page/<?= htmlspecialchars($page['slug']) ?>" class="page-list-item-link">
                        <h3 class="page-list-item-title"><?= htmlspecialchars($page['title']) ?></h3>
                        
                        <div class="page-list-item-meta">
                            <?php if (!empty($page['category'])): ?>
                                <span class="page-category-pill"><?= htmlspecialchars($page['category']) ?></span>
                            <?php endif; ?>
                            
                            <span class="page-list-author">
                                👤 <?= htmlspecialchars($page['author'] ?? 'Nieznany') ?>
                            </span>
                            
                            <?php if (!empty($page['updated_at'])): ?>
                                <span class="page-list-date">
                                    📅 <?= date('d.m.Y', strtotime($page['updated_at'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Paginacja -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="btn btn-outline">
                        ← Poprzednia
                    </a>
                <?php endif; ?>
                
                <span class="pagination-info">Strona <?= $currentPage ?> z <?= $totalPages ?></span>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="btn btn-outline">
                        Następna →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
