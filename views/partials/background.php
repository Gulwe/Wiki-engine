<?php
// Wczytaj helper do tła
if (!class_exists('BackgroundHelper')) {
    require_once __DIR__ . '/../../core/BackgroundHelper.php';
}

// Pobierz aktualny motyw
$currentTheme = BackgroundHelper::getCurrentTheme();

// Pobierz tło dla motywu
$randomBg = BackgroundHelper::getThemeBackground($currentTheme);

// Generuj inline CSS jeśli tło istnieje
if (!empty($randomBg)):
?>
<style>
    :root {
        --header-height: 70px;
    }

    body {
        position: relative;
        min-height: 100vh;
        overflow-x: hidden;
    }
    /* Tło z obrazkiem dla motywu: <?= htmlspecialchars($currentTheme) ?> */
    body::after {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: -2;
        background-image: url('<?= htmlspecialchars($randomBg) ?>');
        background-size: cover; /* Cover - wypełnia cały ekran */
        background-position: center center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        opacity: 1.0; /* Przezroczystość - zwiększ do 0.2-0.3 jeśli chcesz wyraźniejsze */
        transition: opacity 0.3s ease;
        transform: scale(1.0); /* Możesz zwiększyć do 1.1 lub 1.2 aby bardziej powiększyć */
    }

    /* Gradient overlay */
    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: -1;
    }

    /* Kontener główny również musi mieć wyższy z-index */
    .container {
        position: relative;
        z-index: 1;
    }

    /* Zwiększ kontrast dla głównych kontenerów */
    .container,
    .page-content,
    .home-hero,
    .home-sidebar-section,
    .page-list-item,
    .external-link-card,
    .discord-widget,
    .admin-section,
    .card,
    .wiki-card,
    .comment-item {
        backdrop-filter: blur(10px);
    }
    .container {
       opacity:0.95;
    }

    /* Responsywność */
    @media (max-width: 768px) {
        :root {
            --header-height: 60px;
        }
    }
</style>
<?php endif; ?>
