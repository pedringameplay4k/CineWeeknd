<?php
/**
 * CineWeeknd - Supporting Models
 */

require_once __DIR__ . '/../../config/database.php';

// -------------------------------------------------------
class GenreModel {
    public static function all(): array {
        $stmt = getDB()->query("SELECT * FROM genres ORDER BY name");
        return $stmt->fetchAll();
    }
}

// -------------------------------------------------------
class ComboModel {
    public static function all(): array {
        $stmt = getDB()->query("SELECT * FROM combos WHERE is_active = 1 ORDER BY price");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM combos WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO combos (name, description, price, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['description'] ?? null, $data['price'], $data['image'] ?? null]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = getDB();
        $stmt = $db->prepare("UPDATE combos SET name=?, description=?, price=?, image=?, is_active=? WHERE id=?");
        return $stmt->execute([$data['name'], $data['description'] ?? null, $data['price'], $data['image'] ?? null, $data['is_active'] ?? 1, $id]);
    }

    public static function delete(int $id): bool {
        $db = getDB();
        $stmt = $db->prepare("UPDATE combos SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

// -------------------------------------------------------
class CouponModel {
    public static function findByCode(string $code): ?array {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) AND (max_uses IS NULL OR used_count < max_uses)");
        $stmt->execute([$code]);
        return $stmt->fetch() ?: null;
    }

    public static function incrementUse(int $id): void {
        $db = getDB();
        $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?")->execute([$id]);
    }

    public static function applyDiscount(array $coupon, float $subtotal): float {
        if ($subtotal < $coupon['min_order_value']) return 0;
        if ($coupon['discount_type'] === 'percent') {
            return round($subtotal * ($coupon['discount_value'] / 100), 2);
        }
        return min((float)$coupon['discount_value'], $subtotal);
    }

    public static function all(): array {
        $stmt = getDB()->query("SELECT * FROM coupons ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public static function create(array $data): int {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order_value, max_uses, expires_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['code'], $data['discount_type'], $data['discount_value'], $data['min_order_value'] ?? 0, $data['max_uses'] ?? null, $data['expires_at'] ?? null]);
        return (int) $db->lastInsertId();
    }
}

// -------------------------------------------------------
class OrderModel {
    public static function create(int $userId, array $items, float $subtotal, float $discount, float $total, ?int $couponId = null): int {
        $db = getDB();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO orders (user_id, coupon_id, subtotal, discount, total) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $couponId, $subtotal, $discount, $total]);
            $orderId = (int) $db->lastInsertId();

            $itemStmt = $db->prepare("INSERT INTO order_items (order_id, item_type, item_id, item_name, quantity, unit_price, seat) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $itemStmt->execute([$orderId, $item['type'], $item['id'], $item['name'], $item['quantity'], $item['price'], $item['seat'] ?? null]);
            }

            if ($couponId) {
                CouponModel::incrementUse($couponId);
            }

            $db->commit();
            return $orderId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function findById(int $id): ?array {
        $db = getDB();
        $stmt = $db->prepare("SELECT o.*, u.name AS user_name, u.email AS user_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getItems(int $orderId): array {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public static function getByUser(int $userId, int $limit = 10, int $offset = 0): array {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public static function countByUser(int $userId): int {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function allOrders(int $limit = 50, int $offset = 0): array {
        $db = getDB();
        $stmt = $db->prepare("SELECT o.*, u.name AS user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
}

// -------------------------------------------------------
class FavoriteModel {
    public static function toggle(int $userId, int $movieId): string {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND movie_id = ?");
        $stmt->execute([$userId, $movieId]);
        if ($stmt->fetch()) {
            $db->prepare("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?")->execute([$userId, $movieId]);
            return 'removed';
        } else {
            $db->prepare("INSERT INTO favorites (user_id, movie_id) VALUES (?, ?)")->execute([$userId, $movieId]);
            return 'added';
        }
    }

    public static function getByUser(int $userId): array {
        $db = getDB();
        $stmt = $db->prepare("SELECT m.*, g.name AS genre_name FROM favorites f JOIN movies m ON f.movie_id = m.id LEFT JOIN genres g ON m.genre_id = g.id WHERE f.user_id = ? AND m.is_active = 1 ORDER BY f.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function isFavorite(int $userId, int $movieId): bool {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND movie_id = ?");
        $stmt->execute([$userId, $movieId]);
        return (bool) $stmt->fetch();
    }
}

// -------------------------------------------------------
class ReviewModel {
    public static function getByMovie(int $movieId): array {
        $db = getDB();
        $stmt = $db->prepare("SELECT r.*, u.name AS user_name, u.avatar FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.movie_id = ? ORDER BY r.created_at DESC");
        $stmt->execute([$movieId]);
        return $stmt->fetchAll();
    }

    public static function upsert(int $userId, int $movieId, int $rating, string $comment): void {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO reviews (user_id, movie_id, rating, comment) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), updated_at = NOW()");
        $stmt->execute([$userId, $movieId, $rating, $comment]);
    }
}
