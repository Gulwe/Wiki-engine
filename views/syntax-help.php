<?php
// views/syntax-help.php
require_once __DIR__ . '/../core/ThemeLoader.php';
?>  
    <div class="container">
                <div class="page-header">
            <h1>📚 Składnia Wiki</h1>
            <p class="subtitle">Kompletny przewodnik po dostępnych elementach formatowania</p>
        </div>
        
        <!-- Podstawowe Formatowanie -->
        <section class="syntax-section">
            <h2>📝 Podstawowe Formatowanie</h2>
            <table class="wiki-table wikitable">
                <thead>
                    <tr>
                        <th>Składnia</th>
                        <th>Rezultat</th>
                        <th>Opis</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>**pogrubienie**</code></td>
                        <td><strong>pogrubienie</strong></td>
                        <td>Pogrubiony tekst</td>
                    </tr>
                    <tr>
                        <td><code>*kursywa*</code></td>
                        <td><em>kursywa</em></td>
                        <td>Pochylony tekst</td>
                    </tr>
                    <tr>
                        <td><code>__podkreślenie__</code></td>
                        <td><u>podkreślenie</u></td>
                        <td>Podkreślony tekst</td>
                    </tr>
                    <tr>
                        <td><code>~~przekreślenie~~</code></td>
                        <td><del>przekreślenie</del></td>
                        <td>Przekreślony tekst</td>
                    </tr>
                    <tr>
                        <td><code>==zaznaczenie==</code></td>
                        <td><mark>zaznaczenie</mark></td>
                        <td>Podświetlony tekst</td>
                    </tr>
                    <tr>
                        <td><code>`kod`</code></td>
                        <td><code>kod</code></td>
                        <td>Kod inline</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Nagłówki -->
        <section class="syntax-section">
            <h2>📑 Nagłówki</h2>
            <table class="wiki-table wikitable">
                <thead>
                    <tr>
                        <th>Składnia</th>
                        <th>Poziom</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code># Nagłówek 1</code></td>
                        <td><h1 style="margin:0;">Nagłówek 1</h1></td>
                    </tr>
                    <tr>
                        <td><code>## Nagłówek 2</code></td>
                        <td><h2 style="margin:0;">Nagłówek 2</h2></td>
                    </tr>
                    <tr>
                        <td><code>### Nagłówek 3</code></td>
                        <td><h3 style="margin:0;">Nagłówek 3</h3></td>
                    </tr>
                    <tr>
                        <td><code>#### Nagłówek 4</code></td>
                        <td><h4 style="margin:0;">Nagłówek 4</h4></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Linki -->
        <section class="syntax-section">
            <h2>🔗 Linki</h2>
            <table class="wiki-table wikitable">
                <thead>
                    <tr>
                        <th>Składnia</th>
                        <th>Rezultat</th>
                        <th>Opis</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[Tekst](https://example.com)</code></td>
                        <td><a href="https://example.com">Tekst</a></td>
                        <td>Link zewnętrzny</td>
                    </tr>
                    <tr>
                        <td><code>[[Strona]]</code></td>
                        <td><a href="/page/strona">Strona</a></td>
                        <td>Link wewnętrzny</td>
                    </tr>
                    <tr>
                        <td><code>[[Strona|Własny tekst]]</code></td>
                        <td><a href="/page/strona">Własny tekst</a></td>
                        <td>Link z własnym tekstem</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Listy -->
        <section class="syntax-section">
            <h2>📝 Listy</h2>
            <div class="example-grid">
                <div>
                    <h3>Lista punktowana</h3>
                    <pre><code>- Element 1
- Element 2
  - Podpunkt 2.1
  - Podpunkt 2.2
- Element 3</code></pre>
                </div>
                <div>
                    <h3>Lista numerowana</h3>
                    <pre><code>1. Pierwszy
2. Drugi
   1. Podpunkt 2.1
   2. Podpunkt 2.2
3. Trzeci</code></pre>
                </div>
            </div>
        </section>

        <!-- Tabele -->
        <section class="syntax-section">
            <h2>📊 Tabele</h2>
            <h3>Składnia WikiTable</h3>
            <pre><code>{| class="wikitable"
|+ Tytuł tabeli (opcjonalnie)
|-
! Nagłówek 1 !! Nagłówek 2 !! Nagłówek 3
|-
| Komórka 1 || Komórka 2 || Komórka 3
|-
| Komórka 4 || Komórka 5 || Komórka 6
|}</code></pre>

            <h3>Składnia Markdown</h3>
            <pre><code>| Kolumna 1 | Kolumna 2 | Kolumna 3 |
|-----------|-----------|-----------|
| Wartość 1 | Wartość 2 | Wartość 3 |
| Wartość 4 | Wartość 5 | Wartość 6 |</code></pre>
        </section>

        <!-- Obrazki -->
        <section class="syntax-section">
            <h2>🖼️ Obrazki</h2>
            <table class="wiki-table wikitable">
                <thead>
                    <tr>
                        <th>Składnia</th>
                        <th>Opis</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>{{image:nazwa.jpg}}</code></td>
                        <td>Obrazek domyślny (wyśrodkowany, 300px)</td>
                    </tr>
                    <tr>
                        <td><code>{{image:nazwa.jpg|Opis|center|500px}}</code></td>
                        <td>Pełna składnia z opisem, pozycją i rozmiarem</td>
                    </tr>
                    <tr>
                        <td><code>{{image:nazwa.jpg||left|400px}}</code></td>
                        <td>Bez opisu, wyrównany do lewej, 400px</td>
                    </tr>
                </tbody>
            </table>
            <p><strong>Pozycje:</strong> <code>left</code>, <code>center</code>, <code>right</code></p>
            <p><strong>Rozmiar:</strong> dowolna wartość px, np. <code>500px</code></p>
        </section>

        <!-- Layouty -->
        <section class="syntax-section">
            <h2>📐 Layouty i Kolumny</h2>
            
            <h3>Kolumny równomiernie</h3>
            <pre><code>{{columns|2}}
Treść lewej kolumny
---
Treść prawej kolumny
{{/columns}}</code></pre>
            <p>Dostępne: <code>2</code>, <code>3</code>, <code>4</code> kolumny</p>

            <h3>Podział custom (split)</h3>
            <pre><code>{{split|40}}
Lewa strona (40%)
---
Prawa strona (60%)
{{/split}}</code></pre>

            <h3>Siatka (grid)</h3>
            <pre><code>{{grid|3}}
Element 1
---
Element 2
---
Element 3
{{/grid}}</code></pre>

            <h3>Sekcja</h3>
            <pre><code>{{section|full|dark}}
Treść sekcji z pełną szerokością i ciemnym tłem
{{/section}}</code></pre>
            <p><strong>Szerokość:</strong> <code>full</code>, <code>boxed</code></p>
            <p><strong>Style:</strong> <code>default</code>, <code>dark</code>, <code>light</code>, <code>accent</code></p>
        </section>

        <!-- Boxy i Alerty -->
        <section class="syntax-section">
            <h2>📦 Boxy i Alerty</h2>
            
            <h3>Box informacyjny</h3>
            <pre><code>{{box|info|Tytuł}}
Treść boxa informacyjnego
{{/box}}</code></pre>
            <p><strong>Typy:</strong> <code>info</code>, <code>warning</code>, <code>success</code>, <code>danger</code>, <code>tip</code></p>

            <h3>Alert</h3>
            <pre><code>{{alert|warning|Uwaga|Treść alertu}}</code></pre>
            <p><strong>Typy:</strong> <code>info</code>, <code>success</code>, <code>warning</code>, <code>danger</code></p>

            <h3>Karta (Card)</h3>
            <pre><code>{{card|Tytuł karty|Opis karty|/link|primary}}</code></pre>
            <p><strong>Kolory:</strong> <code>primary</code>, <code>success</code>, <code>warning</code>, <code>danger</code></p>

            <h3>Sidebar / Infobox</h3>
            <pre><code>{{sidebar|Tytuł|right|center}}
Treść sidebara
{{/sidebar}}</code></pre>
            <p><strong>Pozycje:</strong> <code>left</code>, <code>right</code></p>
            <p><strong>Wyrównanie tekstu:</strong> <code>left</code>, <code>center</code>, <code>right</code></p>
        </section>

        <!-- Elementy interaktywne -->
        <section class="syntax-section">
            <h2>⚡ Elementy Interaktywne</h2>
            
            <h3>Accordion (zwijane)</h3>
            <pre><code>{{accordion|Kliknij aby rozwinąć}}
Treść ukryta w środku
{{/accordion}}</code></pre>

            <h3>Pasek postępu</h3>
            <pre><code>{{progress|75|Ukończone 75%}}</code></pre>

            <h3>Przycisk</h3>
            <pre><code>{{button|https://example.com|Kliknij tutaj|primary}}</code></pre>
            <p><strong>Kolory:</strong> <code>primary</code>, <code>success</code>, <code>danger</code></p>

            <h3>Oś czasu (Timeline)</h3>
            <pre><code>{{timeline}}
2020|Początek projektu|Pierwszy commit
2021|Wersja beta|Publiczne testy
2022|Stabilne wydanie|Wersja 1.0
{{/timeline}}</code></pre>
        </section>

        <!-- Multimedia -->
        <section class="syntax-section">
            <h2>🎬 Multimedia</h2>
            
            <h3>YouTube</h3>
            <pre><code>{{youtube|dQw4w9WgXcQ}}</code></pre>
            <p>Wklej ID filmu lub pełny URL</p>

            <h3>Audio</h3>
            <pre><code>{{audio|plik.mp3}}</code></pre>

            <h3>Video</h3>
            <pre><code>{{video|film.mp4}}</code></pre>
        </section>

        <!-- Kod -->
        <section class="syntax-section">
            <h2>💻 Kod</h2>
            
            <h3>Blok kodu</h3>
            <pre><code>```
function hello() {
    echo "Hello World!";
}
```</code></pre>
            <p>Dostępne języki: <code>php</code>, <code>javascript</code>, <code>python</code>, <code>css</code>, <code>html</code>, <code>sql</code></p>
        </section>

        <!-- Małe elementy -->
        <section class="syntax-section">
            <h2>🏷️ Małe Elementy</h2>
            
            <table class="wiki-table wikitable">
                <thead>
                    <tr>
                        <th>Składnia</th>
                        <th>Rezultat</th>
                        <th>Opis</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>{{badge|NEW|success}}</code></td>
                        <td><span class="badge badge-success">NEW</span></td>
                        <td>Etykieta / Badge</td>
                    </tr>
                    <tr>
                        <td><code>{{icon|star|gold}}</code></td>
                        <td>⭐</td>
                        <td>Ikona z kolorem</td>
                    </tr>
                    <tr>
                        <td><code>#przykład</code></td>
                        <td><span class="tag">#przykład</span></td>
                        <td>Tag / Hashtag</td>
                    </tr>
                    <tr>
                        <td><code>{{flag:PL}}</code></td>
                        <td>{{flag:PL}}</td>
                        <td>Flaga kraju</td>
                    </tr>
                    <tr>
                        <td><code>{{flag:pl|Polski}}</code></td>
                        <td>🇵🇱 Polski</td>
                        <td>Flaga z etykietą</td>
                    </tr>
                    <tr>
                        <td><code>{{symbol:am_small}}</code></td>
                        <td><img src="/symbols/am_small.png" alt="AM" style="height:20px;"></td>
                        <td>Symbol kampanii</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Narzędzia -->
        <section class="syntax-section">
            <h2>🔧 Narzędzia</h2>
            
            <table class="wiki-table wikitable">
                <thead>
                    <tr>
                        <th>Składnia</th>
                        <th>Opis</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>{{toc}}</code></td>
                        <td>Automatyczny spis treści z nagłówków</td>
                    </tr>
                    <tr>
                        <td><code>{{divider}}</code></td>
                        <td>Pozioma linia oddzielająca</td>
                    </tr>
                    <tr>
                        <td><code>{{clear}}</code></td>
                        <td>Wyczyść float (przydatne po obrazkach)</td>
                    </tr>
                    <tr>
                        <td><code>{{date}}</code></td>
                        <td>Obecna data</td>
                    </tr>
                    <tr>
                        <td><code>{{br}}</code></td>
                        <td>Łamanie linii</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Cytaty -->
        <section class="syntax-section">
            <h2>💬 Cytaty</h2>
            
            <h3>Prosty cytat</h3>
            <pre><code>> To jest cytat
> Może być wieloliniowy</code></pre>

            <h3>Blok cytatu</h3>
            <pre><code>{{quote}}
Długi cytat lub wypowiedź
{{/quote}}</code></pre>
        </section>

        <!-- Szablony -->
        <section class="syntax-section">
            <h2>🧩 Szablony</h2>
            <p>Szablony to gotowe fragmenty treści, które możesz wstawić w edytorze.</p>
            <p>Administratorzy mogą zarządzać szablonami w <a href="/admin/templates">Panelu Admina → Szablony</a>.</p>
            <p>W edytorze wybierz szablon z menu <strong>"🧩 Wstaw szablon..."</strong></p>
        </section>

        <!-- Przykład kompletnej strony -->
        <section class="syntax-section">
            <h2>📄 Przykład Kompletnej Strony</h2>
            <pre><code>## Tytuł główny

{{box|info|Ważna informacja}}
To jest przykładowa strona wiki z wieloma elementami.
{{/box}}

### Opis

To jest **pogrubiony tekst**, a to *kursywa*. Możesz także ==zaznaczyć== tekst.

{{columns|2}}
**Lewa kolumna**
- Punkt 1
- Punkt 2
---
**Prawa kolumna**
- Punkt A
- Punkt B
{{/columns}}

### Tabela

| Funkcja | Opis | Status |
|---------|------|--------|
| Login | Logowanie | ✅ |
| Register | Rejestracja | ⏳ |

### Multimedia

{{image:screen.jpg|Screenshot|center|600px}}

{{youtube|dQw4w9WgXcQ}}

### Kod

function hello() {
echo "Hello World!";
}


{{divider}}

{{button|/|Powrót do głównej|primary}}</code></pre>
        </section>

        <!-- Powrót -->
        <div class="syntax-actions">
            <a href="/" class="btn btn-primary">🏠 Powrót na stronę główną</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/page/new" class="btn btn-success">➕ Utwórz nową stronę</a>
            <?php endif; ?>
        </div>
    </div>
