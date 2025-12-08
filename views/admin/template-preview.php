<?php /** @var array $template */ ?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Podgląd szablonu: <?= htmlspecialchars($template['name']) ?> - Wiki Engine</title>
    <link rel="stylesheet" href="/css/base.css">
    <link rel="stylesheet" href="/css/components.css">
    <link rel="stylesheet" href="/css/layout.css">
    <link rel="stylesheet" href="/css/wiki.css">
    <link rel="stylesheet" href="/css/admin.css">
    <?= ThemeLoader::generateCSS(); ?>
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>🧩 Podgląd szablonu: <?= htmlspecialchars($template['name']) ?></h1>
        <div class="page-actions">
            <a href="/admin/templates" class="btn-small">⬅ Wróć do listy</a>
            <a href="/admin/templates/edit/<?= (int)$template['template_id'] ?>" class="btn-small">
                ✏️ Edytuj
            </a>
        </div>
    </div>

    <div class="admin-section">
        <table class="info-table">
            <tr>
                <td><strong>ID</strong></td>
                <td>#<?= (int)$template['template_id'] ?></td>
            </tr>
            <tr>
                <td><strong>Nazwa</strong></td>
                <td><?= htmlspecialchars($template['name']) ?></td>
            </tr>
            <tr>
                <td><strong>Klucz</strong></td>
                <td>de><?= htmlspecialchars($template['slug']) ?></code></td>
            </tr>
            <tr>
                <td><strong>Ostatnia aktualizacja</strong></td>
                <td><?= htmlspecialchars($template['updated_at']) ?></td>
            </tr>
        </table>
    </div>

    <div class="admin-section">
        <h2>📄 Zawartość szablonu</h2>
        <div class="code-block">
            <e><?= htmlspecialchars($template['content']) ?></code>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
