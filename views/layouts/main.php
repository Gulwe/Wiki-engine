<?php
// views/layouts/main.php
require_once __DIR__ . '/../../core/ThemeLoader.php';

$siteName = ThemeLoader::get('site_name', 'Wiki Engine');

// Wykryj aktualną stronę po URL lub przekazanej zmiennej
$currentPage = $currentPage ?? basename($_SERVER['REQUEST_URI'], '.php');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? htmlspecialchars($siteName) ?></title>
    <link rel="icon" type="image/x-icon" href="/css/favicon.ico"> 

    <!-- CSS bazowe -->
    <link rel="stylesheet" href="/css/base.css">
    <link rel="stylesheet" href="/css/components.css"> 
    <link rel="stylesheet" href="/css/layout.css">
    <link rel="stylesheet" href="/css/wiki.css">
    <link rel="stylesheet" href="/css/admin.css">
    
    <!-- Motywy -->
    <link rel="stylesheet" href="/css/themes/sos.css">
    <link rel="stylesheet" href="/css/themes/ru.css">
    <link rel="stylesheet" href="/css/themes/zsi.css">
    <link rel="stylesheet" href="/css/themes/am.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom CSS (jeśli przekazane) -->
    <?php if (!empty($customCSS)): ?>
        <style><?= $customCSS ?></style>
    <?php endif; ?>
</head>
<body class="<?= htmlspecialchars($currentPage ?? 'page-default') ?>">
    <!-- GLOBALNE TŁO -->
    <?php include __DIR__ . '/../partials/background.php'; ?>

    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="container">
        <?php
        // Renderuj content
        if (!isset($content)) {
            echo "<h1 style='color:red;'>ERROR: \$content NOT SET!</h1>";
        } elseif (empty($content)) {
            echo "<h1 style='color:red;'>ERROR: \$content IS EMPTY!</h1>";
        } else {
            echo $content;
        }
        ?>
    </div>
    
    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <!-- Default JS -->
    <script src="/js/search.js"></script>
    <script src="/js/wiki.js"></script>
    
    <!-- Custom JS (jeśli przekazane) -->
    <?php if (!empty($customJS)): ?>
        <script src="<?= htmlspecialchars($customJS) ?>"></script>
    <?php endif; ?>
    
    <!-- Inline JS (jeśli przekazane) -->
    <?php if (!empty($inlineJS)): ?>
        <script><?= $inlineJS ?></script>
    <?php endif; ?>

<script>
// Ignoruj błędy rozszerzeń Chrome
window.addEventListener('unhandledrejection', function(event) {
    if (event.reason && event.reason.message && 
        event.reason.message.includes('message channel closed')) {
        event.preventDefault();
        return;
    }
});
</script>

</body>
</html>
