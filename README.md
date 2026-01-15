# Wiki Engine

Silnik wiki napisany w PHP, z prostą strukturą zbliżoną do MVC, obsługą Markdown, kont użytkowników oraz podstawową kontrolą dostępu.

## Funkcje

- Tworzenie i edycja stron w Markdown, renderowanych za pomocą Parsedown.  
- Czyste adresy URL skonfigurowane przez `.htaccess` i prosty front controller w katalogu `public/`.   
- Rejestracja, logowanie i zmiana hasła użytkownika z kontrolą dostępu przez middleware. 
- Prosty model ról/uprawnień do ochrony wybranych akcji i stron.   
- Podstawowe motywy wizualne oparte o CSS, z możliwością zmiany tła/wyglądu. 
- Modularna struktura katalogów: `controllers`, `models`, `views`, `core`, `config`, `api`.

## Stos technologiczny

- PHP (mieszanka podejścia proceduralnego i OOP, własny mini-framework).
- Baza danych MySQL.   
- Parsedown do parsowania Markdown (`vendor/parsedown`).  

## Jak uruchomić

1. Sklonuj repozytorium:  
   `git clone https://github.com/Gulwe/Wiki-engine.git`  
2. Utwórz nową bazę MySQL i zaimportuj plik `wiki_engine.sql`. 
3. Zaktualizuj ustawienia bazy i aplikacji w katalogu `config/` (np. dane dostępowe do DB, bazowy URL). 
4. Ustaw katalog `public/` jako document root serwera i włącz przepisywanie adresów. 
5. Wejdź w aplikację w przeglądarce, utwórz pierwsze konto i zacznij tworzyć strony. 

## Struktura projektu

- `public/` – punkt wejścia, front controller, publiczne zasoby.  
- `core/` – routing, bazowe klasy kontrolerów i modeli, helpery. 
- `controllers/` – kontrolery HTTP dla stron wiki, autoryzacji, API itd. 
- `models/` – logika dostępu do danych i warstwa biznesowa (strony, użytkownicy, ustawienia). 
- `views/` – szablony PHP dla stron, layoutu, formularzy, paska narzędzi i stopki.
- `config/` – pliki konfiguracyjne (baza, ustawienia aplikacji). 
- `api/` – endpointy dla zapytań AJAX/JSON. 
- `misc/` – dodatkowe narzędzia, zasoby i skrypty pomocnicze. 
