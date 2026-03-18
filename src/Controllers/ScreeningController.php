<?php
/**
 * CineWeeknd — ScreeningController
 */

require_once __DIR__ . '/../Models/ScreeningModel.php';
require_once __DIR__ . '/../Services/EmailService.php';

class ScreeningController {

    /** Página de sessões de um filme (AJAX ou full) */
    public static function show(string $slug): void {
        $movie = MovieModel::findBySlug($slug);
        if (!$movie) { http_response_code(404); echo 'Filme não encontrado'; exit; }

        // Gera sessões automaticamente se não existirem para os próximos 7 dias
        $db   = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM screenings WHERE movie_id = ? AND starts_at > NOW()");
        $stmt->execute([$movie['id']]);
        if ((int)$stmt->fetchColumn() === 0) {
            ScreeningModel::generateForDays($movie['id'], (int)($movie['duration_min'] ?? $movie['duration'] ?? 120));
        }

        $screeningsDigital = ScreeningModel::getByMovie($movie['id'], 'digital');
        $screeningsCinema  = ScreeningModel::getByMovie($movie['id'], 'cinema');
        $screenings        = array_merge_recursive($screeningsDigital, $screeningsCinema); // fallback
        $pageTitle  = 'Sessões — ' . $movie['title'];
        require __DIR__ . '/../Views/pages/screenings.php';
    }

    /** Acesso ao filme via token */
    public static function watch(string $token): void {
        $data = AccessTokenModel::validate($token);

        if (!$data) {
            $pageTitle = 'Acesso Inválido';
            require __DIR__ . '/../Views/pages/watch-invalid.php';
            exit;
        }

        // Marca como usado na primeira vez
        if (empty($data['used_at'])) {
            AccessTokenModel::markUsed($token);
        }

        $pageTitle = 'Assistindo — ' . $data['title'];
        require __DIR__ . '/../Views/pages/watch.php';
    }

    /** Meus ingressos digitais */
    public static function myTickets(): void {
        requireLogin();
        $tokens    = AccessTokenModel::getByUser($_SESSION['user_id']);
        $pageTitle = 'Meus Ingressos Digitais';
        require __DIR__ . '/../Views/pages/my-tickets.php';
    }
}
