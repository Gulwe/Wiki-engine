<footer class="wiki-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h4>📚 <?= htmlspecialchars(ThemeLoader::get('site_name', 'Wiki Engine')) ?></h4>
            <p><?= htmlspecialchars(ThemeLoader::get('site_description', 'Twoja wiedza w jednym miejscu')) ?></p>
        </div>
        
        <div class="footer-section">
            <h4>🔗 Szybkie linki</h4>
            <ul>
                <li><a href="/">🏠 Strona główna</a></li>
                <li><a href="/categories">📂 Kategorie</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/page/new">✏️ Nowa strona</a></li>
                    <li><a href="/media">🖼️ Galeria</a></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4>ℹ️ Statystyki</h4>
            <ul>
                <?php
                try {
                    $db = Database::getInstance()->getConnection();
                    $stats = $db->query("SELECT 
                        (SELECT COUNT(*) FROM pages) as total_pages,
                        (SELECT COUNT(*) FROM users) as total_users,
                        (SELECT COUNT(*) FROM categories) as total_categories
                    ")->fetch();
                    ?>
                    <li>📄 Stron: <?= $stats['total_pages'] ?></li>
                    <li>👥 Użytkowników: <?= $stats['total_users'] ?></li>
                    <li>📂 Kategorii: <?= $stats['total_categories'] ?></li>
                <?php } catch (Exception $e) { ?>
                    <li>Statystyki niedostępne</li>
                <?php } ?>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4>⚙️ Panel</h4>
            <ul>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="/admin">🔧 Panel admina</a></li>
                        <li><a href="/analytics">📊 Statystyki</a></li>
                        <li><a href="/admin/customize">🎨 Personalizuj</a></li>
                    <?php endif; ?>
                    <li><a href="/logout">🚪 Wyloguj się</a></li>
                <?php else: ?>
                    <li><a href="/login">🔑 Zaloguj się</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>
© 2012-<?= date('Y') ?> SoSteam - Wszelkie prawa zastrzeżone. + Powered by "<?= htmlspecialchars(ThemeLoader::get('site_name', 'Wiki Engine')) ?>"

        </p>
    </div>
</footer>