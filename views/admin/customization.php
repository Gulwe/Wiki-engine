<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customizacja - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="container">
        <h1>🎨 Customizacja CSS/JS</h1>
        
        <div class="admin-nav">
            <a href="/admin" class="btn">📊 Dashboard</a>
            <a href="/admin/users" class="btn">👥 Użytkownicy</a>
            <a href="/admin/categories" class="btn">📁 Kategorie</a>
            <a href="/admin/customization" class="btn active">🎨 Customizacja</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">✓ Zapisano!</div>
        <?php endif; ?>
        
        <div class="admin-section">
            <h2>🎨 Custom CSS</h2>
            <form method="POST" action="/admin/customization/save" id="css-form">
                <input type="hidden" name="type" value="css">
                <textarea name="content" id="custom-css" rows="15" placeholder="/* Twój custom CSS */"><?= htmlspecialchars($customCSS ?? '') ?></textarea>
                <button type="submit" class="btn">💾 Zapisz CSS</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>⚡ Custom JavaScript</h2>
            <form method="POST" action="/admin/customization/save" id="js-form">
                <input type="hidden" name="type" value="js">
                <textarea name="content" id="custom-js" rows="15" placeholder="// Twój custom JavaScript"><?= htmlspecialchars($customJS ?? '') ?></textarea>
                <button type="submit" class="btn">💾 Zapisz JavaScript</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>💡 Przykłady</h2>
            <div class="examples">
                <h3>CSS - Zmiana kolorów:</h3>
                <pre><code>/* Zmień kolor tła */
body {
    background: #000033;
}

/* Zmień kolor linków */
a {
    color: #00ccff;
}

/* Custom header */
header {
    background: linear-gradient(90deg, #001a00, #003300);
}</code></pre>
                
                <h3>JavaScript - Animacje:</h3>
                <pre><code>// Animacja przywitania
$(document).ready(function() {
    $('h1').hide().fadeIn(1000);
    
    // Dodaj efekt do przycisków
    $('.btn').hover(
        function() { $(this).css('transform', 'scale(1.05)'); },
        function() { $(this).css('transform', 'scale(1)'); }
    );
});</code></pre>
            </div>
        </div>
    </div>
</body>
</html>
