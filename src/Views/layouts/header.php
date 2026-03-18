<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?> — <?= APP_NAME ?></title>
    <meta name="description" content="CineWeeknd — O melhor cinema pra desligar o cérebro e dar boas risadas.">

    <!-- App URL for JS (must be in head) -->
    <meta name="app-url" content="<?= APP_URL ?>">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrfToken()) ?>">
    <script>window.APP_URL = '<?= APP_URL ?>';</script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">

    <!-- Bootstrap 5 JS with defer so dropdown works everywhere -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>
<?php if (!empty(AutoInstaller::$isFirstInstall)): ?>
<div id="cineLoader" class="cine-loader-overlay">
  <div class="cine-loader-box">
    <div class="cine-loader-logo">🎬 Cine<span>Weeknd</span></div>
    <div class="cine-loader-bar-wrap">
      <div class="cine-loader-bar" id="loaderBar"></div>
    </div>
    <div class="cine-loader-status" id="loaderStatus">Preparando sua experiência cinematográfica…</div>
  </div>
</div>
<style>
.cine-loader-overlay {
    position:fixed;inset:0;z-index:99999;
    background:#0d0a1a;
    display:flex;align-items:center;justify-content:center;
    flex-direction:column;
}
.cine-loader-box { text-align:center; width:320px; }
.cine-loader-logo {
    font-family:var(--font-display,sans-serif);
    font-size:2.2rem;font-weight:700;
    color:#f0eaff;margin-bottom:32px;
    animation:logoPulse 1.5s ease-in-out infinite;
}
.cine-loader-logo span{color:#ff5f1f}
@keyframes logoPulse{0%,100%{opacity:1}50%{opacity:.7}}
.cine-loader-bar-wrap {
    background:rgba(255,255,255,.08);border-radius:99px;
    height:6px;overflow:hidden;margin-bottom:14px;
}
.cine-loader-bar {
    height:100%;width:0%;border-radius:99px;
    background:linear-gradient(90deg,#7c3aed,#ff5f1f);
    transition:width .4s ease;
}
.cine-loader-status{font-size:.82rem;color:#8b7fb5;font-weight:600}
</style>
<script>
(function(){
    const bar    = document.getElementById('loaderBar');
    const status = document.getElementById('loaderStatus');
    const steps  = [
        [15, 'Criando banco de dados…'],
        [35, 'Carregando filmes…'],
        [55, 'Configurando combos…'],
        [70, 'Buscando posters…'],
        [85, 'Finalizando…'],
        [100,'Pronto! Abrindo o cinema…'],
    ];
    let i = 0;
    const run = () => {
        if (i >= steps.length) {
            setTimeout(() => {
                document.getElementById('cineLoader').style.opacity = '0';
                document.getElementById('cineLoader').style.transition = 'opacity .5s';
                setTimeout(() => document.getElementById('cineLoader').remove(), 500);
            }, 600);
            return;
        }
        const [pct, msg] = steps[i++];
        bar.style.width   = pct + '%';
        status.textContent = msg;
        setTimeout(run, pct === 100 ? 800 : 380);
    };
    setTimeout(run, 200);
})();
</script>
<?php endif; ?>


<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg cine-navbar sticky-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand" href="<?= APP_URL ?>/">
            <span class="brand-icon">🎬</span>
            <span class="brand-name">Cine<span class="brand-accent">Weeknd</span></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <i class="bi bi-list text-white fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/movies">Filmes</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/cart">Combos</a></li>
                <?php if (isLoggedIn()): ?>
                <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/orders">Meus Pedidos</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/my-tickets"><i class="bi bi-ticket-perforated me-1"></i>Ingressos</a></li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <a href="<?= APP_URL ?>/cart" class="cart-btn position-relative">
                    <i class="bi bi-bag"></i>
                    <span class="cart-badge" id="cartBadge"><?= count($_SESSION['cart'] ?? []) ?></span>
                </a>

                <?php if (isLoggedIn()): ?>
                <div class="dropdown">
                    <button class="btn btn-ghost-light dropdown-toggle user-pill"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= e($_SESSION['user_name'] ?? 'Conta') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end cine-dropdown">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/profile"><i class="bi bi-person me-2"></i>Perfil</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/orders"><i class="bi bi-receipt me-2"></i>Meus Pedidos</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/my-tickets"><i class="bi bi-ticket-perforated me-2"></i>Meus Ingressos</a></li>
                        <?php if (!empty($_SESSION['is_admin'])): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-warning" href="<?= APP_URL ?>/admin"><i class="bi bi-shield-check me-2"></i>Admin</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="<?= APP_URL ?>/logout" class="px-3">
                                <?= csrfField() ?>
                                <button type="submit" class="dropdown-item text-danger px-0">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sair
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                <?php else: ?>
                <a href="<?= APP_URL ?>/login" class="btn btn-ghost-light">Login</a>
                <a href="<?= APP_URL ?>/register" class="btn btn-cine">Entre!</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- FLASH MESSAGES -->
<?php $flash = getFlash(); if ($flash): ?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999">
    <div class="toast show align-items-center text-white border-0 toast-<?= e($flash['type']) ?>" role="alert">
        <div class="d-flex">
            <div class="toast-body"><?= $flash['message'] ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toast').forEach(function(t) {
        setTimeout(function() { bootstrap.Toast.getOrCreateInstance(t).hide(); }, 4000);
    });
});
</script>
<?php endif; ?>

<main>
