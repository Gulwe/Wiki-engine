<?php
// views/pages/history.php - TYLKO TREŚĆ
?>

<div class="history-page">
    <header class="page-header">
        <h1>📜 Historia: <?= htmlspecialchars($page['title']) ?></h1>
        <a href="/page/<?= htmlspecialchars($page['slug']) ?>" class="btn btn-outline">← Powrót do strony</a>
    </header>

    <?php if (empty($revisions)): ?>
        <p class="info">Brak historii zmian.</p>
    <?php else: ?>
        <div class="revisions-list">
            <?php foreach ($revisions as $rev): ?>
                <div class="revision-item">
                    <div class="revision-meta">
                        <strong>Rewizja #<?= $rev['revision_id'] ?></strong>
                        <span class="revision-date">
                            📅 <?= date('d.m.Y H:i', strtotime($rev['created_at'])) ?>
                        </span>
                        <span class="revision-author">
                            👤 <?= htmlspecialchars($rev['author'] ?? 'Nieznany') ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($rev['revision_comment'])): ?>
                        <div class="revision-comment">
                            💬 <?= htmlspecialchars($rev['revision_comment']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="revision-actions">
                        <a href="/page/<?= $page['slug'] ?>/revision/<?= $rev['revision_id'] ?>" class="btn btn-sm">
                            👁️ Podgląd
                        </a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="/page/<?= $page['slug'] ?>/restore/<?= $rev['revision_id'] ?>" 
                               class="btn btn-sm btn-primary"
                               onclick="return confirm('Czy na pewno przywrócić tę wersję?')">
                                ↩️ Przywróć
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
