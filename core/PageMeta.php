<?php

class PageMeta
{
    /**
     * Wyciąga kody języków z sekcji:
     * #### Obsługiwane języki:
     * {{flag:PL}}
     * {{flag:EN}}
     * lub np. "PL, EN"
     */
    public static function extractLanguages(string $content): array
    {
        $lines = preg_split('/\R/u', $content);
        $languages = [];
        $foundHeader = false;

        foreach ($lines as $line) {
            $trim = trim($line);

            if (!$foundHeader) {
                // nagłówek sekcji
                if (mb_stripos($trim, '#### Obsługiwane języki:') === 0) {
                    $foundHeader = true;
                }
                continue;
            }

            // koniec sekcji przy pustej linii albo kolejnym nagłówku
            if ($trim === '' || preg_match('/^#+\s/', $trim)) {
                break;
            }

            // {{flag:PL}} / {{flaga|PL}}
            if (preg_match_all('/\{\{\s*(?:flag|flaga)[:|]\s*([A-Z]{2})\s*\}\}/i', $trim, $m)) {
                foreach ($m[1] as $code) {
                    $code = strtoupper(trim($code));
                    if ($code !== '') {
                        $languages[] = $code;
                    }
                }
            } else {
                // alternatywnie zwykła lista: "PL, EN"
                $trim = preg_replace('/^[-*]\s*/', '', $trim);
                foreach (explode(',', $trim) as $code) {
                    $code = strtoupper(trim($code));
                    if ($code !== '') {
                        $languages[] = $code;
                    }
                }
            }
        }

        return array_values(array_unique($languages));
    }

    /**
     * Wyciąga opis moda z sekcji:
     * ### Opis moda
     * (jeden lub kilka wierszy tekstu)
     */
    public static function extractModDescription(string $content): string
    {
        $lines = preg_split('/\R/u', $content);
        $foundHeader = false;
        $buffer = [];

        foreach ($lines as $line) {
            $trim = trim($line);

            if (!$foundHeader) {
                if (mb_stripos($trim, '### Opis moda') === 0) {
                    $foundHeader = true;
                }
                continue;
            }

            // koniec sekcji przy pustej linii lub kolejnym nagłówku
            if ($trim === '' || preg_match('/^#+\s/', $trim)) {
                break;
            }

            $buffer[] = $trim;
        }

        if (empty($buffer)) {
            return '';
        }

        // połącz linie w jeden akapit
        $text = implode(' ', $buffer);

        // usuń wikikod / bold / kursywę / szablony
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);      // **bold**
        $text = preg_replace('/\*(.*?)\*/', '$1', $text);          // *italic*
        $text = preg_replace('/\{\{.*?\}\}/s', '', $text);         // {{...}}
        $text = preg_replace('/\[\[(.*?)\]\]/', '$1', $text);      // [[link]]

        $text = strip_tags($text);
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        // przytnij do sensownej długości
        if (mb_strlen($text) > 200) {
            $text = mb_substr($text, 0, 200) . '…';
        }

        return $text;
    }
}
