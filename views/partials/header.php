<?php
// views/partials/header.php
require_once __DIR__ . '/../../core/ThemeLoader.php';

$siteName    = ThemeLoader::get('site_name', 'Wiki Engine');
$siteTagline = ThemeLoader::get('site_tagline', 'Twoja baza wiedzy');
$siteLogo    = ThemeLoader::get('site_logo', '');
?>

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
                        <a href="/categories" class="dropdown-item">
                            <span class="dropdown-icon">📁</span>
                            Kategorie
                        </a>
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
                        <img src="/symbols/soslogo.png" alt="Default">
                        <span>SoS</span>
                    </div>
                    <div class="theme-option" data-theme="am">
                        <img src="/symbols/AM.png" alt="AM">
                        <span>AM</span>
                    </div>
                    <div class="theme-option" data-theme="ru">
                        <img src="/symbols/RU.png" alt="RU">
                        <span>RU</span>
                    </div>
                    <div class="theme-option" data-theme="zsi">
                        <img src="/symbols/ZSI.png" alt="ZSI">
                        <span>ZSI</span>
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

    // ========================================
    // === CUSTOM THEME SWITCHER ===
    // ========================================
    const themeSelectorBtn = document.getElementById('theme-selector-btn');
    const themeDropdown = document.getElementById('theme-dropdown');
    const currentThemeIcon = document.getElementById('current-theme-icon');
    const themeOptions = document.querySelectorAll('.theme-option');
    const THEME_KEY = 'wiki-theme';

    // Mapa ikon motywów
    const themeIcons = {
        'default': '/symbols/soslogo.png',
        'ru': '/symbols/RU.png',
        'zsi': '/symbols/ZSI.png',
        'am': '/symbols/AM.png'
    };

    // Funkcja ustawiająca cookie
    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
    }

    // Funkcja odczytująca cookie
    function getCookie(name) {
        const nameEQ = name + '=';
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    // Funkcja aplikująca motyw
    function applyTheme(theme, reload = false) {
        console.log('Applying theme:', theme);
        
        // Ustaw atrybut data-theme
        if (theme === 'default') {
            document.documentElement.removeAttribute('data-theme');
        } else {
            document.documentElement.setAttribute('data-theme', theme);
        }
        
        // Zapisz do localStorage (dla JS)
        localStorage.setItem(THEME_KEY, theme);
        
        // Zapisz do cookie (dla PHP)
        setCookie('theme', theme, 365);
        
        // Zmień ikonkę w przycisku
        currentThemeIcon.src = themeIcons[theme] || themeIcons['default'];
        
        // Zamknij dropdown
        themeDropdown.classList.remove('active');
        
        if (reload) {
            location.reload();
        }
    }

    // Załaduj zapisany motyw przy starcie
    const savedTheme = getCookie('theme') || localStorage.getItem(THEME_KEY) || 'default';
    applyTheme(savedTheme, false);

    // Toggle dropdown
    themeSelectorBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        themeDropdown.classList.toggle('active');
        
        // Zamknij inne dropdowny
        $('.dropdown-menu').removeClass('active');
    });

    // Wybór motywu
    themeOptions.forEach(option => {
        option.addEventListener('click', function() {
            const theme = this.getAttribute('data-theme');
            applyTheme(theme, false);
        });
    });

    // Zamknij dropdown przy kliknięciu poza
    document.addEventListener('click', function() {
        themeDropdown.classList.remove('active');
    });

    // Zapobiegaj zamknięciu przy kliknięciu wewnątrz dropdown
    themeDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});
</script>
