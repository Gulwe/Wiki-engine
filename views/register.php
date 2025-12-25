<div class="container">
    <div class="page-header">
        <h1>✍️ Rejestracja</h1>
    </div>

    <div class="form-container">
        <form id="register-form" class="auth-form">
            
            <div id="message-box" style="display: none; margin-bottom: 20px;"></div>

            <div class="form-group">
                <label for="username">Nazwa użytkownika *</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       required 
                       autocomplete="username"
                       minlength="3"
                       placeholder="Minimum 3 znaki">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required 
                       autocomplete="email"
                       placeholder="twoj@email.pl">
            </div>

            <div class="form-group">
                <label for="password">Hasło *</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       autocomplete="new-password"
                       minlength="6"
                       placeholder="Minimum 6 znaków">
            </div>

            <div class="form-group">
                <label for="confirm-password">Potwierdź hasło *</label>
                <input type="password" 
                       id="confirm-password" 
                       name="confirm_password" 
                       required 
                       autocomplete="new-password"
                       minlength="6"
                       placeholder="Powtórz hasło">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block" id="submit-btn">
                    ✍️ Zarejestruj się
                </button>
            </div>

            <div class="form-footer">
                <p>Masz już konto? <a href="/login">Zaloguj się</a></p>
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

.auth-form .form-group {
    margin-bottom: 20px;
}

.auth-form label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--text-primary);
}

.auth-form input[type="text"],
.auth-form input[type="email"],
.auth-form input[type="password"] {
    width: 100%;
    padding: 12px 16px;
    font-size: 1rem;
    background: var(--bg-surface);
    color: var(--text-primary);
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    transition: all 0.2s ease;
}

.auth-form input:focus {
    outline: none;
    border-color: var(--accent-main);
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.btn-block {
    width: 100%;
    text-align: center;
}

.form-footer {
    margin-top: 24px;
    text-align: center;
    color: var(--text-muted);
}

.form-footer a {
    color: var(--accent-main);
    text-decoration: none;
    font-weight: 600;
}

.form-footer a:hover {
    text-decoration: underline;
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
    const form = $('#register-form');
    const submitBtn = $('#submit-btn');
    const messageBox = $('#message-box');

    form.on('submit', function(e) {
        e.preventDefault();

        const username = $('#username').val().trim();
        const email = $('#email').val().trim();
        const password = $('#password').val();
        const confirmPassword = $('#confirm-password').val();

        // Walidacja po stronie klienta
        if (username.length < 3) {
            showMessage('error', '❌ Nazwa użytkownika musi mieć minimum 3 znaki');
            return;
        }

        if (password.length < 6) {
            showMessage('error', '❌ Hasło musi mieć minimum 6 znaków');
            return;
        }

        if (password !== confirmPassword) {
            showMessage('error', '❌ Hasła nie są identyczne');
            return;
        }

        // Wyślij request
        submitBtn.prop('disabled', true).text('⏳ Rejestrowanie...');

        $.ajax({
            url: '/api/register',
            type: 'POST',
            data: {
                username: username,
                email: email,
                password: password,
                confirm_password: confirmPassword
            },
            success: function(response) {
                if (response.success) {
                    showMessage('success', '✅ ' + response.message);
                    form[0].reset();
                    
                    // Przekieruj do logowania po 2 sekundach
                    setTimeout(() => {
                        window.location.href = '/login?registered=1';
                    }, 2000);
                } else {
                    showMessage('error', '❌ ' + response.error);
                    submitBtn.prop('disabled', false).text('✍️ Zarejestruj się');
                }
            },
            error: function() {
                showMessage('error', '❌ Błąd serwera. Spróbuj ponownie.');
                submitBtn.prop('disabled', false).text('✍️ Zarejestruj się');
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
