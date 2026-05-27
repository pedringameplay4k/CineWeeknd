<?php
/**
 * CineWeeknd v3 - Front Controller & Router
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/AutoInstaller.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/Controllers.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Controllers/ScreeningController.php';

startSecureSession();
AutoInstaller::boot(); // Instala silenciosamente na primeira visita

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Remove o prefixo do projeto (ex: /CineWeeknd_final/public ou /CineWeeknd_final)
$script = $_SERVER['SCRIPT_NAME']; // ex: /CineWeeknd_final/public/index.php
$base   = rtrim(dirname($script), '/'); // ex: /CineWeeknd_final/public
// Tenta remover /public do base caso REQUEST_URI não tenha /public
$baseNoPublic = rtrim(str_replace('/public', '', $base), '/');
if (str_starts_with($uri, $base)) {
    $path = substr($uri, strlen($base));
} elseif (str_starts_with($uri, $baseNoPublic)) {
    $path = substr($uri, strlen($baseNoPublic));
} else {
    $path = $uri;
}
$path = rtrim($path, '/') ?: '/';
// Remove /public prefix se sobrou
if (str_starts_with($path, '/public')) {
    $path = substr($path, 7) ?: '/';
}
$method = $_SERVER['REQUEST_METHOD'];

function matchRoute(string $pattern, string $path): array|false {
    $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
    if (preg_match('#^' . $regex . '$#', $path, $m)) {
        return array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
    }
    return false;
}

// Home
if ($path === '/' && $method === 'GET')               { MovieController::index(); exit; }

// Auth
if ($path === '/login'    && $method === 'GET')        { AuthController::showLogin();    exit; }
if ($path === '/login'    && $method === 'POST')       { AuthController::login();        exit; }
if ($path === '/register' && $method === 'GET')        { AuthController::showRegister(); exit; }
if ($path === '/register' && $method === 'POST')       { AuthController::register();     exit; }
if ($path === '/logout'   && $method === 'POST')       { AuthController::logout();       exit; }

// Movies
if ($path === '/movies'              && $method === 'GET') { MovieController::index();          exit; }
if ($path === '/movies/aleatorio'    && $method === 'GET') { MovieController::random();         exit; }
if ($path === '/movies/autocomplete' && $method === 'GET') { MovieController::autocomplete();   exit; }
if ($p = matchRoute('/movies/{slug}', $path))              { MovieController::show($p['slug']); exit; }
if ($path === '/favorites/toggle' && $method === 'POST'){ MovieController::toggleFavorite(); exit; }
if ($path === '/reviews/submit'   && $method === 'POST'){ MovieController::submitReview();   exit; }

// Cart
if ($path === '/cart'          && $method === 'GET')   { CartController::index();       exit; }
if ($path === '/cart/movie'    && $method === 'POST')  { CartController::addMovie();    exit; }
if ($path === '/cart/combo'    && $method === 'POST')  { CartController::addCombo();    exit; }
if ($path === '/cart/remove'   && $method === 'POST')  { CartController::remove();      exit; }
if ($path === '/cart/qty'      && $method === 'POST')  { CartController::updateQty();  exit; }
if ($path === '/cart/seat'     && $method === 'POST')  { CartController::updateSeat(); exit; }
if ($path === '/cart/coupon'   && $method === 'POST')  { CartController::applyCoupon(); exit; }
if ($path === '/cart/checkout' && $method === 'POST')  { CartController::checkout();    exit; }

// Screenings & Watch
if (($p = matchRoute('/movies/{slug}/sessions', $path)) && $method === 'GET') { ScreeningController::show($p['slug']); exit; }
if ($p = matchRoute('/watch/{token}', $path))          { ScreeningController::watch($p['token']); exit; }
if ($path === '/my-tickets' && $method === 'GET')       { ScreeningController::myTickets(); exit; }

// Orders
if ($path === '/orders' && $method === 'GET')                      { OrderController::index(); exit; }
if ($path === '/orders/resend-email' && $method === 'POST')        { OrderController::resendEmail(); exit; }
if ($p = matchRoute('/orders/{id}', $path))                        { OrderController::show((int)$p['id']); exit; }

// Profile
if ($path === '/profile'                 && $method === 'GET')  { ProfileController::index();          exit; }
if ($path === '/profile/update'          && $method === 'POST') { ProfileController::update();         exit; }
if ($path === '/profile/change-password' && $method === 'POST') { ProfileController::changePassword(); exit; }

// Admin
if ($path === '/admin'         && $method === 'GET')  { AdminController::dashboard();   exit; }
if ($path === '/admin/movies'  && $method === 'GET')  { AdminController::movies();      exit; }
if ($path === '/admin/movies'  && $method === 'POST') { AdminController::createMovie(); exit; }
if (($p = matchRoute('/admin/movies/{id}/update', $path)) && $method === 'POST') { AdminController::updateMovie((int)$p['id']); exit; }
if (($p = matchRoute('/admin/movies/{id}/delete', $path)) && $method === 'POST') { AdminController::deleteMovie((int)$p['id']); exit; }
if ($path === '/admin/combos'  && $method === 'GET')  { AdminController::combos();      exit; }
if ($path === '/admin/combos'  && $method === 'POST') { AdminController::createCombo(); exit; }
if ($path === '/admin/users'   && $method === 'GET')  { AdminController::users();       exit; }
if ($path === '/admin/coupons' && $method === 'GET')  { AdminController::coupons();     exit; }
if ($path === '/admin/add-movie'    && $method === 'GET')  { AdminController::addMoviePage();  exit; }
if ($path === '/admin/tmdb-search'  && $method === 'GET')  { AdminController::tmdbSearch();    exit; }
if ($path === '/admin/tmdb-import'  && $method === 'POST') { AdminController::tmdbImport();    exit; }
if ($path === '/admin/fetch-posters' && $method === 'GET')  { AdminController::fetchPosters();  exit; }
if ($path === '/admin/coupons' && $method === 'POST') { AdminController::createCoupon();exit; }

// 404
http_response_code(404);
require __DIR__ . '/../src/Views/pages/404.php';
