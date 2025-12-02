<header>
    <nav>
        <a href="/" class="logo">📖 Wiki Engine</a>
        
        <div class="search-box">
            <input type="text" id="search-input" placeholder="🔍 Szukaj..." autocomplete="off">
            <div id="search-results"></div>
        </div>
        
<div class="nav-links">
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="/admin">⚙️ Admin</a>
        <?php endif; ?>
        <a href="/categories">📁 Kategorie</a>
        <a href="/media">🖼️ Galeria</a>
        <a href="/page/new">➕ Nowa strona</a>
        <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="/logout">Wyloguj</a>
    <?php else: ?>
        <a href="/categories">📁 Kategorie</a>
        <a href="/login">Zaloguj</a>
    <?php endif; ?>
</div>



    </nav>
</header>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/js/search.js"></script>

