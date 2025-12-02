<?php
// Pobierz podobne strony na podstawie kategorii
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("
    SELECT DISTINCT p.*, u.username as author
    FROM pages p
    JOIN page_categories pc1 ON p.page_id = pc1.page_id
    JOIN page_categories pc2 ON pc1.category_id = pc2.category_id
    LEFT JOIN users u ON p.created_by = u.user_id
    WHERE pc2.page_id = :current_page 
    AND p.page_id != :exclude_page
    LIMIT 5
");

$stmt->execute([
    'current_page' => $page['page_id'],
    'exclude_page' => $page['page_id']
]);

$relatedPages = $stmt->fetchAll();

if (!empty($relatedPages)):
?>
<div class="related-pages">
    <h3>📚 Podobne Strony</h3>
    <div class="related-grid">
        <?php foreach ($relatedPages as $related): ?>
            <a href="/page/<?= htmlspecialchars($related['slug']) ?>" class="related-card">
                <div class="related-title"><?= htmlspecialchars($related['title']) ?></div>
                <div class="related-meta">
                    👤 <?= htmlspecialchars($related['author'] ?? 'Nieznany') ?> | 
                    🕐 <?= date('d.m.Y', strtotime($related['created_at'])) ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
