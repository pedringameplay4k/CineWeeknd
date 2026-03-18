<?php require __DIR__ . '/../layouts/header.php'; ?>

<section class="py-5">
<div class="container" style="max-width:760px">

  <h1 class="section-title mb-1">Meus Ingressos<span class="dot">.</span></h1>
  <p class="text-muted mb-4">Seus acessos digitais — clique no ingresso para assistir</p>

  <?php if (empty($tokens)): ?>
  <div class="empty-state">
    <div class="empty-state-icon">🎫</div>
    <div class="empty-state-title">Nenhum ingresso ainda</div>
    <p class="empty-state-desc">Compre um filme para receber seu acesso digital.</p>
    <a href="<?= APP_URL ?>/movies" class="btn-cine mt-2">Ver Filmes</a>
  </div>

  <?php else: ?>
  <div class="tickets-grid">
    <?php foreach ($tokens as $t):
      $now       = time();
      $validFrom = strtotime($t['valid_from']);
      $validUntil= strtotime($t['valid_until']);
      $isActive  = $now >= $validFrom && $now <= $validUntil;
      $isPast    = $now > $validUntil;
      $isFuture  = $now < $validFrom;
    ?>
    <div class="ticket-card <?= $isPast ? 'ticket-past' : ($isActive ? 'ticket-active' : 'ticket-future') ?>">

      <!-- Poster -->
      <?php if (!empty($t['poster'])): ?>
      <img src="<?= e($t['poster']) ?>" class="ticket-card-poster" alt="">
      <?php else: ?>
      <div class="ticket-card-poster ticket-no-poster">🎬</div>
      <?php endif; ?>

      <!-- Info -->
      <div class="ticket-card-info">
        <div class="ticket-card-title"><?= e($t['title']) ?></div>
        <div class="ticket-card-meta">
          <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($t['starts_at'])) ?>
          · <?= date('H:i', strtotime($t['starts_at'])) ?>
          · <?= e($t['room']) ?>
        </div>
        <div class="ticket-card-status mt-2">
          <?php if ($isActive): ?>
            <span class="status-active">🟢 Sessão ao vivo — assistir agora</span>
          <?php elseif ($isFuture): ?>
            <span class="status-future">🟡 Disponível às <?= date('H:i', $validFrom) ?></span>
          <?php else: ?>
            <span class="status-past">⚫ Sessão encerrada</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Ação -->
      <div class="ticket-card-action">
        <?php if ($isActive): ?>
        <a href="<?= APP_URL ?>/watch/<?= e($t['token']) ?>" class="btn-cine py-2 px-3 text-center" style="font-size:.85rem">
          <i class="bi bi-play-fill me-1"></i>Assistir
        </a>
        <?php elseif ($isFuture): ?>
        <div class="ticket-countdown" data-until="<?= $validFrom ?>">
          <div class="countdown-label">Começa em</div>
          <div class="countdown-timer">--:--:--</div>
        </div>
        <?php else: ?>
        <span class="ticket-expired">Expirado</span>
        <?php endif; ?>
      </div>

    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>
</section>

<style>
.tickets-grid { display:flex; flex-direction:column; gap:14px; }
.ticket-card {
    display:flex; align-items:center; gap:16px;
    background:var(--dark-2); border:1px solid var(--border);
    border-radius:16px; padding:16px; transition:all .2s;
}
.ticket-card.ticket-active {
    border-color:rgba(16,185,129,.4);
    box-shadow:0 0 20px rgba(16,185,129,.1);
}
.ticket-card.ticket-future { border-color:rgba(255,233,77,.2); }
.ticket-card.ticket-past { opacity:.5; }
.ticket-card-poster {
    width:56px; height:78px; object-fit:cover;
    border-radius:8px; flex-shrink:0; border:1px solid var(--border);
}
.ticket-no-poster {
    display:flex; align-items:center; justify-content:center;
    font-size:1.8rem; background:var(--dark-4);
}
.ticket-card-info { flex:1; }
.ticket-card-title { font-weight:700; color:#f0eaff; font-size:.95rem; margin-bottom:4px; }
.ticket-card-meta { font-size:.78rem; color:#8b7fb5; }
.status-active { color:#10b981; font-size:.82rem; font-weight:700; }
.status-future { color:#ffe94d; font-size:.82rem; font-weight:700; }
.status-past   { color:#5e5580; font-size:.82rem; }
.ticket-card-action { flex-shrink:0; text-align:center; }
.ticket-countdown { text-align:center; }
.countdown-label { font-size:.68rem; color:#8b7fb5; text-transform:uppercase; letter-spacing:.8px; font-weight:700; }
.countdown-timer { font-family:var(--font-display); font-size:1rem; color:#ffe94d; letter-spacing:2px; margin-top:2px; }
.ticket-expired { font-size:.78rem; color:#5e5580; font-weight:700; }
</style>

<script>
document.querySelectorAll('.ticket-countdown').forEach(el => {
    const until = parseInt(el.dataset.until) * 1000;
    const timer = el.querySelector('.countdown-timer');
    function update() {
        const diff = Math.max(0, Math.floor((until - Date.now()) / 1000));
        const h = String(Math.floor(diff / 3600)).padStart(2,'0');
        const m = String(Math.floor((diff % 3600) / 60)).padStart(2,'0');
        const s = String(diff % 60).padStart(2,'0');
        timer.textContent = `${h}:${m}:${s}`;
        if (diff <= 0) { timer.textContent = 'Disponível!'; timer.style.color = '#10b981'; location.reload(); }
    }
    update();
    setInterval(update, 1000);
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
