<?php
// views/admin/users.php

function getUserBadges(array $user): array
{
    $badges   = [];
    $pages    = (int)($user['pages_created']   ?? 0);
    $edits    = (int)($user['total_edits']     ?? 0);
    $comments = (int)($user['total_comments']  ?? 0);

    if ($edits >= 1)  $badges[] = ['label' => '1 edycja',       'color' => 'success'];
    if ($edits >= 10) $badges[] = ['label' => '10 edycji',      'color' => 'info'];
    if ($edits >= 50) $badges[] = ['label' => '50 edycji',      'color' => 'warning'];

    if ($pages >= 1)  $badges[] = ['label' => '1 strona',       'color' => 'success'];
    if ($pages >= 5)  $badges[] = ['label' => '5 stron',        'color' => 'info'];

    if ($comments >= 1)  $badges[] = ['label' => '1 komentarz',    'color' => 'success'];
    if ($comments >= 50) $badges[] = ['label' => '50 komentarzy',  'color' => 'warning'];

    return $badges;
}
?>
  
    <div class="container">
        <h1>👥 Zarządzanie Użytkownikami</h1>
        
        <div class="admin-nav">
            <a href="/admin" class="btn">📊 Dashboard</a>
            <a href="/admin/users" class="btn active">👥 Użytkownicy</a>
            <a href="/admin/categories" class="btn">📁 Kategorie</a>
            <a href="/admin/customization" class="btn">🎨 Customizacja</a>
            <a href="/admin/templates" class="btn">🧩 Szablony</a>
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
                        <th>Status</th>
                        <th>Odznaki</th>
                        <th>Stron</th>
                        <th>Edycji</th>
                        <th>Komentarzy</th>
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
                                <div>
                                    <a href="/user/<?= $user['user_id'] ?>" style="font-size:11px; color:#818cf8;">
                                        👁️ Zobacz profil
                                    </a>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge-role badge-<?= $user['role'] ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($user['is_banned'])): ?>
                                    <span class="badge-status badge-banned">Zbanowany</span>
                                <?php else: ?>
                                    <span class="badge-status badge-ok">Aktywny</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php $badges = getUserBadges($user); ?>
                                <?php if (empty($badges)): ?>
                                    <span style="color:#6b7280; font-size:11px;">Brak</span>
                                <?php else: ?>
                                    <div class="user-badges">
                                        <?php foreach ($badges as $b): ?>
                                            <span class="badge badge-<?= htmlspecialchars($b['color']) ?>">
                                                <?= htmlspecialchars($b['label']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?= $user['pages_created'] ?></td>
                            <td><?= $user['total_edits'] ?></td>
                            <td><?= $user['total_comments'] ?? 0 ?></td>
                            <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                                    <?php if (empty($user['is_banned'])): ?>
                                        <a href="/admin/users/ban/<?= $user['user_id'] ?>" 
                                           class="btn-small btn-danger" 
                                           onclick="return confirm('Zbanować użytkownika <?= htmlspecialchars($user['username']) ?>?\n\nNie będzie mógł się logować ani dodawać treści.');">
                                            🚫 Zbanuj
                                        </a>
                                    <?php else: ?>
                                        <a href="/admin/users/unban/<?= $user['user_id'] ?>" 
                                           class="btn-small"
                                           onclick="return confirm('Odbanować użytkownika <?= htmlspecialchars($user['username']) ?>?');">
                                            ✅ Odbanuj
                                        </a>
                                    <?php endif; ?>

                                    <a href="/admin/users/delete/<?= $user['user_id'] ?>" 
                                       class="btn-small btn-danger" 
                                       onclick="return confirm('Czy na pewno usunąć użytkownika <?= htmlspecialchars($user['username']) ?>?\n\nUsunięte zostaną:\n- Wszystkie utworzone strony\n- Wszystkie komentarze\n- Cała historia edycji');">
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
