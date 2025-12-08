<?php
require_once __DIR__ . '/../../core/WikiParser.php';
$parser = new WikiParser();
// views/admin/templates.php
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szablony stron - Wiki Engine</title>

    <link rel="stylesheet" href="/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/components.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/layout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/wiki.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/admin.css?v=<?= time() ?>">

    <?= ThemeLoader::generateCSS(); ?>
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <h1>🧩 Szablony stron</h1>

    <div class="admin-nav">
        <a href="/admin" class="btn">📊 Dashboard</a>
        <a href="/admin/users" class="btn">👥 Użytkownicy</a>
        <a href="/admin/categories" class="btn">📁 Kategorie</a>
        <a href="/admin/customization" class="btn">🎨 Customizacja</a>
        <a href="/admin/templates" class="btn active">🧩 Szablony</a>
    </div>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert-success">
            ✅ Operacja na szablonie zakończona pomyślnie.
        </div>
    <?php endif; ?>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert-error">
            ⚠️ Błąd: <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <!-- Formularz dodawania / edycji głównego szablonu (jeśli taki masz) -->
    <div class="admin-section">
        <h2>➕ Dodaj nowy szablon</h2>
        <form method="post" action="/admin/templates/save" class="admin-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Nazwa szablonu</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Klucz (slug)</label>
                    <input type="text" name="slug" required placeholder="np. de-game_mod">
                </div>
            </div>

            <div class="form-group">
                <label>Treść szablonu</label>
                <textarea name="content" rows="8" class="custom-css" placeholder="Wpisz treść szablonu..."></textarea>
            </div>

            <button type="submit" class="btn">💾 Zapisz szablon</button>
        </form>
    </div>

    <!-- Lista istniejących szablonów -->
    <div class="admin-section">
        <h2>📚 Istniejące szablony (<?= count($templates ?? []) ?>)</h2>

        <?php if (empty($templates)): ?>
            <p class="info">Brak zdefiniowanych szablonów.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nazwa</th>
                        <th>Klucz</th>
                        <th>Zaktualizowano</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($templates as $tpl): ?>
                    <tr>
                        <td><?= (int)$tpl['template_id'] ?></td>
                        <td><?= htmlspecialchars($tpl['name']) ?></td>
                        <td><code><?= htmlspecialchars($tpl['slug']) ?></code></td>
                        <td><?= htmlspecialchars($tpl['updated_at'] ?? '') ?></td>
                        <td>
                            <!-- PODGLĄD -->
                            <button type="button"
                                    class="btn-small"
                                    onclick="toggleTemplatePreview(<?= (int)$tpl['template_id'] ?>)">
                                👁️ Podgląd
                            </button>

                            <!-- EDYTUJ (jak miałeś) -->
                            <button type="button"
                                    class="btn-small"
                                    onclick="toggleTemplateForm(<?= (int)$tpl['template_id'] ?>)">
                                ✏️ Edytuj
                            </button>
                        </td>
                    </tr>


<!-- Rząd z podglądem szablonu (domyślnie ukryty) -->
<tr id="tpl-preview-<?= (int)$tpl['template_id'] ?>" style="display:none;">
    <td colspan="5">
        <div class="admin-section" style="margin-top:10px;">
            <h3>📄 Podgląd: <?= htmlspecialchars($tpl['name']) ?></h3>
            <div class="page-content">
                <?= $parser->parse($tpl['content']); ?>
            </div>
        </div>
    </td>
</tr>


                    <!-- Rząd z formularzem edycji, jeśli już taki miałeś (opcjonalnie) -->
                    <tr id="tpl-form-<?= (int)$tpl['template_id'] ?>" style="display:none;">
                        <td colspan="5">
                            <div class="admin-section" style="margin-top:10px;">
                                <h3>✏️ Edycja szablonu: <?= htmlspecialchars($tpl['name']) ?></h3>
                                <form method="post" action="/admin/templates/save" class="admin-form">
                                    <input type="hidden" name="template_id" value="<?= (int)$tpl['template_id'] ?>">

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Nazwa szablonu</label>
                                            <input type="text" name="name"
                                                   value="<?= htmlspecialchars($tpl['name']) ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Klucz (slug)</label>
                                            <input type="text" name="slug"
                                                   value="<?= htmlspecialchars($tpl['slug']) ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Treść szablonu</label>
                                        <textarea name="content" rows="8" class="custom-css"><?= htmlspecialchars($tpl['content']) ?></textarea>
                                    </div>

                                    <button type="submit" class="btn">💾 Zapisz zmiany</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<script>
function toggleTemplatePreview(id) {
    const row = document.getElementById('tpl-preview-' + id);
    if (!row) return;
    row.style.display = (row.style.display === 'none' || row.style.display === '')
        ? 'table-row'
        : 'none';
}

function toggleTemplateForm(id) {
    const row = document.getElementById('tpl-form-' + id);
    if (!row) return;
    row.style.display = (row.style.display === 'none' || row.style.display === '')
        ? 'table-row'
        : 'none';
}
</script>
</body>
</html>
