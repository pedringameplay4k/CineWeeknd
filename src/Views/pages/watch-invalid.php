<?php require __DIR__ . '/../layouts/header.php'; ?>
<section class="py-5 text-center">
  <div class="container" style="max-width:480px">
    <div style="font-size:4rem;margin-bottom:16px">🔒</div>
    <h2 class="section-title">Acesso Inválido<span class="dot">.</span></h2>
    <p class="text-muted mb-4">Este link expirou, já foi utilizado ou é inválido.<br>Verifique seus ingressos ou adquira um novo.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="<?= APP_URL ?>/my-tickets" class="btn-cine">🎫 Meus Ingressos</a>
      <a href="<?= APP_URL ?>/movies" class="btn btn-ghost-light">Ver Filmes</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
