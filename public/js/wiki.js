// public/js/wiki.js

$(document).ready(function() {
    // Live search - wyszukiwanie w czasie rzeczywistym
    $('#search-input').on('keyup', function() {
        const query = $(this).val();
        
        if (query.length < 3) {
            $('#search-results').hide();
            return;
        }
        
        $.ajax({
            type: 'GET',
            url: '/api/search.php',
            data: { q: query },
            dataType: 'json',
            success: function(data) {
                displaySearchResults(data);
            },
            error: function(xhr, status, error) {
                console.error('Search error:', error);
            }
        });
    });
    
    // Auto-save drafts co 30 sekund
    let autoSaveTimer;
    $('#page-editor').on('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(saveDraft, 30000);
    });
    
    function saveDraft() {
        const content = $('#page-editor').val();
        const pageId = $('#page-id').val();
        
        $.ajax({
            type: 'POST',
            url: '/api/pages.php',
            data: {
                action: 'save_draft',
                page_id: pageId,
                content: content
            },
            success: function(response) {
                showNotification('Draft saved', 'success');
            }
        });
    }
    
    // Upload obrazków z drag & drop
    $('#image-upload-zone').on('drop', function(e) {
        e.preventDefault();
        const files = e.originalEvent.dataTransfer.files;
        uploadImages(files);
    });
    
    function uploadImages(files) {
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
                insertImageLinks(response.urls);
            }
        });
    }
    
    // Preview zmian przed zapisaniem
    $('#preview-btn').on('click', function() {
        const content = $('#page-editor').val();
        
        $.ajax({
            type: 'POST',
            url: '/api/pages.php',
            data: {
                action: 'preview',
                content: content
            },
            success: function(html) {
                $('#preview-container').html(html).show();
            }
        });
    });
});

function displaySearchResults(results) {
    const container = $('#search-results');
    container.empty();
    
    if (results.length === 0) {
        container.html('<p>No results found</p>');
        return;
    }
    
    results.forEach(function(page) {
        container.append(
            `<div class="search-result">
                <a href="/page/${page.slug}">${page.title}</a>
                <p>${page.excerpt}</p>
            </div>`
        );
    });
    
    container.show();
}
