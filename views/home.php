<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(ThemeLoader::get('site_name', 'Wiki Engine')) ?> - Strona Główna</title>
    <link rel="stylesheet" href="/css/style.css">
    <?= ThemeLoader::generateCSS() ?>
    <style>
        .home-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(260px, 1fr);
            gap: 30px;
            align-items: flex-start;
        }
        @media (max-width: 900px) {
            .home-layout {
                grid-template-columns: 1fr;
            }
        }
        .home-hero {
            margin-bottom: 25px;
        }
        .home-subtitle {
            color: #a78bfa;
            font-size: 15px;
            max-width: 520px;
        }
        .home-actions {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 999px;
            border: 1px solid rgba(129, 140, 248, 0.7);
            color: #c7d2fe;
            font-size: 13px;
            text-decoration: none;
            background: rgba(15, 23, 42, 0.6);
            transition: all 0.2s;
        }
        .btn-outline:hover {
            background: rgba(79, 70, 229, 0.4);
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.6);
        }
        .home-sidebar-section {
            margin-bottom: 25px;
            padding: 18px 20px;
            border-radius: 14px;
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(129, 140, 248, 0.4);
        }
        .home-sidebar-title {
            font-size: 14px;
            font-weight: 700;
            color: #c4b5fd;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .home-sidebar-list {
            list-style: none;
            margin: 0;
            padding: 0;
            font-size: 13px;
        }
        .home-sidebar-list li + li {
            margin-top: 6px;
        }
        .home-sidebar-list a {
            color: #a5b4fc;
            text-decoration: none;
        }
        .home-sidebar-list a:hover {
            color: #e5e7ff;
        }
        .home-meta-small {
            color: #64748b;
            font-size: 11px;
        }

        /* Kafelki listy stron na stronie głównej jako pełny link */
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
            box-shadow: 0 0 0 1px rgba(24, 24, 48, 0.8);
            cursor: pointer;
        }
        .page-list-item-title {
            display: inline-block;
            font-weight: 700;
            font-size: 16px;
            color: #e5e7ff;
            text-decoration: none;
            margin-bottom: 6px;
        }
        .page-list-item-link:hover .page-list-item-title {
            color: #f9fafb;
            text-shadow: 0 0 14px rgba(167, 139, 250, 0.9);
        }
        .page-list-item-meta {
            display: block;
            font-size: 12px;
            color: #a5b4fc;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <div class="container">
        <div class="home-hero">
            <h1>🏠 Witaj w <?= htmlspecialchars(ThemeLoader::get('site_name', 'Wiki Engine')) ?></h1>
            <p class="home-subtitle">
                Twoje centrum wiedzy o modach do Original War – przeglądaj kampanie, poradniki i narzędzia społeczności w jednym miejscu.
            </p>

            <div class="stats">
                <span>📚 Wszystkich stron: <?= count($pages) ?></span>
                <?php if (isset($_SESSION['username'])): ?>
                    <span>👤 Zalogowany jako: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                <?php else: ?>
                    <span>🔑 <a href="/login">Zaloguj się, aby edytować wiki</a></span>
                <?php endif; ?>
            </div>

            <div class="home-actions">
                <a href="/page/new" class="btn">➕ Utwórz nową stronę</a>
                <a href="/categories" class="btn-outline">
                    <span>📂 Przeglądaj kategorie</span>
                </a>
                <a href="/syntax-help" class="btn-outline">
                    <span>📘 Pomoc składni</span>
                </a>
            </div>
        </div>

        <div class="home-layout">
            <!-- Lewa kolumna: ostatnie strony -->
            <div>
                <h2>Ostatnio zaktualizowane strony</h2>

                <?php if (empty($pages)): ?>
                    <p class="info">
                        Brak stron. <a href="/page/home/edit">Stwórz pierwszą stronę!</a>
                    </p>
                <?php else: ?>
                    <ul class="page-list">
                        <?php foreach ($pages as $page): ?>
                            <li>
                                <a href="/page/<?= htmlspecialchars($page['slug']) ?>" class="page-list-item-link">
                                    <div class="page-list-item">
                                        <div class="page-list-item-title">
                                            <?= htmlspecialchars($page['title']) ?>
                                        </div>
                                        <span class="page-list-item-meta">
                                            Autor artykułu: <?= htmlspecialchars($page['author'] ?? 'Nieznany') ?> | 
                                            <?= date('d.m.Y H:i', strtotime($page['updated_at'])) ?>
                                        </span>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Prawa kolumna: ostatnia aktywność / top strony -->
            <aside>
                <div class="home-sidebar-section">
                    <div class="home-sidebar-title">📊 Ostatnia aktywność</div>
                    <?php if (!empty($recentActivity ?? [])): ?>
                        <ul class="home-sidebar-list">
                            <?php foreach (array_slice($recentActivity, 0, 8) as $item): ?>
                                <li>
                                    <a href="/page/<?= htmlspecialchars($item['slug']) ?>">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </a>
                                    <div class="home-meta-small">
                                        <?= date('d.m H:i', strtotime($item['viewed_at'])) ?>
                                        • <?= (int)$item['views'] ?> wyświetleń
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="font-size:13px;color:#a78bfa;">
                            Brak danych o aktywności. Odwiedź kilka stron, aby zobaczyć historię.
                        </p>
                    <?php endif; ?>
                </div>

                <div class="home-sidebar-section">
                    <div class="home-sidebar-title">⭐ Najczęściej oglądane</div>
                    <?php if (!empty($topPages ?? [])): ?>
                        <ul class="home-sidebar-list">
                            <?php foreach (array_slice($topPages, 0, 6) as $p): ?>
                                <li>
                                    <a href="/page/<?= htmlspecialchars($p['slug']) ?>">
                                        <?= htmlspecialchars($p['title']) ?>
                                    </a>
                                    <div class="home-meta-small">
                                        <?= (int)$p['views'] ?> wyświetleń
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="font-size:13px;color:#a78bfa;">
                            Statystyki pojawią się po kilku odwiedzinach stron.
                        </p>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
