<?php
// Dynamiczne ustawienia motywu
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
                    <span class="logo-icon">📖</span>
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
               <?php endif; ?>     
                    
                </a>

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

        // Toggle obecne
        $menu.toggleClass('active');
    });

    // Zamknij przy kliknięciu poza
    $(document).on('click', function() {
        $('.dropdown-menu').removeClass('active');
    });

    // Mobile menu
    $('#mobile-toggle').on('click', function() {
        $(this).toggleClass('active');
        $('.main-nav').toggleClass('mobile-active');
    });
});
</script>
