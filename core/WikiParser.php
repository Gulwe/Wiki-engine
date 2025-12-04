<?php
class WikiParser {

    // === ALERTY / POWIADOMIENIA ===
    private function parseAlerts(string $content): string {
        // {{alert|info|Tytuł|Treść}}
        $pattern = '/\{\{alert\|([a-z]+)\|([^|]+)\|([^}]+)\}\}/';

        return preg_replace_callback($pattern, function($matches) {
            $type  = $matches[1];
            $title = trim($matches[2]);
            $text  = trim($matches[3]);

            $icons = array(
                'info'    => 'ℹ️',
                'success' => '✅',
                'warning' => '⚠️',
                'danger'  => '🚫'
            );

            $icon = $icons[$type] ?? 'ℹ️';

            return '<div class="alert alert-' . $type . '">
                        <span class="alert-icon">' . $icon . '</span>
                        <div class="alert-content">
                            <strong>' . htmlspecialchars($title) . '</strong>
                            <p>' . htmlspecialchars($text) . '</p>
                        </div>
                    </div>';
        }, $content);
    }

    // === PROGRESS BAR ===
    private function parseProgress(string $content): string {
        // {{progress|75|Ukończono}}
        $pattern = '/\{\{progress\|(\d+)(?:\|([^}]+))?\}\}/';

        return preg_replace_callback($pattern, function($matches) {
            $percent = min(100, max(0, (int)$matches[1]));
            $label   = isset($matches[2]) ? htmlspecialchars($matches[2]) : $percent . '%';

            return '<div class="progress-bar">
                        <div class="progress-fill" style="width: ' . $percent . '%;">
                            <span class="progress-label">' . $label . '</span>
                        </div>
                    </div>';
        }, $content);
    }

    // === ACCORDION / ZWIJANE SEKCJE ===
    private function parseAccordion(string $content): string {
        static $accordionId = 0;

        $pattern = '/\{\{accordion\|([^}]+)\}\}(.*?)\{\{\/accordion\}\}/s';

        return preg_replace_callback($pattern, function($matches) use (&$accordionId) {
            $accordionId++;
            $title            = trim($matches[1]);
            $accordionContent = trim($matches[2]);

            return '<div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(' . $accordionId . ')">
                            <span class="accordion-title">' . htmlspecialchars($title) . '</span>
                            <span class="accordion-icon" id="icon-' . $accordionId . '">▼</span>
                        </div>
                        <div class="accordion-content" id="accordion-' . $accordionId . '" style="display: none;">
                            ' . nl2br(htmlspecialchars($accordionContent)) . '
                        </div>
                    </div>';
        }, $content);
    }

// === YOUTUBE EMBED ===
private function parseYouTube(string $content): string {
    // {{youtube|VIDEO_ID}} lub {{youtube|VIDEO_ID|560|315}}
    $pattern = '/\{\{youtube\|([a-zA-Z0-9_-]+)(?:\|(\d+))?(?:\|(\d+))?\}\}/';

    return preg_replace_callback($pattern, function($matches) {
        $videoId = $matches[1];
        $width   = isset($matches[2]) ? (int)$matches[2] : 560;
        $height  = isset($matches[3]) ? (int)$matches[3] : 315;

        return '<div class="youtube-embed" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; margin: 25px 0;">
                    <iframe 
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 2px solid rgba(139, 92, 246, 0.3); border-radius: 12px;"
                        src="https://www.youtube.com/embed/' . htmlspecialchars($videoId) . '" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>';
    }, $content);
}


    // === BADGE / ETYKIETA ===
    private function parseBadges(string $content): string {
        // {{badge|text|color}}
        $pattern = '/\{\{badge\|([^|]+)(?:\|([a-z]+))?\}\}/';

        return preg_replace_callback($pattern, function($matches) {
            $text  = htmlspecialchars(trim($matches[1]));
            $color = isset($matches[2]) ? trim($matches[2]) : 'primary';

            return '<span class="badge badge-' . $color . '">' . $text . '</span>';
        }, $content);
    }

    // === TIMELINE / OŚ CZASU ===
    private function parseTimeline(string $content): string {
        // {{timeline}}
        // 2020|Początek projektu|Opis
        // {{/timeline}}

        $pattern = '/\{\{timeline\}\}(.*?)\{\{\/timeline\}\}/s';

        return preg_replace_callback($pattern, function($matches) {
            $lines = explode("\n", trim($matches[1]));
            $html  = '<div class="timeline">';

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $parts = explode('|', $line);
                if (count($parts) >= 2) {
                    $date = htmlspecialchars(trim($parts[0]));
                    $title = htmlspecialchars(trim($parts[1]));
                    $desc  = isset($parts[2]) ? htmlspecialchars(trim($parts[2])) : '';

                    $html .= '<div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-date">' . $date . '</div>
                                    <div class="timeline-title">' . $title . '</div>'
                                    . ($desc ? '<div class="timeline-desc">' . $desc . '</div>' : '') .
                                '</div>
                              </div>';
                }
            }

            $html .= '</div>';
            return $html;
        }, $content);
    }

    // === KARTY / CARDS ===
    private function parseCards(string $content): string {
        // {{card|Tytuł|Treść|link|color}}
        $pattern = '/\{\{card\|([^|]+)\|([^|]+)(?:\|([^|]*)?)?(?:\|([a-z]+))?\}\}/';

        return preg_replace_callback($pattern, function($matches) {
            $title = htmlspecialchars(trim($matches[1]));
            $text  = htmlspecialchars(trim($matches[2]));
            $link  = isset($matches[3]) && !empty(trim($matches[3])) ? trim($matches[3]) : null;
            $color = isset($matches[4]) ? trim($matches[4]) : 'primary';

            $html  = '<div class="wiki-card wiki-card-' . $color . '">';
            $html .= '<h4>' . $title . '</h4>';
            $html .= '<p>' . $text . '</p>';

            if ($link) {
                $html .= '<a href="' . htmlspecialchars($link) . '" class="card-link">Czytaj więcej →</a>';
            }

            $html .= '</div>';
            return $html;
        }, $content);
    }

    // === IKONY ===
    private function parseIcons(string $content): string {
        // {{icon|check}} lub {{icon|star|gold}}
        $pattern = '/\{\{icon\|([a-z\-]+)(?:\|([a-z]+))?\}\}/';

        $icons = array(
            'check'       => '✓',
            'cross'       => '✗',
            'star'        => '⭐',
            'heart'       => '❤️',
            'fire'        => '🔥',
            'rocket'      => '🚀',
            'light'       => '💡',
            'warning'     => '⚠️',
            'info'        => 'ℹ️',
            'arrow-right' => '→',
            'arrow-left'  => '←',
            'arrow-up'    => '↑',
            'arrow-down'  => '↓',
            'plus'        => '➕',
            'minus'       => '➖',
            'search'      => '🔍',
            'home'        => '🏠',
            'user'        => '👤',
            'settings'    => '⚙️',
            'calendar'    => '📅',
            'clock'       => '🕐',
            'email'       => '✉️',
            'phone'       => '📞',
            'location'    => '📍',
            'link'        => '🔗',
            'download'    => '⬇️',
            'upload'      => '⬆️',
            'edit'        => '✏️',
            'delete'      => '🗑️',
            'save'        => '💾',
            'share'       => '🔄',
            'thumbs-up'   => '👍',
            'thumbs-down' => '👎'
        );

        return preg_replace_callback($pattern, function($matches) use ($icons) {
            $icon  = $matches[1];
            $color = isset($matches[2]) ? $matches[2] : '';

            $symbol = isset($icons[$icon]) ? $icons[$icon] : '•';

            return '<span class="wiki-icon icon-' . $color . '">' . $symbol . '</span>';
        }, $content);
    }

    // === SEKCJE (FULL / BOXED) ===
    // {{section|full|dark}} ... {{/section}}
    private function parseSections(string $content): string {
        $pattern = '/\{\{section\|([a-z]+)(?:\|([a-z]+))?\}\}(.*?)\{\{\/section\}\}/s';

        return preg_replace_callback($pattern, function($m) {
            $width = trim($m[1]);                      // full / boxed
            $style = isset($m[2]) ? trim($m[2]) : 'default'; // dark/light/accent/default
            $inner = trim($m[3]);

            $classes = array('wiki-section', 'section-' . $width, 'section-' . $style);

            $html  = '<section class="' . implode(' ', $classes) . '">';
            $html .= $this->parseInline($inner);
            $html .= '</section>';

            return $html;
        }, $content);
    }

    // === GRID / SIATKA ===
    // {{grid|3}} ... {{/grid}} z separatorami ---
    private function parseGrid(string $content): string {
        $pattern = '/\{\{grid\|(\d+)\}\}(.*?)\{\{\/grid\}\}/s';

        return preg_replace_callback($pattern, function($m) {
            $cols  = max(1, min(4, (int)$m[1]));
            $items = preg_split('/\r?\n---\r?\n/', trim($m[2]));
            $html  = '<div class="wiki-grid wiki-grid-' . $cols . '">';

            foreach ($items as $item) {
                $item = trim($item);
                if ($item === '') continue;
                $html .= '<div class="wiki-grid-item">' . $this->parseInline($item) . '</div>';
            }

            $html .= '</div>';
            return $html;
        }, $content);
    }

    // === SPLIT / DWUPANEL ===
    // {{split|40}} lewa --- prawa {{/split}}
    private function parseSplit(string $content): string {
        $pattern = '/\{\{split\|(\d+)\}\}(.*?)\{\{\/split\}\}/s';

        return preg_replace_callback($pattern, function($m) {
            $left  = max(10, min(90, (int)$m[1]));
            $right = 100 - $left;
            $parts = preg_split('/\r?\n---\r?\n/', trim($m[2]), 2);

            $leftContent  = isset($parts[0]) ? trim($parts[0]) : '';
            $rightContent = isset($parts[1]) ? trim($parts[1]) : '';

            $html  = '<div class="wiki-split" style="display:grid;grid-template-columns:' . $left . '% ' . $right . '%;gap:20px;">';
            $html .= '<div class="wiki-split-left">' . $this->parseInline($leftContent) . '</div>';
            $html .= '<div class="wiki-split-right">' . $this->parseInline($rightContent) . '</div>';
            $html .= '</div>';

            return $html;
        }, $content);
    }

    // === GŁÓWNE PARSOWANIE ===
    public function parse(string $content): string {
        if (empty($content)) {
            return '';
        }

        // 1. Kod (inline)
        $content = $this->parseCode($content);

        // 2. Struktury / bloki
        $content = $this->parseTemplates($content);
        $content = $this->parseAlerts($content);
        $content = $this->parseAccordion($content);
        $content = $this->parseTimeline($content);
        $content = $this->parseButtons($content);
        $content = $this->parseCards($content);
        $content = $this->parseBoxes($content);
        $content = $this->parseSidebar($content);
        $content = $this->parseColumns($content);
        $content = $this->parseGrid($content);
        $content = $this->parseSplit($content);
        $content = $this->parseSections($content);
        $content = $this->parseTables($content);
        $content = $this->parseImages($content);
        $content = $this->parseFlags($content);
        $content = $this->parseSymbols($content);
        $content = $this->parseYouTube($content);

        // 3. Inline
        $content = $this->parseHeadings($content);
        $content = $this->parseFormatting($content);
        $content = $this->parseBadges($content);
        $content = $this->parseIcons($content);
        $content = $this->parseProgress($content);
        $content = $this->parseLists($content);
        $content = $this->parseQuotes($content);
        $content = $this->parseLinks($content);
        $content = $this->parseTags($content);

        // 4. Paragrafy
        $content = $this->parseParagraphs($content);

        return $content;
    }

    // === PRZYCISKI ===
    // {{button|URL|Tekst|color}}
    private function parseButtons(string $content): string {
        $pattern = '/\{\{button\|([^|]+)\|([^|]+)(?:\|([a-z]+))?\}\}/';

        return preg_replace_callback($pattern, function($matches) {
            $url   = trim($matches[1]);
            $text  = trim($matches[2]);
            $color = isset($matches[3]) ? trim($matches[3]) : 'primary';

            return '<a href="' . htmlspecialchars($url) . '" class="wiki-button wiki-button-' . $color . '">' . htmlspecialchars($text) . '</a>';
        }, $content);
    }

    // === NOTATKI BOCZNE ===
private function parseSidebar(string $content): string {
    // {{sidebar|Tytuł|right|center}}...{{/sidebar}}
    $pattern = '/\{\{sidebar\|([^|}]+)(?:\|([^|}]+))?(?:\|([^}]+))?\}\}(.*?)\{\{\/sidebar\}\}/s';

    return preg_replace_callback($pattern, function($matches) {
        $title          = trim($matches[1]);
        $alignRaw       = isset($matches[2]) ? trim($matches[2]) : '';
        $textAlignRaw   = isset($matches[3]) ? trim($matches[3]) : '';
        $sidebarContent = trim($matches[4]);

        $align = in_array($alignRaw, ['left', 'right'], true) ? $alignRaw : 'right';
        $textAlign = in_array($textAlignRaw, ['left', 'center', 'right'], true) ? $textAlignRaw : 'left';

        $classes = 'wiki-sidebar sidebar-' . $align . ' sidebar-text-' . $textAlign;

        $html  = '<aside class="' . $classes . '">';
        $html .= '<div class="sidebar-title">' . htmlspecialchars($title) . '</div>';
        $html .= '<div class="sidebar-content">' . $this->parseInline($sidebarContent) . '</div>';
        $html .= '</aside>';

        return $html;
    }, $content);
}



// === TABELE ===
private function parseTables(string $content): string {
    // MediaWiki-like
    $pattern = '/\{\|(.*?)\n(.*?)\|\}/s';

    return preg_replace_callback($pattern, function($matches) {
        $attributes    = trim($matches[1]);
        $tableContent  = $matches[2];

        $class = 'wiki-table';
        if (preg_match('/class=["\']([^"\']+)["\']/', $attributes, $classMatch)) {
            $class .= ' ' . $classMatch[1];
        }

        $html = '<table class="' . $class . '">';

        // Caption
        if (preg_match('/\|\+\s*(.+)/', $tableContent, $captionMatch)) {
            $html .= '<caption>' . htmlspecialchars(trim($captionMatch[1])) . '</caption>';
            $tableContent = preg_replace('/\|\+\s*.+/', '', $tableContent);
        }

        // Podziel na wiersze po |-
        $rows = preg_split('/\n\|-/', "\n" . $tableContent);

        foreach ($rows as $row) {
            $row = trim($row);
            if (empty($row)) continue;

            $html .= '<tr>';

            // Nagłówki (zaczynają się od !)
            if (preg_match('/^!\s*(.+)/s', $row, $headerMatch)) {
                $cells = preg_split('/\s*!!\s*/', trim($headerMatch[1]));
                foreach ($cells as $cell) {
                    $cell = trim($cell);
                    if (!empty($cell)) {
                        $html .= '<th>' . $this->parseInline($cell) . '</th>';
                    }
                }
            }
            // Komórki danych (zaczynają się od |)
            elseif (preg_match('/^\|\s*(.+)/s', $row, $dataMatch)) {
                $cells = preg_split('/\s*\|\|\s*/', trim($dataMatch[1]));
                foreach ($cells as $cell) {
                    $cell = trim($cell);
                    if (!empty($cell)) {
                        $html .= '<td>' . $this->parseInline($cell) . '</td>';
                    }
                }
            }

            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }, $content);
}


    // === CYTATY ===
    private function parseQuotes(string $content): string {
        // > Cytat
        $content = preg_replace_callback('/^>\s+(.+)$/m', function($matches) {
            return '<blockquote class="wiki-quote">' . htmlspecialchars($matches[1]) . '</blockquote>';
        }, $content);

        return $content;
    }

    // === KOLUMNY ===
    private function parseColumns(string $content): string {
        // {{columns|3}} ... --- ... {{/columns}}
        $pattern = '/\{\{columns\|(\d+)\}\}(.*?)\{\{\/columns\}\}/s';

        return preg_replace_callback($pattern, function($matches) {
            $columnCount    = (int)$matches[1];
            $columnsContent = $matches[2];

            // DEBUG (możesz usunąć jak już działa)
            // error_log("RAW CONTENT: " . var_export($columnsContent, true));

            $columns = preg_split('/\r?\n---\r?\n/', trim($columnsContent));

            // error_log("FOUND COLUMNS: " . count($columns));

            $html = '<div class="wiki-columns wiki-columns-' . $columnCount . '">';

            foreach ($columns as $column) {
                $trimmed = trim($column);
                if (!empty($trimmed)) {
                    $html .= '<div class="wiki-column">';
                    $html .= nl2br($trimmed);
                    $html .= '</div>';
                }
            }

            $html .= '</div>';
            return $html;
        }, $content);
    }

    // === BOXY / PANELE ===
    // {{box|info|Tytuł}} ... {{/box}}
    private function parseBoxes(string $content): string {
        $pattern = '/\{\{box\|([a-z]+)(?:\|([^}]*))?\}\}(.*?)\{\{\/box\}\}/s';

        return preg_replace_callback($pattern, function($matches) {
            $type       = $matches[1];
            $title      = isset($matches[2]) ? trim($matches[2]) : '';
            $boxContent = trim($matches[3]);

            $icons = array(
                'info'    => 'ⓘ',
                'warning' => '⚠',
                'success' => '✅',
                'danger'  => '🚫',
                'tip'     => '💡'
            );

            $icon = $icons[$type] ?? '??';

            $html = '<div class="wiki-box wiki-box-' . $type . '">';

            if ($title) {
                $html .= '<div class="wiki-box-title">' . $icon . ' ' . htmlspecialchars($title) . '</div>';
            }

            $html .= '<div class="wiki-box-content">' . $this->parseInline($boxContent) . '</div>';
            $html .= '</div>';

            return $html;
        }, $content);
    }

    // === TEMPLATES / WSTAWKI ===
    private function parseTemplates(string $content): string {
        $content = preg_replace(
            '/\{\{toc\}\}/',
            '<div class="wiki-toc" id="toc"><strong>📁 Spis treści</strong><ul id="toc-list"></ul></div>',
            $content
        );

        $content = preg_replace('/\{\{clear\}\}/', '<div style="clear:both;"></div>', $content);
        $content = preg_replace('/\{\{divider\}\}/', '<hr class="wiki-divider">', $content);

        $content = preg_replace_callback('/\{\{date\}\}/', function() {
            return date('d.m.Y');
        }, $content);

        return $content;
    }

// === SYMBOLE ===
private function parseSymbols(string $content): string {
    // {{symbol:iconName|Alt text}}
    $pattern = '/\{\{symbol:([a-zA-Z0-9_-]+)(?:\|([^}]*))?\}\}/';

    return preg_replace_callback($pattern, function ($m) {
        $iconName = trim($m[1]);
        $label = isset($m[2]) && trim($m[2]) !== '' ? trim($m[2]) : ucfirst($iconName);

        // Sprawdź rozszerzenie - .png, .svg, .jpg
        $src = "/symbols/{$iconName}.png";

        return '<img class="wiki-symbol" src="' . htmlspecialchars($src) . '" 
                     alt="' . htmlspecialchars($label) . '" 
                     title="' . htmlspecialchars($label) . '">';
    }, $content);
}


// === FLAGI ===
private function parseFlags(string $content): string {
    $pattern = '/\{\{flag:([A-Za-z]{2})(?:\|([^}]*))?\}\}/';

    return preg_replace_callback($pattern, function ($m) {
        $code = strtoupper($m[1]);
        $codeLC = strtolower($code);
        $label = isset($m[2]) && trim($m[2]) !== '' ? trim($m[2]) : $code;

        $src = "https://flagcdn.com/w40/{$codeLC}.png";

        // $src = "/flags/{$codeLC}.svg";

        return '<img class="wiki-flag" src="' . htmlspecialchars($src) . '" 
                     alt="' . htmlspecialchars($label) . '" 
                     title="' . htmlspecialchars($label) . '">';
    }, $content);
}



// === OBRAZKI ===
private function parseImages(string $content): string {
    // {{image:filename.jpg|Alt text|left|300px|hero}}
    // {{image:https://example.com/img.png|Alt text|center|400px|shadow}}
    // W alt tekście możesz użyć "\n" jako separatora linii dla podpisu, np.:
    // {{image:...jpg|Premiery \n Oryginał: 27.03.2012 \n Remaster: 08.08.2024|center|250px}}
    $pattern = '/\{\{image:([^|]+)(?:\|([^|]*)?)?(?:\|(left|right|center))?(?:\|(\d+|full)px?)?(?:\|(hero|shadow))?\}\}/';

    return preg_replace_callback($pattern, function($matches) {
        $srcRaw   = trim($matches[1]);
        $altRaw   = isset($matches[2]) && !empty(trim($matches[2])) ? trim($matches[2]) : '';
        $align    = isset($matches[3]) && $matches[3] !== '' ? $matches[3] : 'center';
        $widthRaw = isset($matches[4]) && $matches[4] !== '' ? $matches[4] : '100';
        $mode     = isset($matches[5]) ? $matches[5] : '';

        // Zewnętrzny URL czy lokalny upload
        if (preg_match('~^https?://~i', $srcRaw)) {
            $src = $srcRaw;
        } else {
            $src = '/uploads/' . ltrim($srcRaw, '/');
        }

        $alignClass = 'wiki-image-' . $align;

        $classes = ['wiki-image', $alignClass];
        if ($mode === 'hero') {
            $classes[] = 'wiki-image-hero';
        }
        if ($mode === 'shadow') {
            $classes[] = 'wiki-image-shadow';
        }

        // Szerokość
        if ($widthRaw === 'full') {
            $widthCss = '100%';
        } else {
            $widthCss = (int)$widthRaw . 'px';
        }

        // Przetwarzanie alt:
        // - w alt="" jedna linia (screen readery)
        // - w figcaption "\n" → <br> dla wizualnego łamania linii
        $altForAttr    = str_replace('\n', ' ', $altRaw);
        $altForCaption = str_replace('\n', "\n", $altRaw);
        $altForCaption = nl2br(htmlspecialchars($altForCaption));

        $html  = '<figure class="' . implode(' ', $classes) . '">';
        $html .= '<img src="' . htmlspecialchars($src) . '" ';
        $html .= 'alt="' . htmlspecialchars($altForAttr) . '" ';
        $html .= 'style="max-width:' . $widthCss . ';width:' . $widthCss . ';">';

        if (!empty($altRaw)) {
            $html .= '<figcaption>' . $altForCaption . '</figcaption>';
        }

        $html .= '</figure>';

        return $html;
    }, $content);
}



    // === NAGŁÓWKI ===
    private function parseHeadings(string $content): string {
        $content = preg_replace_callback('/^(#{2,6})\s+(.+)$/m', function($matches) {
            $level = strlen($matches[1]);
            $text  = trim($matches[2]);
            $id    = $this->slugify($text);

            return '<h' . $level . ' id="' . $id . '">' . htmlspecialchars($text) . '</h' . $level . '>';
        }, $content);

        return $content;
    }

    // === FORMATOWANIE ===
    private function parseFormatting(string $content): string {
        $content = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $content);
        $content = preg_replace('/~~(.+?)~~/', '<del>$1</del>', $content);
        $content = preg_replace('/__(.+?)__/', '<u>$1</u>', $content);
        $content = preg_replace('/==(.+?)==/', '<mark>$1</mark>', $content);

        return $content;
    }

// === LISTY ===
private function parseLists(string $content): string {
    // Unordered (*)
    $content = preg_replace_callback('/((?:^\*\s+.+$\n?)+)/m', function($matches) {
        $items = preg_replace('/^\*\s+(.+)$/m', '<li>$1</li>', trim($matches[1]));
        return '<ul>' . $items . '</ul>';
    }, $content);

    // Unordered (-)
    $content = preg_replace_callback('/((?:^-\s+.+$\n?)+)/m', function($matches) {
        $items = preg_replace('/^-\s+(.+)$/m', '<li>$1</li>', trim($matches[1]));
        return '<ul>' . $items . '</ul>';
    }, $content);

    // Ordered
    $content = preg_replace_callback('/((?:^\d+\.\s+.+$\n?)+)/m', function($matches) {
        $items = preg_replace('/^\d+\.\s+(.+)$/m', '<li>$1</li>', trim($matches[1]));
        return '<ol>' . $items . '</ol>';
    }, $content);

    return $content;
}


    // === LINKI ===
    private function parseLinks(string $content): string {
        // [Text](url)
        $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $content);

        // [[Internal Page]]
        $content = preg_replace_callback('/\[\[([^\]]+)\]\]/', function($matches) {
            $page = trim($matches[1]);
            $slug = $this->slugify($page);
            return '<a href="/page/' . $slug . '">' . htmlspecialchars($page) . '</a>';
        }, $content);

        return $content;
    }

    // === KOD (INLINE) ===
    private function parseCode(string $content): string {
        $content = preg_replace_callback("/`([^`]+)`/", function($m) {
            return '<code>' . htmlspecialchars($m[1]) . '</code>';
        }, $content);

        return $content;
    }

    // === PARAGRAFY ===
    private function parseParagraphs(string $content): string {
        $lines       = explode("\n", $content);
        $result      = array();
        $inParagraph = false;
        $inBlock     = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (preg_match('/^<(pre|div|table|figure|ul|ol|h[1-6]|section|aside)/i', $trimmed)) {
                if ($inParagraph) {
                    $result[] = '</p>';
                    $inParagraph = false;
                }
                $inBlock   = true;
                $result[]  = $line;
                continue;
            }

            if (preg_match('/^<\/(pre|div|table|figure|ul|ol|h[1-6]|section|aside)/i', $trimmed)) {
                $inBlock  = false;
                $result[] = $line;
                continue;
            }

            if ($inBlock) {
                $result[] = $line;
                continue;
            }

            if (empty($trimmed)) {
                if ($inParagraph) {
                    $result[] = '</p>';
                    $inParagraph = false;
                }
                continue;
            }

            if (preg_match('/^<[^>]+>/', $trimmed)) {
                if ($inParagraph) {
                    $result[] = '</p>';
                    $inParagraph = false;
                }
                $result[] = $line;
                continue;
            }

            if (!$inParagraph && !$inBlock) {
                $result[]   = '<p>';
                $inParagraph = true;
            }

            $result[] = $line;
        }

        if ($inParagraph) {
            $result[] = '</p>';
        }

        return implode("\n", $result);
    }

    // === TAGI / HASHTAGI ===
    private function parseTags(string $content): string {
        $content = preg_replace_callback(
            '/#([a-zA-Z0-9ąćęłńóśźżĄĆĘŁŃÓŚŹŻ_-]+)/u',
            function($matches) {
                $tag = $matches[1];
                return '<span class="wiki-tag">#' . htmlspecialchars($tag) . '</span>';
            },
            $content
        );

        return $content;
    }

// === HELPER: Parse inline ===
private function parseInline(string $content): string {
    // Dodaj parsowanie list PRZED formatowaniem
    $content = $this->parseLists($content);
    
    $content = $this->parseFormatting($content);
    $content = $this->parseLinks($content);
    $content = $this->parseTags($content);
    $content = $this->parseIcons($content);
    $content = $this->parseBadges($content);
    return nl2br($content);
}


    // === HELPER: Slugify ===
    private function slugify(string $text): string {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^a-z0-9-]+/u', '-', $text);
        $text = trim($text, '-');
        return $text;
    }
}
