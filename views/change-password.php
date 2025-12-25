<?php
require_once __DIR__ . '/../core/ThemeLoader.php';
?>

<div class="container">
    <div class="page-header">
        <h1>🔑 Zmiana hasła</h1>
    </div>

    <div class="form-container">
        <form id="change-password-form" class="admin-form">
            
            <div id="message-box" style="display: none; margin-bottom: 20px;"></div>

            <div class="form-group">
                <label for="current-password">Obecne hasło *</label>
                <input type="password" 
                       id="current-password" 
                       name="current_password" 
                       required 
                       autocomplete="current-password"
                       placeholder="Wpisz obecne hasło">
            </div>

            <div class="form-group">
                <label for="new-password">Nowe hasło *</label>
                <input type="password" 
                       id="new-password" 
                       name="new_password" 
                       required 
                       autocomplete="new-password"
                       minlength="6"
                       placeholder="Minimum 6 znaków">
                <small style="color: var(--text-muted); display: block; margin-top: 4px;">
                    Hasło musi mieć minimum 6 znaków
                </small>
            </div>

            <div class="form-group">
                <label for="confirm-password">Potwierdź nowe hasło *</label>
                <input type="password" 
                       id="confirm-password" 
                       name="confirm_password" 
                       required 
                       autocomplete="new-password"
                       minlength="6"
                       placeholder="Powtórz nowe hasło">
            </div>

            <div class="form-actions" style="display: flex; gap: 12px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary" id="submit-btn">
                    🔑 Zmień hasło
                </button>
                <a href="/" class="btn btn-outline">
                    ← Anuluj
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.form-container {
    max-width: 500px;
    margin: 40px auto;
    background: var(--card-bg);
    padding: 40px;
    border-radius: 14px;
    border: 1px solid var(--border-subtle);
    box-shadow: var(--shadow-md);
}

.admin-form .form-group {
    margin-bottom: 20px;
}

.admin-form label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--text-primary);
}

.admin-form input[type="password"] {
    width: 100%;
    padding: 12px 16px;
    font-size: 1rem;
    background: var(--bg-surface);
    color: var(--text-primary);
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    transition: all 0.2s ease;
}

.admin-form input[type="password"]:focus {
    outline: none;
    border-color: var(--accent-main);
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.btn {
    padding: 12px 24px;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-block;
    border: none;
}

.btn-primary {
    background: var(--accent-main);
    color: white;
}

.btn-primary:hover {
    background: var(--accent-secondary);
    transform: translateY(-1px);
}

.btn-primary:disabled {
    background: #6b7280;
    cursor: not-allowed;
    transform: none;
}

.btn-outline {
    background: transparent;
    color: var(--text-primary);
    border: 1px solid var(--border-subtle);
}

.btn-outline:hover {
    background: var(--bg-surface);
    border-color: var(--accent-main);
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #86efac;
    padding: 16px;
    border-radius: 8px;
    font-weight: 500;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
    padding: 16px;
    border-radius: 8px;
    font-weight: 500;
}
</style>

<script>
$(document).ready(function() {
    const form = $('#change-password-form');
    const submitBtn = $('#submit-btn');
    const messageBox = $('#message-box');

    form.on('submit', function(e) {
        e.preventDefault();

        const currentPassword = $('#current-password').val();
        const newPassword = $('#new-password').val();
        const confirmPassword = $('#confirm-password').val();

        // Walidacja po stronie klienta
        if (newPassword !== confirmPassword) {
            showMessage('error', '❌ Nowe hasła nie są identyczne');
            return;
        }

        if (newPassword.length < 6) {
            showMessage('error', '❌ Nowe hasło musi mieć minimum 6 znaków');
            return;
        }

        if (currentPassword === newPassword) {
            showMessage('error', '❌ Nowe hasło musi być inne niż obecne');
            return;
        }

        // Wyślij request
        submitBtn.prop('disabled', true).text('⏳ Zmienianie...');

        $.ajax({
            url: '/api/change-password',
            type: 'POST',
            data: {
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            },
            success: function(response) {
                if (response.success) {
                    showMessage('success', '✅ ' + response.message);
                    form[0].reset();
                    
                    // Przekieruj po 2 sekundach
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 2000);
                } else {
                    showMessage('error', '❌ ' + response.error);
                    submitBtn.prop('disabled', false).text('🔑 Zmień hasło');
                }
            },
            error: function() {
                showMessage('error', '❌ Błąd serwera. Spróbuj ponownie.');
                submitBtn.prop('disabled', false).text('🔑 Zmień hasło');
            }
        });
    });

    function showMessage(type, text) {
        messageBox
            .removeClass('alert-success alert-error')
            .addClass('alert-' + type)
            .html(text)
            .fadeIn();

        setTimeout(() => {
            messageBox.fadeOut();
        }, 5000);
    }
});
</script>
