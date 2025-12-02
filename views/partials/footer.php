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

<style>
/* ========================================
   STOPKA / FOOTER - POZIOMA
======================================== */
.wiki-footer {
    background: rgba(10, 0, 30, 0.95);
    border-top: 2px solid rgba(139, 92, 246, 0.4);
    margin-top: 80px;
    padding: 40px 0 20px 0;
    backdrop-filter: blur(10px);
    width: 100%;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 30px;
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* 4 równe kolumny */
    gap: 40px;
    margin-bottom: 30px;
}

.footer-section h4 {
    color: #a78bfa;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid rgba(139, 92, 246, 0.3);
}

.footer-section p {
    color: #d1d5ff;
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}

.footer-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-section ul li {
    margin: 10px 0;
}

.footer-section ul li a {
    color: #c4b5fd;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s;
    display: inline-block;
}

.footer-section ul li a:hover {
    color: #fff;
    transform: translateX(5px);
    text-shadow: 0 0 10px rgba(139, 92, 246, 0.6);
}

.footer-bottom {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 30px 0 30px;
    border-top: 1px solid rgba(139, 92, 246, 0.2);
    text-align: center;
}

.footer-bottom p {
    color: #a78bfa;
    font-size: 13px;
    margin: 0;
}

.footer-bottom a {
    color: #818cf8;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-bottom a:hover {
    color: #a78bfa;
}

/* Responsywność */
@media (max-width: 1024px) {
    .footer-content {
        grid-template-columns: repeat(2, 1fr); /* 2 kolumny na tabletach */
    }
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr; /* 1 kolumna na mobile */
        gap: 30px;
    }
    
    .wiki-footer {
        margin-top: 50px;
    }
}

</style>