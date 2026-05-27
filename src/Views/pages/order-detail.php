<?php
$pageTitle = 'Pedido #' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
require __DIR__ . '/../layouts/header.php';
$orderNum = str_pad($order['id'], 6, '0', STR_PAD_LEFT);
?>
<section class="py-5">
  <div class="container" style="max-width:680px">

    <a href="<?= APP_URL ?>/orders" class="text-muted small mb-4 d-inline-flex align-items-center gap-1">
      <i class="bi bi-arrow-left"></i> Voltar aos Pedidos
    </a>

    <!-- Banner de confirmação -->
    <div class="confirm-banner">
      <div class="confirm-icon">🎬</div>
      <div>
        <h2 class="confirm-title">Pagamento Confirmado!</h2>
        <p class="confirm-sub">Boa sessão, <?= e(explode(' ', $_SESSION['user_name'] ?? 'Cinéfilo')[0]) ?>! Seu ingresso está pronto.</p>
      </div>
    </div>

    <!-- E-mail notif animado -->
    <div class="email-notif">
      <div class="email-notif-icon">✉️</div>
      <div class="email-notif-text">
        <span class="email-notif-title">Comprovante enviado para seu e-mail!</span>
        <span class="email-notif-sub">Verifique sua caixa de entrada — de: noreply@cineweeknd.com.br</span>
      </div>
      <div class="email-notif-badge">PIX</div>
    </div>

    <!-- INGRESSO -->
    <div class="ticket" id="ticket">
      <div class="ticket-header">
        <div class="ticket-brand">🎬 Cine<span>Weeknd</span></div>
        <div class="ticket-num">#<?= $orderNum ?></div>
      </div>

      <div class="ticket-items">
        <?php foreach ($items as $item): ?>
        <div class="ticket-item">
          <div class="ticket-item-icon"><?= $item['item_type'] === 'movie' ? '🎥' : '🍿' ?></div>
          <div class="ticket-item-info">
            <div class="ticket-item-name"><?= e($item['item_name']) ?></div>
            <div class="ticket-item-type">
              <?= $item['item_type'] === 'movie' ? 'INGRESSO' : 'COMBO' ?> · <?= $item['quantity'] ?>x
              <?php if (!empty($item['seat'])): ?>
              &nbsp;<span style="background:#ff5f1f;color:#fff;font-weight:800;font-size:.7rem;padding:2px 8px;border-radius:6px;letter-spacing:.5px">🪑 <?= e($item['seat']) ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="ticket-item-price">R$ <?= number_format($item['unit_price'] * $item['quantity'], 2, ',', '.') ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Linha picotada -->
      <div class="ticket-tear">
        <div class="ticket-tear-circle left"></div>
        <div class="ticket-tear-line"></div>
        <div class="ticket-tear-circle right"></div>
      </div>

      <div class="ticket-footer">
        <div class="ticket-meta-row">
          <div class="ticket-meta">
            <div class="ticket-meta-label">Data</div>
            <div class="ticket-meta-value"><?= date('d/m/Y', strtotime($order['created_at'])) ?></div>
          </div>
          <div class="ticket-meta">
            <div class="ticket-meta-label">Horário</div>
            <div class="ticket-meta-value"><?= date('H:i', strtotime($order['created_at'])) ?></div>
          </div>
          <div class="ticket-meta">
            <div class="ticket-meta-label">Pagamento</div>
            <div class="ticket-meta-value">PIX</div>
          </div>
          <div class="ticket-meta">
            <div class="ticket-meta-label">Status</div>
            <div class="ticket-meta-value" style="color:#10b981">✓ Confirmado</div>
          </div>
        </div>

        <div class="ticket-total-row">
          <?php if ($order['discount'] > 0): ?>
          <div class="ticket-discount">Desconto: -R$ <?= number_format($order['discount'], 2, ',', '.') ?></div>
          <?php endif; ?>
          <div class="ticket-total">
            <span>Total Pago</span>
            <strong>R$ <?= number_format($order['total'], 2, ',', '.') ?></strong>
          </div>
        </div>

        <!-- Barcode fake -->
        <div class="ticket-barcode">
          <canvas id="barcodeCanvas" width="280" height="48"></canvas>
          <div class="ticket-barcode-num"><?= $orderNum ?> · CineWeeknd · <?= date('dmY') ?></div>
        </div>
      </div>
    </div><!-- /ticket -->

    <?php
    $movieLinks = array_filter($items, fn($i) => $i['item_type'] === 'movie' && !empty($i['gdrive_url']));
    if ($movieLinks): ?>
    <!-- Google Drive — links liberados após pagamento -->
    <div class="gdrive-section">
      <div class="gdrive-header">
        <span class="gdrive-icon">▶</span>
        <div>
          <div class="gdrive-title">Assista Agora</div>
          <div class="gdrive-sub">Link de acesso liberado após confirmação do pagamento</div>
        </div>
      </div>
      <?php foreach ($movieLinks as $item): ?>
      <a href="<?= e($item['gdrive_url']) ?>" target="_blank" rel="noopener" class="gdrive-btn">
        <span class="gdrive-btn-icon">
          <svg width="20" height="20" viewBox="0 0 87.3 78" xmlns="http://www.w3.org/2000/svg"><path d="m6.6 66.85 3.85 6.65c.8 1.4 1.95 2.5 3.3 3.3l13.75-23.8h-27.5c0 1.55.4 3.1 1.2 4.5z" fill="#0066da"/><path d="m43.65 25-13.75-23.8c-1.35.8-2.5 1.9-3.3 3.3l-25.4 44a9.06 9.06 0 0 0 -1.2 4.5h27.5z" fill="#00ac47"/><path d="m73.55 76.8c1.35-.8 2.5-1.9 3.3-3.3l1.6-2.75 7.65-13.25c.8-1.4 1.2-2.95 1.2-4.5h-27.502l5.852 11.5z" fill="#ea4335"/><path d="m43.65 25 13.75-23.8c-1.35-.8-2.9-1.2-4.5-1.2h-18.5c-1.6 0-3.15.45-4.5 1.2z" fill="#00832d"/><path d="m59.8 53h-32.3l-13.75 23.8c1.35.8 2.9 1.2 4.5 1.2h50.8c1.6 0 3.15-.45 4.5-1.2z" fill="#2684fc"/><path d="m73.4 26.5-12.7-22c-.8-1.4-1.95-2.5-3.3-3.3l-13.75 23.8 16.15 27h27.45c0-1.55-.4-3.1-1.2-4.5z" fill="#ffba00"/></svg>
        </span>
        <span class="gdrive-btn-label">
          <span class="gdrive-btn-movie"><?= e($item['item_name']) ?></span>
          <span class="gdrive-btn-hint">Abrir no Google Drive</span>
        </span>
        <i class="bi bi-box-arrow-up-right gdrive-btn-arrow"></i>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Ações -->
    <div class="d-flex gap-3 mt-4 flex-wrap">
      <a href="<?= APP_URL ?>/movies" class="btn-cine flex-1 text-center py-3">
        <i class="bi bi-film me-2"></i>Ver Mais Filmes
      </a>
      <button onclick="window.print()" class="btn-ticket-print flex-1 py-3">
        <i class="bi bi-printer me-2"></i>Imprimir Ingresso
      </button>
      <a href="<?= APP_URL ?>/orders" class="btn btn-ghost-light flex-1 text-center py-3">
        <i class="bi bi-receipt me-2"></i>Meus Pedidos
      </a>
    </div>

    <!-- Reenviar e-mail -->
    <div class="resend-wrap mt-3">
      <button class="resend-btn" id="btnResendEmail" data-order="<?= $order['id'] ?>">
        <i class="bi bi-envelope-arrow-up me-2"></i>Não recebeu o comprovante? Reenviar agora
      </button>
    </div>

<style>
/* Google Drive section */
.gdrive-section{
    background:linear-gradient(135deg,rgba(26,115,232,.1),rgba(52,168,83,.08));
    border:1.5px solid rgba(52,168,83,.35);border-radius:18px;
    padding:20px 22px;margin-top:20px;margin-bottom:4px;
    animation:fadeInDown .5s ease .3s both;
}
.gdrive-header{display:flex;align-items:center;gap:14px;margin-bottom:16px}
.gdrive-icon{font-size:1.6rem;width:42px;height:42px;background:linear-gradient(135deg,#1a73e8,#34a853);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;flex-shrink:0}
.gdrive-title{font-weight:800;color:#f0eaff;font-size:1.05rem;line-height:1.2}
.gdrive-sub{font-size:.78rem;color:#8b7fb5;margin-top:2px}
.gdrive-btn{
    display:flex;align-items:center;gap:14px;
    background:rgba(255,255,255,.05);border:1px solid rgba(52,168,83,.3);
    border-radius:12px;padding:14px 16px;margin-top:10px;
    text-decoration:none;transition:all .2s;cursor:pointer;
}
.gdrive-btn:hover{background:rgba(52,168,83,.12);border-color:rgba(52,168,83,.6);transform:translateY(-1px)}
.gdrive-btn-icon{flex-shrink:0;display:flex;align-items:center;justify-content:center;width:40px;height:40px;background:#fff;border-radius:10px;padding:4px}
.gdrive-btn-label{flex:1}
.gdrive-btn-movie{display:block;font-weight:700;color:#f0eaff;font-size:.92rem}
.gdrive-btn-hint{display:block;font-size:.75rem;color:#34a853;margin-top:2px;font-weight:600}
.gdrive-btn-arrow{color:#8b7fb5;font-size:1rem;flex-shrink:0}
.gdrive-btn:hover .gdrive-btn-arrow{color:#34a853}
.resend-wrap { text-align: center; }
.resend-btn {
    background: none; border: none;
    color: #8b7fb5; font-size: .82rem; font-weight: 600;
    cursor: pointer; padding: 8px 16px; border-radius: 8px;
    transition: all .2s; text-decoration: underline; text-underline-offset: 3px;
}
.resend-btn:hover { color: var(--purple); }
.resend-btn:disabled { opacity: .5; cursor: not-allowed; text-decoration: none; }
</style>

<script>
document.getElementById('btnResendEmail')?.addEventListener('click', async function() {
    const btn     = this;
    const orderId = btn.dataset.order;
    const csrf    = document.querySelector('meta[name="csrf-token"]')?.content || '';

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando…';

    try {
        const res  = await fetch('<?= APP_URL ?>/orders/resend-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ order_id: orderId, _csrf_token: csrf })
        });
        const data = await res.json();

        if (data.success) {
            btn.innerHTML = '<i class="bi bi-check-circle-fill me-2" style="color:#10b981"></i>' + data.message;
            btn.style.color = '#10b981';
            btn.style.textDecoration = 'none';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-exclamation-circle me-2" style="color:#ef4444"></i>' + data.message;
            btn.style.color = '#ef4444';
            btn.style.textDecoration = 'none';
            // Permite tentar de novo após 3s
            setTimeout(() => {
                btn.disabled = false;
                btn.style.color = '';
                btn.style.textDecoration = '';
                btn.innerHTML = '<i class="bi bi-envelope-arrow-up me-2"></i>Tentar novamente';
            }, 3000);
        }
    } catch(e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-envelope-arrow-up me-2"></i>Não recebeu o comprovante? Reenviar agora';
    }
});
</script>

  </div>
</section>

<style>
.confirm-banner {
    display:flex;align-items:center;gap:18px;
    background:linear-gradient(135deg,rgba(16,185,129,.12),rgba(5,150,105,.06));
    border:1px solid rgba(16,185,129,.3);border-radius:18px;
    padding:20px 24px;margin-bottom:16px;
    animation:fadeInDown .5s ease;
}
@keyframes fadeInDown{from{opacity:0;transform:translateY(-16px)}to{opacity:1;transform:translateY(0)}}
.confirm-icon{font-size:2.8rem}
.confirm-title{font-family:var(--font-display);font-size:1.6rem;color:#f0eaff;margin:0 0 4px}
.confirm-sub{color:#8b7fb5;font-size:.9rem;margin:0}

.email-notif{
    display:flex;align-items:center;gap:14px;
    background:var(--dark-3);border:1px solid var(--border);
    border-radius:14px;padding:14px 18px;margin-bottom:24px;
    animation:slideRight .5s ease .2s both;
}
@keyframes slideRight{from{opacity:0;transform:translateX(-20px)}to{opacity:1;transform:translateX(0)}}
.email-notif-icon{font-size:1.8rem;animation:envelopeWiggle 2s ease-in-out 1s 3}
@keyframes envelopeWiggle{0%,100%{transform:rotate(0)}25%{transform:rotate(-10deg)}75%{transform:rotate(10deg)}}
.email-notif-text{flex:1}
.email-notif-title{display:block;font-weight:700;color:#f0eaff;font-size:.92rem}
.email-notif-sub{display:block;font-size:.78rem;color:#8b7fb5}
.email-notif-badge{background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:.72rem;font-weight:800;padding:3px 10px;border-radius:99px;letter-spacing:1px}

/* INGRESSO */
.ticket{
    background:var(--dark-2);border:1px solid var(--border);border-radius:20px;overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,.5),0 0 0 1px rgba(168,85,247,.1);
    animation:ticketReveal .6s cubic-bezier(.34,1.56,.64,1) .1s both;
}
@keyframes ticketReveal{from{opacity:0;transform:scale(.95) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
.ticket-header{
    display:flex;align-items:center;justify-content:space-between;
    padding:20px 24px 16px;
    background:linear-gradient(135deg,rgba(168,85,247,.15),rgba(255,95,31,.08));
    border-bottom:1px dashed rgba(255,255,255,.08);
}
.ticket-brand{font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:#f0eaff}
.ticket-brand span{color:var(--orange)}
.ticket-num{font-family:'Courier New',monospace;font-size:.85rem;color:#8b7fb5;letter-spacing:2px}
.ticket-items{padding:16px 24px}
.ticket-item{display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid rgba(255,255,255,.05)}
.ticket-item:last-child{border-bottom:none}
.ticket-item-icon{font-size:1.4rem;width:36px;text-align:center}
.ticket-item-info{flex:1}
.ticket-item-name{font-weight:700;color:#f0eaff;font-size:.92rem}
.ticket-item-type{font-size:.72rem;color:#8b7fb5;text-transform:uppercase;letter-spacing:.8px;font-weight:700;margin-top:2px}
.ticket-item-price{font-weight:700;color:var(--orange);font-size:.95rem}
.ticket-tear{position:relative;display:flex;align-items:center}
.ticket-tear-circle{width:22px;height:22px;border-radius:50%;background:var(--dark-1);flex-shrink:0;z-index:2}
.ticket-tear-circle.left{margin-left:-11px}
.ticket-tear-circle.right{margin-right:-11px}
.ticket-tear-line{flex:1;border-top:2px dashed rgba(255,255,255,.1)}
.ticket-footer{padding:20px 24px 22px}
.ticket-meta-row{display:flex;gap:0;margin-bottom:16px}
.ticket-meta{flex:1;text-align:center;padding:0 8px;border-right:1px solid rgba(255,255,255,.06)}
.ticket-meta:last-child{border-right:none}
.ticket-meta-label{font-size:.68rem;color:#8b7fb5;text-transform:uppercase;letter-spacing:.8px;font-weight:700;margin-bottom:4px}
.ticket-meta-value{font-size:.88rem;color:#f0eaff;font-weight:700}
.ticket-discount{text-align:right;font-size:.82rem;color:#10b981;margin-bottom:4px}
.ticket-total{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:rgba(168,85,247,.08);border:1px solid rgba(168,85,247,.2);border-radius:10px;margin-bottom:16px}
.ticket-total span{color:#8b7fb5;font-size:.88rem;font-weight:700}
.ticket-total strong{color:var(--orange);font-size:1.3rem;font-family:var(--font-display)}
.ticket-barcode{text-align:center}
.ticket-barcode-num{font-family:'Courier New',monospace;font-size:.68rem;color:rgba(255,255,255,.2);margin-top:6px;letter-spacing:1.5px}

.btn-ticket-print{
    background:var(--dark-3);border:1px solid var(--border);color:var(--text-primary);
    border-radius:12px;font-weight:700;font-size:.92rem;cursor:pointer;
    transition:all .2s;display:flex;align-items:center;justify-content:center;gap:6px;
}
.btn-ticket-print:hover{border-color:var(--purple);color:var(--purple)}

@media print {
    nav,.email-notif,.confirm-banner,.d-flex.gap-3,footer{display:none!important}
    body{background:#fff!important}
    .ticket{box-shadow:none!important;border:1.5px solid #ccc!important;color:#000!important}
    .ticket-brand,.ticket-item-name,.ticket-meta-value{color:#000!important}
    .ticket-brand span,.ticket-item-price,.ticket-total strong{color:#c05000!important}
    .ticket-meta-label,.ticket-item-type,.ticket-num,.ticket-barcode-num{color:#666!important}
    .ticket-header{background:#f5f5f5!important}
    .ticket-tear-circle{background:#fff!important;border:1px solid #ddd}
    .ticket-total{background:#f9f9f9!important;border:1px solid #ddd!important}
    .ticket-total span{color:#666!important}
}
</style>

<script>
(function(){
    const canvas = document.getElementById('barcodeCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const w = 280, h = 48;
    ctx.clearRect(0, 0, w, h);

    const seed = '<?= $orderNum ?>';
    let hash = 0;
    for (let i = 0; i < seed.length; i++) hash = (Math.imul(31, hash) + seed.charCodeAt(i)) | 0;
    function rand() { hash = (Math.imul(1664525, hash) + 1013904223) | 0; return (hash >>> 0) / 4294967296; }

    ctx.fillStyle = 'rgba(240,234,255,0.85)';
    let x = 4;
    while (x < w - 4) {
        const barW = rand() > 0.6 ? (rand() > 0.5 ? 4 : 2) : 1;
        if (rand() > 0.42) ctx.fillRect(x, 4, barW, h - 8);
        x += barW + (rand() > 0.7 ? 2 : 1);
    }
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
