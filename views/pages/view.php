<?php
require_once __DIR__ . '/../../core/WikiParser.php';
require_once __DIR__ . '/../../core/ThemeLoader.php';

$parser   = new WikiParser();
$siteName = ThemeLoader::get('site_name', 'Wiki Engine');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page['title']) ?> - <?= htmlspecialchars($siteName) ?></title>

    <!-- Główny CSS + motyw -->
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <?= ThemeLoader::generateCSS(); ?>
</head>
<body>

<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <?php if (isset($_GET['restored'])): ?>
        <div class="alert alert-success">
            <span class="alert-icon">✅</span>
            <div class="alert-content">
                <strong>Rewizja została przywrócona!</strong>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($page['is_old_revision'])): ?>
        <div class="revision-warning">
            ⚠️ <strong>To jest starsza wersja tej strony</strong>
            z dnia <?= date('d.m.Y H:i', strtotime($page['revision_date'])) ?>
            (autor: <?= htmlspecialchars($page['revision_author']) ?>)
            <br>
            <a href="/page/<?= $page['slug'] ?>">← Wróć do aktualnej wersji</a>
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                | <a href="/page/<?= $page['slug'] ?>/restore/<?= $page['current_revision_id_display'] ?>"
                     onclick="return confirm('Przywrócić tę wersję jako aktualną?')">↩️ Przywróć tę wersję</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <div>
            <h1><?= htmlspecialchars($page['title']) ?></h1>

            <?php
            // Kategorie strony
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT c.* FROM categories c
                JOIN page_categories pc ON c.category_id = pc.category_id
                WHERE pc.page_id = :page_id
                ORDER BY c.name ASC
            ");
            $stmt->execute(['page_id' => $page['page_id']]);
            $pageCategories = $stmt->fetchAll();
            ?>

            <?php if (!empty($pageCategories)): ?>
                <div class="page-categories">
                    <?php foreach ($pageCategories as $cat): ?>
                        <a href="/category/<?= $cat['category_id'] ?>" class="category-badge">
                            📁 <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="page-actions">
            <?php if (!empty($_SESSION['user_id']) && (empty($page['is_old_revision']) || !$page['is_old_revision'])): ?>
                              <?php if (!empty($_SESSION['role']) && $_SESSION['role'] !== 'viewer'): ?>
                <a href="/page/<?= $page['slug'] ?>/edit" class="btn">✏️ Edytuj</a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="/page/<?= $page['slug'] ?>/history" class="btn">📜 Historia</a>

            <?php endif; ?>

            <a href="#" onclick="window.print(); return false;"
               class="btn"
               style="background: rgba(74, 222, 128, 0.2); border-color: #4ade80;">🖨️ Drukuj</a>
            <a href="/" class="btn">🏠 Strona główna</a>
        </div>
    </div>

    <div class="page-content">
        <?php if (!empty($page['content'])): ?>
            <?= $parser->parse($page['content']) ?>
        <?php else: ?>
            <p class="info">
                Ta strona jest pusta.
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a href="/page/<?= $page['slug'] ?>/edit">Dodaj treść!</a>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="page-meta">
        <small>
            <?php if (!empty($page['is_old_revision'])): ?>
                📝 Wersja z:
                <?= date('d.m.Y H:i', strtotime($page['revision_date'])) ?>
                |
                👤 Autor tej wersji: <?= htmlspecialchars($page['revision_author']) ?>
                <?php if (!empty($page['revision_comment'])): ?>
                    | 💬 <?= htmlspecialchars($page['revision_comment']) ?>
                <?php endif; ?>
            <?php else: ?>
                📝 Autor:
                <?= htmlspecialchars($page['author'] ?? 'Nieznany') ?>
                |
                🕐 Ostatnia modyfikacja:
                <?= date('d.m.Y H:i', strtotime($page['last_modified'] ?? $page['created_at'])) ?>
                |
                👁️ Wyświetleń:
                <strong><?= number_format($page['views'] ?? 0) ?></strong>
            <?php endif; ?>
        </small>
    </div>

    <?php include __DIR__ . '/../partials/comments.php'; ?>
    <?php include __DIR__ . '/../partials/related-pages.php'; ?>
</div>

<div class="breadcrumbs">
    <a href="/">🏠 Strona główna</a>
    <span class="separator">›</span>

    <?php if (!empty($pageCategories)): ?>
        <?php foreach ($pageCategories as $cat): ?>
            <a href="/category/<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
            <span class="separator">›</span>
        <?php endforeach; ?>
    <?php endif; ?>

    <span class="current"><?= htmlspecialchars($page['title']) ?></span>
</div>

<?php if (!empty($_SESSION['user_id'])): ?>
    <div class="quick-actions-fab">
        <button class="fab-button" id="fab-toggle">⚡</button>
        <div class="fab-menu" id="fab-menu">
            <a href="/page/new" class="fab-item">➕ Nowa strona</a>
            <a href="/page/<?= $page['slug'] ?>/edit" class="fab-item">✏️ Edytuj</a>
            <a href="/media" class="fab-item">🖼️ Galeria</a>
            <a href="#" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;" class="fab-item">⬆️ Do góry</a>
        </div>
    </div>

    <script>
    $('#fab-toggle').on('click', function() {
        $('#fab-menu').toggleClass('active');
        $(this).toggleClass('active');
    });
    </script>
<?php endif; ?>

<!-- Skrypt do automatycznego spisu treści -->
<script>
// Auto-generuj spis treści
$(document).ready(function() {
    const tocList = $('#toc-list');

    if (tocList.length) {
        $('.page-content h2, .page-content h3').each(function(index) {
            const heading = $(this);
            const text = heading.text();
            let id = heading.attr('id');

            if (!id) {
                id = 'heading-' + index;
                heading.attr('id', id);
            }

            const level  = heading.prop('tagName');
            const indent = (level === 'H3') ? 'margin-left: 20px;' : '';

            tocList.append(
                '<li style="' + indent + '"><a href="#' + id + '">' + text + '</a></li>'
            );
        });

        if (tocList.children().length === 0) {
            $('#toc').hide();
        }
    }

    $('#toc').on('click', 'a', function(e) {
        e.preventDefault();
        const target = $(this).attr('href');
        $('html, body').animate({
            scrollTop: $(target).offset().top - 100
        }, 500);
    });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
