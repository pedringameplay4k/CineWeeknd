<?php
/**
 * CineWeeknd v3 - Application Controllers
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/MovieModel.php';
require_once __DIR__ . '/../Models/Models.php';
require_once __DIR__ . '/../Services/EmailService.php';
require_once __DIR__ . '/../Models/ScreeningModel.php';

// -------------------------------------------------------
class MovieController {

public static function index(): void {
        $search   = trim($_GET['search'] ?? '');
        $genreId  = (int)($_GET['genre'] ?? 0);
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = MOVIES_PER_PAGE;
        $offset   = ($page - 1) * $perPage;
        $category = $_GET['category'] ?? '';
        $order    = $_GET['order'] ?? '';
        $isAjax   = !empty($_GET['ajax']);

        $filters = [];
        if ($search)  $filters['search']   = $search;
        if ($genreId) $filters['genre_id'] = $genreId;
        if ($order)   $filters['order']    = $order;

        if ($category === 'national')   $filters['national']   = true;
        if ($category === 'besteirol')  $filters['besteirol']  = true;
        if ($category === 'oscar')      $filters['oscar']      = true;
        if ($category === 'bestseller') $filters['bestseller'] = true;

        $movies   = MovieModel::all($filters, $perPage, $offset);
        $total    = MovieModel::count($filters);
        $pages    = (int) ceil($total / $perPage);
        $genres   = GenreModel::all();
        $featured = MovieModel::getFeatured(10);

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            ob_start();
            if (empty($movies)) {
                echo '<div class="empty-state"><div class="empty-state-icon">🎬</div>';
                echo '<div class="empty-state-title">Nenhum filme encontrado</div>';
                echo '<p class="empty-state-desc">Tente outra categoria ou busca.</p></div>';
            } else {
                echo '<div class="row g-3">';
                foreach ($movies as $movie) {
                    echo '<div class="col-6 col-md-4 col-lg-3 col-xl-2 fade-in-card">';
                    include __DIR__ . '/../Views/components/movie-card.php';
                    echo '</div>';
                }
                echo '</div>';
                if ($pages > 1) {
                    echo '<div class="cine-pagination mt-4">';
                    for ($i = 1; $i <= $pages; $i++) {
                        $active = $i === $page ? ' active' : '';
                        echo "<a href='#' data-page='$i' class='page-btn$active'>$i</a>";
                    }
                    echo '</div>';
                }
            }
            $html = ob_get_clean();
            echo json_encode(['html' => $html, 'total' => $total]);
            exit;
        }

        require __DIR__ . '/../Views/pages/movies.php';
    }

    public static function autocomplete(): void {
        header('Content-Type: application/json; charset=utf-8');
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) { echo json_encode([]); exit; }
        $db   = getDB();
        $like = '%' . $q . '%';
        $stmt = $db->prepare(
            "SELECT id, title, slug, poster, release_year, rating
             FROM movies
             WHERE is_active = 1 AND (title LIKE ? OR director LIKE ?)
             ORDER BY popularity_score DESC, rating DESC
             LIMIT 6"
        );
        $stmt->execute([$like, $like]);
        echo json_encode($stmt->fetchAll());
        exit;
    }

    public static function random(): void {
        $movie = MovieModel::getRandom();
        if ($movie) {
            redirect('/movies/' . $movie['slug']);
        }
        redirect('/movies');
    }

    public static function show(string $slug): void {
        $movie = MovieModel::findBySlug($slug);
        if (!$movie) {
            setFlash('error', 'Filme não encontrado.');
            redirect('/movies');
        }
        $reviews    = ReviewModel::getByMovie($movie['id']);
        $isFavorite = isLoggedIn() ? FavoriteModel::isFavorite($_SESSION['user_id'], $movie['id']) : false;
        require __DIR__ . '/../Views/pages/movie-detail.php';
    }

    public static function toggleFavorite(): void {
        requireLogin();
        $movieId = (int)($_POST['movie_id'] ?? 0);
        if (!$movieId) { http_response_code(400); echo json_encode(['error' => 'ID inválido']); exit; }
        $action = FavoriteModel::toggle($_SESSION['user_id'], $movieId);
        header('Content-Type: application/json');
        echo json_encode(['action' => $action]);
        exit;
    }

    public static function submitReview(): void {
        requireLogin();
        if (!verifyCsrf($_POST[CSRF_TOKEN_NAME] ?? '')) {
            setFlash('error', 'Token inválido.'); redirect('/movies'); }
        $movieId = (int)($_POST['movie_id'] ?? 0);
        $rating  = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        if ($movieId && $rating >= 1 && $rating <= 5) {
            ReviewModel::upsert($_SESSION['user_id'], $movieId, $rating, $comment);
            setFlash('success', 'Avaliação enviada!');
        }
        $movie = MovieModel::findById($movieId);
        redirect('/movies/' . ($movie['slug'] ?? ''));
    }
}

// -------------------------------------------------------
class CartController {

    public static function index(): void {
        $cart   = $_SESSION['cart'] ?? [];
        $combos = ComboModel::all();
        require __DIR__ . '/../Views/pages/cart.php';
    }

    public static function addMovie(): void {
        requireLogin();
        // Aceita tanto movie_id (int) quanto slug (string)
        $movieId = (int)($_POST['movie_id'] ?? 0);
        $slug    = $_POST['slug'] ?? '';
        $movie   = $movieId ? MovieModel::findById($movieId) : MovieModel::findBySlug($slug);

        // Resposta JSON para chamadas AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
                  ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json';

        if (!$movie) {
            if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['error' => 'Filme não encontrado.']); exit; }
            setFlash('error', 'Filme não encontrado.'); redirect('/movies');
        }

        $screeningId = (int)($_POST['screening_id'] ?? 0);
        $seat        = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $_POST['seat'] ?? ''));
        $mode        = in_array($_POST['mode'] ?? '', ['digital','cinema']) ? $_POST['mode'] : '';
        $customPrice = (float)($_POST['price'] ?? 0);

        // Usa preço do modo se informado, senão preço padrão do filme
        if ($customPrice > 0) {
            $price = $customPrice;
        } elseif ($mode === 'digital') {
            $price = (float)($movie['price_digital'] ?? $movie['price']);
        } elseif ($mode === 'cinema') {
            $price = (float)($movie['price_cinema'] ?? $movie['price']);
        } else {
            $price = (float)$movie['price'];
        }

        $cart = $_SESSION['cart'] ?? [];
        $key  = 'movie_' . $movie['id'] . ($screeningId ? '_s' . $screeningId : '');
        if (isset($cart[$key])) {
            $msg = 'Já está no carrinho.';
            $status = 'info';
        } else {
            $modeLabel = $mode === 'digital' ? '💻 Digital' : ($mode === 'cinema' ? '🎭 Presencial' : '');
            $cart[$key] = [
                'type'         => 'movie',
                'id'           => $movie['id'],
                'name'         => $movie['title'] . ($modeLabel ? ' — ' . $modeLabel : ''),
                'price'        => $price,
                'qty'          => 1,
                'screening_id' => $screeningId,
                'seat'         => $seat,
                'mode'         => $mode,
            ];
            $_SESSION['cart'] = $cart;
            $msg = 'Adicionado ao carrinho!';
            $status = 'success';
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => $status, 'message' => $msg, 'cartCount' => count($_SESSION['cart'])]);
            exit;
        }
        setFlash($status, $msg);
        redirect('/movies/' . $movie['slug']);
    }

    public static function addCombo(): void {
        requireLogin();
        $isAjax  = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
        $comboId = (int)($_POST['combo_id'] ?? 0);
        $combo   = ComboModel::findById($comboId);

        if (!$combo) {
            if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['error' => 'Combo não encontrado.']); exit; }
            setFlash('error', 'Combo não encontrado.'); redirect('/cart');
        }

        $cart = $_SESSION['cart'] ?? [];
        $key  = 'combo_' . $combo['id'];
        if (isset($cart[$key])) {
            $msg = 'Combo já está no carrinho.'; $status = 'info';
        } else {
            $cart[$key] = ['type'=>'combo','id'=>$combo['id'],'name'=>$combo['name'],'price'=>$combo['price'],'qty'=>1];
            $_SESSION['cart'] = $cart;
            $msg = 'Combo adicionado!'; $status = 'success';
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => $status, 'message' => $msg, 'cartCount' => count($_SESSION['cart'])]);
            exit;
        }
        setFlash($status, $msg);
        redirect('/cart');
    }

    public static function updateSeat(): void {
        $key  = $_POST['key']  ?? '';
        $seat = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $_POST['seat'] ?? ''));
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
        $cart = $_SESSION['cart'] ?? [];

        if ($key && isset($cart[$key]) && $seat) {
            $cart[$key]['seat'] = $seat;
            $_SESSION['cart']   = $cart;
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'seat' => $seat]);
                exit;
            }
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false]);
            exit;
        }
        redirect('/cart');
    }

    public static function updateQty(): void {
        $key    = $_POST['key']    ?? '';
        $change = (int)($_POST['change'] ?? 0); // +1 ou -1
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
        $cart   = $_SESSION['cart'] ?? [];

        if ($key && isset($cart[$key])) {
            $cart[$key]['qty'] = max(1, ($cart[$key]['qty'] ?? 1) + $change);
            $_SESSION['cart'] = $cart;
            $newQty   = $cart[$key]['qty'];
            $newTotal = $cart[$key]['price'] * $newQty;
            $subtotal = array_sum(array_map(fn($i) => $i['price'] * ($i['qty'] ?? 1), $cart));

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'    => true,
                    'qty'        => $newQty,
                    'itemTotal'  => 'R$ ' . number_format($newTotal,  2, ',', '.'),
                    'subtotal'   => 'R$ ' . number_format($subtotal,  2, ',', '.'),
                    'cartCount'  => array_sum(array_column($cart, 'qty')),
                ]);
                exit;
            }
        }
        redirect('/cart');
    }

    public static function remove(): void {
        $key    = $_POST['key'] ?? '';
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
        $removed = false;
        if ($key && isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
            $removed = true;
        }
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'   => $removed,
                'cartCount' => count($_SESSION['cart'] ?? []),
            ]);
            exit;
        }
        if ($removed) setFlash('success', 'Item removido do carrinho.');
        redirect('/cart');
    }

    public static function applyCoupon(): void {
        $code   = strtoupper(trim($_POST['coupon'] ?? ''));
        $coupon = CouponModel::findByCode($code);
        if (!$coupon) { setFlash('error', 'Cupom inválido ou expirado.'); redirect('/cart'); }
        if (isset($_SESSION['coupon'])) { setFlash('info', 'Cupom já aplicado.'); redirect('/cart'); }
        $_SESSION['coupon'] = $coupon;
        setFlash('success', 'Cupom aplicado!');
        redirect('/cart');
    }

    public static function checkout(): void {
        requireLogin();
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) { setFlash('error', 'Seu carrinho está vazio.'); redirect('/cart'); return; }

        $subtotal = array_sum(array_map(fn($i) => $i['price'] * ($i['qty'] ?? 1), $cart));
        $coupon   = $_SESSION['coupon'] ?? null;
        $discount = 0;
        if ($coupon) {
            $discount = $coupon['discount_type'] === 'percent'
                ? round($subtotal * $coupon['discount_value'] / 100, 2)
                : min($coupon['discount_value'], $subtotal);
        }
        $total    = max(0, $subtotal - $discount);
        $couponId = $coupon ? $coupon['id'] : null;

        // Normaliza itens — preserva seat e screening_id
        $items = array_values(array_map(fn($i) => [
            'type'         => $i['type'],
            'id'           => $i['id'],
            'name'         => $i['name'],
            'price'        => $i['price'],
            'quantity'     => $i['qty'] ?? 1,
            'seat'         => $i['seat']         ?? null,
            'screening_id' => $i['screening_id'] ?? null,
        ], $cart));

        $orderId = OrderModel::create($_SESSION['user_id'], $items, $subtotal, $discount, $total, $couponId);

        // Gera tokens de acesso para itens com sessão
        foreach ($items as $item) {
            if ($item['type'] === 'movie' && !empty($item['screening_id'])) {
                try {
                    AccessTokenModel::create(
                        $orderId,
                        (int)$item['screening_id'],
                        (int)$item['id'],
                        $_SESSION['user_id']
                    );
                    ScreeningModel::takeSeat((int)$item['screening_id']);
                } catch (\Throwable $e) {
                    error_log('Token creation error: ' . $e->getMessage());
                }
            }
        }

        // Envia e-mail de confirmação
        try {
            $orderData = [
                'id'         => $orderId,
                'subtotal'   => $subtotal,
                'discount'   => $discount,
                'total'      => $total,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            // Sempre busca e-mail atualizado do banco para garantir entrega correta
            $userId    = (int)$_SESSION['user_id'];
            $userRow   = UserModel::findById($userId);
            $toEmail   = $userRow['email']  ?? $_SESSION['user_email'] ?? '';
            $toName    = $userRow['name']   ?? $_SESSION['user_name']  ?? 'Cinéfilo';
            // Atualiza sessão com dados frescos
            if ($toEmail) $_SESSION['user_email'] = $toEmail;
            if ($toName)  $_SESSION['user_name']  = $toName;

            EmailService::sendOrderConfirmation(
                $toEmail,
                $toName,
                $orderData,
                $items
            );
        } catch (\Throwable $e) {
            error_log('Email error: ' . $e->getMessage());
        }

        unset($_SESSION['cart'], $_SESSION['coupon']);
        setFlash('success', 'Pedido confirmado! Comprovante enviado para seu e-mail.');
        redirect('/orders/' . $orderId);
    }
}

// -------------------------------------------------------
class OrderController {

    public static function resendEmail(): void {
        requireLogin();
        header('Content-Type: application/json');

        $orderId = (int)($_POST['order_id'] ?? 0);
        $order   = OrderModel::findById($orderId);

        // Verifica se o pedido pertence ao usuário logado
        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Pedido não encontrado.']);
            exit;
        }

        $items = OrderModel::getItems($orderId);
        $email = $_SESSION['user_email'] ?? '';

        // Se não tem e-mail na sessão, busca do banco
        if (empty($email)) {
            $user  = UserModel::findById($_SESSION['user_id']);
            $email = $user['email'] ?? '';
            // Salva na sessão para próximas vezes
            if ($email) $_SESSION['user_email'] = $email;
        }

        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'E-mail não encontrado. Faça logout e login novamente.']);
            exit;
        }

        try {
            $sent = EmailService::sendOrderConfirmation(
                $email,
                $_SESSION['user_name'] ?? 'Cinéfilo',
                $order,
                $items
            );
            if ($sent) {
                echo json_encode(['success' => true, 'message' => "Comprovante reenviado para {$email}!"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Falha ao enviar. Verifique a configuração do e-mail.']);
            }
        } catch (\Throwable $e) {
            error_log('resendEmail error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
        exit;
    }

    public static function index(): void {
        requireLogin();
        $perPage = 10;
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;
        $total   = OrderModel::countByUser($_SESSION['user_id']);
        $pages   = (int) ceil($total / $perPage);
        $orders  = OrderModel::getByUser($_SESSION['user_id'], $perPage, $offset);
        $orderItems = [];
        foreach ($orders as $order) {
            $orderItems[$order['id']] = OrderModel::getItems($order['id']);
        }
        require __DIR__ . '/../Views/pages/order-history.php';
    }
    public static function show(int $id): void {
        requireLogin();
        $order = OrderModel::findById($id);
        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            setFlash('error', 'Pedido não encontrado.'); redirect('/orders'); }
        $items = OrderModel::getItems($id);
        require __DIR__ . '/../Views/pages/order-detail.php';
    }
}

// -------------------------------------------------------
class ProfileController {
    public static function index(): void {
        requireLogin();
        $user      = UserModel::findById($_SESSION['user_id']);
        $orders    = OrderModel::getByUser($_SESSION['user_id'], 3);
        $favorites = FavoriteModel::getByUser($_SESSION['user_id']);
        require __DIR__ . '/../Views/pages/profile.php';
    }
    public static function update(): void {
        requireLogin();
        if (!verifyCsrf($_POST[CSRF_TOKEN_NAME] ?? '')) {
            setFlash('error', 'Token inválido.'); redirect('/profile'); }
        $name = trim($_POST['name'] ?? '');
        if ($name) UserModel::updateName($_SESSION['user_id'], $name);
        setFlash('success', 'Perfil atualizado com sucesso.');
        redirect('/profile');
    }
    public static function changePassword(): void {
        requireLogin();
        if (!verifyCsrf($_POST[CSRF_TOKEN_NAME] ?? '')) {
            setFlash('error', 'Token inválido.'); redirect('/profile'); }
        $user = UserModel::findById($_SESSION['user_id']);
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $user['password'])) {
            setFlash('error', 'Senha atual incorreta.'); redirect('/profile'); }
        if ($new !== $confirm) {
            setFlash('error', 'As novas senhas não conferem.'); redirect('/profile'); }
        if (strlen($new) < 8) {
            setFlash('error', 'A senha deve ter pelo menos 8 caracteres.'); redirect('/profile'); }
        UserModel::updatePassword($_SESSION['user_id'], password_hash($new, PASSWORD_BCRYPT));
        setFlash('success', 'Senha atualizada com sucesso.');
        redirect('/profile');
    }
}
