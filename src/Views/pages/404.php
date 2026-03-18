<?php
$pageTitle = '404 — Página Não Encontrada';
require __DIR__ . '/../layouts/header.php';
?>
<section class="py-5 text-center" style="min-height:60vh;display:flex;align-items:center">
    <div class="container">
        <div style="font-size:6rem;font-family:var(--font-display);color:var(--orange);line-height:1">404</div>
        <h2 class="section-title my-3">Página Não Encontrada</h2>
        <p class="text-muted mb-4">O filme que você procura não existe ou foi removido. 🎬</p>
        <a href="<?= APP_URL ?>/" class="btn-cine d-inline-flex align-items-center gap-2">
            <i class="bi bi-house"></i> Voltar ao Início
        </a>
    </div>
</section>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
