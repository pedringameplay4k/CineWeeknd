<?php
/**
 * CineWeeknd - Admin Controller
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/MovieModel.php';
require_once __DIR__ . '/../Models/Models.php';

class AdminController {

    public static function dashboard(): void {
        requireAdmin();
        $db = getDB();
        $stats = [
            'users'   => (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'movies'  => (int) $db->query("SELECT COUNT(*) FROM movies WHERE is_active=1")->fetchColumn(),
            'orders'  => (int) $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
            'revenue' => (float) $db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='confirmed'")->fetchColumn(),
        ];
        $recentOrders = OrderModel::allOrders(10, 0);
        require __DIR__ . '/../Views/pages/admin/dashboard.php';
    }

    // --- Movies ---
    public static function movies(): void {
        requireAdmin();
        $movies = MovieModel::all([], 100, 0);
        $genres = GenreModel::all();
        require __DIR__ . '/../Views/pages/admin/movies.php';
    }

    public static function createMovie(): void {
        requireAdmin();
        if (!verifyCsrf($_POST[CSRF_TOKEN_NAME] ?? '')) {
            setFlash('error', 'Invalid token.'); redirect('/admin/movies'); }

        $data = self::sanitizeMovieInput($_POST);
        $data['poster'] = self::handlePosterUpload();
        MovieModel::create($data);
        setFlash('success', 'Movie created!');
        redirect('/admin/movies');
    }

    public static function updateMovie(int $id): void {
        requireAdmin();
        if (!verifyCsrf($_POST[CSRF_TOKEN_NAME] ?? '')) {
            setFlash('error', 'Invalid token.'); redirect('/admin/movies'); }

        $data = self::sanitizeMovieInput($_POST);
        $upload = self::handlePosterUpload();
        if ($upload) $data['poster'] = $upload;
        MovieModel::update($id, $data);
        setFlash('success', 'Movie updated!');
        redirect('/admin/movies');
    }

    public static function deleteMovie(int $id): void {
        requireAdmin();
        MovieModel::delete($id);
        setFlash('success', 'Filme excluído.');
        redirect('/admin/movies');
    }

    // --- Combos ---
    public static function combos(): void {
        requireAdmin();
        $combos = ComboModel::all();
        require __DIR__ . '/../Views/pages/admin/combos.php';
    }

    public static function createCombo(): void {
        requireAdmin();
        if (!verifyCsrf($_POST[CSRF_TOKEN_NAME] ?? '')) {
            setFlash('error', 'Invalid token.'); redirect('/admin/combos'); }
        ComboModel::create([
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price'       => (float)($_POST['price'] ?? 0),
        ]);
        setFlash('success', 'Combo created!');
        redirect('/admin/combos');
    }

    // --- Users ---
    public static function users(): void {
        requireAdmin();
        $users = UserModel::all();
        require __DIR__ . '/../Views/pages/admin/users.php';
    }

    // --- Coupons ---
    public static function coupons(): void {
        requireAdmin();
        $coupons = CouponModel::all();
        require __DIR__ . '/../Views/pages/admin/coupons.php';
    }

    public static function createCoupon(): void {
        requireAdmin();
        if (!verifyCsrf($_POST[CSRF_TOKEN_NAME] ?? '')) {
            setFlash('error', 'Invalid token.'); redirect('/admin/coupons'); }
        CouponModel::create([
            'code'           => strtoupper(trim($_POST['code'] ?? '')),
            'discount_type'  => $_POST['discount_type'] ?? 'percent',
            'discount_value' => (float)($_POST['discount_value'] ?? 0),
            'min_order_value'=> (float)($_POST['min_order_value'] ?? 0),
            'max_uses'       => !empty($_POST['max_uses']) ? (int)$_POST['max_uses'] : null,
            'expires_at'     => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
        ]);
        setFlash('success', 'Coupon created!');
        redirect('/admin/coupons');
    }

    private static function sanitizeMovieInput(array $post): array {
        $title = trim($post['title'] ?? '');
        $slug  = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
        return [
            'title'        => $title,
            'slug'         => $slug,
            'synopsis'     => trim($post['synopsis'] ?? ''),
            'director'     => trim($post['director'] ?? ''),
            'cast_list'    => trim($post['cast_list'] ?? ''),
            'release_year' => !empty($post['release_year']) ? (int)$post['release_year'] : null,
            'duration_min' => !empty($post['duration_min']) ? (int)$post['duration_min'] : null,
            'rating'       => (float)($post['rating'] ?? 0),
            'genre_id'     => !empty($post['genre_id']) ? (int)$post['genre_id'] : null,
            'trailer_url'  => trim($post['trailer_url'] ?? ''),
            'price'        => (float)($post['price'] ?? 0),
            'is_featured'  => isset($post['is_featured']) ? 1 : 0,
            'is_active'    => 1,
        ];
    }

    private static function handlePosterUpload(): ?string {
        if (empty($_FILES['poster']['tmp_name'])) return null;
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $mime    = mime_content_type($_FILES['poster']['tmp_name']);
        if (!in_array($mime, $allowed)) return null;
        $ext      = pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION);
        $filename = 'movie_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        move_uploaded_file($_FILES['poster']['tmp_name'], UPLOAD_DIR . $filename);
        return $filename;
    }

    // ── TMDB: Página de adicionar filme ──────────────────
    public static function addMoviePage(): void {
        requireAdmin();
        require __DIR__ . '/../Views/pages/admin/add-movie.php';
    }

    // ── TMDB: Busca filmes (AJAX) ─────────────────────────
    public static function tmdbSearch(): void {
        requireAdmin();
        require_once __DIR__ . '/../Services/TMDBService.php';
        header('Content-Type: application/json');
        $query   = trim($_GET['q'] ?? '');
        if (!$query) { echo json_encode([]); exit; }
        $results = TMDBService::searchForAdmin($query);
        echo json_encode($results);
        exit;
    }

    // ── TMDB: Importa filme para o banco (AJAX POST) ──────
    public static function tmdbImport(): void {
        requireAdmin();
        require_once __DIR__ . '/../Services/TMDBService.php';
        header('Content-Type: application/json');

        $tmdbId   = (int)($_POST['tmdb_id']  ?? 0);
        $price    = (float)($_POST['price']  ?? 24.90);
        $featured = (int)($_POST['featured'] ?? 0);
        $category = trim($_POST['category']  ?? '');

        if (!$tmdbId) {
            echo json_encode(['success'=>false,'message'=>'ID TMDB inválido.']);
            exit;
        }

        // Verifica se já existe
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, slug FROM movies WHERE title = (SELECT title FROM movies WHERE id IN (SELECT movie_id FROM movies LIMIT 1)) LIMIT 1");

        $movieId = TMDBService::importMovie($tmdbId, $price, $featured === 1);

        if (!$movieId) {
            // Pode ser duplicata — busca slug existente
            $check = $db->prepare("SELECT id, slug FROM movies ORDER BY id DESC LIMIT 1");
            $check->execute();
            $row = $check->fetch();
            if ($row) {
                echo json_encode(['success'=>true,'message'=>'Filme já existia ou foi importado.','slug'=>$row['slug']]);
            } else {
                echo json_encode(['success'=>false,'message'=>'Erro ao importar. Verifique o log.']);
            }
            exit;
        }

        // Aplica categoria especial se escolhida
        $allowed = ['is_oscar','is_national','is_bestseller','is_besteirol'];
        if ($category && in_array($category, $allowed)) {
            $db->prepare("UPDATE movies SET {$category} = 1 WHERE id = ?")->execute([$movieId]);
        }

        // Busca slug para link
        $slug = $db->prepare("SELECT slug FROM movies WHERE id = ?");
        $slug->execute([$movieId]);
        $slugVal = $slug->fetchColumn() ?: '';

        echo json_encode([
            'success' => true,
            'message' => 'Filme importado com sucesso! Poster sendo baixado em background.',
            'slug'    => $slugVal,
            'id'      => $movieId,
        ]);
        exit;
    }


    // ── Busca posters faltantes via TMDB (AJAX) ───────────
    public static function fetchPosters(): void {
        requireAdmin();
        require_once __DIR__ . '/../Services/TMDBService.php';
        header('Content-Type: application/json');

        $db    = getDB();
        $limit = (int)($_GET['limit'] ?? 5);

        // Pega filmes sem poster
        $stmt  = $db->prepare("SELECT id, title, slug, release_year FROM movies WHERE (poster IS NULL OR poster = '' OR poster = 'default.jpg') LIMIT ?");
        $stmt->execute([$limit]);
        $movies = $stmt->fetchAll();

        $updated = [];
        $failed  = [];

        foreach ($movies as $movie) {
            $file = TMDBService::fetchPosterForMovie($movie);
            if ($file) {
                $db->prepare("UPDATE movies SET poster = ? WHERE id = ?")->execute([$file, $movie['id']]);
                $updated[] = $movie['title'];
            } else {
                $failed[] = $movie['title'];
            }
            usleep(300000);
        }

        // Conta quantos ainda faltam
        $remaining = (int)$db->query("SELECT COUNT(*) FROM movies WHERE poster IS NULL OR poster = '' OR poster = 'default.jpg'")->fetchColumn();

        echo json_encode([
            'success'   => true,
            'updated'   => $updated,
            'failed'    => $failed,
            'remaining' => $remaining,
        ]);
        exit;
    }

}
