<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin') ?> — CineWeeknd Admin</title>
    <meta name="app-url" content="<?= APP_URL ?>">
    <script>window.APP_URL = '<?= APP_URL ?>';</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>

<?php $flash = getFlash(); if ($flash): ?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999">
    <div class="toast show align-items-center text-white border-0 toast-<?= e($flash['type']) ?>" role="alert">
        <div class="d-flex">
            <div class="toast-body"><?= $flash['message'] ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="d-flex" style="min-height:100vh">
    <div class="admin-sidebar" style="width:220px;flex-shrink:0">
        <a href="<?= APP_URL ?>/" class="d-block px-4 pb-4 mb-2 text-decoration-none border-bottom" style="border-color:var(--border)!important">
            <span class="brand-name" style="font-size:1.2rem">Cine<span class="brand-accent">Weeknd</span></span>
            <div class="small text-muted" style="font-size:.7rem;letter-spacing:1px">PAINEL ADMIN</div>
        </a>
        <a href="<?= APP_URL ?>/admin" class="admin-nav-link <?= $pageTitle==='Dashboard'?'active':'' ?>"><i class="bi bi-grid-1x2"></i> Dashboard</a>
        <a href="<?= APP_URL ?>/admin/movies" class="admin-nav-link <?= strpos($pageTitle,'Filmes')!==false?'active':'' ?>"><i class="bi bi-film"></i> Filmes</a>
        <a href="<?= APP_URL ?>/admin/combos" class="admin-nav-link <?= strpos($pageTitle,'Combos')!==false?'active':'' ?>"><i class="bi bi-cup-straw"></i> Combos</a>
        <a href="<?= APP_URL ?>/admin/users" class="admin-nav-link <?= strpos($pageTitle,'Usuários')!==false?'active':'' ?>"><i class="bi bi-people"></i> Usuários</a>
        <a href="<?= APP_URL ?>/admin/coupons" class="admin-nav-link <?= strpos($pageTitle,'Cupons')!==false?'active':'' ?>"><i class="bi bi-ticket-perforated"></i> Cupons</a>
        <div class="mt-auto pt-4 px-4">
            <form method="POST" action="<?= APP_URL ?>/logout">
                <?= csrfField() ?>
                <button type="submit" class="admin-nav-link w-100 text-danger border-0 bg-transparent" style="cursor:pointer">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </button>
            </form>
        </div>
    </div>
    <div class="flex-1 p-4" style="overflow-x:auto">
