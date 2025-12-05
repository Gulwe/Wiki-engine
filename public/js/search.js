// public/js/search.js
$(document).ready(function() {
    let searchTimeout;
    
    $('#search-input').on('keyup', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        
        if (query.length < 2) {
            $('#search-results').hide().empty();
            return;
        }
        
        searchTimeout = setTimeout(function() {
            performSearch(query);
        }, 300);
    });
    
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-box').length) {
            $('#search-results').hide();
        }
    });
    
    function performSearch(query) {
        $.ajax({
            type: 'GET',
            url: '/api/search',
            data: { q: query },
            dataType: 'json',
            success: function(results) {
                console.log('Front results:', results);
                displayResults(results);
            },
            error: function() {
                $('#search-results')
                    .html('<div class="search-error">Błąd wyszukiwania</div>')
                    .show();
            }
        });
    }
    
function displayResults(results) {
    const container = $('#search-results');
    container.empty();
    
    if (!Array.isArray(results) || results.length === 0) {
        container.html('<div class="search-item">Brak wyników</div>');
    } else {
        results.forEach(function(page) {
            const slug  = page.slug  ? String(page.slug)  : '';
            const title = page.title ? String(page.title) : 'Bez tytułu';
            const category = page.category ? String(page.category) : 'Bez kategorii';
            
            // każdy wynik jest klikalnym linkiem
            const item = $('<a>')
                .addClass('search-item')
                .attr('href', '/page/' + slug)
                .html(
                    '<strong>' + escapeHtml(title) + '</strong>' +
                    '<br><small>📁 ' + escapeHtml(category) + '</small>'
                );
            
            container.append(item);
        });
    }
    
    container.show();
}



    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }
});
