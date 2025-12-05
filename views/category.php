<?php
// views/category.php
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategoria: <?= htmlspecialchars($category['name']) ?> - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .category-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(139, 92, 246, 0.4);
        }
        .category-title {
            font-size: 28px;
            font-weight: 800;
        }
        .category-description {
            color: #a78bfa;
            font-size: 15px;
            margin-top: 10px;
        }
        .category-stats {
            margin-top: 10px;
            font-size: 13px;
            color: #818cf8;
        }
        .page-list {
            list-style: none;
            margin: 25px 0 0 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* LINK OWIJA CAŁY KAFEL */
        .page-list-item-link {
            display: block;
            text-decoration: none;
            color: inherit;
        }
        .page-list-item-link:hover {
            text-decoration: none;
        }

        .page-list-item {
            background: rgba(15, 6, 40, 0.9);
            border-radius: 14px;
            padding: 16px 20px;
            cursor: pointer;
        }
        .page-list-title {
            display: inline-block;
            font-weight: 700;
            font-size: 16px;
            color: #e5e7ff;
            text-decoration: none;
            margin-bottom: 6px;
        }
        .page-list-item-link:hover .page-list-title {
            color: #f9fafb;
            text-shadow: 0 0 14px rgba(167, 139, 250, 0.9);
        }
        .page-list-desc {
            margin: 6px 0 8px 0;
            font-size: 13px;
            color: #c4b5fd;
        }
        .page-list-tags {
            margin-bottom: 6px;
        }
        .page-lang-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 9px;
            border-radius: 999px;
            background: rgba(56, 189, 248, 0.15);
            border: 1px solid rgba(56, 189, 248, 0.3);
            font-size: 11px;
            color: #e0f2fe;
            margin-right: 4px;
        }
        .page-meta {
            display: block;
            font-size: 11px;
            color: #a5b4fc;
            margin-top: 4px;
        }
        .wiki-flag {
            width: 20px;
            height: 15px;
            border-radius: 3px;
            object-fit: cover;
            box-shadow: 0 0 4px rgba(0,0,0,0.6);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container">
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
                                        <?= htmlspecialchars($page['mod_description']) ?>
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
                                                style="width:50px;height:50px;margin-right:4px;vertical-align:middle;"
                                            >
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <span class="page-meta">
                                    Autor artykułu: <?= htmlspecialchars($page['author'] ?? 'Nieznany') ?>
                                    <?php if (!empty($page['updated_at'])): ?>
                                        | <?= date('d.m.Y', strtotime($page['updated_at'])) ?>
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
    </div>
</body>
</html>
