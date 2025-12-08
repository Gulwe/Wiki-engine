<?php
// Pobierz zewnętrzne linki
require_once __DIR__ . '/../models/ExternalLink.php';
$linkModel = new ExternalLink();
$externalLinks = $linkModel->getRecent(3);

// Pobierz ostatnio edytowane strony
require_once __DIR__ . '/../models/Page.php';
$pageModel = new Page();
$recentlyEdited = $pageModel->getRecentlyUpdated(5); // 5 ostatnio edytowanych
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(ThemeLoader::get('site_name', 'Wiki Engine')) ?> - Strona Główna</title>
    <link rel="stylesheet" href="/css/style.css">
    <?= ThemeLoader::generateCSS() ?>
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
                <span>📚 Wszystkich stron: <?= isset($totalPagesCount) ? (int)$totalPagesCount : count($pages) ?></span>

                <?php if (isset($_SESSION['username'])): ?>
                    <span>👤 Zalogowany jako: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                <?php else: ?>
                    <span>🔑 <a href="/login">Zaloguj się, aby edytować wiki</a></span>
                <?php endif; ?>
            </div>

            <div class="home-actions">
                <?php if (!empty($_SESSION['role']) && $_SESSION['role'] !== 'viewer'): ?>
    <a href="/page/new" class="btn">➕ Utwórz nową stronę</a>
<?php endif; ?>

                <a href="/categories" class="btn-outline">
                    <span>📂 Przeglądaj kategorie</span>
                </a>
                <a href="/syntax-help" class="btn-outline">
                    <span>📘 Pomoc składni</span>
                </a>
            </div>
        </div>

        <!-- TRZY KOLUMNY -->
        <div class="home-layout">
            <!-- Kolumna 1: ostatnio odwiedzane -->
            <div class="home-main">
                <section class="home-sidebar-section home-sidebar-section-main">
                    <header class="home-section-header">
                        <h2 class="home-section-title">Ostatnio odwiedzane strony</h2>
                        <a href="/pages" class="home-section-link">
                            Zobacz wszystkie
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </header>

                    <?php if (empty($pages)): ?>
                        <p class="info">
                            Brak stron. <a href="/page/home/edit">Stwórz pierwszą stronę!</a>
                        </p>
                    <?php else: ?>
                        <ul class="page-list">
                            <?php foreach ($pages as $page): ?>
                                <li class="page-list-item">
                                    <a href="/page/<?= htmlspecialchars($page['slug']) ?>" class="page-list-item-link">
                                        <div class="page-list-item-title">
                                            <?= htmlspecialchars($page['title']) ?>
                                        </div>
                                        <span class="page-list-item-meta">
                                            Autor artykułu: <?= htmlspecialchars($page['author'] ?? 'Nieznany') ?> | 
                                            <?= date('d.m.Y H:i', strtotime($page['updated_at'])) ?>
                                        </span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Kolumna 2: ostatnio edytowane -->
<aside class="home-middle">
    <section class="home-sidebar-section home-sidebar-section-main">
        <header class="home-section-header">
            <h2 class="home-section-title">Ostatnio zaktualizowane strony</h2>
            <a href="/pages" class="home-section-link">
                Zobacz wszystkie
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
        </header>

        <?php if (empty($recentlyEdited)): ?>
            <p class="info">Brak ostatnio edytowanych stron.</p>
        <?php else: ?>
            <ul class="page-list">
                <?php foreach ($recentlyEdited as $page): ?>
                    <li class="page-list-item">
                        <a href="/page/<?= htmlspecialchars($page['slug']) ?>" class="page-list-item-link">
                            <div class="page-list-item-title">
                                <?= htmlspecialchars($page['title']) ?>
                            </div>
                            <span class="page-list-item-meta">
                                Edytował: <?= htmlspecialchars($page['author'] ?? 'Nieznany') ?> |
                                <?= date('d.m.Y H:i', strtotime($page['last_modified'])) ?>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</aside>


            <!-- Kolumna 3: z zewnątrz -->
            <aside>
                <section class="home-sidebar-section">
                    <div class="home-sidebar-title">🌐 Z zewnątrz</div>

                    <?php if (!empty($externalLinks)): ?>
                        <div class="home-external-list">
                            <?php foreach ($externalLinks as $link): ?>
                                <a href="<?= htmlspecialchars($link['url']) ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="external-link-card">
                                    
                                    <!-- Miniatura -->
                                    <div class="external-link-thumb">
                                        <?php if (!empty($link['thumbnail'])): ?>
                                            <img src="<?= htmlspecialchars($link['thumbnail']) ?>" 
                                                 alt="<?= htmlspecialchars($link['title']) ?>">
                                            <div class="external-link-play">
                                                <div class="external-link-play-icon"></div>
                                            </div>
                                        <?php else: ?>
                                            <div class="external-link-placeholder">
                                                🔗
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Treść -->
                                    <div class="external-link-body">
                                        <div class="external-link-title">
                                            <?= htmlspecialchars($link['title']) ?>
                                        </div>
                                        <div class="external-link-meta">
                                            <?php if (!empty($link['source'])): ?>
                                                <span class="external-link-source">
                                                    📍 <?= htmlspecialchars($link['source']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($link['added_at'])): ?>
                                                <span class="external-link-date">
                                                    <?= date('d.m.Y', strtotime($link['added_at'])) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Strzałka "otwórz" -->
                                    <div class="external-link-arrow">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/>
                                        </svg>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="home-meta-small home-external-empty">
                            Brak zewnętrznych linków.
                        </p>
                    <?php endif; ?>
                </section>
            </aside>
        </div>
    </div>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
