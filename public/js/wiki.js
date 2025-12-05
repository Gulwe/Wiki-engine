// public/js/wiki.js

$(document).ready(function() {
    
    // Auto-save drafts co 30 sekund
    let autoSaveTimer;
    $('#page-editor').on('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(saveDraft, 30000);
    });
    
    function saveDraft() {
        const content = $('#page-editor').val();
        const pageId = $('#page-id').val();
        
        if (!pageId || !content) return;
        
        $.ajax({
            type: 'POST',
            url: '/api/pages.php',
            data: {
                action: 'save_draft',
                page_id: pageId,
                content: content
            },
            success: function(response) {
                showNotification('Szkic zapisany', 'success');
            },
            error: function() {
                console.error('B³¹d zapisu szkicu');
            }
        });
    }
    
    // Upload obrazków z drag & drop
    const uploadZone = $('#image-upload-zone');
    
    if (uploadZone.length) {
        uploadZone.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });
        
        uploadZone.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
        });
        
        uploadZone.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            const files = e.originalEvent.dataTransfer.files;
            uploadImages(files);
        });
    }
    
    function uploadImages(files) {
        if (!files || files.length === 0) return;
        
        const formData = new FormData();
        
        for (let i = 0; i < files.length; i++) {
            formData.append('images[]', files[i]);
        }
        
        $.ajax({
            type: 'POST',
            url: '/api/media.php',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.urls && Array.isArray(response.urls)) {
                    insertImageLinks(response.urls);
                    showNotification('Obrazki za³adowane', 'success');
                }
            },
            error: function() {
                showNotification('B³¹d wgrywania obrazków', 'error');
            }
        });
    }
    
    function insertImageLinks(urls) {
        const editor = $('#page-editor');
        if (!editor.length) return;
        
        let markdown = '';
        urls.forEach(function(url) {
            markdown += '{{image|' + url + '}}\n';
        });
        
        const currentContent = editor.val();
        editor.val(currentContent + '\n' + markdown);
    }
    
    // Preview zmian przed zapisaniem
    $('#preview-btn').on('click', function() {
        const content = $('#page-editor').val();
        
        if (!content) {
            showNotification('Brak treœci do podgl¹du', 'warning');
            return;
        }
        
        $.ajax({
            type: 'POST',
            url: '/api/pages.php',
            data: {
                action: 'preview',
                content: content
            },
            success: function(html) {
                $('#preview-container').html(html).show();
            },
            error: function() {
                showNotification('B³¹d generowania podgl¹du', 'error');
            }
        });
    });
    
    // Accordion z jQuery
    $(document).on('click', '.accordion-header', function() {
        const item = $(this).closest('.accordion-item');
        const content = item.find('.accordion-content');
        const icon = item.find('.accordion-icon');
        
        // Toggle obecny
        content.slideToggle(300);
        
        if (content.is(':visible')) {
            icon.html('^').css('transform', 'rotate(0deg)');
            item.addClass('active');
        } else {
            icon.html('¡').css('transform', 'rotate(0deg)');
            item.removeClass('active');
        }
    });
    
    // Helper: wyœwietlanie powiadomieñ
    function showNotification(message, type) {
        type = type || 'info';
        
        const notification = $('<div>')
            .addClass('notification notification-' + type)
            .text(message)
            .appendTo('body');
        
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
});
