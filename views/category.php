<!-- GLOBALNE TŁO -->
<?php include __DIR__ . '/partials/background.php'; ?>
        <div class="category-header">
            <h1 class="category-title">📁 <?= htmlspecialchars($category['name']) ?></h1>

            <?php if (!empty($category['description'])): ?>
                <p class="category-description">
                    <?= nl2br(htmlspecialchars($category['description'])) ?>
                </p>
            <?php endif; ?>

            <div class="category-stats">
                📄 <?= count($pages) ?> stron w tej kategorii
            </div>
        </div>

        <?php if (empty($pages)): ?>
            <p class="info">Brak stron w tej kategorii.</p>
        <?php else: ?>
            <ul class="page-list">
                <?php foreach ($pages as $page): ?>
                    <li>
                        <a href="/page/<?= htmlspecialchars($page['slug']) ?>" class="page-list-item-link">
                            <div class="page-list-item">
                                <div class="page-list-title">
                                    <?= htmlspecialchars($page['title']) ?>
                                </div>

<?php if (!empty($page['mod_description'])): ?>
    <div class="page-list-desc">
        <?php 
            $desc = htmlspecialchars($page['mod_description']);
            if (mb_strlen($desc) > 200) {
                // Obetnij na 200 znakach
                $truncated = mb_substr($desc, 0, 200);
                // Znajdź ostatnią spację, żeby nie ciąć w połowie słowa
                $lastSpace = mb_strrpos($truncated, ' ');
                if ($lastSpace !== false) {
                    $truncated = mb_substr($truncated, 0, $lastSpace);
                }
                echo $truncated . '...';
            } else {
                echo $desc;
            }
        ?>
    </div>
<?php endif; ?>



                                <?php if (!empty($page['languages'])): ?>
                                    <div class="page-list-tags">
                                        <?php foreach ($page['languages'] as $lang): ?>
                                            <?php
                                                $code  = strtolower($lang['code']);
                                                $label = $lang['label'];
                                                $src   = "https://flagcdn.com/w40/{$code}.png";
                                            ?>
                                            <span class="page-lang-badge">
                                                <img
                                                    class="wiki-flag"
                                                    src="<?= htmlspecialchars($src) ?>"
                                                    alt="<?= htmlspecialchars($label) ?>"
                                                    title="<?= htmlspecialchars($label) ?>"
                                                >
                                                <span><?= htmlspecialchars($label) ?></span>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($page['campaign_symbols'])): ?>
                                    <div class="page-list-tags" style="margin-top:4px;">
                                        <?php foreach ($page['campaign_symbols'] as $sym): ?>
                                            <img
                                                src="<?= htmlspecialchars($sym['src']) ?>"
                                                alt="<?= htmlspecialchars($sym['name']) ?>"
                                                title="<?= htmlspecialchars($sym['name']) ?>"
                                                style="width:50px;height:50px;margin-right:4px;vertical-align:middle;border-radius:6px;"
                                            >
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <span class="page-meta">
                                    Autor artykułu: <?= htmlspecialchars($page['author'] ?? 'Nieznany') ?>
                                    <?php if (!empty($page['updated_at'])): ?>
                                        | <?= date('d.m.Y H:i', strtotime($page['updated_at'])) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="/" class="btn">🏠 Strona główna</a>
        </div>