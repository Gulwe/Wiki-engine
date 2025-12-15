<!-- GLOBALNE TŁO -->
<?php include __DIR__ . '/partials/background.php'; ?>

<h1>📁 Wszystkie Kategorie</h1>

<?php if (empty($categories)): ?>
    <p class="info">
        Brak kategorii.
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="/admin/categories">Dodaj pierwszą kategorię</a>
        <?php endif; ?>
    </p>
<?php else: ?>
    <div class="categories-grid">
        <?php foreach ($categories as $cat): ?>
            <a href="/category/<?= (int)$cat['category_id'] ?>" class="category-card">
                <div class="category-icon">📁</div>

                <div class="category-name">
                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                </div>

                <?php if (!empty($cat['description'])): ?>
                    <div class="category-desc">
                        <?= htmlspecialchars($cat['description'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <div class="category-count">
                    <?= (int)$cat['pages_count'] ?> stron
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
