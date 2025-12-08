<?php
// views/user/profile.php

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

$badges = getUserBadges($profileUser);
$isSelf = !empty($_SESSION['user_id']) && $_SESSION['user_id'] == $profileUser['user_id'];
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Profil: <?= htmlspecialchars($profileUser['username']) ?> - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
    <?= ThemeLoader::generateCSS() ?>
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>👤 Profil użytkownika: <?= htmlspecialchars($profileUser['username']) ?></h1>
    </div>

    <div class="admin-section">
        <table class="info-table">
            <tr>
                <td><strong>ID użytkownika</strong></td>
                <td>#<?= (int)$profileUser['user_id'] ?></td>
            </tr>
            <tr>
                <td><strong>Nazwa</strong></td>
                <td>
                    <?= htmlspecialchars($profileUser['username']) ?>
                    <?php if ($isSelf): ?>
                        <span class="badge-you">TY</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong>Email</strong></td>
                <td><?= htmlspecialchars($profileUser['email']) ?></td>
            </tr>
            <tr>
                <td><strong>Rola</strong></td>
                <td>
                    <span class="badge-role badge-<?= $profileUser['role'] ?>">
                        <?= ucfirst($profileUser['role']) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Status</strong></td>
                <td>
                    <?php if (!empty($profileUser['is_banned'])): ?>
                        <span class="badge-status badge-banned">Zbanowany</span>
                    <?php else: ?>
                        <span class="badge-status badge-ok">Aktywny</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong>Dołączył</strong></td>
                <td><?= date('d.m.Y', strtotime($profileUser['created_at'])) ?></td>
            </tr>
        </table>
    </div>

    <div class="admin-section">
        <h2>🏅 Odznaki</h2>
        <?php if (empty($badges)): ?>
            <p class="info">Ten użytkownik nie ma jeszcze żadnych odznak.</p>
        <?php else: ?>
            <div class="user-badges">
                <?php foreach ($badges as $b): ?>
                    <span class="badge badge-<?= htmlspecialchars($b['color']) ?>">
                        <?= htmlspecialchars($b['label']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-section">
        <h2>📊 Aktywność</h2>
        <table class="info-table">
            <tr>
                <td><strong>Utworzone strony</strong></td>
                <td><?= (int)$profileUser['pages_created'] ?></td>
            </tr>
            <tr>
                <td><strong>Wszystkie edycje</strong></td>
                <td><?= (int)$profileUser['total_edits'] ?></td>
            </tr>
            <tr>
                <td><strong>Komentarze</strong></td>
                <td><?= (int)($profileUser['total_comments'] ?? 0) ?></td>
            </tr>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
