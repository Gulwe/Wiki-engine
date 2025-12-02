<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Pomoc - Składnia Wiki - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <div class="container">
        <h1>📚 Składnia Wiki</h1>
        
        <h2>📝 Podstawowe Formatowanie</h2>
        <table class="wiki-table wikitable">
            <tr>
                <th>Składnia</th>
                <th>Rezultat</th>
            </tr>
            <tr>
                <td>de>**pogrubienie**</code></td>
                <td><strong>pogrubienie</strong></td>
            </tr>
            <tr>
                <td>de>*kursywa*</code></td>
                <td><em>kursywa</em></td>
            </tr>
            <tr>
                <td>de>__podkreślenie__</code></td>
                <td><u>podkreślenie</u></td>
            </tr>
            <tr>
                <td>de>~~przekreślenie~~</code></td>
                <td><del>przekreślenie</del></td>
            </tr>
            <tr>
                <td>de>==zaznaczenie==</code></td>
                <td><mark>zaznaczenie</mark></td>
            </tr>
        </table>
        
        <h2>🔗 Linki</h2>
        <pre>de>[Tekst linku](https://example.com)
[[Wewnętrzna Strona]]</code></pre>
        
        <h2>📊 Tabele</h2>
        <pre>de>{| class="wikitable"
|+ Tytuł tabeli
|-
! Nagłówek 1 !! Nagłówek 2 !! Nagłówek 3
|-
| Komórka 1 || Komórka 2 || Komórka 3
|-
| Komórka 4 || Komórka 5 || Komórka 6
|}</code></pre>
        
        <h2>📐 Kolumny</h2>
        <pre>de>{{columns|2}}
Treść lewej kolumny
---
Treść prawej kolumny
{{/columns}}</code></pre>
        
        <h2>📦 Boxy</h2>
        <pre>de>{{box|info|Tytuł}}
Treść boxa informacyjnego
{{/box}}

Typy: info, warning, success, danger, tip</code></pre>
        
        <h2>🖼️ Obrazki</h2>
        <pre>de>{{image:nazwa.jpg|Opis|center|500px}}

Pozycje: left, right, center
Rozmiar opcjonalny: 500px</code></pre>
        
        <h2>🔧 Inne</h2>
        <pre>de>{{toc}} - Automatyczny spis treści
{{divider}} - Pozioma linia
{{date}} - Obecna data
{{clear}} - Wyczyść float</code></pre>
        
        <a href="/" class="btn">🏠 Powrót</a>
    </div>
</body>
</html>
