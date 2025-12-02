<?php
require_once __DIR__ . '/../core/Database.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 3) {
    echo json_encode([]);
    exit;
}

$db = Database::getInstance()->getConnection();

// Fulltext search w MySQL
$stmt = $db->prepare("
    SELECT p.page_id, p.slug, p.title, 
           SUBSTRING(r.content, 1, 200) as excerpt
    FROM pages p
    JOIN revisions r ON p.current_revision_id = r.revision_id
    WHERE MATCH(p.title) AGAINST(:query IN NATURAL LANGUAGE MODE)
       OR MATCH(r.content) AGAINST(:query IN NATURAL LANGUAGE MODE)
    LIMIT 10
");

$stmt->execute(['query' => $query]);
$results = $stmt->fetchAll();

echo json_encode($results);
