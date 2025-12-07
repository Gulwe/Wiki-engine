<?php
// views/admin/links.php
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zewnętrzne linki - Admin</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container">
        <h1>🔗 Zewnętrzne linki</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">
                <?php if ($_GET['success'] == 'added'): ?>
                    ✅ Link dodany pomyślnie!
                <?php elseif ($_GET['success'] == 'deleted'): ?>
                    ✅ Link usunięty.
                <?php elseif ($_GET['success'] == 'moved'): ?>
                    ✅ Kolejność zaktualizowana.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert-error">
                ❌ Błąd: wypełnij wszystkie pola.
            </div>
        <?php endif; ?>

        <!-- Formularz dodawania -->
        <div class="admin-section">
            <h3>➕ Dodaj nowy link</h3>
            <form method="POST" action="/admin/links/add" class="admin-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Tytuł *</label>
                        <input type="text" name="title" required placeholder="np. Gameplay na YouTube">
                    </div>
                    <div class="form-group">
                        <label>URL *</label>
                        <input type="url" name="url" required placeholder="https://...">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Źródło</label>
                        <input type="text" name="source" placeholder="np. YouTube, ModDB, Forum">
                    </div>
                    <div class="form-group">
                        <label>Ikona</label>
                        <select name="icon" style="padding:14px;background:rgba(30,0,60,0.5);border:2px solid rgba(139,92,246,0.3);color:#e0e0ff;border-radius:10px;width:100%;font-family:inherit;">
                            <option value="🔗">🔗 Link ogólny</option>
                            <option value="🎥">🎥 YouTube</option>
                            <option value="🎮">🎮 ModDB / Gra</option>
                            <option value="💬">💬 Forum / Discord</option>
                            <option value="📰">📰 Artykuł / News</option>
                            <option value="🛠️">🛠️ Narzędzie</option>
                            <option value="📦">📦 Download</option>
                            <option value="🌐">🌐 Strona WWW</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Opis (opcjonalnie)</label>
                    <textarea name="description" rows="2" placeholder="Krótki opis..."></textarea>
                </div>
                <button type="submit" class="btn">➕ Dodaj link</button>
            </form>
        </div>

        <!-- Lista linków -->
        <div class="admin-section" style="margin-top: 40px;">
            <h2>📋 Wszystkie linki</h2>
            <?php if (empty($links)): ?>
                <p class="info">Brak linków. Dodaj pierwszy!</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ikona</th>
                            <th>Tytuł</th>
                            <th>URL</th>
                            <th>Źródło</th>
                            <th>Widoczny</th>
                            <th>Dodał</th>
                            <th>Data</th>
                            <th>Kolejność</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($links as $link): ?>
                            <tr>
                                <td style="font-size:24px;"><?= htmlspecialchars($link['icon'] ?? '🔗') ?></td>
                                <td><strong><?= htmlspecialchars($link['title']) ?></strong></td>
                                <td>
                                    <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank" style="font-size:12px;color:#818cf8;">
                                        <?= htmlspecialchars(substr($link['url'], 0, 50)) ?><?= strlen($link['url']) > 50 ? '...' : '' ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($link['source'] ?? '-') ?></td>
                                <td>
                                    <?php if ($link['is_visible']): ?>
                                        <span style="color:#4ade80;">✅ Tak</span>
                                    <?php else: ?>
                                        <span style="color:#fca5a5;">❌ Nie</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($link['author'] ?? 'Nieznany') ?></td>
                                <td><?= date('d.m.Y', strtotime($link['added_at'])) ?></td>
                                <td>
                                    <div style="display:flex;flex-direction:column;gap:4px;align-items:center;">
                                        <a href="/admin/links/move/up/<?= $link['link_id'] ?>" 
                                           class="btn-small" 
                                           title="Wyżej">▲</a>
                                        <a href="/admin/links/move/down/<?= $link['link_id'] ?>" 
                                           class="btn-small" 
                                           title="Niżej">▼</a>
                                    </div>
                                </td>
                                <td>
                                    <a href="/admin/links/toggle/<?= $link['link_id'] ?>" class="btn-small">
                                        <?= $link['is_visible'] ? '👁️ Ukryj' : '👁️‍🗨️ Pokaż' ?>
                                    </a>
                                    <a href="/admin/links/delete/<?= $link['link_id'] ?>" 
                                       class="btn-small btn-danger"
                                       onclick="return confirm('Usunąć ten link?');">
                                        🗑️ Usuń
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px;">
            <a href="/admin" class="btn">⬅️ Panel admina</a>
        </div>
    </div>
</body>
</html>
