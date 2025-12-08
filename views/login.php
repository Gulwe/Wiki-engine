<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="login-page">
    <div class="login-card">
        <h1>🔐 Logowanie</h1>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error">❌ Nieprawidłowa nazwa użytkownika lub hasło</div>
        <?php endif; ?>
        
        <form method="POST" action="/login">
            <div class="form-group">
                <label for="username">Nazwa użytkownika:</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Hasło:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Zaloguj się</button>
        </form>
        
        <p class="login-back">
            <a href="/">← Powrót do strony głównej</a>
        </p>
    </div>
</body>
</html>
<style>

/* LOGIN – LAYOUT */

.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 16px;
    /* tło: ciemny gradient, niezależny od reszty */
    background: radial-gradient(circle at 0% 0%, #022233 0%, #010b10 55%, #00040a 100%);
}

/* Główny panel logowania */
.login-card {
    width: 100%;
    max-width: 420px;
    background: #03111b;
    border-radius: 18px;
    border: 1px solid #1f3a4d;
    box-shadow: 0 18px 60px rgba(0, 0, 0, 0.75);
    padding: 26px 28px 24px 28px;
    color: #e5f2ff;
}

/* Nagłówek */
.login-card h1 {
    font-size: 22px;
    margin: 0 0 18px 0;
    text-align: center;
    color: #c7ecff;
}

/* Komunikat błędu */
.login-card .error {
    background: rgba(239, 68, 68, 0.12);
    border: 1px solid #ef4444;
    color: #fecaca;
    padding: 8px 10px;
    border-radius: 10px;
    font-size: 13px;
    margin-bottom: 14px;
}

/* Pola formularza */
.login-card .form-group {
    margin-bottom: 16px;
}

.login-card label {
    display: block;
    font-size: 13px;
    margin-bottom: 6px;
    color: #cbd5f5;
}

.login-card input[type="text"],
.login-card input[type="password"] {
    width: 100%;
    padding: 9px 12px;
    border-radius: 10px;
    border: 1px solid #1e4a63;
    background: #020b12;
    color: #e5f2ff;
    font-size: 14px;
    outline: none;
}

.login-card input::placeholder {
    color: #6b7280;
}

.login-card input:focus {
    border-color: #1fb5ff;
    box-shadow: 0 0 0 1px rgba(31, 181, 255, 0.45);
}

/* Przycisk logowania */
.login-card .btn {
    width: 100%;
    margin-top: 4px;
    padding: 9px 12px;
    border-radius: 999px;
    border: none;
    background: linear-gradient(135deg, #06b6d4, #0ea5e9);
    color: #f9fafb;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
}

.login-card .btn:hover {
    filter: brightness(1.08);
    box-shadow: 0 10px 30px rgba(14, 165, 233, 0.4);
    transform: translateY(-1px);
}

/* Link powrotu */
.login-back {
    margin-top: 16px;
    text-align: center;
    font-size: 12px;
}

.login-back a {
    color: #7dd3fc;
    text-decoration: none;
}

.login-back a:hover {
    text-decoration: underline;
}

/* Nadpisanie globalnego h1 tylko na loginie */
.login-card h1 {
    background: none !important;
    -webkit-background-clip: initial !important;
    -webkit-text-fill-color: initial !important;
    color: #e5f2ff !important; /* jasny, czytelny tekst */
}


/* Mobile */
@media (max-width: 480px) {
    .login-card {
        padding: 22px 18px 20px 18px;
    }
}


</style>