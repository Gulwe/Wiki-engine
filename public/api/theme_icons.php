<?php
// public/api/theme_icons.php

require_once __DIR__ . '/../../core/BackgroundHelper.php';

header('Content-Type: application/json; charset=utf-8');

$theme = $_GET['theme'] ?? 'default';

$icons = [
    'bazy'        => BackgroundHelper::getNationIconForTheme($theme, 'bazy'),
    'profesje'    => BackgroundHelper::getNationIconForTheme($theme, 'profesje'),
    'fabryka'     => BackgroundHelper::getNationIconForTheme($theme, 'fabryka'),
    'budynki'     => BackgroundHelper::getNationIconForTheme($theme, 'budynki'),
    'postacie'    => BackgroundHelper::getNationIconForTheme($theme, 'postacie'),
    'technologie' => BackgroundHelper::getNationIconForTheme($theme, 'technologie'),
    'modyfikacje' => BackgroundHelper::getNationIconForTheme($theme, 'modyfikacje'),
    'autorzy'     => BackgroundHelper::getNationIconForTheme($theme, 'autorzy'),
    'dead'     => BackgroundHelper::getNationIconForTheme($theme, 'dead'),
    'potyczki'     => BackgroundHelper::getNationIconForTheme($theme, 'potyczki'),
    'multiplayer'     => BackgroundHelper::getNationIconForTheme($theme, 'multiplayer'),
];

echo json_encode($icons);
