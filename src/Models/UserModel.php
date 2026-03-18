<?php
/**
 * CineWeeknd - User Model
 */

require_once __DIR__ . '/../../config/database.php';

class UserModel {

    public static function findById(int $id): ?array {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $name, string $email, string $password): int {
        $db = getDB();
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hash]);
        return (int) $db->lastInsertId();
    }

    public static function updateProfile(int $id, array $data): bool {
        $db = getDB();
        $fields = [];
        $params = [];
        $allowed = ['name', 'avatar'];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $stmt = $db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function updatePassword(int $id, string $newPassword): bool {
        $db = getDB();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    public static function all(int $limit = 50, int $offset = 0): array {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, email, is_admin, is_active, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public static function setResetToken(int $id, string $token): bool {
        $db = getDB();
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        return $stmt->execute([$token, $expires, $id]);
    }

    public static function findByResetToken(string $token): ?array {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public static function clearResetToken(int $id): bool {
        $db = getDB();
        $stmt = $db->prepare("UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
