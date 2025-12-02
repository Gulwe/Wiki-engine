<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj: <?= htmlspecialchars($page['title'] ?? 'Nowa strona') ?> - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="container">
        <h1>✏️ <?= isset($page['page_id']) && $page['page_id'] ? 'Edytuj' : 'Utwórz' ?> Stronę</h1>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'empty'): ?>
            <div class="error">Tytuł i treść nie mogą być puste!</div>
        <?php endif; ?>
        
        <form method="POST" action="/page/<?= htmlspecialchars($page['slug']) ?>/save">
            <div class="form-group">
                <label>Tytuł strony:</label>
                <input type="text" name="title" value="<?= htmlspecialchars($page['title'] ?? '') ?>" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Treść (Markdown):</label>
                
                <!-- Toolbar -->
                <div class="markdown-toolbar">
                    <!-- Formatowanie tekstu -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('**', '**', 'pogrubiony tekst')" title="Pogrubienie">
                            <strong>B</strong>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('*', '*', 'pochylony tekst')" title="Kursywa">
                            <em>I</em>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('~~', '~~', 'przekreślony')" title="Przekreślenie">
                            <s>S</s>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('__', '__', 'podkreślony')" title="Podkreślenie">
                            <u>U</u>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('==', '==', 'podświetlony')" title="Highlight">
                            🖍️
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('`', '`', 'kod')" title="Kod inline">
                            &lt;/&gt;
                        </button>
                    </div>
                    
                    <!-- Nagłówki -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertAtStart('## ', 'Nagłówek 2')" title="Nagłówek 2">
                            H2
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertAtStart('### ', 'Nagłówek 3')" title="Nagłówek 3">
                            H3
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertAtStart('#### ', 'Nagłówek 4')" title="Nagłówek 4">
                            H4
                        </button>
                    </div>
                    
                    <!-- Listy i cytaty -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertAtStart('- ', 'Element listy')" title="Lista">
                            📝
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertAtStart('1. ', 'Element numerowany')" title="Lista numerowana">
                            🔢
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertAtStart('> ', 'Cytat')" title="Cytat">
                            💬
                        </button>
                    </div>
                    
                    <!-- Linki i media -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertLink()" title="Link">
                            🔗
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertImage()" title="Obrazek">
                            🖼️
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertTable()" title="Tabela">
                            📊
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertYouTube()" title="YouTube">
    ▶️
</button>

                    </div>
                    
                    <!-- Struktury wiki -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertBox()" title="Box / Panel">
                            📦
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertAlert()" title="Alert / Powiadomienie">
                            🔔
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertCard()" title="Karta">
                            🃏
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertSidebar()" title="Sidebar / Infobox">
                            📌
                        </button>
                    </div>
                    
                    <!-- Layouty -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertColumns()" title="Kolumny">
                            ⚏
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertGrid()" title="Siatka">
                            ⊞
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertSplit()" title="Podział 2-kolumnowy">
                            ⚏⚏
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertSection()" title="Sekcja">
                            📄
                        </button>
                    </div>
                    
                    <!-- Interaktywne -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertAccordion()" title="Accordion / Zwijane">
                            ▼
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertProgress()" title="Pasek postępu">
                            ▬▬▬
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertTimeline()" title="Oś czasu">
                            ⏱️
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertButton()" title="Przycisk">
                            🔘
                        </button>
                    </div>
                    
                    <!-- Dodatki -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertBadge()" title="Etykieta / Badge">
                            🏷️
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertIcon()" title="Ikona">
                            ⭐
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertTag()" title="Hashtag">
                            #️⃣
                        </button>
                    </div>
                    
                    <!-- Narzędzia -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertText('{{toc}}\n\n')" title="Spis treści">
                            📑
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertCodeBlock()" title="Blok kodu">
                            💻
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertText('{{divider}}\n\n')" title="Separator">
                            ➖
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertText('{{clear}}\n\n')" title="Clear float">
                            🧹
                        </button>
                    </div>
                </div>
                
                <textarea name="content" id="content" rows="20" required><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Komentarz do zmian:</label>
                <input type="text" name="comment" placeholder="Opcjonalny opis zmian (np. 'Poprawiono błędy', 'Dodano sekcję X')">
            </div>
            
            <div class="form-group">
                <label>Kategorie:</label>
                <?php
                $db = Database::getInstance()->getConnection();
                $allCategories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
                
                $assignedCategories = [];
                if (isset($page['page_id']) && $page['page_id']) {
                    $stmt = $db->prepare("SELECT category_id FROM page_categories WHERE page_id = :page_id");
                    $stmt->execute(['page_id' => $page['page_id']]);
                    $assignedCategories = array_column($stmt->fetchAll(), 'category_id');
                }
                ?>
                
                <?php if (empty($allCategories)): ?>
                    <p style="color:#a78bfa; font-size:14px;">
                        Brak kategorii. 
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="/admin/categories" style="color:#818cf8;">Dodaj kategorię w panelu admina</a>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <div class="categories-checkboxes">
                        <?php foreach ($allCategories as $cat): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="categories[]" value="<?= $cat['category_id'] ?>" 
                                       <?= in_array($cat['category_id'], $assignedCategories) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="editor-actions">
                <button type="submit" class="btn">💾 Zapisz</button>
                
                <?php if (isset($page['page_id']) && $page['page_id']): ?>
                    <a href="/page/<?= htmlspecialchars($page['slug']) ?>" class="btn" style="background: rgba(239, 68, 68, 0.2); border-color: #ef4444;">
                        ❌ Anuluj
                    </a>
                <?php else: ?>
                    <a href="/" class="btn" style="background: rgba(239, 68, 68, 0.2); border-color: #ef4444;">
                        ❌ Anuluj
                    </a>
                <?php endif; ?>
                
                <button type="button" id="preview-btn" class="btn" style="background: rgba(59, 130, 246, 0.2); border-color: #3b82f6;">
                    👁️ Podgląd
                </button>
            </div>
        </form>
        
        <div id="preview-container" style="display:none; margin-top: 30px;">
            <h2>👁️ Podgląd</h2>
            <div id="preview-content" style="padding: 20px; background: rgba(30, 0, 60, 0.4); border-radius: 12px; border: 2px solid rgba(139, 92, 246, 0.3);"></div>
        </div>
    </div>
    
    <style>
    .markdown-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 12px;
        background: rgba(30, 0, 60, 0.4);
        border: 2px solid rgba(139, 92, 246, 0.3);
        border-radius: 10px 10px 0 0;
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .toolbar-section {
        display: flex;
        gap: 4px;
        padding-right: 8px;
        border-right: 1px solid rgba(139, 92, 246, 0.2);
    }
    
    .toolbar-section:last-child {
        border-right: none;
    }
    
    .toolbar-btn {
        padding: 8px 12px;
        background: rgba(139, 92, 246, 0.2);
        border: 1px solid rgba(139, 92, 246, 0.3);
        border-radius: 6px;
        color: #c4b5fd;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 36px;
    }
    
    .toolbar-btn:hover {
        background: rgba(139, 92, 246, 0.4);
        border-color: #8b5cf6;
        transform: translateY(-1px);
    }
    
    .toolbar-btn:active {
        transform: translateY(0);
    }
    
    #content {
        border-radius: 0 0 10px 10px !important;
        border-top: none !important;
    }
    
    @media (max-width: 768px) {
        .toolbar-section {
            border-right: none;
            padding-right: 0;
        }
    }
    </style>
    
    <script>
    function insertAtCursor(textarea, text) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const value = textarea.value;
        
        textarea.value = value.substring(0, start) + text + value.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + text.length;
        textarea.focus();
    }
    
    function insertMarkdown(before, after, placeholder) {
        const textarea = document.getElementById('content');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        
        const textToInsert = selectedText || placeholder;
        const fullText = before + textToInsert + after;
        
        textarea.value = textarea.value.substring(0, start) + fullText + textarea.value.substring(end);
        
        if (!selectedText) {
            textarea.selectionStart = start + before.length;
            textarea.selectionEnd = start + before.length + textToInsert.length;
        } else {
            textarea.selectionStart = textarea.selectionEnd = start + fullText.length;
        }
        
        textarea.focus();
    }
    
    function insertAtStart(prefix, placeholder) {
        const textarea = document.getElementById('content');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        
        const textToInsert = selectedText || placeholder;
        const fullText = prefix + textToInsert;
        
        textarea.value = textarea.value.substring(0, start) + fullText + textarea.value.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + fullText.length;
        textarea.focus();
    }
    
    function insertText(text) {
        const textarea = document.getElementById('content');
        insertAtCursor(textarea, text);
    }
    
    function insertLink() {
        const url = prompt('Podaj URL:', 'https://');
        if (!url) return;
        
        const text = prompt('Tekst linku:', 'kliknij tutaj');
        if (!text) return;
        
        insertText(`[${text}](${url})`);
    }
    
function insertImage() {
    const filename = prompt('Nazwa pliku lub URL:', 'obrazek.jpg');
    if (!filename) return;
    
    const alt = prompt('Opis alternatywny (opcjonalnie):', '');
    const align = prompt('Wyrównanie (left/center/right):', 'center');
    const width = prompt('Szerokość w px:', '300');

    // Domyślne wartości zgodne z parserem:
    const altPart   = alt || '';
    const alignPart = align || 'center';
    const widthPart = width + 'px' || '300';

    const syntax = `{{image:${filename}|${altPart}|${alignPart}|${widthPart}}}`;

    insertText(syntax + '\n\n');
}

    
    function insertTable() {
        insertText(`| Kolumna 1 | Kolumna 2 | Kolumna 3 |
|-----------|-----------|-----------|
| Wartość 1 | Wartość 2 | Wartość 3 |
| Wartość 4 | Wartość 5 | Wartość 6 |

`);
    }
    
    function insertBox() {
        const type = prompt('Typ boxa (info, warning, success, danger, tip):', 'tip');
        if (!type) return;
        
        const title = prompt('Tytuł boxa:', 'Ważna informacja');
        if (!title) return;
        
        insertText(`{{box|${type}|${title}}}
Treść boxa...
{{/box}}

`);
    }
    
    function insertAlert() {
        const type = prompt('Typ alertu (info, success, warning, danger):', 'info');
        if (!type) return;
        
        const title = prompt('Tytuł alertu:', 'Uwaga');
        if (!title) return;
        
        const text = prompt('Treść alertu:', 'Treść powiadomienia');
        if (!text) return;
        
        insertText(`{{alert|${type}|${title}|${text}}}

`);
    }
    
    function insertCard() {
        const title = prompt('Tytuł karty:', 'Tytuł');
        if (!title) return;
        
        const text = prompt('Treść karty:', 'Opis karty');
        if (!text) return;
        
        const link = prompt('Link (opcjonalnie):', '');
        const color = prompt('Kolor (primary/success/warning/danger):', 'primary');
        
        let syntax = `{{card|${title}|${text}`;
        if (link) syntax += `|${link}`;
        if (color && color !== 'primary') syntax += `||${color}`;
        syntax += '}}';
        
        insertText(syntax + '\n\n');
    }
    
    function insertSidebar() {
        const title = prompt('Tytuł sidebara:', 'Infobox');
        if (!title) return;
        
        const align = prompt('Wyrównanie (left/right):', 'right');
        const textAlign = prompt('Wyrównanie tekstu (left/center/right):', 'center');
        
        insertText(`{{sidebar|${title}|${align}|${textAlign}}}
Treść sidebara...
{{/sidebar}}

`);
    }
    
    function insertColumns() {
        const cols = prompt('Liczba kolumn (2, 3 lub 4):', '2');
        if (!cols) return;
        
        insertText(`{{columns|${cols}}}
Treść kolumny 1
---
Treść kolumny 2
{{/columns}}

`);
    }
    
    function insertGrid() {
        const cols = prompt('Liczba kolumn (2, 3 lub 4):', '3');
        if (!cols) return;
        
        insertText(`{{grid|${cols}}}
Element 1
---
Element 2
---
Element 3
{{/grid}}

`);
    }
    
    function insertSplit() {
        const left = prompt('Szerokość lewej kolumny (10-90%):', '40');
        if (!left) return;
        
        insertText(`{{split|${left}}}
Lewa strona
---
Prawa strona
{{/split}}

`);
    }
    
    function insertSection() {
        const width = prompt('Szerokość (full/boxed):', 'full');
        const style = prompt('Styl (default/dark/light/accent):', 'default');
        
        insertText(`{{section|${width}|${style}}}
Treść sekcji...
{{/section}}

`);
    }
    
    function insertAccordion() {
        const title = prompt('Tytuł accordion:', 'Kliknij aby rozwinąć');
        if (!title) return;
        
        insertText(`{{accordion|${title}}}
Treść zwijana...
{{/accordion}}

`);
    }
    
    function insertProgress() {
        const percent = prompt('Procent postępu (0-100):', '75');
        if (!percent) return;
        
        const label = prompt('Etykieta (opcjonalnie):', '');
        
        let syntax = `{{progress|${percent}`;
        if (label) syntax += `|${label}`;
        syntax += '}}';
        
        insertText(syntax + '\n\n');
    }
    
    function insertTimeline() {
        insertText(`{{timeline}}
2020|Początek projektu|Pierwszy commit
2021|Wersja beta|Publiczne testy
2022|Stabilne wydanie|Wersja 1.0
{{/timeline}}

`);
    }
    
    function insertButton() {
        const url = prompt('URL przycisku:', 'https://');
        if (!url) return;
        
        const text = prompt('Tekst przycisku:', 'Kliknij tutaj');
        if (!text) return;
        
        const color = prompt('Kolor (primary/success/danger):', 'primary');
        
        insertText(`{{button|${url}|${text}|${color}}}

`);
    }
    
    function insertBadge() {
        const text = prompt('Tekst etykiety:', 'NEW');
        if (!text) return;
        
        const color = prompt('Kolor (primary/success/warning/danger/info):', 'primary');
        
        insertText(`{{badge|${text}|${color}}} `);
    }
    
    function insertIcon() {
        const icon = prompt('Nazwa ikony (check, star, fire, rocket, heart, warning itp.):', 'star');
        if (!icon) return;
        
        const color = prompt('Kolor (gold/red/green/blue, opcjonalnie):', '');
        
        let syntax = `{{icon|${icon}`;
        if (color) syntax += `|${color}`;
        syntax += '}} ';
        
        insertText(syntax);
    }
    
    function insertTag() {
        const tag = prompt('Nazwa tagu:', 'przykład');
        if (!tag) return;
        
        insertText(`#${tag} `);
    }
    
    function insertYouTube() {
    const url = prompt('Podaj URL filmu YouTube:', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
    if (!url) return;
    
    // Wyciągnij ID z różnych formatów URL
    let videoId = '';
    
    // https://www.youtube.com/watch?v=VIDEO_ID
    let match = url.match(/[?&]v=([^&]+)/);
    if (match) {
        videoId = match[1];
    }
    
    // https://youtu.be/VIDEO_ID
    match = url.match(/youtu\.be\/([^?]+)/);
    if (match) {
        videoId = match[1];
    }
    
    // https://www.youtube.com/embed/VIDEO_ID
    match = url.match(/youtube\.com\/embed\/([^?]+)/);
    if (match) {
        videoId = match[1];
    }
    
    if (!videoId) {
        // Może to już jest samo ID
        videoId = url;
    }
    
    insertText(`{{youtube|${videoId}}}

`);
}

    
    function insertCodeBlock() {
        const lang = prompt('Język programowania (np. php, javascript, css):', 'php');
        if (lang === null) return;
        
        insertText(`\`\`\`${lang}
// Twój kod tutaj
\`\`\`

`);
    }
    
    $(document).ready(function() {
        $('#preview-btn').on('click', function() {
            const content = $('#content').val();
            
            if (!content.trim()) {
                alert('Treść jest pusta!');
                return;
            }
            
            $.ajax({
                type: 'POST',
                url: '/api/preview',
                data: { content: content },
                success: function(html) {
                    $('#preview-content').html(html);
                    $('#preview-container').slideDown();
                    
                    $('html, body').animate({
                        scrollTop: $('#preview-container').offset().top - 100
                    }, 500);
                },
                error: function() {
                    alert('Błąd podczas generowania podglądu');
                }
            });
        });
        
        // Ctrl+S do zapisu
        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 83) {
                e.preventDefault();
                $('form').submit();
            }
        });
    });
    </script>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
