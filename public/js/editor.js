// public/js/editor.js
$(document).ready(function() {
    // Podgląd
    $('#preview-btn').on('click', function() {
        const content = $('#page-editor').val();
        
        $.ajax({
            type: 'POST',
            url: '/api/preview',
            data: { content: content },
            success: function(html) {
                $('#preview-content').html(html);
                $('#preview-container').slideDown();
            },
            error: function() {
                alert('Błąd podczas generowania podglądu');
            }
        });
    });
    
    // Auto-save draft (co 60 sekund)
    let autoSaveTimer;
    $('#page-editor').on('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            saveDraft();
        }, 60000);
    });
    
    function saveDraft() {
        const content = $('#page-editor').val();
        localStorage.setItem('wiki_draft_' + window.location.pathname, content);
        showNotification('✓ Draft zapisany lokalnie');
    }
    
    // Przywróć draft jeśli istnieje
    const draft = localStorage.getItem('wiki_draft_' + window.location.pathname);
    if (draft && $('#page-editor').val() === '') {
        if (confirm('Znaleziono zapisany draft. Przywrócić?')) {
            $('#page-editor').val(draft);
        }
    }
    
    // Wyczyść draft po zapisaniu
    $('#edit-form').on('submit', function() {
        localStorage.removeItem('wiki_draft_' + window.location.pathname);
    });
    
    function showNotification(message) {
        const notification = $('<div class="notification">' + message + '</div>');
        notification.css({
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: '#003300',
            color: '#00ff00',
            padding: '15px 20px',
            borderRadius: '4px',
            border: '1px solid #00ff00',
            zIndex: 9999
        });
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
});
