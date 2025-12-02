<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin-top: 100px;">
        <h1>🔐 Logowanie</h1>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error">❌ Nieprawidłowa nazwa użytkownika lub hasło</div>
        <?php endif; ?>
        
        <form method="POST" action="/login">
            <div class="form-group">
                <label>Nazwa użytkownika:</label>
                <input type="text" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Hasło:</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Zaloguj się</button>
        </form>
        
        <p style="margin-top: 20px; text-align: center;">
            <a href="/">← Powrót do strony głównej</a>
        </p>
    </div>
</body>
</html>
