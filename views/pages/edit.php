<?php
/* session_start();
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/ThemeLoader.php';

Auth::requireLogin(); */

// Pobierz dane strony jeśli edycja
$page = $page ?? null;
$isEdit = !empty($page['page_id']);
$pageTitle = $isEdit ? 'Edytuj: ' . htmlspecialchars($page['title']) : 'Nowa strona';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
    <?= ThemeLoader::generateCSS() ?>
    
    <!-- GLOBALNE TŁO -->
    <?php include __DIR__ . '/../partials/background.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>✏️ <?= $isEdit ? 'Edytuj stronę' : 'Utwórz stronę' ?></h1>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ❌ <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <form method="POST" action="<?= $isEdit ? '/page/' . htmlspecialchars($page['slug']) . '/save' : '/page/store' ?>" class="editor-form">
            
            <!-- Tytuł strony -->
            <div class="form-group">
                <label for="title">📝 Tytuł strony: <span class="required">*</span></label>
                <input type="text" id="title" name="title"
                       value="<?= htmlspecialchars($page['title'] ?? '') ?>" required autofocus
                       placeholder="Np. John Macmillan">
                <small class="form-hint">Główny tytuł widoczny na stronie</small>
            </div>
            
            <!-- Slug (tylko dla nowych stron) -->
            <?php if (!$isEdit): ?>
            <div class="form-group">
                <label for="slug">🔗 URL strony (slug): <span class="required">*</span></label>
                <div class="slug-input-wrapper">
                    <span class="slug-prefix">/page/</span>
                    <input 
                        type="text" 
                        id="slug" 
                        name="slug" 
                        class="form-input slug-input" 
                        value="<?= htmlspecialchars($page['slug'] ?? '') ?>"
                        required
                        placeholder="john-macmillan"
                        pattern="[a-z0-9\-]+"
                        title="Tylko małe litery, cyfry i myślniki"
                    >
                    <button type="button" id="generate-slug-btn" class="btn-small btn-secondary">
                        🔄 Generuj z tytułu
                    </button>
                </div>
                <small class="form-hint">Tylko małe litery, cyfry i myślniki. Zostanie automatycznie wygenerowany z tytułu.</small>
            </div>
            <?php endif; ?>
            
            <!-- Treść strony -->
            <div class="form-group">
                <label for="content">📄 Treść (Markdown): <span class="required">*</span></label>

                <!-- Toolbar -->
                <div class="markdown-toolbar">
                    <!-- Formatowanie tekstu -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('**', '**', 'pogrubiony tekst')" title="Pogrubienie"><strong>B</strong></button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('*', '*', 'pochylony tekst')" title="Kursywa"><em>I</em></button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('~~', '~~', 'przekreślony')" title="Przekreślenie"><s>S</s></button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('__', '__', 'podkreślony')" title="Podkreślenie"><u>U</u></button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('==', '==', 'podświetlony')" title="Highlight">🖍️</button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('`', '`', 'kod')" title="Kod inline">&lt;/&gt;</button>
                    </div>

                    <!-- Nagłówki -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertAtStart('## ', 'Nagłówek 2')" title="Nagłówek 2">H2</button>
                        <button type="button" class="toolbar-btn" onclick="insertAtStart('### ', 'Nagłówek 3')" title="Nagłówek 3">H3</button>
                        <button type="button" class="toolbar-btn" onclick="insertAtStart('#### ', 'Nagłówek 4')" title="Nagłówek 4">H4</button>
                    </div>

                    <!-- Listy i cytaty -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertAtStart('- ', 'Element listy')" title="Lista">📝</button>
                        <button type="button" class="toolbar-btn" onclick="insertAtStart('1. ', 'Element numerowany')" title="Lista numerowana">🔢</button>
                        <button type="button" class="toolbar-btn" onclick="insertAtStart('> ', 'Cytat')" title="Cytat">💬</button>
                    </div>

                    <!-- Linki i media -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertLink()" title="Link">🔗</button>
                        <button type="button" class="toolbar-btn" onclick="insertImage()" title="Obrazek">🖼️</button>
                        <button type="button" class="toolbar-btn" onclick="insertTable()" title="Tabela">📊</button>
                        <button type="button" class="toolbar-btn" onclick="insertYouTube()" title="YouTube">▶️</button>
                    </div>

                    <!-- Struktury wiki -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertBox()" title="Box / Panel">📦</button>
                        <button type="button" class="toolbar-btn" onclick="insertAlert()" title="Alert / Powiadomienie">🔔</button>
                        <button type="button" class="toolbar-btn" onclick="insertCard()" title="Karta">🃏</button>
                        <button type="button" class="toolbar-btn" onclick="insertSidebar()" title="Sidebar / Infobox">📌</button>
                    </div>

                    <!-- Layouty -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertColumns()" title="Kolumny">⚏</button>
                        <button type="button" class="toolbar-btn" onclick="insertGrid()" title="Siatka">⊞</button>
                        <button type="button" class="toolbar-btn" onclick="insertSplit()" title="Podział 2-kolumnowy">⚏⚏</button>
                        <button type="button" class="toolbar-btn" onclick="insertSection()" title="Sekcja">📄</button>
                    </div>

                    <!-- Interaktywne -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertAccordion()" title="Accordion / Zwijane">▼</button>
                        <button type="button" class="toolbar-btn" onclick="insertProgress()" title="Pasek postępu">▬▬▬</button>
                        <button type="button" class="toolbar-btn" onclick="insertTimeline()" title="Oś czasu">⏱️</button>
                        <button type="button" class="toolbar-btn" onclick="insertButton()" title="Przycisk">🔘</button>
                    </div>

                    <!-- Dodatki -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertBadge()" title="Etykieta / Badge">🏷️</button>
                        <button type="button" class="toolbar-btn" onclick="insertIcon()" title="Ikona">⭐</button>
                        <button type="button" class="toolbar-btn" onclick="insertTag()" title="Hashtag">#️⃣</button>
                    </div>

                    <!-- Narzędzia -->
                    <div class="toolbar-section">
                        <button type="button" class="toolbar-btn" onclick="insertText('{{toc}}\n\n')" title="Spis treści">📑</button>
                        <button type="button" class="toolbar-btn" onclick="insertCodeBlock()" title="Blok kodu">💻</button>
                        <button type="button" class="toolbar-btn" onclick="insertText('{{divider}}\n\n')" title="Separator">➖</button>
                        <button type="button" class="toolbar-btn" onclick="insertText('{{clear}}\n\n')" title="Clear float">🧹</button>
                    </div>
                    
                    <!-- Szablony stron -->
                    <div class="toolbar-section">
                        <select id="template-select" class="toolbar-select"
                                onchange="insertTemplate(this.value); this.value='';">
                            <option value="">🧩 Wstaw szablon…</option>
                            <?php if (!empty($templates)): ?>
                                <?php foreach ($templates as $tpl): ?>
                                    <option value="<?= htmlspecialchars($tpl['machine_key']) ?>">
                                        <?= htmlspecialchars($tpl['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <textarea name="content" id="content" rows="20" required><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="comment">💬 Komentarz do zmian:</label>
                <input type="text" id="comment" name="comment" placeholder="Opcjonalny opis zmian (np. 'Poprawiono błędy', 'Dodano sekcję X')">
            </div>
            
            <div class="form-group">
                <label>📁 Kategorie:</label>
                <?php
                $db = Database::getInstance()->getConnection();
                $allCategories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
                
                $assignedCategories = [];
                if (!empty($page['page_id'])) {
                    $stmt = $db->prepare("SELECT category_id FROM page_categories WHERE page_id = :page_id");
                    $stmt->execute(['page_id' => $page['page_id']]);
                    $assignedCategories = array_column($stmt->fetchAll(), 'category_id');
                }
                ?>
                
                <?php if (empty($allCategories)): ?>
                    <p class="info">
                        Brak kategorii.
                        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="/admin/categories">Dodaj kategorię w panelu admina</a>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <div class="categories-checkboxes">
                        <?php foreach ($allCategories as $cat): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="categories[]" value="<?= $cat['category_id'] ?>"
                                       <?= in_array($cat['category_id'], $assignedCategories, true) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="editor-actions">
                <button type="submit" class="btn">💾 Zapisz</button>

                <?php if ($isEdit): ?>
                    <a href="/page/<?= htmlspecialchars($page['slug']) ?>" class="btn btn-danger">
                        ❌ Anuluj
                    </a>
                <?php else: ?>
                    <a href="/" class="btn btn-danger">
                        ❌ Anuluj
                    </a>
                <?php endif; ?>
                
                <button type="button" id="preview-btn" class="btn btn-secondary">
                    👁️ Podgląd
                </button>
            </div>
        </form>
        
        <section id="preview-container" class="preview-container" style="display:none;">
            <h2>👁️ Podgląd</h2>
            <div id="preview-content" class="preview-content"></div>
        </section>
    </div>
    
    <?php include __DIR__ . '/../partials/footer.php'; ?>

<style>
/* Editor toolbar */
.markdown-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 12px;
    background: var(--card-bg-soft);
    border: 1px solid var(--border-subtle);
    border-radius: 12px 12px 0 0;
    border-bottom: none;
    margin-bottom: 0;
}

.toolbar-section {
    display: flex;
    gap: 4px;
    padding-right: 8px;
    border-right: 1px solid var(--border-subtle);
}

.toolbar-section:last-child {
    border-right: none;
    padding-right: 0;
}

.toolbar-btn {
    padding: 6px 10px;
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 6px;
    color: var(--text-secondary);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    min-width: 34px;
}

.toolbar-btn:hover {
    background: var(--bg-surface-alt);
    border-color: var(--accent-main);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.toolbar-btn:active {
    transform: translateY(0);
    box-shadow: none;
}

/* Slug input wrapper */
.slug-input-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
}

.slug-prefix {
    padding: 10px 12px;
    background: rgba(139, 92, 246, 0.1);
    border: 1px solid var(--border-subtle, rgba(255, 255, 255, 0.1));
    border-radius: 8px 0 0 8px;
    color: var(--text-muted, #a1a1aa);
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    white-space: nowrap;
}

.slug-input {
    flex: 1;
    border-radius: 0 8px 8px 0 !important;
    font-family: 'Courier New', monospace;
}

.btn-small {
    padding: 8px 12px;
    font-size: 0.85em;
    white-space: nowrap;
}

/* Alert messages */
.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #86efac;
}

.form-hint {
    display: block;
    margin-top: 6px;
    color: var(--text-muted, #a1a1aa);
    font-size: 0.85em;
}

.form-hint a {
    color: var(--accent-primary, #8b5cf6);
    text-decoration: none;
}

.form-hint a:hover {
    text-decoration: underline;
}

.required {
    color: #ef4444;
}

#content {
    border-radius: 0 0 10px 10px;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}

.editor-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 25px;
}

.btn.btn-secondary {
    background: var(--bg-surface);
    color: var(--text-secondary);
    border: 1px solid var(--border-subtle);
}

.preview-container {
    margin-top: 30px;
    padding: 20px;
    background: var(--card-bg);
    border-radius: 14px;
    border: 1px solid var(--border-subtle);
}

.preview-content {
    margin-top: 10px;
}

.toolbar-select {
    padding: 6px 10px;
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 6px;
    color: var(--text-secondary);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
    
@media (max-width: 768px) {
    .toolbar-section {
        border-right: none;
        padding-right: 0;
    }
    
    .slug-input-wrapper {
        flex-direction: column;
        align-items: stretch;
    }
    
    .slug-prefix {
        border-radius: 8px 8px 0 0;
    }
    
    .slug-input {
        border-radius: 0 0 8px 8px !important;
    }
}
</style>
    
<script>
window.WIKI_TEMPLATES = <?= json_encode(
    array_column($templates ?? [], 'content', 'machine_key'),
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
); ?>;

<?php if (!$isEdit): ?>
// Auto-generuj slug z tytułu
const titleInput = document.getElementById('title');
const slugInput = document.getElementById('slug');
const generateSlugBtn = document.getElementById('generate-slug-btn');

function slugify(text) {
    const polishChars = {
        'ą': 'a', 'ć': 'c', 'ę': 'e', 'ł': 'l', 'ń': 'n',
        'ó': 'o', 'ś': 's', 'ź': 'z', 'ż': 'z',
        'Ą': 'a', 'Ć': 'c', 'Ę': 'e', 'Ł': 'l', 'Ń': 'n',
        'Ó': 'o', 'Ś': 's', 'Ź': 'z', 'Ż': 'z'
    };

    return text
        .split('')
        .map(char => polishChars[char] || char)
        .join('')
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

// Auto-generuj przy wpisywaniu tytułu
titleInput.addEventListener('input', function() {
    if (slugInput.value === '') {
        slugInput.value = slugify(this.value);
    }
});

// Przycisk do ręcznego wygenerowania
generateSlugBtn.addEventListener('click', function() {
    slugInput.value = slugify(titleInput.value);
});

// Walidacja slug przy wpisywaniu
slugInput.addEventListener('input', function() {
    this.value = this.value.toLowerCase().replace(/[^a-z0-9\-]/g, '');
});
<?php endif; ?>

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
    
    let videoId = '';
    let match = url.match(/[?&]v=([^&]+)/);
    if (match) videoId = match[1];
    
    match = url.match(/youtu\.be\/([^?]+)/);
    if (match) videoId = match[1];
    
    match = url.match(/youtube\.com\/embed\/([^?]+)/);
    if (match) videoId = match[1];
    
    if (!videoId) videoId = url;
    
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

function insertTemplate(type) {
    if (!type || !window.WIKI_TEMPLATES) return;
    const tpl = window.WIKI_TEMPLATES[type];
    if (!tpl) return;
    insertText(tpl);
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
</body>
</html>
