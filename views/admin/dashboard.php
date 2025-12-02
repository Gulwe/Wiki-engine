<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admina - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="container">
        <h1>⚙️ Panel Administracyjny</h1>
        
        <div class="admin-nav">
            <a href="/admin" class="btn active">📊 Dashboard</a>
            <a href="/admin/users" class="btn">👥 Użytkownicy</a>
            <a href="/admin/categories" class="btn">📁 Kategorie</a>
            <a href="/admin/customize" class="btn">🎨 Customizacja</a>
            <a href="/analytics" class="btn">📊 Statystyki</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📄</div>
                <div class="stat-number"><?= $stats['pages'] ?></div>
                <div class="stat-label">Stron</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?= $stats['users'] ?></div>
                <div class="stat-label">Użytkowników</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-number"><?= $stats['revisions'] ?></div>
                <div class="stat-label">Edycji</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🖼️</div>
                <div class="stat-number"><?= $stats['media'] ?></div>
                <div class="stat-label">Obrazków</div>
            </div>
        </div>
        
        <div class="admin-section">
            <h2>🚀 Szybkie Akcje</h2>
            <div class="quick-actions">
                <a href="/page/new" class="action-card">
                    <span class="action-icon">➕</span>
                    <span>Nowa strona</span>
                </a>
                <a href="/media" class="action-card">
                    <span class="action-icon">📤</span>
                    <span>Upload obrazka</span>
                </a>
                <a href="/admin/users" class="action-card">
                    <span class="action-icon">👤</span>
                    <span>Dodaj użytkownika</span>
                </a>
                <a href="/admin/customization" class="action-card">
                    <span class="action-icon">🎨</span>
                    <span>Customizuj</span>
                </a>
            </div>
        </div>
        
        <div class="admin-section">
            <h2>ℹ️ Informacje Systemowe</h2>
            <table class="info-table">
                <tr>
                    <td><strong>PHP Version:</strong></td>
                    <td><?= phpversion() ?></td>
                </tr>
                <tr>
                    <td><strong>Zalogowany jako:</strong></td>
                    <td><?= htmlspecialchars($_SESSION['username']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</td>
                </tr>
                <tr>
                    <td><strong>Wersja Wiki:</strong></td>
                    <td>1.0.0</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
