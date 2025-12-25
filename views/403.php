<?php include __DIR__ . '/partials/background.php'; ?>
<div class="error-page">
    <div class="error-content">
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Brak dostępu</h2>
        <p class="error-description">
            Nie masz uprawnień do przeglądania tej strony.
        </p>
        <div class="error-actions">
            <button onclick="goBack()" class="btn btn-back">⬅️ Wróć</button>
            <a href="/" class="btn btn-primary">🏠 Strona główna</a>
        </div>
    </div>
</div>

<style>
.error-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.error-content {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 24px;
    padding: 4rem 3rem;
    text-align: center;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    max-width: 500px;
    width: 100%;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.error-code {
    font-size: clamp(4rem, 12vw, 8rem);
    font-weight: 800;
    background: rgba(239, 68, 68, 1.0);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 1rem 0;
    letter-spacing: -0.05em;
}

.error-title {
    font-size: clamp(1.5rem, 4vw, 2.5rem);
    font-weight: 700;
    color: #2d3748;
    margin: 0 0 1.5rem 0;
}

.error-description {
    color: #718096;
    font-size: 1.125rem;
    line-height: 1.6;
    margin: 0 0 2.5rem 0;
}

.error-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-back {
    background: #6b7280;
    color: white;
}

.btn-back:hover {
    background: #4b5563;
    transform: translateY(-2px);
}

.btn-primary {
    background: rgba(0, 0, 0, 1.0);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
}
</style>

<script>
function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/';
    }
}
</script>
