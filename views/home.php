<?php
// views/home.php - TYLKO TREŚĆ

require_once __DIR__ . '/../models/ExternalLink.php';
$linkModel = new ExternalLink();
$externalLinks = $linkModel->getRecent(3);

require_once __DIR__ . '/../models/Page.php';
$pageModel = new Page();
$recentlyEdited = $pageModel->getRecentlyUpdated(5);

// motyw / ikony frakcji
require_once __DIR__ . '/../core/BackgroundHelper.php';
$theme = BackgroundHelper::getCurrentTheme();

$iconBazy        = BackgroundHelper::getNationIconForTheme($theme, 'bazy');
$iconProfesje    = BackgroundHelper::getNationIconForTheme($theme, 'profesje');
$iconFabryka     = BackgroundHelper::getNationIconForTheme($theme, 'fabryka');
$iconBudynki     = BackgroundHelper::getNationIconForTheme($theme, 'budynki');
$iconPostacie    = BackgroundHelper::getNationIconForTheme($theme, 'postacie');
$iconTechnologie = BackgroundHelper::getNationIconForTheme($theme, 'technologie');
$iconMody        = BackgroundHelper::getNationIconForTheme($theme, 'modyfikacje');
$iconAutorzy     = BackgroundHelper::getNationIconForTheme($theme, 'autorzy');
$iconDead     = BackgroundHelper::getNationIconForTheme($theme, 'dead');
$iconPotyczki     = BackgroundHelper::getNationIconForTheme($theme, 'potyczki');
$iconMultiplayer     = BackgroundHelper::getNationIconForTheme($theme, 'multiplayer');
?>

<!-- GLOBALNE TŁO -->
<?php include __DIR__ . '/partials/background.php'; ?>

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
        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] !== 'viewer'): ?>
            <a href="/syntax-help" class="btn-outline">📘 Pomoc składni</a>
        <?php endif; ?>
    </div>

    <!-- SEKCJA KAMPANII I LORE -->
    <div class="home-categories">
        <!-- Kampanie -->
        <div class="category-section">
            <h3 class="category-title">Kampanie</h3>
            <div class="category-grid">
                <a href="/page/kampania-amerykanska" class="category-card">
                    <div class="category-icon">
                        <img src="/uploads/icons/am.png" alt="Amerykanie">
                    </div>
                    <div class="category-name">Kampania Amerykańska</div>
                </a>
                <a href="/page/kampania-rosyjska" class="category-card">
                    <div class="category-icon">
                        <img src="/uploads/icons/ru.png" alt="Rosjanie">
                    </div>
                    <div class="category-name">Kampania Rosyjska</div>
                </a>
                <a href="/page/kampania-ares" class="category-card">
                    <div class="category-icon">
                        <img src="/uploads/icons/zsi.png" alt="ZSI">
                    </div>
                    <div class="category-name">Kampania Arabska</div>
                </a>
                <a href="/page/kampania-ares" class="category-card">
                    <div class="category-icon">
                        <img src="/uploads/icons/Ares.png" alt="Ares">
                    </div>
                    <div class="category-name">Kampania Ares</div>
                </a>
            </div>
        </div>
        <!-- Gameplay -->
                <div class="category-section">
            <h3 class="category-title">Gameplay</h3>
            <div class="category-grid">
                <a href="/page/potyczki" class="category-card">
                    <div class="category-icon">
                        <img src="<?= htmlspecialchars($iconPotyczki, ENT_QUOTES) ?>" alt="Potyczki"
                             class="lore-icon icon-main" data-category="potyczki">
                    </div>
                    <div class="category-name">Potyczki</div>
                </a>
                                <a href="/page/multiplayer" class="category-card">
                    <div class="category-icon">
                        <img src="<?= htmlspecialchars($iconMultiplayer, ENT_QUOTES) ?>" alt="Multiplayer"
                             class="lore-icon icon-main" data-category="multiplayer">
                    </div>
                    <div class="category-name">Multiplayer</div>
                </a>
                            </div>
        </div>

        <!-- Lore i opisy -->
        <div class="category-section">
            <h3 class="category-title">Lore i opisy</h3>
            <div class="category-grid">
                <a href="/page/bazy" class="category-card">
                    <div class="category-icon">
                        <img src="<?= htmlspecialchars($iconBazy, ENT_QUOTES) ?>" alt="Bazy"
                             class="lore-icon icon-main" data-category="bazy">
                    </div>
                    <div class="category-name">Bazy</div>
                </a>
                <a href="/page/profesje" class="category-card">
                    <div class="category-icon">
                        <img src="<?= htmlspecialchars($iconProfesje, ENT_QUOTES) ?>" alt="Profesje"
                             class="lore-icon icon-main" data-category="profesje">
                    </div>
                    <div class="category-name">Profesje</div>
                </a>
                <a href="/page/fabryka" class="category-card">
                    <div class="category-icon">
                        <img src="<?= htmlspecialchars($iconFabryka, ENT_QUOTES) ?>" alt="Fabryka"
                             class="lore-icon icon-main" data-category="fabryka">
                    </div>
                    <div class="category-name">Fabryka</div>
                </a>
                <a href="/page/budynki" class="category-card">
                    <div class="category-icon">
                        <img src="<?= htmlspecialchars($iconBudynki, ENT_QUOTES) ?>" alt="Budynki"
                             class="lore-icon icon-main" data-category="budynki">
                    </div>
                    <div class="category-name">Budynki</div>
                </a>
                <a href="/page/postacie" class="category-card">
                    <div class="category-icon">
                        <img src="<?= htmlspecialchars($iconPostacie, ENT_QUOTES) ?>" alt="Postacie"
                             class="lore-icon icon-main" data-category="postacie">
                    </div>
                    <div class="category-name">Postacie</div>
                </a>
                <a href="/page/technologie" class="category-card">
                    <div class="category-icon">
                        <img src="<?= htmlspecialchars($iconTechnologie, ENT_QUOTES) ?>" alt="Technologie"
                             class="lore-icon icon-main" data-category="technologie">
                    </div>
                    <div class="category-name">Technologie</div>
                </a>
            </div>
        </div>

        <!-- Modyfikacje -->
        <div class="category-section">
            <h3 class="category-title">Modyfikacje</h3>
            <div class="category-grid">
                <a href="/page/modyfikacje" class="category-card">
                    <div class="category-icon">
                        <img src="<?= htmlspecialchars($iconMody, ENT_QUOTES) ?>" alt="Mody"
                             class="lore-icon icon-main" data-category="modyfikacje">
                    </div>
                    <div class="category-name">Mody</div>
                </a>
                <a href="/page/autorzy" class="category-card">
                    <div class="category-icon">
                        <img src="<?= htmlspecialchars($iconAutorzy, ENT_QUOTES) ?>" alt="Autorzy"
                             class="lore-icon icon-main" data-category="autorzy">
                    </div>
                    <div class="category-name">Autorzy</div>
                </a>
            </div>
        </div>
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

    <!-- Kolumna 3: z zewnątrz + Discord -->
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
                            <div class="external-link-thumb">
                                <?php if (!empty($link['thumbnail'])): ?>
                                    <img src="<?= htmlspecialchars($link['thumbnail']) ?>" 
                                         alt="<?= htmlspecialchars($link['title']) ?>">
                                <?php else: ?>
                                    <div class="external-link-placeholder">
                                        🔗
                                    </div>
                                <?php endif; ?>
                            </div>
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

            <div class="discord-widget">
                <iframe
                    src="https://discord.com/widget?id=1120738124276977745&theme=dark"
                    allowtransparency="true"
                    frameborder="0"
                    sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts">
                </iframe>
            </div>
        </section>
    </aside>
</div>
