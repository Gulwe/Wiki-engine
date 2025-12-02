<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historia: <?= htmlspecialchars($page['title']) ?> - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>📜 Historia: <?= htmlspecialchars($page['title']) ?></h1>
            <div class="page-actions">
                <a href="/page/<?= $page['slug'] ?>" class="btn">👁️ Widok</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/page/<?= $page['slug'] ?>/edit" class="btn">✏️ Edytuj</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="revisions-list">
            <?php if (empty($revisions)): ?>
                <p class="info">Brak historii dla tej strony.</p>
            <?php else: ?>
                <?php 
                $totalRevisions = count($revisions);
                foreach ($revisions as $index => $revision): 
                    $versionNumber = $totalRevisions - $index;
                ?>
                    <div class="revision-item">
                        <div class="revision-header">
                            <strong>Wersja #<?= $versionNumber ?></strong>
                            <?php if ($revision['revision_id'] == $page['current_revision_id']): ?>
                                <span class="badge-current">AKTUALNA</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="revision-meta">
                            👤 <?= htmlspecialchars($revision['author'] ?? 'Nieznany') ?> | 
                            🕐 <?= date('d.m.Y H:i:s', strtotime($revision['created_at'])) ?>
                            <?php if ($revision['revision_comment']): ?>
                                <br>💬 <em><?= htmlspecialchars($revision['revision_comment']) ?></em>
                            <?php endif; ?>
                        </div>
                        
                        <div class="revision-actions">
                            <a href="/page/<?= $page['slug'] ?>/revision/<?= $revision['revision_id'] ?>" class="btn-small">👁️ Zobacz</a>
                            <?php if ($revision['revision_id'] != $page['current_revision_id'] && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a href="/page/<?= $page['slug'] ?>/restore/<?= $revision['revision_id'] ?>" 
                                   class="btn-small" 
                                   onclick="return confirm('Przywrócić wersję #<?= $versionNumber ?> jako aktualną?')">
                                    ↩️ Przywróć
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
