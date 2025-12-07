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
            $link['thumbnail'] = $this->getThumbnail($link['url'] ?? '');
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
            $link['thumbnail'] = $this->getThumbnail($link['url'] ?? '');
        }

        return $results;
    }

    // Dodaj link
    public function create($title, $url, $description, $source, $icon, $userId) {
        // następna wartość sort_order (ostatni + 1)
        $maxSort = (int)$this->db->query("SELECT COALESCE(MAX(sort_order), 0) FROM external_links")->fetchColumn();
        $nextSort = $maxSort + 1;

        $stmt = $this->db->prepare("
            INSERT INTO external_links (title, url, description, source, icon, added_by, is_visible, sort_order)
            VALUES (:title, :url, :description, :source, :icon, :added_by, 1, :sort_order)
        ");

        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':url', $url, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, PDO::PARAM_STR);
        $stmt->bindValue(':source', $source, PDO::PARAM_STR);
        $stmt->bindValue(':icon', $icon, PDO::PARAM_STR);
        $stmt->bindValue(':sort_order', $nextSort, PDO::PARAM_INT);

        if ($userId === null) {
            $stmt->bindValue(':added_by', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':added_by', (int)$userId, PDO::PARAM_INT);
        }

        return $stmt->execute();
    }

    // Usuń link
    public function delete($linkId) {
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

    // Wyznacz thumbnail (na razie tylko YouTube)
    private function getThumbnail($url) {
        // YouTube: https://www.youtube.com/watch?v=ID lub https://youtu.be/ID
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            $videoId = $m[1];
            return 'https://img.youtube.com/vi/' . $videoId . '/mqdefault.jpg';
        }

        // inne linki – brak miniatury
        return null;
    }
}
