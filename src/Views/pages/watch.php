<?php require __DIR__ . '/../layouts/header.php'; ?>

<section class="py-4">
<div class="container" style="max-width:960px">

  <!-- Breadcrumb -->
  <a href="<?= APP_URL ?>/my-tickets" class="text-muted small mb-3 d-inline-flex align-items-center gap-1">
    <i class="bi bi-arrow-left"></i> Meus Ingressos
  </a>

  <!-- Info da sessão -->
  <div class="watch-session-bar mb-4">
    <div class="watch-session-info">
      <span class="watch-live-dot"></span>
      <span class="watch-session-title"><?= e($data['title']) ?></span>
      <span class="watch-session-meta">
        <?= e($data['room']) ?> · <?= date('d/m/Y H:i', strtotime($data['starts_at'])) ?>
      </span>
    </div>
    <div class="watch-session-valid">
      <i class="bi bi-clock me-1"></i>
      Acesso válido até <?= date('H:i', strtotime($data['valid_until'])) ?>
    </div>
  </div>

  <!-- Player de vídeo -->
  <div class="watch-player-wrap">
    <?php if (!empty($data['video_url'])): ?>
      <?php
        // Detecta se é YouTube, MP4 local ou outro
        $url = $data['video_url'];
        $isYoutube = str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be');
        $isLocal   = str_starts_with($url, '/') || str_starts_with($url, 'http://localhost');
      ?>

      <?php if ($isYoutube):
        // Converte URL para embed
        preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m);
        $videoId = $m[1] ?? '';
      ?>
      <iframe class="watch-iframe"
              src="https://www.youtube.com/embed/<?= $videoId ?>?autoplay=1&rel=0&modestbranding=1"
              allow="autoplay; fullscreen"
              allowfullscreen></iframe>

      <?php elseif ($isLocal): ?>
      <video class="watch-video" controls autoplay>
        <source src="<?= e($url) ?>" type="video/mp4">
        Seu navegador não suporta vídeo HTML5.
      </video>

      <?php else: ?>
      <iframe class="watch-iframe" src="<?= e($url) ?>" allow="autoplay; fullscreen" allowfullscreen></iframe>
      <?php endif; ?>

    <?php else: ?>
    <!-- Placeholder quando não há video_url cadastrado -->
    <div class="watch-placeholder">
      <div class="watch-placeholder-icon">🎬</div>
      <h3 class="watch-placeholder-title"><?= e($data['title']) ?></h3>
      <p class="watch-placeholder-sub">
        O link do filme ainda não foi configurado.<br>
        Adicione a URL em <strong>video_url</strong> no banco de dados.
      </p>
      <div class="watch-placeholder-token">
        <span class="label-gold">Seu token de acesso</span>
        <code><?= substr($data['token'] ?? '—', 0, 16) ?>…</code>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Countdown para expiração -->
  <div class="watch-footer mt-3">
    <div class="watch-expire-bar">
      <span class="watch-expire-label">Sessão encerra em</span>
      <span class="watch-expire-timer" id="expireTimer">--:--</span>
    </div>
  </div>

</div>
</section>

<style>
.watch-session-bar {
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;
    background:var(--dark-3); border:1px solid var(--border); border-radius:12px;
    padding:12px 20px;
}
.watch-session-info { display:flex; align-items:center; gap:10px; }
.watch-live-dot {
    width:10px; height:10px; border-radius:50%; background:#10b981;
    animation:livePulse 1.5s ease-in-out infinite; flex-shrink:0;
}
@keyframes livePulse{0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,.5)}50%{box-shadow:0 0 0 6px rgba(16,185,129,0)}}
.watch-session-title { font-weight:700; color:#f0eaff; font-size:.95rem; }
.watch-session-meta { font-size:.8rem; color:#8b7fb5; }
.watch-session-valid { font-size:.8rem; color:#ffe94d; font-weight:600; }

.watch-player-wrap {
    background:#000; border-radius:16px; overflow:hidden;
    border:1px solid var(--border);
    aspect-ratio:16/9; position:relative;
}
.watch-iframe, .watch-video {
    width:100%; height:100%; border:none; display:block;
}
.watch-placeholder {
    width:100%; height:100%; display:flex; flex-direction:column;
    align-items:center; justify-content:center; gap:12px;
    background:linear-gradient(135deg,#0d0a1a,#1a1530);
    padding:40px; text-align:center;
}
.watch-placeholder-icon { font-size:4rem; }
.watch-placeholder-title { font-family:var(--font-display); font-size:1.8rem; color:#f0eaff; margin:0; }
.watch-placeholder-sub { color:#8b7fb5; font-size:.9rem; margin:0; }
.watch-placeholder-token { background:var(--dark-3); border:1px solid var(--border); border-radius:10px; padding:12px 20px; }
.watch-placeholder-token code { display:block; margin-top:4px; color:var(--purple); font-size:.85rem; }

.watch-footer { display:flex; justify-content:center; }
.watch-expire-bar { display:flex; align-items:center; gap:10px; background:var(--dark-3); border:1px solid var(--border); border-radius:10px; padding:8px 18px; }
.watch-expire-label { font-size:.78rem; color:#8b7fb5; font-weight:700; }
.watch-expire-timer { font-family:var(--font-display); font-size:1rem; color:#ffe94d; letter-spacing:2px; }
</style>

<script>
(function(){
    const validUntil = new Date('<?= $data['valid_until'] ?>').getTime();
    const timerEl   = document.getElementById('expireTimer');

    function update() {
        const diff = Math.max(0, Math.floor((validUntil - Date.now()) / 1000));
        const h    = String(Math.floor(diff / 3600)).padStart(2,'0');
        const m    = String(Math.floor((diff % 3600) / 60)).padStart(2,'0');
        const s    = String(diff % 60).padStart(2,'0');
        timerEl.textContent = h > 0 ? `${h}:${m}:${s}` : `${m}:${s}`;
        if (diff <= 300) timerEl.style.color = '#ef4444';
        if (diff <= 0) {
            timerEl.textContent = 'Expirado';
            timerEl.style.color = '#ef4444';
        }
    }
    update();
    setInterval(update, 1000);
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
