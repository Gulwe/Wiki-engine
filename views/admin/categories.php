<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie Kategoriami - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="container">
        <h1>📁 Zarządzanie Kategoriami</h1>
        
        <div class="admin-nav">
            <a href="/admin" class="btn">📊 Dashboard</a>
            <a href="/admin/users" class="btn">👥 Użytkownicy</a>
            <a href="/admin/categories" class="btn active">📁 Kategorie</a>
            <a href="/admin/customization" class="btn">🎨 Customizacja</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">✓ Kategoria dodana!</div>
        <?php endif; ?>
        
        <div class="admin-section">
            <h2>➕ Dodaj Kategorię</h2>
            <form method="POST" action="/admin/categories/add" class="admin-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nazwa kategorii:</label>
                        <input type="text" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Opis:</label>
                        <input type="text" name="description" placeholder="Opcjonalny opis">
                    </div>
                </div>
                
                <button type="submit" class="btn">➕ Dodaj Kategorię</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>📋 Lista Kategorii (<?= count($categories) ?>)</h2>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nazwa</th>
                        <th>Opis</th>
                        <th>Liczba stron</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">Brak kategorii</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?= $cat['category_id'] ?></td>
                                <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                                <td><?= htmlspecialchars($cat['description'] ?? '-') ?></td>
                                <td><?= $cat['pages_count'] ?></td>
                                <td>
                                    <a href="#" class="btn-small">✏️ Edytuj</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
