<?php
// php fix_chars_paths.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
$pdo = new PDO(
    'mysql:host=127.0.0.1;port=3306;dbname=wiki_engine;charset=utf8mb4',
    'root',
    '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]
);
echo "Połączono\n";

} catch (PDOException $e) {
    die("Błąd połączenia: " . $e->getMessage() . "\n");
}

// bierzemy wszystkie rewizje z infobox-postac i /uploads/chars
$sql = "SELECT revision_id, content 
        FROM revisions
        WHERE content LIKE '%{{infobox-postac%' 
          AND content LIKE '%/uploads/chars%'";
$stmt = $pdo->query($sql);

$count = 0;

$update = $pdo->prepare(
    "UPDATE revisions SET content = :content WHERE revision_id = :id"
);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $orig = $row['content'];
    $new  = preg_replace_callback(
        '#/uploads/chars/([^|\}\s]+)#u',
        function ($m) {
            $filename = $m[1];

            $ext  = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);

            $name = strtolower($name);
            $name = str_replace([' ', '_'], '-', $name);
            $ext  = strtolower($ext);

            return '/uploads/chars/' . $name . ($ext ? '.' . $ext : '');
        },
        $orig
    );

    if ($new !== $orig) {
        $update->execute([
            ':content' => $new,
            ':id'      => $row['revision_id'],
        ]);
        $count++;
        echo "Zaktualizowano revision_id={$row['revision_id']}\n";
    }
}

echo "Gotowe, zmieniono $count rekordów.\n";
