<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie Użytkownikami - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="container">
        <h1>👥 Zarządzanie Użytkownikami</h1>
        
        <div class="admin-nav">
            <a href="/admin" class="btn">📊 Dashboard</a>
            <a href="/admin/users" class="btn active">👥 Użytkownicy</a>
            <a href="/admin/categories" class="btn">📁 Kategorie</a>
            <a href="/admin/customization" class="btn">🎨 Customizacja</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">✓ Operacja zakończona sukcesem!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert-error">✗ Wystąpił błąd: <?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        
        <div class="admin-section">
            <h2>➕ Dodaj Użytkownika</h2>
            <form method="POST" action="/admin/users/add" class="admin-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nazwa użytkownika:</label>
                        <input type="text" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Hasło:</label>
                        <input type="password" name="password" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label>Rola:</label>
                        <select name="role">
                            <option value="viewer">Viewer (Tylko czytanie)</option>
                            <option value="editor">Editor (Edycja)</option>
                            <option value="admin">Admin (Pełny dostęp)</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn">➕ Dodaj Użytkownika</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>📋 Lista Użytkowników (<?= count($users) ?>)</h2>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Użytkownik</th>
                        <th>Email</th>
                        <th>Rola</th>
                        <th>Stron</th>
                        <th>Edycji</th>
                        <th>Dołączył</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($user['username']) ?></strong>
                                <?php if ($user['user_id'] === $_SESSION['user_id']): ?>
                                    <span class="badge-you">TY</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge-role badge-<?= $user['role'] ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td><?= $user['pages_created'] ?></td>
                            <td><?= $user['total_edits'] ?></td>
                            <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                                    <a href="/admin/users/delete/<?= $user['user_id'] ?>" 
                                       class="btn-small btn-danger" 
                                       onclick="return confirm('Czy na pewno usunąć użytkownika <?= htmlspecialchars($user['username']) ?>?')">
                                        🗑️ Usuń
                                    </a>
                                <?php else: ?>
                                    <span style="color:#666;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
