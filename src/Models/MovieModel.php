<?php
/**
 * CineWeeknd v3 - Movie Model
 */

require_once __DIR__ . '/../../config/database.php';

class MovieModel {

    public static function all(array $filters = [], int $limit = 12, int $offset = 0): array {
        $db = getDB();
        $where = ['m.is_active = 1'];
        $params = [];

        if (!empty($filters['genre_id'])) {
            $where[] = 'm.genre_id = ?';
            $params[] = (int) $filters['genre_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = 'MATCH(m.title, m.synopsis, m.director) AGAINST(? IN BOOLEAN MODE)';
            $params[] = $filters['search'] . '*';
        }
        if (!empty($filters['featured'])) {
            $where[] = 'm.is_featured = 1';
        }
        if (!empty($filters['national'])) {
            $where[] = 'm.is_national = 1';
        }
        if (!empty($filters['besteirol'])) {
            $where[] = 'm.is_besteirol = 1';
        }
        if (!empty($filters['oscar'])) {
            $where[] = 'm.is_oscar = 1';
        }
        if (!empty($filters['bestseller'])) {
            $where[] = 'm.is_bestseller = 1';
        }

        // Ordenação
        $order = match($filters['order'] ?? '') {
            'rating'     => 'm.rating DESC',
            'year'       => 'm.release_year DESC',
            'popularity' => 'm.popularity_score DESC',
            'az'         => 'm.title ASC',
            default      => 'm.rating DESC, m.popularity_score DESC'
        };

        $sql = "SELECT m.*, g.name AS genre_name
                FROM movies m
                LEFT JOIN genres g ON m.genre_id = g.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $order
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function count(array $filters = []): int {
        $db = getDB();
        $where = ['m.is_active = 1'];
        $params = [];

        if (!empty($filters['genre_id'])) {
            $where[] = 'm.genre_id = ?';
            $params[] = (int) $filters['genre_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = 'MATCH(m.title, m.synopsis, m.director) AGAINST(? IN BOOLEAN MODE)';
            $params[] = $filters['search'] . '*';
        }
        if (!empty($filters['national']))    $where[] = 'm.is_national = 1';
        if (!empty($filters['besteirol']))   $where[] = 'm.is_besteirol = 1';
        if (!empty($filters['oscar']))       $where[] = 'm.is_oscar = 1';
        if (!empty($filters['bestseller']))  $where[] = 'm.is_bestseller = 1';

        $stmt = $db->prepare("SELECT COUNT(*) FROM movies m WHERE " . implode(' AND ', $where));
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function findById(int $id): ?array {
        $db = getDB();
        $stmt = $db->prepare("SELECT m.*, g.name AS genre_name FROM movies m LEFT JOIN genres g ON m.genre_id = g.id WHERE m.id = ? AND m.is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array {
        $db = getDB();
        $stmt = $db->prepare("SELECT m.*, g.name AS genre_name FROM movies m LEFT JOIN genres g ON m.genre_id = g.id WHERE m.slug = ? AND m.is_active = 1");
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    /** Destaques: ranqueados por nota + popularidade (misto) */
    public static function getFeatured(int $limit = 10): array {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT m.*, g.name AS genre_name
             FROM movies m
             LEFT JOIN genres g ON m.genre_id = g.id
             WHERE m.is_active = 1 AND m.is_featured = 1
             ORDER BY (m.rating * 0.6 + m.popularity_score * 0.4) DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /** Filme aleatório */
    public static function getRandom(): ?array {
        $db = getDB();
        $stmt = $db->query("SELECT m.*, g.name AS genre_name FROM movies m LEFT JOIN genres g ON m.genre_id = g.id WHERE m.is_active = 1 ORDER BY RAND() LIMIT 1");
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO movies (title,slug,synopsis,director,cast_list,release_year,duration_min,rating,genre_id,poster,trailer_url,gdrive_url,price,is_featured,is_national,is_besteirol,is_oscar,is_bestseller,popularity_score) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['title'], $data['slug'], $data['synopsis'] ?? null,
            $data['director'] ?? null, $data['cast_list'] ?? null,
            $data['release_year'] ?? null, $data['duration_min'] ?? null,
            $data['rating'] ?? 0, $data['genre_id'] ?? null,
            $data['poster'] ?? null, $data['trailer_url'] ?? null,
            $data['gdrive_url'] ?? null,
            $data['price'], $data['is_featured'] ?? 0,
            $data['is_national'] ?? 0, $data['is_besteirol'] ?? 0,
            $data['is_oscar'] ?? 0, $data['is_bestseller'] ?? 0,
            $data['popularity_score'] ?? 0,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = getDB();
        $stmt = $db->prepare("UPDATE movies SET title=?,slug=?,synopsis=?,director=?,cast_list=?,release_year=?,duration_min=?,rating=?,genre_id=?,poster=?,trailer_url=?,gdrive_url=?,price=?,is_featured=?,is_active=?,is_national=?,is_besteirol=?,is_oscar=?,is_bestseller=?,popularity_score=? WHERE id=?");
        return $stmt->execute([
            $data['title'], $data['slug'], $data['synopsis'] ?? null,
            $data['director'] ?? null, $data['cast_list'] ?? null,
            $data['release_year'] ?? null, $data['duration_min'] ?? null,
            $data['rating'] ?? 0, $data['genre_id'] ?? null,
            $data['poster'] ?? null, $data['trailer_url'] ?? null,
            $data['gdrive_url'] ?? null,
            $data['price'], $data['is_featured'] ?? 0,
            $data['is_active'] ?? 1,
            $data['is_national'] ?? 0, $data['is_besteirol'] ?? 0,
            $data['is_oscar'] ?? 0, $data['is_bestseller'] ?? 0,
            $data['popularity_score'] ?? 0, $id
        ]);
    }

    public static function delete(int $id): bool {
        $db = getDB();
        $stmt = $db->prepare("UPDATE movies SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getAverageRating(int $movieId): float {
        $db = getDB();
        $stmt = $db->prepare("SELECT AVG(rating) FROM reviews WHERE movie_id = ?");
        $stmt->execute([$movieId]);
        return round((float) $stmt->fetchColumn(), 1);
    }
}
