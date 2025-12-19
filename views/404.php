<?php include __DIR__ . '/partials/background.php'; ?>
<div class="error-page">
    <div class="error-content">
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Strona nie znaleziona</h2>
        <p class="error-description">
            Ups! Strona, której szukasz nie istnieje lub została przeniesiona.
        </p>
        <div class="error-actions">
            <a href="/" class="btn btn-primary">🏠 Strona główna</a>
            <a href="/pages" class="btn btn-outline">📄 Wszystkie strony</a>
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
    background: rgba(0, 0, 0, 1.0);
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
    line-height: 1.2;
}

.error-description {
    color: #718096;
    font-size: 1.125rem;
    line-height: 1.6;
    margin: 0 0 2.5rem 0;
    text-align: center;
}

.error-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    max-width: 300px;
    margin: 0 auto;
}

.btn {
    display: inline-flex;
    background: rgba(0, 0, 0, 1.0);
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn-primary {
    color: white;
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.6);
}

.btn-outline {
    background: transparent;
    color: #4a5568;
    border: 2px solid #e2e8f0;
}

.btn-outline:hover {
    background: #f7fafc;
    border-color: #667eea;
    color: #667eea;
    transform: translateY(-1px);
}

/* Responsywność */
@media (max-width: 480px) {
    .error-page {
        padding: 1rem;
    }
    
    .error-content {
        padding: 2.5rem 2rem;
        margin: 1rem;
    }
    
    .error-actions {
        flex-direction: column;
    }
}

</style>