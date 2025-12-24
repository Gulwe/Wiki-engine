<?php include __DIR__ . '/partials/background.php'; ?>
<div class="construction-page">
    <div class="construction-content">
        <div class="construction-icon">🚧</div>
        <h1 class="construction-title">Strona w budowie</h1>
        <p class="construction-description">
            Ta sekcja wiki jest obecnie rozwijana. Wróć wkrótce, aby zobaczyć nową zawartość!
        </p>
        <div class="construction-progress">
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <p class="progress-text">Trwają prace nad treścią...</p>
        </div>
        <div class="construction-actions">
            <button onclick="goBack()" class="btn btn-back">⬅️ Wróć</button>
            <a href="/" class="btn btn-primary">🏠 Strona główna</a>
            <a href="/pages" class="btn btn-outline">📄 Wszystkie strony</a>
        </div>
    </div>
</div>

<style>
.construction-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.construction-content {
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

.construction-icon {
    font-size: clamp(4rem, 12vw, 6rem);
    margin: 0 0 1.5rem 0;
    animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}

.construction-title {
    font-size: clamp(1.75rem, 4vw, 2.5rem);
    font-weight: 700;
    color: #2d3748;
    margin: 0 0 1rem 0;
    line-height: 1.2;
}

.construction-description {
    color: #718096;
    font-size: 1.125rem;
    line-height: 1.6;
    margin: 0 0 2rem 0;
    text-align: center;
}

.construction-progress {
    margin: 0 0 2.5rem 0;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 0.75rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    width: 45%;
    border-radius: 999px;
    animation: progressPulse 2s ease-in-out infinite;
}

@keyframes progressPulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.6;
    }
}

.progress-text {
    color: #a0aec0;
    font-size: 0.875rem;
    font-weight: 500;
    margin: 0;
}

.construction-actions {
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
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    border: none;
    cursor: pointer;
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

.btn-back {
    background: #6b7280;
    color: white;
    box-shadow: 0 10px 25px rgba(107, 114, 128, 0.4);
}

.btn-back:hover {
    background: #4b5563;
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(107, 114, 128, 0.6);
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
    .construction-page {
        padding: 1rem;
    }
    
    .construction-content {
        padding: 2.5rem 2rem;
        margin: 1rem;
    }
    
    .construction-actions {
        flex-direction: column;
    }
}
</style>

<script>
function goBack() {
    // Sprawdź czy jest historia w przeglądarce
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // Jeśli nie ma historii, przekieruj na stronę główną
        window.location.href = '/';
    }
}
</script>
