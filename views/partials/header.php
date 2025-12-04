<?php
// Dynamiczne ustawienia motywu
require_once __DIR__ . '/../../core/ThemeLoader.php';

$siteName    = ThemeLoader::get('site_name', 'Wiki Engine');
$siteTagline = ThemeLoader::get('site_tagline', 'Twoja baza wiedzy');
$siteLogo    = ThemeLoader::get('site_logo', '');
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteName) ?></title>

    <!-- CSS w kolejności: base → komponenty → layout → wiki → admin → motywy -->
    <link rel="stylesheet" href="/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/components.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/layout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/wiki.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/admin.css?v=<?= time() ?>">
    
    <!-- Motywy -->
    <link rel="stylesheet" href="/css/themes/dark.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/themes/purple.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/themes/light.css?v=<?= time() ?>">

    <?= ThemeLoader::generateCSS(); ?>
</head>
<body>

<header class="modern-header">
    <div class="header-content">
        <!-- Logo -->
        <a href="/" class="logo">
            <?php if (!empty($siteLogo)): ?>
                <img src="<?= htmlspecialchars($siteLogo) ?>" alt="<?= htmlspecialchars($siteName) ?>">
            <?php else: ?>
                <div class="logo-wrapper">
                    <img src="/symbols/soslogo.png" alt="Logo" class="logo-icon">
                    <div class="logo-text">
                        <span class="logo-name"><?= htmlspecialchars($siteName) ?></span>
                        <?php if (!empty($siteTagline)): ?>
                            <small class="logo-tagline"><?= htmlspecialchars($siteTagline) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </a>

        <!-- Search Box -->
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" id="search-input" placeholder="Szukaj w Wiki..." autocomplete="off">
            <div id="search-results"></div>
        </div>

        <!-- Navigation -->
        <nav class="main-nav">
            <a href="/" class="nav-item">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Strona główna</span>
            </a>

            <a href="/categories" class="nav-item">
                <span class="nav-icon">📁</span>
                <span class="nav-text">Kategorie</span>
            </a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (!empty($_SESSION['role']) && $_SESSION['role'] !== 'viewer'): ?>
                    <a href="/page/new" class="nav-item nav-highlight">
                        <span class="nav-icon">➕</span>                  
                        <span class="nav-text">Nowa strona</span>
                    </a>
                <?php endif; ?>

                <!-- Dropdown: Więcej -->
                <div class="nav-dropdown">
                    <button class="nav-item dropdown-toggle">
                        <span class="nav-icon">⚡</span>
                        <span class="nav-text">Więcej</span>
                        <span class="dropdown-arrow">▼</span>
                    </button>

                    <div class="dropdown-menu">
                        <a href="/media" class="dropdown-item">
                            <span class="dropdown-icon">🖼️</span>
                            Galeria
                        </a>
                        <a href="/syntax-help" class="dropdown-item">
                            <span class="dropdown-icon">📚</span>
                            Składnia
                        </a>

                        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <div class="dropdown-divider"></div>
                            <a href="/admin" class="dropdown-item">
                                <span class="dropdown-icon">⚙️</span>
                                Panel Admina
                            </a>
                            <a href="/analytics" class="dropdown-item">
                                <span class="dropdown-icon">📊</span>
                                Analytics
                            </a>
                            <a href="/admin/customize" class="dropdown-item">
                                <span class="dropdown-icon">🎨</span>
                                Customizacja
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dropdown: Użytkownik -->
                <div class="nav-dropdown user-dropdown">
                    <button class="nav-item dropdown-toggle">
                        <span class="nav-icon">👤</span>
                        <span class="nav-text"><?= htmlspecialchars($_SESSION['username']) ?></span>
                        <span class="dropdown-arrow">▼</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right">
                        <div class="dropdown-header">
                            <strong>👤 <?= htmlspecialchars($_SESSION['username']) ?></strong>
                            <small><?= htmlspecialchars($_SESSION['role']) ?></small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="/logout" class="dropdown-item">
                            <span class="dropdown-icon">🚪</span>
                            Wyloguj
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/login" class="nav-item nav-login">
                    <span class="nav-icon">🔐</span>
                    <span class="nav-text">Zaloguj</span>
                </a>
            <?php endif; ?>

            <!-- Custom Theme Selector -->
            <div class="custom-theme-selector">
                <button class="theme-selector-btn" id="theme-selector-btn">
                    <img src="/symbols/AM.png" alt="Theme" id="current-theme-icon">
                </button>
                <div class="theme-dropdown" id="theme-dropdown">
                    <div class="theme-option" data-theme="default">
                        <img src="/symbols/AM.png" alt="Default">
                        <span>AM</span>
                    </div>
                    <div class="theme-option" data-theme="dark">
                        <img src="/symbols/RU.png" alt="Dark">
                        <span>RU</span>
                    </div>
                    <div class="theme-option" data-theme="purple">
                        <img src="/symbols/ZSI.png" alt="Purple">
                        <span>ZSI</span>
                    </div>
                    <div class="theme-option" data-theme="light">
                        <img src="/symbols/soslogo.png" alt="Light">
                        <span>SOS</span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobile-toggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<!-- JS (jQuery + skrypty wiki) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/js/search.js"></script>
<script src="/js/wiki.js"></script>

<script>
$(document).ready(function() {
    // Dropdown menu
    $('.dropdown-toggle').on('click', function(e) {
        e.stopPropagation();
        const $menu = $(this).siblings('.dropdown-menu');

        // Zamknij inne
        $('.dropdown-menu').not($menu).removeClass('active');
        $('.theme-dropdown').removeClass('active');

        // Toggle obecne
        $menu.toggleClass('active');
    });

    // Zamknij przy kliknięciu poza
    $(document).on('click', function() {
        $('.dropdown-menu').removeClass('active');
        $('.theme-dropdown').removeClass('active');
    });

    // Mobile menu
    $('#mobile-toggle').on('click', function() {
        $(this).toggleClass('active');
        $('.main-nav').toggleClass('mobile-active');
    });

    // Custom Theme Switcher
    const themeSelectorBtn = document.getElementById('theme-selector-btn');
    const themeDropdown = document.getElementById('theme-dropdown');
    const currentThemeIcon = document.getElementById('current-theme-icon');
    const themeOptions = document.querySelectorAll('.theme-option');
    const THEME_KEY = 'wiki-theme';

    // Mapa ikon motywów
    const themeIcons = {
        'default': '/symbols/AM.png',
        'dark': '/symbols/RU.png',
        'purple': '/symbols/ZSI.png',
        'light': '/symbols/soslogo.png'
    };

    // Funkcja aplikująca motyw
    function applyTheme(theme) {
        if (theme === 'default') {
            document.documentElement.removeAttribute('data-theme');
        } else {
            document.documentElement.setAttribute('data-theme', theme);
        }
        localStorage.setItem(THEME_KEY, theme);

        // Zmień ikonkę w przycisku
        currentThemeIcon.src = themeIcons[theme] || themeIcons['default'];
    }

    // Przy ładowaniu strony
    const savedTheme = localStorage.getItem(THEME_KEY) || 'default';
    applyTheme(savedTheme);

    // Toggle dropdown
    themeSelectorBtn.addEventListener('click', function(e) {
        e.stopPropagation();

        // Zamknij inne dropdowny
        $('.dropdown-menu').removeClass('active');

        // Toggle theme dropdown
        themeDropdown.classList.toggle('active');
    });

    // Wybór motywu
    themeOptions.forEach(option => {
        option.addEventListener('click', function() {
            const theme = this.getAttribute('data-theme');
            applyTheme(theme);
            themeDropdown.classList.remove('active');
        });
    });
});
</script>
