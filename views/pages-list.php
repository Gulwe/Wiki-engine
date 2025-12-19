<?php
// views/pages-list.php - TYLKO TREŚĆ
?>

<div class="pages-list-page">
    <header class="page-header">
        <h1>📄 Wszystkie strony</h1>
        
        <form method="GET" class="pages-filter">
            <input
                type="text"
                name="search"
                placeholder="Szukaj..."
                value="<?= htmlspecialchars($search ?? '') ?>"
            >

            <select name="category">
                <option value="">Wszystkie kategorie</option>
                <?php foreach ($categories as $cat): ?>
                    <?php
                        $selected = isset($categoryFilter) && (int)$categoryFilter === (int)$cat['category_id']
                            ? 'selected'
                            : '';
                    ?>
                    <option value="<?= (int)$cat['category_id'] ?>" <?= $selected ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                        (<?= (int)($cat['pages_count'] ?? 0) ?>)
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
                    <a
                        href="/page/<?= htmlspecialchars($page['slug']) ?>"
                        class="page-list-item-link"
                    >
                        <h3 class="page-list-item-title">
                            <?= htmlspecialchars($page['title']) ?>
                        </h3>
                        
                        <div class="page-list-item-meta">
                            <?php if (!empty($page['category_name'])): ?>
                                <span class="page-category-pill">
                                    <?= htmlspecialchars($page['category_name']) ?>
                                </span>
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
        <?php if (!empty($totalPagesCount) && $totalPagesCount > 1): ?>
            <?php
                // budujemy query string bez parametru page
                $baseQuery = $_GET;
                unset($baseQuery['page']);
                $baseQueryString = http_build_query($baseQuery);
                $baseUrl = $baseQueryString ? ('?' . $baseQueryString . '&') : '?';
            ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a
                        href="<?= $baseUrl . 'page=' . ($currentPage - 1) ?>"
                        class="btn btn-outline"
                    >
                        ← Poprzednia
                    </a>
                <?php endif; ?>
                
                <span class="pagination-info">
                    Strona <?= (int)$currentPage ?> z <?= (int)$totalPagesCount ?>
                </span>
                
                <?php if ($currentPage < $totalPagesCount): ?>
                    <a
                        href="<?= $baseUrl . 'page=' . ($currentPage + 1) ?>"
                        class="btn btn-outline"
                    >
                        Następna →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
