// public/js/media.js
$(document).ready(function() {
    // Click to upload
    $('#upload-area').on('click', function() {
        $('#image-input').click();
    });
    
    // File input change
    $('#image-input').on('change', function() {
        const file = this.files[0];
        if (file) {
            uploadFile(file);
        }
    });
    
    // Drag & Drop
    const uploadArea = document.getElementById('upload-area');
    
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            uploadFile(file);
        } else {
            showStatus('Tylko pliki obrazkowe!', 'error');
        }
    });
    
    // Upload function
    function uploadFile(file) {
        const formData = new FormData();
        formData.append('image', file);
        
        showStatus('Uploading...', 'info');
        
        $.ajax({
            type: 'POST',
            url: '/api/upload',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showStatus('✓ Upload sukces!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showStatus('✗ Błąd: ' + response.error, 'error');
                }
            },
            error: function() {
                showStatus('✗ Błąd uploadu', 'error');
            }
        });
    }
    
    function showStatus(message, type) {
        const statusDiv = $('#upload-status');
        statusDiv.html('<div class="status-' + type + '">' + message + '</div>');
        
        if (type === 'success') {
            setTimeout(() => statusDiv.empty(), 3000);
        }
    }
    
    // Copy URL
    $('.copy-url').on('click', function() {
        const url = $(this).data('url');
        copyToClipboard(window.location.origin + url);
        showNotification('✓ URL skopiowany!');
    });
    
    // Copy Wiki Link
    $('.copy-markdown').on('click', function() {
        const filename = $(this).data('filename');
        const wikiLink = '{{image:' + filename + '|Alt text}}';
        copyToClipboard(wikiLink);
        showNotification('✓ Wiki link skopiowany!');
    });
    
    function copyToClipboard(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }
    
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
        }, 2000);
    }
});
