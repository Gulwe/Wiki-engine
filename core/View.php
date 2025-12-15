<?php
// core/View.php

class View
{
    public static function render($viewFile, $data = [], $layout = 'main')
    {
        // Wyciągnij zmienne do lokalnego scope
        extract($data);
        
        // Pobierz zawartość widoku
        ob_start();
        require __DIR__ . "/../views/{$viewFile}.php";
        $content = ob_get_clean();
        
        
        // Renderuj layout
        if ($layout) {
            require __DIR__ . "/../views/layouts/{$layout}.php";
        } else {
            echo $content;
        }
    }
}
