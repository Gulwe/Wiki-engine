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

        return '<div class="youtube-embed" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; margin: 25px 0;"><iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 2px solid rgba(139, 92, 246, 0.3); border-radius: 12px;" src="https://www.youtube.com/embed/' . htmlspecialchars($videoId) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
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

public function parse(string $content): string {
    if (empty($content)) {
        return '';
    }

    // 0. TABELE - MUSZĄ BYĆ PIERWSZE!
    $content = $this->parseTables($content);

    // 1. Kod (inline)
    $content = $this->parseCode($content);
    $content = $this->parseLineBreaks($content);

    // 2. Struktury / bloki
    $content = $this->parseInfobox($content);
    $content = $this->parseQuote($content);
    $content = $this->parseRightImage($content);
    $content = $this->parseTemplates($content);
    $content = $this->parseCharacters($content);
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
    $content = $this->parseImages($content);
    $content = $this->parseFlags($content);
    $content = $this->parseSymbols($content);
    $content = $this->parseYouTube($content);


    // 3. Inline
    $content = $this->parseHeadings($content);
    $content = $this->parseLists($content);
    $content = $this->parseFormatting($content);
    $content = $this->parseBadges($content);
    $content = $this->parseIcons($content);
    $content = $this->parseProgress($content);

    $content = $this->parseQuotes($content);
    $content = $this->parseLinks($content);
    $content = $this->parseTags($content);

    // 4. Paragrafy
    //$content = $this->parseParagraphs($content);

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

private function parseQuote(string $content): string
{
    $pattern = '/\{\{quote\s*(.*?)\}\}/is';

    return preg_replace_callback($pattern, function ($m) {
        $raw = $m[1];
        $params = [];

        preg_match_all(
            '/\|\s*([a-z_]+)\s*=\s*(.*?)(?=\n\|\s*[a-z_]+\s*=|\}\}|$)/is',
            $raw,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $key = trim($match[1]);
            $value = trim($match[2]);
            $params[$key] = $value;
        }

        $text   = $params['text']   ?? '';
        $author = $params['author'] ?? '';

        if ($text === '') {
            return '';
        }

        $html  = '<figure class="wiki-quote">';
        $html .=    '<blockquote class="wiki-quote-text">' . $text . '</blockquote>';

        if ($author !== '') {
            $html .= '<figcaption class="wiki-quote-author">— ' .
                     htmlspecialchars($author) . '</figcaption>';
        }

        $html .= '</figure>';

        return $html;
    }, $content);
}


// === TABELE ===
// private function parseTables(string $content): string {
//     // MediaWiki-like
//     $pattern = '/\{\|(.*?)\n(.*?)\|\}/s';

//     return preg_replace_callback($pattern, function($matches) {
//         $attributes    = trim($matches[1]);
//         $tableContent  = $matches[2];

//         $class = 'wiki-table';
//         if (preg_match('/class=["\']([^"\']+)["\']/', $attributes, $classMatch)) {
//             $class .= ' ' . $classMatch[1];
//         }

//         $html = '<table class="' . $class . '">';

//         // Caption
//         if (preg_match('/\|\+\s*(.+)/', $tableContent, $captionMatch)) {
//             $html .= '<caption>' . htmlspecialchars(trim($captionMatch[1])) . '</caption>';
//             $tableContent = preg_replace('/\|\+\s*.+/', '', $tableContent);
//         }

//         // Podziel na wiersze po |-
//         $rows = preg_split('/\n\|-/', "\n" . $tableContent);

//         foreach ($rows as $row) {
//             $row = trim($row);
//             if (empty($row)) continue;

//             $html .= '<tr>';

//             // Nagłówki (zaczynają się od !)
//             if (preg_match('/^!\s*(.+)/s', $row, $headerMatch)) {
//                 $cells = preg_split('/\s*!!\s*/', trim($headerMatch[1]));
//                 foreach ($cells as $cell) {
//                     $cell = trim($cell);
//                     if (!empty($cell)) {
//                         $html .= '<th>' . $this->parseInline($cell) . '</th>';
//                     }
//                 }
//             }
//             // Komórki danych (zaczynają się od |)
//             elseif (preg_match('/^\|\s*(.+)/s', $row, $dataMatch)) {
//                 $cells = preg_split('/\s*\|\|\s*/', trim($dataMatch[1]));
//                 foreach ($cells as $cell) {
//                     $cell = trim($cell);
//                     if (!empty($cell)) {
//                         $html .= '<td>' . $this->parseInline($cell) . '</td>';
//                     }
//                 }
//             }

//             $html .= '</tr>';
//         }

//         $html .= '</table>';
//         return $html;
//     }, $content);
// }

// === NOWA LINIA [br] ===
private function parseLineBreaks(string $content): string {
    return str_replace('[br]', '<br>', $content);
}

// === TABELE [table] ===
private function parseTables(string $content): string {
    return preg_replace_callback('/\[table(?:\s+class="([^"]+)")?\](.*?)\[\/table\]/s', function($matches) {
        $tableClass = !empty($matches[1]) ? 'wiki-table ' . $matches[1] : 'wiki-table';
        $tableContent = $matches[2];
        
        $output = '<table class="' . $tableClass . '">';
        
        preg_match_all('/\[row\](.*?)\[\/row\]/s', $tableContent, $rowMatches);
        
        foreach ($rowMatches[1] as $rowContent) {
            $output .= '<tr>';
            
            // Nagłówki [header]
            if (preg_match_all('/\[header(?:\s+rowspan="(\d+)")?(?:\s+colspan="(\d+)")?\](.*?)\[\/header\]/s', $rowContent, $headerMatches, PREG_SET_ORDER)) {
                foreach ($headerMatches as $header) {
                    $rowspan = !empty($header[1]) ? ' rowspan="' . $header[1] . '"' : '';
                    $colspan = !empty($header[2]) ? ' colspan="' . $header[2] . '"' : '';
                    $output .= '<th' . $rowspan . $colspan . '>' . htmlspecialchars(trim($header[3])) . '</th>';
                }
            }
            
            // Komórki [cell]
            if (preg_match_all('/\[cell(?:\s+rowspan="(\d+)")?(?:\s+colspan="(\d+)")?\](.*?)\[\/cell\]/s', $rowContent, $cellMatches, PREG_SET_ORDER)) {
                foreach ($cellMatches as $cell) {
                    $rowspan = !empty($cell[1]) ? ' rowspan="' . $cell[1] . '"' : '';
                    $colspan = !empty($cell[2]) ? ' colspan="' . $cell[2] . '"' : '';
                    
                    $cellText = trim($cell[3]);
                    
                    // Parsuj TYLKO obrazki {{image:...}}
                    $cellText = preg_replace_callback('/\{\{image:([^}]+)\}\}/', function($m) {
                        return '<img src="/uploads/' . htmlspecialchars($m[1]) . '" alt="" style="max-width:auto;height:auto;vertical-align:middle;">';
                    }, $cellText);
                    
                    // Parsuj TYLKO bold **...**
                    $cellText = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $cellText);
                    
                    // Zamień [br] na <br>
                    $cellText = str_replace('[br]', '<br>', $cellText);
                    
                    // Zamień entery na <br>
                    $cellText = nl2br($cellText);
                    
                    $output .= '<td' . $rowspan . $colspan . '>' . $cellText . '</td>';
                }
            }
            
            $output .= '</tr>';
        }
        
        $output .= '</table>';
        return $output;
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
    // TOC
    $content = preg_replace(
        '/\{\{toc\}\}/',
        '<div class="wiki-toc" id="toc"><strong>📁 Spis treści</strong><ul id="toc-list"></ul></div>',
        $content
    );

    // Inne proste wstawki
    $content = preg_replace('/\{\{clear\}\}/', '<div style="clear:both;"></div>', $content);
    $content = preg_replace('/\{\{divider\}\}/', '<hr class="wiki-divider">', $content);

    $content = preg_replace_callback('/\{\{date\}\}/', function () {
        return date('d.m.Y');
    }, $content);

    // === INFOBOX POSTAĆ ===
    $content = preg_replace_callback(
        '/\{\{infobox-postac\s*(.*?)\}\}/si',
        function ($matches) {
            $rawParams = trim($matches[1]);
            $params    = $this->parseTemplateParams($rawParams);
            return $this->renderInfoboxPostac($params);
        },
        $content
    );
    // === MAPA INTERAKTYWNA ===
$content = preg_replace_callback(
    '/\{\{mapa\s*(.*?)\}\}/si',
    function ($matches) {
        $rawParams = trim($matches[1]);
        $params    = $this->parseTemplateParams($rawParams);
        return $this->renderMap($params);
    },
    $content
);
// === PRZYCISK POWROTU ===
$content = preg_replace_callback(
    '/\{\{back-button\s*(.*?)\}\}/si',
    function ($matches) {
        $raw    = trim($matches[1]);
        $params = $this->parseTemplateParams($raw);
        return $this->renderBackButton($params);
    },
    $content
);

// INFOBOX BAZY
$content = preg_replace_callback(
    '/\{\{baza\s*(.*?)\}\}/si',
    function ($m) {
        $raw    = trim($m[1]);
        $params = $this->parseTemplateParams($raw);
        return $this->renderInfoboxBaza($params);
    },
    $content
);


// KARTA MODA
$content = preg_replace_callback(
    '/\{\{mod\s*(.*?)\}\}/si',
    function ($m) {
        $raw    = trim($m[1]);
        $params = $this->parseTemplateParams($raw);
        return $this->renderModCard($params);
    },
    $content
);




    return $content;
}



private function renderInfoboxBaza(array $params): string
{
    $nazwa      = htmlspecialchars($params['nazwa']   ?? 'Nieznana baza');
    $obrazek    = trim($params['obrazek'] ?? 'NA.png');
    
    // dodatkowe zdjęcia
    $obrazek2       = trim($params['obrazek2'] ?? '');
    $obrazek3       = trim($params['obrazek3'] ?? '');
    $obrazek4       = trim($params['obrazek4'] ?? '');
    $obrazek5       = trim($params['obrazek5'] ?? '');
    $obrazek1Label  = htmlspecialchars($params['obrazek1_label'] ?? '1');
    $obrazek2Label  = htmlspecialchars($params['obrazek2_label'] ?? '2');
    $obrazek3Label  = htmlspecialchars($params['obrazek3_label'] ?? '3');
    $obrazek4Label  = htmlspecialchars($params['obrazek4_label'] ?? '4');
    $obrazek5Label  = htmlspecialchars($params['obrazek5_label'] ?? '5');
    
    $typ        = htmlspecialchars($params['typ']     ?? '');
    $dataRaw    = trim($params['data']    ?? '');
    $nacjaPath  = trim($params['nacja']   ?? ''); // pełna ścieżka /uploads/XYZ.png
    $dowodcaRaw = trim($params['dowodca'] ?? '');

    $html = '<div class="infobox infobox-baza">';

    // nagłówek
    $html .= '<div class="infobox-header">' . $nazwa . '</div>';

    // ==== OBRAZKI (główny + przełączane) ====
    $images = [];

    if ($obrazek !== '') {
        $images[] = [
            'src'   => htmlspecialchars($obrazek, ENT_QUOTES),
            'label' => $obrazek1Label,
        ];
    }
    if ($obrazek2 !== '') {
        $images[] = [
            'src'   => htmlspecialchars($obrazek2, ENT_QUOTES),
            'label' => $obrazek2Label,
        ];
    }
    if ($obrazek3 !== '') {
        $images[] = [
            'src'   => htmlspecialchars($obrazek3, ENT_QUOTES),
            'label' => $obrazek3Label,
        ];
    }
    if ($obrazek4 !== '') {
        $images[] = [
            'src'   => htmlspecialchars($obrazek4, ENT_QUOTES),
            'label' => $obrazek4Label,
        ];
    }
        if ($obrazek5 !== '') {
        $images[] = [
            'src'   => htmlspecialchars($obrazek5, ENT_QUOTES),
            'label' => $obrazek5Label,
        ];
    }

    if (!empty($images)) {
        $first = $images[0];

        $html .= '<div class="infobox-image infobox-image-multi">';
        $html .= '<img data-infobox-main="1" src="' . $first['src'] . '" alt="' . $nazwa . '">';
        $html .= '</div>';

        if (count($images) > 1) {
            $html .= '<div class="infobox-image-tabs">';
            foreach ($images as $idx => $img) {
                $active = $idx === 0 ? ' active' : '';
                $html .= '<button type="button" class="infobox-image-tab' . $active . '"'
                       . ' data-target-src="' . $img['src'] . '">'
                       . $img['label']
                       . '</button>';
            }
            $html .= '</div>';
        }
    }

    $html .= '<div class="infobox-section">';

    // Nacja – obrazek ze ścieżki
    if ($nacjaPath !== '') {
        $srcNacja = htmlspecialchars($nacjaPath, ENT_QUOTES);
        $html .= '<div class="infobox-row infobox-row-nacja">'
               .  '<span class="infobox-label">Nacja:</span> '
               .  '<span class="infobox-value infobox-value-nacja">'
               .    '<img src="' . $srcNacja . '" alt="Nacja" class="infobox-icon">'
               .  '</span>'
               . '</div>';
    }

    // Dowódca – linki wiki (obsługa listy)
    if ($dowodcaRaw !== '') {
        // Rozdziel po znaku nowej linii
        $dowodcy = preg_split('/[\r\n]+/', $dowodcaRaw);
        $dowodcy = array_filter(array_map('trim', $dowodcy));
        
        if (!empty($dowodcy)) {
            $label = count($dowodcy) > 1 ? 'Dowódcy:' : 'Dowódca:';
            
            $html .= '<div class="infobox-row">'
                   .  '<span class="infobox-label">' . $label . '</span> '
                   .  '<span class="infobox-value infobox-value-dowodcy">';
            
            $parsedDowodcy = [];
            foreach ($dowodcy as $dowodca) {
                $parsedDowodcy[] = $this->parseInline($dowodca);
            }
            
            $html .= implode('', $parsedDowodcy);
            
            $html .= '</span></div>';
        }
    }

    // Typ
    if ($typ !== '') {
        $html .= '<div class="infobox-row">'
               .  '<span class="infobox-label">Typ:</span> '
               .  '<span class="infobox-value">' . $typ . '</span>'
               . '</div>';
    }

    // Data
    if ($dataRaw !== '') {
        $html .= '<div class="infobox-row">'
               .  '<span class="infobox-label">Data założenia:</span> '
               .  '<span class="infobox-value">' . htmlspecialchars($dataRaw) . '</span>'
               . '</div>';
    }

    $html .= '</div>'; // section
    $html .= '</div>'; // infobox

    return $html;
}








private function renderModCard(array $params): string
{
    $name  = trim($params['name'] ?? '');
    $link  = trim($params['link'] ?? '');
    $img   = trim($params['img']  ?? '');
    $desc  = trim($params['desc'] ?? '');
    $author      = trim($params['author'] ?? '');
    $author_link = trim($params['author_link'] ?? '');

    if ($name === '') {
        return '';
    }

    // Escaping
    $nameEsc   = htmlspecialchars($name, ENT_QUOTES);
    $descEsc   = htmlspecialchars($desc, ENT_QUOTES);
    $linkEsc   = $link !== '' ? htmlspecialchars($link, ENT_QUOTES) : '';
    $imgEsc    = $img  !== '' ? htmlspecialchars($img, ENT_QUOTES)  : '';
    $authorEsc = htmlspecialchars($author, ENT_QUOTES);
    $authorLinkEsc = $author_link !== '' ? htmlspecialchars($author_link, ENT_QUOTES) : '';

    // Tytuł jako link do strony moda
    $titleHtml = $linkEsc !== ''
        ? '<a class="mod-card-title-link" href="' . $linkEsc . '">' . $nameEsc . '</a>'
        : $nameEsc;

    // Autor jako link (jeśli podany)
    $authorHtml = '';
    if ($authorEsc !== '') {
        $authorLabel = $authorEsc;
        if ($authorLinkEsc !== '') {
            $authorLabel = '<a href="' . $authorLinkEsc . '">' . $authorLabel . '</a>';
        }
        $authorHtml = '<div class="mod-card-author">Autor: ' . $authorLabel . '</div>';
    }

    // Obrazek miniaturka (opcjonalny)
    $thumbHtml = '';
    if ($imgEsc !== '') {
        $thumbHtml =
            '<div class="mod-card-thumb">' .
                '<img src="' . $imgEsc . '" alt="' . $nameEsc . '">' .
            '</div>';
    }

    // Opis
    $descHtml = $descEsc !== ''
        ? '<div class="mod-card-desc">' . $descEsc . '</div>'
        : '';

    return
        '<article class="mod-card">' .
            $thumbHtml .
            '<div class="mod-card-body">' .
                '<h3 class="mod-card-title">' . $titleHtml . '</h3>' .
                $descHtml .
                $authorHtml .
            '</div>' .
        '</article>';
}


private function renderBackButton(array $params): string
{
    $href = trim($params['href'] ?? '');
    if ($href === '') {
        return '';
    }

    $label  = trim($params['label'] ?? 'Wróć');
    $symbol = trim($params['symbol'] ?? 'arrow-left');
    $symbol = preg_replace('/[^a-zA-Z0-9_-]/', '', $symbol);

    $hrefEsc   = htmlspecialchars($href, ENT_QUOTES);
    $labelHtml = htmlspecialchars($label, ENT_QUOTES);

    // 1. Obecny motyw – zależy skąd go bierzesz
    $theme = $this->currentTheme ?? 'default';

    // 2. Mapowanie nazw symboli na „kategorie” z BackgroundHelpera
    $categoryMap = [
        'bazy'        => 'bazy',
        'profesje'    => 'profesje',
        'fabryka'     => 'fabryka',
        'budynki'     => 'budynki',
        'postacie'    => 'postacie',
        'technologie' => 'technologie',
        'modyfikacje' => 'modyfikacje',
        'autorzy'     => 'autorzy',
        'dead'        => 'dead',
        'potyczki'        => 'potyczki',
        'multiplayer'        => 'multiplayer',
    ];

    $innerIcon = '';

    if (isset($categoryMap[$symbol])) {
        // 3. Pobranie ścieżki ikony dla danego symbolu z BackgroundHelpera
        $iconPath = BackgroundHelper::getNationIconForTheme($theme, $categoryMap[$symbol]);
        $iconEsc  = htmlspecialchars($iconPath, ENT_QUOTES);

        $innerIcon =
            '<img src="' . $iconEsc . '" alt="' . $labelHtml . '" ' .
            'class="lore-icon icon-small lore-icon--back" data-category="' . $categoryMap[$symbol] . '">';

    } else {
        // fallback: stary symbol wiki
        $innerIcon = '{{symbol:' . $symbol . '|' . $label . '}}';
    }

    return '<div class="wiki-back-button">'
         .   '<a href="' . $hrefEsc . '">'
         .     $innerIcon
         .     '<span class="wiki-back-label">' . $labelHtml . '</span>'
         .   '</a>'
         . '</div>';
}





// === RENDER: MAPA INTERAKTYWNA ===
private function renderMap(array $params): string
{
    $src = trim($params['src'] ?? '');
    if ($src === '') {
        return '';
    }

    $width  = (int)($params['width']  ?? 800);
    $height = (int)($params['height'] ?? 600);

    $points = [];

    foreach ($params as $key => $value) {
        if (strpos($key, 'point') === 0 && trim($value) !== '') {
            // x;y;href;title;symbol?
            $parts = array_map('trim', explode(';', $value, 5));
            if (count($parts) < 3) {
                continue;
            }

            $points[] = [
                'x'      => rtrim($parts[0], '%'),
                'y'      => rtrim($parts[1], '%'),
                'href'   => $parts[2],
                'title'  => $parts[3] ?? '',
                'symbol' => $parts[4] ?? 'map-marker',
            ];
        }
    }

    $html  = '<div class="wiki-map" style="--map-w:' . $width . ';--map-h:' . $height . ';';
    $html .= 'background-image:url(\'' . htmlspecialchars($src, ENT_QUOTES) . '\')">';

    foreach ($points as $p) {
        $x = (float)$p['x'];
        $y = (float)$p['y'];
        $href   = htmlspecialchars($p['href'], ENT_QUOTES);
        $title  = $p['title'] ?? '';
        $titleEsc = htmlspecialchars($title, ENT_QUOTES);
        $symbol = preg_replace('/[^a-zA-Z0-9_-]/', '', $p['symbol'] ?? 'map-marker');

        // ikona + etykieta (tooltip na mapie)
        $innerIcon  = '{{symbol:' . $symbol . '|' . $title . '}}';
        $innerLabel = $title !== '' ? '<span class="wiki-map-label">' . $titleEsc . '</span>' : '';

        $html .= '<a class="wiki-map-point" href="' . $href . '"'
              .  ' style="left:' . $x . '%;top:' . $y . '%;">'
              .       $innerIcon . $innerLabel
              .  '</a>';
    }

    $html .= '</div>';

    return $html;
}





    // === KARTY POSTACI ===
private function parseCharacters(string $content): string {
    // {{char|John Macmillan|/wiki/John_Macmillan|chars/john_macmillan.png|Dowódca, USA}}
    $pattern = '/\{\{char\|([^|]+)\|([^|]+)\|([^|]+)\|([^}]+)\}\}/';

    return preg_replace_callback($pattern, function($m) {
        $name = trim($m[1]);
        $url  = trim($m[2]);
        $img  = trim($m[3]);

        return '
        <div class="character-card">
          <a href="'.htmlspecialchars($url).'">
            <div class="character-portrait">
              {{image:'.htmlspecialchars($img).'}}
            </div>
            <div class="character-name">'.htmlspecialchars($name).'</div>
          </a>
        </div>';
    }, $content);
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
    // Zmieniony pattern - akceptuje litery, cyfry, myślniki i podkreślenia
    $pattern = '/\{\{flag:([A-Za-z0-9\-_]+)(?:\|([^}]*))?\}\}/';

    return preg_replace_callback($pattern, function ($m) {
        $code = strtoupper($m[1]);
        $codeLC = strtolower($m[1]); // Nie uppercase całości, tylko dla label
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
    // Dopasuj TYLKO do pierwszego }} (lazy match)
    $pattern = '/\{\{image:([^|}]+?)(?:\|([^|]*)?)?(?:\|(left|right|center))?(?:\|(\d+|full)px?)?(?:\|(hero|shadow))?\}\}/';

    return preg_replace_callback($pattern, function($matches) {
        $srcRaw   = trim($matches[1]);
        $altRaw   = isset($matches[2]) && !empty(trim($matches[2])) ? trim($matches[2]) : '';
        $align    = isset($matches[3]) && $matches[3] !== '' ? $matches[3] : 'center';
        $widthRaw = isset($matches[4]) && $matches[4] !== '' ? $matches[4] : '100';
        $mode     = isset($matches[5]) ? $matches[5] : '';

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

        if ($widthRaw === 'full') {
            $widthCss = '100%';
        } else {
            $widthCss = (int)$widthRaw . 'px';
        }

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
    $content = preg_replace('/\*\*\*(.+?)\*\*\*/s', '<strong><em>$1</em></strong>', $content);  // *** -> bold+italic
    $content = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $content);  // ** -> bold
    $content = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $content);              // * -> italic
    $content = preg_replace('/~~(.+?)~~/s', '<del>$1</del>', $content);
    $content = preg_replace('/__(.+?)__/s', '<u>$1</u>', $content);
    $content = preg_replace('/==(.+?)==/s', '<mark>$1</mark>', $content);

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

    // [[Internal Page]] lub [[Internal Page|Label]]
    $content = preg_replace_callback('/\[\[([^\]|]+)(?:\|([^\]]+))?\]\]/u', function($matches) {
        $page  = trim($matches[1]);                               // np. "Kradzież Stali"
        $label = isset($matches[2]) && $matches[2] !== ''
            ? trim($matches[2])
            : $page;

        // najpierw transliteracja polskich znaków
        $map = [
            'ą'=>'a','ć'=>'c','ę'=>'e','ł'=>'l','ń'=>'n','ó'=>'o','ś'=>'s','ż'=>'z','ź'=>'z',
            'Ą'=>'A','Ć'=>'C','Ę'=>'E','Ł'=>'L','Ń'=>'N','Ó'=>'O','Ś'=>'S','Ż'=>'Z','Ź'=>'Z',
        ];
        $pageForSlug = strtr($page, $map);

        // a potem Twój istniejący slugify
        $slug = $this->slugify($pageForSlug);                     // "kradziez-stali"

        return '<a href="/page/' . htmlspecialchars($slug) . '">' .
               htmlspecialchars($label) .
               '</a>';
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
    ;
    return $content;
}



    // === HELPER: Slugify ===
    private function slugify(string $text): string {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^a-z0-9-]+/u', '-', $text);
        $text = trim($text, '-');
        return $text;
    }
    
private function parseInfobox(string $content): string
{
    // 1) infobox postaci
    $patternPostac = '/\{\{infobox-postac\s*(.*?)\}\}/is';

    $content = preg_replace_callback($patternPostac, function ($matches) {
        $paramsRaw = trim($matches[1]);
        $params    = $this->parseTemplateParams($paramsRaw);
        return $this->renderInfoboxPostac($params);
    }, $content);

    // 2) infobox bazy
    $patternBaza = '/\{\{infobox-baza\s*(.*?)\}\}/is';
    $content = preg_replace_callback($patternBaza, function ($matches) {
        $paramsRaw = $matches[1];

        $params = [];
        preg_match_all(
            '/\|\s*([a-z_]+)\s*=\s*((?:(?!\|\s*[a-z_]+\s*=).)*)/is',
            $paramsRaw,
            $paramMatches,
            PREG_SET_ORDER
        );

        foreach ($paramMatches as $match) {
            $key   = trim($match[1]);
            $value = trim($match[2]);
            $value = preg_replace('/[ \t]+/', ' ', $value);
            $params[$key] = $value;
        }

        return $this->renderInfoboxBaza($params);
    }, $content);

    return $content;
}



    // === PARSUJ PARAMETRY SZABLONU ===
    private function parseTemplateParams(string $paramsString): array {
        $params = [];
        $lines = explode("\n", $paramsString);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] !== '|') {
                continue;
            }
            
            // Usuń początkowy |
            $line = substr($line, 1);
            
            // Podziel na klucz = wartość
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                $params[$key] = $value;
            }
        }
        
        return $params;
    }


private function parseRightImage(string $content): string
{
    // ✅ Bardziej precyzyjny regex - łapie dokładnie {{right-image...}}
    $pattern = '/\{\{right-image\s*\n?(.*?)\n?\}\}/is';

    return preg_replace_callback($pattern, function ($m) {
        $paramsRaw = $m[1];
        
        // Parse parametrów linia po linii
        $params = [];
        $lines = explode("\n", $paramsRaw);
        
        foreach ($lines as $line) {
            if (preg_match('/^\s*\|\s*([a-z_]+)\s*=\s*(.+)$/i', $line, $match)) {
                $key = trim($match[1]);
                $value = trim($match[2]);
                $params[$key] = $value;
            }
        }
        
        $src = $params['src'] ?? '';
        $caption = $params['caption'] ?? '';
        $alt = $params['alt'] ?? '';

        if ($src === '') {
            return ''; // Usuń szablon jeśli brak src
        }

        // Parsuj linki wiki w caption
        $parsedCaption = preg_replace_callback(
            '/\[\[([^\]|]+)(?:\|([^\]]+))?\]\]/',
            function($matches) {
                $target = trim($matches[1]);
                $label = isset($matches[2]) ? trim($matches[2]) : $target;
                $slug = str_replace(' ', '_', $target);
                return '<a href="/page/' . htmlspecialchars($slug) . '" class="wiki-link">' 
                     . htmlspecialchars($label) . '</a>';
            },
            $caption
        );
        
        $altText = $alt !== '' ? $alt : strip_tags($parsedCaption);

        $html = '<figure class="right-image-box">' .
                '<div class="right-image-inner">' .
                    '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($altText) . '">' .
                    ($parsedCaption !== ''
                        ? '<figcaption class="right-image-caption">' . $parsedCaption . '</figcaption>'
                        : ''
                    ) .
                '</div>' .
            '</figure>';
        
        return $html;
    }, $content);
}







private function renderInfoboxPostac(array $params): string
{
    $imie        = htmlspecialchars($params['imie'] ?? 'Nieznana postać');
    $zdjecie     = trim($params['zdjecie'] ?? '/uploads/NA.png');

    // dodatkowe zdjęcia
    $zdjecie2       = trim($params['zdjecie2'] ?? '');
    $zdjecie3       = trim($params['zdjecie3'] ?? '');
    $zdjecie4       = trim($params['zdjecie4'] ?? '');
    $zdjecie1Label  = htmlspecialchars($params['zdjecie1_label'] ?? '2');
    $zdjecie2Label  = htmlspecialchars($params['zdjecie2_label'] ?? '2');
    $zdjecie3Label  = htmlspecialchars($params['zdjecie3_label'] ?? '3');
    $zdjecie4Label  = htmlspecialchars($params['zdjecie4_label'] ?? '4');

        // ścieżka do fallbacku
    $fallbackImg = '/uploads/NA.png';

    $wiekRaw     = trim($params['wiek'] ?? '');
    $pochodzenie = trim($params['pochodzenie'] ?? '');
    $pochodzenieFlag = $params['pochodzenie_flaga'] ?? '';
    $dubbingRaw  = trim($params['dubbing'] ?? '');
    $nacja       = $params['nacja'] ?? '';
    $nacjaAlt    = htmlspecialchars($params['nacja_alt'] ?? 'Nacja');

    $stopien       = trim($params['stopien']  ?? '');
    $stopienLabel  = htmlspecialchars($params['stopien_label'] ?? 'Stopień:');

    $stopienEx      = trim($params['stopienEx'] ?? '');
    $stopienLabel2  = htmlspecialchars($params['stopien_labelEx'] ?? '');

    $profesjaRaw = trim($params['profesja'] ?? '');

    // Umiejętności
    $zolnierz  = $params['zolnierz'] ?? '';
    $inzynier  = $params['inzynier'] ?? '';
    $mechanik  = $params['mechanik'] ?? '';
    $naukowiec = $params['naukowiec'] ?? '';

    // Formatuj stopnie tylko jeśli coś jest
    $stopienFormatted    = $stopien    !== '' ? $this->formatMultilineField($stopien)    : '';
    $stopienExFormatted  = $stopienEx  !== '' ? $this->formatMultilineField($stopienEx)  : '';

    $html = '<div class="infobox infobox-postac">';

    // Nagłówek
    $html .= '<div class="infobox-header">' . $imie . '</div>';

    // ==== ZDJĘCIA (główne + przełączane) ====
    $images = [];

    if ($zdjecie !== '') {
        $images[] = [
            'src'   => htmlspecialchars($zdjecie, ENT_QUOTES),
            'label' => $zdjecie1Label,
        ];
    }
    if ($zdjecie2 !== '') {
        $images[] = [
            'src'   => htmlspecialchars($zdjecie2, ENT_QUOTES),
            'label' => $zdjecie2Label,
        ];
    }
    if ($zdjecie3 !== '') {
        $images[] = [
            'src'   => htmlspecialchars($zdjecie3, ENT_QUOTES),
            'label' => $zdjecie3Label,
        ];
    }
    if ($zdjecie4 !== '') {
        $images[] = [
            'src'   => htmlspecialchars($zdjecie4, ENT_QUOTES),
            'label' => $zdjecie4Label,
        ];
    }

    if (!empty($images)) {
        $first = $images[0];

        $html .= '<div class="infobox-image infobox-image-multi">';
        $html .= '<img data-infobox-main="1" src="' . $first['src'] . '" alt="' . $imie . '">';
        $html .= '</div>';

        if (count($images) > 1) {
            $html .= '<div class="infobox-image-tabs">';
            foreach ($images as $idx => $img) {
                $active = $idx === 0 ? ' active' : '';
                $html .= '<button type="button" class="infobox-image-tab' . $active . '"'
                       . ' data-target-src="' . $img['src'] . '">'
                       . $img['label']
                       . '</button>';
            }
            $html .= '</div>';
        }
    }

    // Podstawowe informacje
    $html .= '<div class="infobox-section">';

    // Wiek – tylko jeśli podany
    if ($wiekRaw !== '') {
        $html .= '<div class="infobox-row">';
        $html .= '<span class="infobox-label">Wiek:</span> ';
        $html .= '<span class="infobox-value">' . htmlspecialchars($wiekRaw) . '</span>';
        $html .= '</div>';
    }

    // Pochodzenie z flagą – tylko jeśli jest flaga lub tekst
    if ($pochodzenieFlag !== '' || $pochodzenie !== '') {
        $html .= '<div class="infobox-row">';
        $html .= '<span class="infobox-label">Pochodzenie:</span> ';
        $html .= '<span class="infobox-value">';
        if ($pochodzenieFlag !== '') {
            $html .= '{{flag:' . $pochodzenieFlag . '}} ';
        }
        $html .= htmlspecialchars($pochodzenie);
        $html .= '</span>';
        $html .= '</div>';
    }

    // Dubbing – tylko jeśli podany
    if ($dubbingRaw !== '') {
        $html .= '<div class="infobox-row">';
        $html .= '<span class="infobox-label">Dubbing:</span> ';
        $html .= '<span class="infobox-value">' . htmlspecialchars($dubbingRaw) . '</span>';
        $html .= '</div>';
    }

    $html .= '</div>'; // /infobox-section

    // Separator tylko jeśli cokolwiek poniżej istnieje
    if ($nacja !== '' || $stopienFormatted !== '' || $stopienExFormatted !== '' || $profesjaRaw !== '') {
        $html .= '<div class="infobox-separator">Informacje</div>';
        $html .= '<div class="infobox-section">';

        // Nacja
        if ($nacja !== '') {
            $html .= '<div class="infobox-row">';
            $html .= '<span class="infobox-label">Nacja:</span> ';
            $html .= '<span class="infobox-value">';
            $html .= '<img src="/symbols/' . htmlspecialchars($nacja) . '.png" alt="' . $nacjaAlt . '" class="infobox-icon"> ';
            $html .= $nacjaAlt;
            $html .= '</span>';
            $html .= '</div>';
        }

        // STOPIEŃ 1
        if ($stopienFormatted !== '') {
            if (strpos($stopienFormatted, '<br>') !== false) {
                $html .= '<div class="infobox-row infobox-row-stacked">';
                $html .= '<span class="infobox-label">' . $stopienLabel . '</span>';
                $html .= '<div class="infobox-value-multi">' . $stopienFormatted . '</div>';
                $html .= '</div>';
            } else {
                $html .= '<div class="infobox-row">';
                $html .= '<span class="infobox-label">' . $stopienLabel . '</span> ';
                $html .= '<span class="infobox-value">' . $stopienFormatted . '</span>';
                $html .= '</div>';
            }
        }

        // STOPIEŃ EX (opcjonalny)
        if ($stopienExFormatted !== '') {
            $label2 = $stopienLabel2 !== '' ? $stopienLabel2 : $stopienLabel;
            if (strpos($stopienExFormatted, '<br>') !== false) {
                $html .= '<div class="infobox-row infobox-row-stacked">';
                $html .= '<span class="infobox-label">' . $label2 . '</span>';
                $html .= '<div class="infobox-value-multi">' . $stopienExFormatted . '</div>';
                $html .= '</div>';
            } else {
                $html .= '<div class="infobox-row">';
                $html .= '<span class="infobox-label">' . $label2 . '</span> ';
                $html .= '<span class="infobox-value">' . $stopienExFormatted . '</span>';
                $html .= '</div>';
            }
        }

        // Profesja
        if ($profesjaRaw !== '') {
            $html .= '<div class="infobox-row">';
            $html .= '<span class="infobox-label">Profesja:</span> ';
            $html .= '<span class="infobox-value">' . htmlspecialchars($profesjaRaw) . '</span>';
            $html .= '</div>';
        }

        $html .= '</div>'; // /infobox-section
    }

    // Umiejętności – sekcja tylko jeśli coś jest
    if ($zolnierz || $inzynier || $mechanik || $naukowiec) {
        $html .= '<div class="infobox-separator">Umiejętności początkowe</div>';
        $html .= '<div class="infobox-section infobox-skills-section">';

        if (!empty($zolnierz)) {
            $html .= '<div class="infobox-skill-row">';
            $html .= '<span class="infobox-skill-label">Żołnierz:</span>';
            $html .= '<img src="/symbols/' . htmlspecialchars($zolnierz) . '.png" alt="Żołnierz" class="infobox-skill-icon">';
            $html .= '</div>';
        }

        if (!empty($inzynier)) {
            $html .= '<div class="infobox-skill-row">';
            $html .= '<span class="infobox-skill-label">Inżynier:</span>';
            $html .= '<img src="/symbols/' . htmlspecialchars($inzynier) . '.png" alt="Inżynier" class="infobox-skill-icon">';
            $html .= '</div>';
        }

        if (!empty($mechanik)) {
            $html .= '<div class="infobox-skill-row">';
            $html .= '<span class="infobox-skill-label">Mechanik:</span>';
            $html .= '<img src="/symbols/' . htmlspecialchars($mechanik) . '.png" alt="Mechanik" class="infobox-skill-icon">';
            $html .= '</div>';
        }

        if (!empty($naukowiec)) {
            $html .= '<div class="infobox-skill-row">';
            $html .= '<span class="infobox-skill-label">Naukowiec:</span>';
            $html .= '<img src="/symbols/' . htmlspecialchars($naukowiec) . '.png" alt="Naukowiec" class="infobox-skill-icon">';
            $html .= '</div>';
        }

        $html .= '</div>'; // /infobox-skills-section
    }

    $html .= '</div>'; // /infobox

    return $html;
}




/**
 * Sprawdź czy pole jest wielolinijkowe
 */
private function isMultilineField($value) {
    return !empty($value) && $value !== '-' && preg_match('/^\s*-/m', $value);
}


/**
 * Formatuj wielolinijkowe pole (zamienia - na bullet points)
 */
private function formatMultilineField($value) {
    if (empty($value) || $value === '-') {
        return '<span class="infobox-empty">-</span>';
    }
    
    // Usuń znaki nowej linii i zbędne spacje
    $value = preg_replace('/\s+/', ' ', trim($value));
    
    // Podziel przez " - " (spacja-myślnik-spacja)
    $parts = preg_split('/\s+-\s+/', $value, -1, PREG_SPLIT_NO_EMPTY);
    
    if (count($parts) <= 1) {
        // Jeśli tylko jedna część, zwróć jako zwykły tekst
        return htmlspecialchars($value);
    }
    
    // Zwróć jako tekst z <br>
    $lines = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if (!empty($part)) {
            $lines[] = htmlspecialchars($part);
        }
    }
    
    return implode('<br>', $lines);
}




// === HELPER: Generuj URL flagi ===
private function generateFlagSrc(string $code): string {
    // Jeśli to dwuliterowy kod ISO
    if (strlen($code) === 2 && ctype_alpha($code)) {
        $codeLC = strtolower($code);
        return "https://flagcdn.com/w40/{$codeLC}.png";
    }
    
    // W przeciwnym razie lokalna flaga
    return "/symbols/{$code}.png";
}



    // === RENDER: INFOBOX JEDNOSTKA ===
    private function renderInfoboxJednostka(array $params): string {
        $nazwa = htmlspecialchars($params['nazwa'] ?? 'Nieznana jednostka');
        $obrazek = htmlspecialchars($params['obrazek'] ?? '');
        $typ = htmlspecialchars($params['typ'] ?? '-');
        $koszt = htmlspecialchars($params['koszt'] ?? '-');
        $hp = htmlspecialchars($params['hp'] ?? '-');
        $atak = htmlspecialchars($params['atak'] ?? '-');
        $pancerz = htmlspecialchars($params['pancerz'] ?? '-');
        
        $html = '<div class="infobox infobox-jednostka">';
        $html .= '<div class="infobox-header">' . $nazwa . '</div>';
        
        if (!empty($obrazek)) {
            $html .= '<div class="infobox-image">';
            $html .= '<img src="' . $obrazek . '" alt="' . $nazwa . '">';
            $html .= '</div>';
        }
        
        $html .= '<div class="infobox-section">';
        $html .= '<div class="infobox-row"><span class="infobox-label">Typ:</span> <span class="infobox-value">' . $typ . '</span></div>';
        $html .= '<div class="infobox-row"><span class="infobox-label">Koszt:</span> <span class="infobox-value">' . $koszt . '</span></div>';
        $html .= '<div class="infobox-row"><span class="infobox-label">HP:</span> <span class="infobox-value">' . $hp . '</span></div>';
        $html .= '<div class="infobox-row"><span class="infobox-label">Atak:</span> <span class="infobox-value">' . $atak . '</span></div>';
        $html .= '<div class="infobox-row"><span class="infobox-label">Pancerz:</span> <span class="infobox-value">' . $pancerz . '</span></div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }

    // === RENDER: INFOBOX BUDYNEK ===
    private function renderInfoboxBudynek(array $params): string {
        $nazwa = htmlspecialchars($params['nazwa'] ?? 'Nieznany budynek');
        $obrazek = htmlspecialchars($params['obrazek'] ?? '');
        
        $html = '<div class="infobox infobox-budynek">';
        $html .= '<div class="infobox-header">' . $nazwa . '</div>';
        
        if (!empty($obrazek)) {
            $html .= '<div class="infobox-image">';
            $html .= '<img src="' . $obrazek . '" alt="' . $nazwa . '">';
            $html .= '</div>';
        }
        
        $html .= '<div class="infobox-section">';
        $html .= '<p style="color: var(--text-muted); font-size: 12px; text-align: center;">Szablon w budowie</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }

    
}
