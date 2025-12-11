<?php
// models/ExternalLink.php

class ExternalLink {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Pobierz ostatnie N linków do wyświetlenia na stronie głównej
public function getRecent($limit = 3) {
    $stmt = $this->db->prepare("
        SELECT el.*, u.username AS author
        FROM external_links el
        LEFT JOIN users u ON el.added_by = u.user_id
        WHERE el.is_visible = 1
        ORDER BY el.sort_order ASC, el.added_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as &$link) {
        // Jeśli nie ma zapisanej miniatury LUB jest pusta, spróbuj automatycznie wykryć
        if (!isset($link['thumbnail']) || empty($link['thumbnail'])) {
            $link['thumbnail'] = $this->getAutoThumbnail($link['url'] ?? '');
        }
    }

    return $results;
}

// Pobierz wszystkie (dla widoku admina)
public function getAll() {
    $stmt = $this->db->query("
        SELECT el.*, u.username AS author
        FROM external_links el
        LEFT JOIN users u ON el.added_by = u.user_id
        ORDER BY el.sort_order ASC, el.added_at DESC
    ");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as &$link) {
        // Jeśli nie ma zapisanej miniatury LUB jest pusta, spróbuj automatycznie wykryć
        if (!isset($link['thumbnail']) || empty($link['thumbnail'])) {
            $link['thumbnail'] = $this->getAutoThumbnail($link['url'] ?? '');
        }
    }

    return $results;
}


    // Dodaj link (nowa wersja z obsługą miniatury)
    public function create($title, $url, $description, $source, $icon, $userId, $thumbnail = null) {
        // następna wartość sort_order (ostatni + 1)
        $maxSort = (int)$this->db->query("SELECT COALESCE(MAX(sort_order), 0) FROM external_links")->fetchColumn();
        $nextSort = $maxSort + 1;

        $stmt = $this->db->prepare("
            INSERT INTO external_links (title, url, description, source, icon, thumbnail, added_by, is_visible, sort_order)
            VALUES (:title, :url, :description, :source, :icon, :thumbnail, :added_by, 1, :sort_order)
        ");

        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':url', $url, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, PDO::PARAM_STR);
        $stmt->bindValue(':source', $source, PDO::PARAM_STR);
        $stmt->bindValue(':icon', $icon, PDO::PARAM_STR);
        $stmt->bindValue(':sort_order', $nextSort, PDO::PARAM_INT);

        // Obsługa miniatury (może być NULL)
        if ($thumbnail === null) {
            $stmt->bindValue(':thumbnail', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':thumbnail', $thumbnail, PDO::PARAM_STR);
        }

        if ($userId === null) {
            $stmt->bindValue(':added_by', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':added_by', (int)$userId, PDO::PARAM_INT);
        }

        return $stmt->execute();
    }

    // Usuń link (wraz z plikiem miniatury jeśli istnieje)
    public function delete($linkId) {
        // Pobierz informacje o linku przed usunięciem
        $stmt = $this->db->prepare("SELECT thumbnail FROM external_links WHERE link_id = :id");
        $stmt->execute(['id' => (int)$linkId]);
        $link = $stmt->fetch(PDO::FETCH_ASSOC);

        // Usuń plik miniatury jeśli istnieje i jest lokalny
        if ($link && !empty($link['thumbnail']) && strpos($link['thumbnail'], '/uploads/') === 0) {
            $filePath = __DIR__ . '/../public' . $link['thumbnail'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Usuń rekord z bazy
        $stmt = $this->db->prepare("DELETE FROM external_links WHERE link_id = :id");
        return $stmt->execute(['id' => (int)$linkId]);
    }

    // Przełącz widoczność
    public function toggleVisibility($linkId) {
        $stmt = $this->db->prepare("
            UPDATE external_links
            SET is_visible = NOT is_visible
            WHERE link_id = :id
        ");
        return $stmt->execute(['id' => (int)$linkId]);
    }

    // Przesuń link w górę lub w dół
    public function move($linkId, $direction) {
        // bieżący rekord
        $stmt = $this->db->prepare("
            SELECT link_id, sort_order
            FROM external_links
            WHERE link_id = :id
        ");
        $stmt->execute(['id' => (int)$linkId]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$current) {
            return false;
        }

        $op    = ($direction === 'up') ? '<' : '>';
        $order = ($direction === 'up') ? 'DESC' : 'ASC';

        // sąsiad nad/poniżej
        $stmt = $this->db->prepare("
            SELECT link_id, sort_order
            FROM external_links
            WHERE sort_order $op :sort
            ORDER BY sort_order $order
            LIMIT 1
        ");
        $stmt->execute(['sort' => (int)$current['sort_order']]);
        $neighbor = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$neighbor) {
            // brak sąsiada (jest już najwyżej/najniżej)
            return false;
        }

        // zamiana sort_order
        $this->db->beginTransaction();

        $upd = $this->db->prepare("UPDATE external_links SET sort_order = :sort WHERE link_id = :id");

        $upd->execute([
            'sort' => (int)$neighbor['sort_order'],
            'id'   => (int)$current['link_id'],
        ]);

        $upd->execute([
            'sort' => (int)$current['sort_order'],
            'id'   => (int)$neighbor['link_id'],
        ]);

        $this->db->commit();
        return true;
    }

    // Obsługa uploadu miniatury
    public function handleThumbnailUpload($file) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadDir = __DIR__ . '/../public/uploads/thumbnails/';
        
        // Utwórz katalog jeśli nie istnieje
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileInfo = pathinfo($file['name']);
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower($fileInfo['extension'] ?? '');

        // Walidacja typu pliku
        if (!in_array($extension, $allowedTypes)) {
            return null;
        }

        // Walidacja rozmiaru (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return null;
        }

        // Walidacja MIME type dla bezpieczeństwa
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            return null;
        }

        // Generuj unikalną nazwę
        $fileName = uniqid('thumb_') . '.' . $extension;
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return '/uploads/thumbnails/' . $fileName;
        }

        return null;
    }

    // Walidacja URL miniatury
    public function validateThumbnailUrl($url) {
        if (empty($url)) {
            return null;
        }

        // Sprawdź czy to poprawny URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        // Opcjonalnie: sprawdź czy URL rzeczywiście prowadzi do obrazka
        $headers = @get_headers($url, 1);
        if ($headers && strpos($headers[0], '200') !== false) {
            $contentType = $headers['Content-Type'] ?? '';
            if (is_array($contentType)) {
                $contentType = $contentType[0];
            }
            
            if (strpos($contentType, 'image/') === 0) {
                return $url;
            }
        }

        return $url; // Zwróć URL nawet jeśli walidacja się nie powiodła
    }

// Automatyczne wykrywanie miniatury (YouTube, itp.)
public function getAutoThumbnail($url) {
    if (empty($url)) {
        return null;
    }

    // YouTube: https://www.youtube.com/watch?v=ID lub https://youtu.be/ID
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
        $videoId = $m[1];
        return 'https://img.youtube.com/vi/' . $videoId . '/mqdefault.jpg';
    }

    // Vimeo: https://vimeo.com/ID
    if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
        $videoId = $m[1];
        return "https://vumbnail.com/{$videoId}.jpg";
    }

    // Twitch: https://www.twitch.tv/videos/ID
    if (preg_match('/twitch\.tv\/videos\/(\d+)/', $url, $m)) {
        $videoId = $m[1];
        return "https://static-cdn.jtvnw.net/cf_vods/d2nvs31859zcd8/{$videoId}/thumb/thumb0-640x360.jpg";
    }

    // ModDB: https://www.moddb.com/mods/nazwa
    if (preg_match('/moddb\.com\/mods\/([^\/]+)/', $url, $m)) {
        // ModDB nie ma prostego API dla miniatur, zwróć null
        // Można by spróbować scrapować stronę, ale to nie jest zalecane
        return null;
    }

    // inne linki – brak miniatury
    return null;
}

}
