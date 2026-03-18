<?php
/**
 * CineWeeknd — ScreeningModel, VenueModel & AccessTokenModel
 */

class VenueModel {
    public static function getAll(): array {
        $db = getDB();
        return $db->query("SELECT * FROM venues WHERE is_active=1 ORDER BY name")->fetchAll();
    }
    public static function findById(int $id): ?array {
        $stmt = getDB()->prepare("SELECT * FROM venues WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}

class ScreeningModel {

    /**
     * Gera sessões para os próximos N dias
     * Modo cinema: 4 horários × 7 shoppings × salas rotativas
     * Modo digital: 6 horários (mais disponibilidade)
     */
    public static function generateForDays(int $movieId, int $durationMin, int $days = 7): int {
        $db    = getDB();
        $count = 0;

        // ── DIGITAL ──────────────────────────────────────────────
        $digitalTimes = ['10:00','13:00','15:30','18:00','20:30','23:00'];
        for ($d = 0; $d < $days; $d++) {
            $date = date('Y-m-d', strtotime("+{$d} days"));
            foreach ($digitalTimes as $time) {
                $startsAt = $date . ' ' . $time . ':00';
                $endsAt   = date('Y-m-d H:i:s', strtotime($startsAt) + ($durationMin + 10) * 60);
                $stmt = $db->prepare("
                    INSERT IGNORE INTO screenings
                        (movie_id, venue_id, mode, starts_at, ends_at, room, capacity, is_active)
                    VALUES (?, NULL, 'digital', ?, ?, 'Online', 999, 1)
                ");
                $stmt->execute([$movieId, $startsAt, $endsAt]);
                if ($stmt->rowCount()) $count++;
            }
        }

        // ── PRESENCIAL (cinema) ───────────────────────────────────
        $venues = VenueModel::getAll();
        $cinemaTimes = ['14:00','16:30','19:00','21:30'];
        $rooms = ['Sala 1','Sala 2','Sala 3','Sala VIP'];
        foreach ($venues as $vi => $venue) {
            for ($d = 0; $d < $days; $d++) {
                $date = date('Y-m-d', strtotime("+{$d} days"));
                foreach ($cinemaTimes as $ti => $time) {
                    $startsAt = $date . ' ' . $time . ':00';
                    $endsAt   = date('Y-m-d H:i:s', strtotime($startsAt) + ($durationMin + 20) * 60);
                    $room     = $rooms[($vi + $ti) % count($rooms)];
                    $stmt = $db->prepare("
                        INSERT IGNORE INTO screenings
                            (movie_id, venue_id, mode, starts_at, ends_at, room, capacity, is_active)
                        VALUES (?, ?, 'cinema', ?, ?, ?, 50, 1)
                    ");
                    $stmt->execute([$movieId, $venue['id'], $startsAt, $endsAt, $room]);
                    if ($stmt->rowCount()) $count++;
                }
            }
        }
        return $count;
    }

    /**
     * Sessões futuras de um filme agrupadas por data
     * Filtra por modo se informado
     */
    public static function getByMovie(int $movieId, ?string $mode = null): array {
        $db   = getDB();
        $where = $mode ? "AND s.mode = " . $db->quote($mode) : '';
        $stmt = $db->prepare("
            SELECT s.*,
                   v.name AS venue_name,
                   v.address AS venue_address,
                   (s.capacity - s.seats_taken) AS seats_left
            FROM screenings s
            LEFT JOIN venues v ON v.id = s.venue_id
            WHERE s.movie_id = ?
              AND s.starts_at > NOW()
              AND s.is_active = 1
              $where
            ORDER BY s.starts_at ASC
            LIMIT 60
        ");
        $stmt->execute([$movieId]);
        $rows = $stmt->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $date = date('Y-m-d', strtotime($row['starts_at']));
            $grouped[$date][] = $row;
        }
        return $grouped;
    }

    public static function findById(int $id): ?array {
        $stmt = getDB()->prepare("
            SELECT s.*, v.name AS venue_name, v.address AS venue_address,
                   m.title, m.slug, m.duration_min, m.poster, m.video_url,
                   m.price_digital, m.price_cinema
            FROM screenings s
            JOIN movies m ON m.id = s.movie_id
            LEFT JOIN venues v ON v.id = s.venue_id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function takeSeat(int $id): void {
        getDB()->prepare("UPDATE screenings SET seats_taken = seats_taken + 1 WHERE id = ?")->execute([$id]);
    }

    public static function getToday(): array {
        $stmt = getDB()->prepare("
            SELECT s.*, m.title, m.slug, m.poster,
                   v.name AS venue_name,
                   (s.capacity - s.seats_taken) AS seats_left
            FROM screenings s
            JOIN movies m ON m.id = s.movie_id
            LEFT JOIN venues v ON v.id = s.venue_id
            WHERE DATE(s.starts_at) = CURDATE()
              AND s.is_active = 1
            ORDER BY s.mode, s.starts_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

// ──────────────────────────────────────────────────────────────
class AccessTokenModel {

    public static function create(int $orderId, int $screeningId, int $movieId, int $userId): string {
        $db        = getDB();
        $token     = bin2hex(random_bytes(32));
        $screening = ScreeningModel::findById($screeningId);
        $validFrom  = date('Y-m-d H:i:s', strtotime($screening['starts_at']) - 1800);
        $validUntil = date('Y-m-d H:i:s', strtotime($screening['starts_at']) + 10800);
        $db->prepare("
            INSERT INTO access_tokens (order_id, screening_id, movie_id, user_id, token, valid_from, valid_until)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([$orderId, $screeningId, $movieId, $userId, $token, $validFrom, $validUntil]);
        return $token;
    }

    public static function validate(string $token): ?array {
        $stmt = getDB()->prepare("
            SELECT at.*, s.starts_at, s.ends_at, s.room, s.mode,
                   v.name AS venue_name,
                   m.title, m.slug, m.video_url
            FROM access_tokens at
            JOIN screenings s ON s.id = at.screening_id
            JOIN movies m     ON m.id = at.movie_id
            LEFT JOIN venues v ON v.id = s.venue_id
            WHERE at.token = ?
              AND at.is_active = 1
              AND NOW() BETWEEN at.valid_from AND at.valid_until
        ");
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public static function markUsed(string $token): void {
        getDB()->prepare("UPDATE access_tokens SET used_at = NOW() WHERE token = ?")->execute([$token]);
    }

    public static function getByUser(int $userId): array {
        $stmt = getDB()->prepare("
            SELECT at.*, m.title, m.slug, m.poster,
                   s.starts_at, s.ends_at, s.room, s.mode,
                   v.name AS venue_name
            FROM access_tokens at
            JOIN movies m     ON m.id  = at.movie_id
            JOIN screenings s ON s.id  = at.screening_id
            LEFT JOIN venues v ON v.id = s.venue_id
            WHERE at.user_id = ?
            ORDER BY s.starts_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
