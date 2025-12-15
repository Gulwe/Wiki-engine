// public/js/media.js

$(document).ready(function() {
    const uploadArea = $('#upload-area');
    const fileInput = $('#image-input');
    const uploadStatus = $('#upload-status');

    // Kliknięcie w upload area otwiera input file
    uploadArea.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation(); // ✅ WAŻNE!
        fileInput.trigger('click'); // Użyj trigger zamiast click()
    });

    // Zapobiegnij propagacji z inputa
    fileInput.on('click', function(e) {
        e.stopPropagation(); // ✅ WAŻNE!
    });

    // Drag & Drop
    uploadArea.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });

    uploadArea.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });

    uploadArea.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');

        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleUpload(files[0]);
        }
    });

    // Wybór pliku z inputa
    fileInput.on('change', function(e) {
        e.stopPropagation(); // ✅ WAŻNE!
        if (this.files.length > 0) {
            handleUpload(this.files[0]);
        }
    });

    // Funkcja uploadująca
    function handleUpload(file) {
        // Walidacja rozmiaru (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            showStatus('error', '❌ Plik jest za duży! Max 5MB.');
            return;
        }

        // Walidacja typu pliku
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            showStatus('error', '❌ Nieprawidłowy format! Tylko JPG, PNG, GIF, WEBP.');
            return;
        }

        // Formularz i upload
        const formData = new FormData();
        formData.append('image', file);

        showStatus('loading', '⏳ Uploading...');

        $.ajax({
            url: '/api/upload',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showStatus('success', '✅ Obrazek uploaded!');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showStatus('error', '❌ Błąd: ' + (response.error || 'Nieznany błąd'));
                }
            },
            error: function(xhr) {
                let errorMsg = 'Błąd uploadu';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.error || errorMsg;
                } catch (e) {
                    errorMsg = 'Błąd serwera';
                }
                showStatus('error', '❌ ' + errorMsg);
            }
        });
    }

    // Pokaż status
    function showStatus(type, message) {
        uploadStatus.removeClass('success error loading');
        uploadStatus.addClass(type);
        uploadStatus.text(message);
        uploadStatus.show();

        if (type === 'success' || type === 'error') {
            setTimeout(() => {
                uploadStatus.fadeOut();
            }, 3000);
        }
    }

    // Kopiuj URL
    $(document).on('click', '.copy-url', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const url = $(this).data('url');
        copyToClipboard(url);
        $(this).text('✅ Skopiowano!');
        setTimeout(() => {
            $(this).text('📋 Kopiuj URL');
        }, 2000);
    });

    // Kopiuj Wiki Link
    $(document).on('click', '.copy-markdown', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const filename = $(this).data('filename');
        const wikiSyntax = `{{image:${filename}}}`;
        copyToClipboard(wikiSyntax);
        $(this).text('✅ Skopiowano!');
        setTimeout(() => {
            $(this).text('📝 Wiki Link');
        }, 2000);
    });

    // Funkcja kopiująca do schowka
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text);
        } else {
            // Fallback dla starszych przeglądarek
            const tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(text).select();
            document.execCommand('copy');
            tempInput.remove();
        }
    }
});
