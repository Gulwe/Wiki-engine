<?php
// views/login.php - TYLKO TREŚĆ
?>

<div class="login-container">
    <div class="login-card">
        <h1 class="login-title">🔐 Logowanie</h1>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php if ($_GET['error'] === 'invalid'): ?>
                    ❌ Nieprawidłowa nazwa użytkownika lub hasło
                <?php elseif ($_GET['error'] === 'banned'): ?>
                    🚫 Twoje konto zostało zablokowane
                <?php else: ?>
                    ❌ Wystąpił błąd podczas logowania
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                ✅ Konto zostało utworzone! Możesz się teraz zalogować.
            </div>
        <?php endif; ?>

        <form method="POST" action="/login" class="login-form">
            <div class="form-group">
                <label for="username">Nazwa użytkownika</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Hasło</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Zaloguj się</button>
        </form>

        <div class="login-footer">
            <a href="/">← Powrót na stronę główną</a>
        </div>
    </div>
</div>
