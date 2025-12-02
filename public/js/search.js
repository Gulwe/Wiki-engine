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
    
    // Ukryj wyniki po kliknięciu poza wyszukiwarką
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
                displayResults(results);
            },
            error: function() {
                $('#search-results').html('<div class="search-error">Błąd wyszukiwania</div>').show();
            }
        });
    }
    
    function displayResults(results) {
        const container = $('#search-results');
        container.empty();
        
        if (results.length === 0) {
            container.html('<div class="search-item">Brak wyników</div>');
        } else {
            results.forEach(function(page) {
                const excerpt = page.excerpt ? page.excerpt.substring(0, 100) + '...' : '';
                const item = $('<a>')
                    .addClass('search-item')
                    .attr('href', '/page/' + page.slug)
                    .html(
                        '<strong>' + escapeHtml(page.title) + '</strong>' +
                        (excerpt ? '<br><small>' + escapeHtml(excerpt) + '</small>' : '')
                    );
                container.append(item);
            });
        }
        
        container.show();
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
