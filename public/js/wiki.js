// public/js/wiki.js

$(document).ready(function() {
    
    // ========================================
    // TABS - PRZEŁĄCZANIE NACJI
    // ========================================
    $('.nation-tab').on('click', function() {
        const nation = $(this).data('nation');
        
        // Usuń active ze wszystkich
        $('.nation-tab').removeClass('active');
        $('.nation-content').removeClass('active');
        
        // Dodaj active do klikniętego
        $(this).addClass('active');
        $('#' + nation + '-content').addClass('active');
    });
    
    // ========================================
    // AUTO-SAVE DRAFTS
    // ========================================
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
                console.error('Błąd zapisu szkicu');
            }
        });
    }
    
    // ========================================
    // DRAG & DROP UPLOAD
    // ========================================
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
                    showNotification('Obrazki załadowane', 'success');
                }
            },
            error: function() {
                showNotification('Błąd wgrywania obrazków', 'error');
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
    
    // ========================================
    // PREVIEW
    // ========================================
    $('#preview-btn').on('click', function() {
        const content = $('#page-editor').val();
        
        if (!content) {
            showNotification('Brak treści do podglądu', 'warning');
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
                showNotification('Błąd generowania podglądu', 'error');
            }
        });
    });
    
    // ========================================
    // ACCORDION
    // ========================================
    $(document).on('click', '.accordion-header', function() {
        const item = $(this).closest('.accordion-item');
        const content = item.find('.accordion-content');
        const icon = item.find('.accordion-icon');
        
        content.slideToggle(300);
        
        if (content.is(':visible')) {
            icon.html('^').css('transform', 'rotate(0deg)');
            item.addClass('active');
        } else {
            icon.html('↓').css('transform', 'rotate(0deg)');
            item.removeClass('active');
        }
    });

    // ========================================
    // INFOBOX POSTAĆ – PRZEŁĄCZANIE ZDJĘĆ
    // ========================================
    $(document).on('click', '.infobox-image-tab', function () {
        const $btn = $(this);
        const $tabs = $btn.closest('.infobox-image-tabs').find('.infobox-image-tab');
        const targetSrc = $btn.data('target-src');

        // aktywny stan zakładek
        $tabs.removeClass('active');
        $btn.addClass('active');

        // podmień src głównego obrazka w tym samym infoboxie
        const $infobox = $btn.closest('.infobox');
        const $mainImg = $infobox.find('.infobox-image-multi img[data-infobox-main="1"]').first();

        if ($mainImg.length && targetSrc) {
            $mainImg.attr('src', targetSrc);
        }
    });

    
    
    // ========================================
    // HELPER: NOTIFICATIONS
    // ========================================
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

        // ========================================
    // PRZEŁĄCZANIE IKON PO ZMIANIE MOTYWU
    // ========================================
    function reloadThemeIcons(theme) {
        $.getJSON('/api/theme_icons.php', { theme: theme })
            .done(function (icons) {
                $('.lore-icon').each(function () {
                    const $img = $(this);
                    const key = $img.data('category'); // np. "postacie"
                    if (icons[key]) {
                        $img.attr('src', icons[key]);
                    }
                });
            })
            .fail(function () {
                console.error('Nie udało się pobrać ikon dla motywu', theme);
            });
    }


});
