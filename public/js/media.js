// public/js/media.js

$(document).ready(function() {
    const uploadArea = $('#upload-area');
    const fileInput = $('#image-input');
    const uploadStatus = $('#upload-status');

    // Dodaj atrybut "multiple" do inputa (w HTML lub tutaj)
    fileInput.attr('multiple', true);

    // Kliknięcie w upload area otwiera input file
    uploadArea.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        fileInput.trigger('click');
    });

    // Zapobiegnij propagacji z inputa
    fileInput.on('click', function(e) {
        e.stopPropagation();
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
            handleMultipleUpload(files); // ✅ Masowy upload
        }
    });

    // Wybór pliku/plików z inputa
    fileInput.on('change', function(e) {
        e.stopPropagation();
        if (this.files.length > 0) {
            handleMultipleUpload(this.files); // ✅ Masowy upload
        }
    });

    // ✅ NOWA FUNKCJA: Masowy upload wielu plików
    function handleMultipleUpload(files) {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        const formData = new FormData();
        let validFilesCount = 0;
        let invalidFiles = [];

        // Walidacja i dodanie plików do FormData
        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            // Walidacja rozmiaru
            if (file.size > maxSize) {
                invalidFiles.push(`${file.name} (za duży)`);
                continue;
            }

            // Walidacja typu
            if (!validTypes.includes(file.type)) {
                invalidFiles.push(`${file.name} (zły format)`);
                continue;
            }

            // Dodaj do FormData (użyj nazwy tablicowej dla PHP)
            formData.append('images[]', file);
            validFilesCount++;
        }

        // Pokaż błędy walidacji
        if (invalidFiles.length > 0) {
            showStatus('error', `❌ Pominięto: ${invalidFiles.join(', ')}`);
            
            // Jeśli wszystkie pliki są nieprawidłowe, zakończ
            if (validFilesCount === 0) {
                return;
            }
        }

        // Upload prawidłowych plików
        if (validFilesCount > 0) {
            showStatus('loading', `⏳ Uploading ${validFilesCount} plików...`);

            $.ajax({
                url: '/api/upload',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        const uploaded = response.uploaded || validFilesCount;
                        showStatus('success', `✅ Uploaded ${uploaded} obrazków!`);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
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
            const tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(text).select();
            document.execCommand('copy');
            tempInput.remove();
        }
    }
});
