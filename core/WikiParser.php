<?php
require_once __DIR__ . '/../vendor/parsedown/Parsedown.php';

class WikiParser {
    private $parsedown;
    
    public function __construct() {
        $this->parsedown = new Parsedown();
        $this->parsedown->setSafeMode(true); // Bezpieczeñstwo
    }
    
    public function parse(string $wikiText): string {
        // Pre-processing: obs³uga custom wiki syntax
        $wikiText = $this->processWikiLinks($wikiText);
        $wikiText = $this->processImageLinks($wikiText);
        
        // Konwersja Markdown do HTML
        $html = $this->parsedown->text($wikiText);
        
        return $html;
    }
    
    // Obs³uga [[WikiLink]] -> <a href="/page/wikilink">WikiLink</a>
    private function processWikiLinks(string $text): string {
        return preg_replace_callback('/\[\[([^\]]+)\]\]/', function($matches) {
            $linkText = $matches[1];
            $parts = explode('|', $linkText);
            $slug = strtolower(str_replace(' ', '-', trim($parts[0])));
            $display = isset($parts[1]) ? trim($parts[1]) : trim($parts[0]);
            
            return "[{$display}](/page/{$slug})";
        }, $text);
    }
    
    // Obs³uga {{image:filename.jpg|alt text}}
    private function processImageLinks(string $text): string {
        return preg_replace_callback('/\{\{image:([^|]+)\|?([^\}]*)\}\}/', function($matches) {
            $filename = trim($matches[1]);
            $alt = isset($matches[2]) ? trim($matches[2]) : '';
            
            return "![{$alt}](/uploads/{$filename})";
        }, $text);
    }
}
