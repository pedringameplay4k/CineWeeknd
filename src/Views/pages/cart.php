<?php
$pageTitle = 'Carrinho & Combos';
require __DIR__ . '/../layouts/header.php';
$cartItems = $_SESSION['cart'] ?? [];
$coupon    = $_SESSION['coupon'] ?? null;
$subtotal  = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cartItems));
$discount  = 0;
if ($coupon) {
    require_once __DIR__ . '/../../../../src/Models/Models.php';
    $discount = CouponModel::applyDiscount($coupon, $subtotal);
}
$total = max(0, $subtotal - $discount);
?>
<section class="py-5">
    <div class="container">
        <h1 class="page-heading">Meu Carrinho<span class="dot">.</span></h1>
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if (empty($cartItems)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🎬</div>
                    <div class="empty-state-title">Seu carrinho está vazio</div>
                    <p class="empty-state-desc">Adicione filmes ou combos para começar.</p>
                    <a href="<?= APP_URL ?>/movies" class="btn-cine d-inline-block mt-3">Ver Filmes</a>
                </div>
                <?php else: ?>
                <div class="cart-table">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Preço</th>
                                <th>Qtd</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $key => $item): ?>
                            <tr data-cart-row data-cart-price="<?= $item['price'] * ($item['qty'] ?? 1) ?>">
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if (!empty($item['image'])): ?>
                                        <img src="<?= UPLOAD_URL . e($item['image']) ?>" class="cart-item-img" alt="">
                                        <?php else: ?>
                                        <div class="cart-item-img d-flex align-items-center justify-content-center" style="background:var(--dark-5);border-radius:8px;font-size:1.5rem">
                                            <?= $item['type'] === 'movie' ? '🎬' : '🍿' ?>
                                        </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="cart-item-name"><?= e($item['name']) ?></div>
                                            <div class="cart-item-type">
                                                <?= $item['type'] === 'movie' ? 'Filme' : 'Combo' ?>
                                                <?php if (!empty($item['seat'])): ?>
                                                · <span style="color:var(--orange);font-weight:700">🪑 Assento <?= e($item['seat']) ?></span>
                                                <button class="btn-choose-seat" data-key="<?= e($key) ?>" data-seat="<?= e($item['seat']) ?>"
                                                        style="font-size:.68rem;color:#8b7fb5;background:none;border:none;cursor:pointer;padding:0;margin-left:4px">
                                                    (trocar)
                                                </button>
                                                <?php elseif ($item['type'] === 'movie'): ?>
                                                · <button class="btn-choose-seat btn-choose-seat-alert" data-key="<?= e($key) ?>" data-seat=""
                                                          title="Escolher assento">
                                                    <i class="bi bi-grid-3x3-gap me-1"></i>Escolher assento
                                                  </button>
                                                <?php endif; ?>
                                                <?php if (!empty($item['screening_id'])): ?>
                                                · <span style="color:#8b7fb5;font-size:.75rem"><i class="bi bi-calendar3"></i> Com sessão</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>R$ <?= number_format($item['price'], 2, ',', '.') ?></td>
                                <td>
                                    <div class="qty-ctrl">
                                        <button class="qty-btn" data-qty-change="-1" data-key="<?= e($key) ?>">−</button>
                                        <span class="qty-val" id="qty-<?= e($key) ?>"><?= $item['qty'] ?? 1 ?></span>
                                        <button class="qty-btn" data-qty-change="1" data-key="<?= e($key) ?>">+</button>
                                    </div>
                                </td>
                                <td class="text-gold fw-bold item-total" id="total-<?= e($key) ?>">R$ <?= number_format($item['price'] * ($item['qty'] ?? 1), 2, ',', '.') ?></td>
                                <td>
                                    <button class="btn-admin-sm btn-delete" data-remove-cart data-key="<?= e($key) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <div class="mt-5">
                    <h3 class="section-title mb-4">Adicionar um Combo<span class="dot">.</span></h3>
                    <div class="row g-3">
                        <?php foreach ($combos as $combo): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="combo-card">
                                <div class="combo-icon">🍿</div>
                                <div class="combo-name"><?= e($combo['name']) ?></div>
                                <p class="combo-desc"><?= e($combo['description']) ?></p>
                                <div class="d-flex align-items-center justify-content-between mt-3">
                                    <div class="combo-price">R$ <?= number_format($combo['price'], 2, ',', '.') ?></div>
                                    <button class="btn-cine" style="padding:.4rem 1rem;font-size:.85rem"
                                            data-add-cart data-type="combo" data-id="<?= $combo['id'] ?>">
                                        <i class="bi bi-plus me-1"></i>Adicionar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="cart-summary-card">
                    <h5 class="cart-summary-title">Resumo do Pedido</h5>
                    <form id="couponForm" class="coupon-input mb-3">
                        <input type="text" name="coupon" class="form-control"
                               placeholder="Código do cupom"
                               value="<?= $coupon ? e($coupon['code']) : '' ?>"
                               <?= $coupon ? 'disabled' : '' ?>>
                        <?php if (!$coupon): ?>
                        <button type="submit" class="btn-apply">Aplicar</button>
                        <?php else: ?>
                        <span class="btn-apply text-success border-success">✓</span>
                        <?php endif; ?>
                    </form>
                    <div id="couponMsg"></div>
                    <div class="divider"></div>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="cartSubtotal">R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                    </div>
                    <?php if ($discount > 0): ?>
                    <div class="summary-row" style="color:#10b981">
                        <span>Desconto (<?= e($coupon['code']) ?>)</span>
                        <span>-R$ <?= number_format($discount, 2, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-total">
                        <span>Total</span>
                        <span id="cartTotal">R$ <?= number_format($total, 2, ',', '.') ?></span>
                    </div>
                    <?php if (!empty($cartItems)): ?>
                    <?php if (isLoggedIn()): ?>
                    <!-- Botão abre modal de pagamento -->
                    <button class="btn-cine w-100 py-3 fw-bold fs-6 mt-3" id="btnAbrirPix">
                        <i class="bi bi-credit-card me-2"></i>Escolher Forma de Pagamento
                    </button>

                    <!-- Form oculto submetido após "pagamento" -->
                    <form method="POST" action="<?= APP_URL ?>/cart/checkout" id="checkoutForm" style="display:none">
                        <?= csrfField() ?>
                        <input type="hidden" name="payment_method" value="pix">
                    </form>
                    <?php else: ?>
                    <a href="<?= APP_URL ?>/login" class="btn-cine d-block text-center mt-3 py-3 fw-bold">
                        <i class="bi bi-lock me-2"></i>Entre para Finalizar
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     MODAL PIX — CineWeeknd
═══════════════════════════════════════════════════════ -->
<div id="pixModal" class="pix-overlay" aria-hidden="true">
  <div class="pix-modal">

    <!-- Cabeçalho -->
    <div class="pix-header">
      <div class="pix-logo">🎬 Cine<span>Weeknd</span></div>
      <button class="pix-close" id="pixClose" aria-label="Fechar">✕</button>
    </div>

    <!-- Seletor de método -->
    <div class="pay-method-selector" id="payMethodSelector">
      <p class="pix-label" style="margin-bottom:12px">Como deseja pagar?</p>
      <div class="pay-method-btns">
        <button class="pay-method-btn active" data-method="pix" id="btnMethodPix">
          <span class="pay-method-icon">📱</span>
          <span class="pay-method-name">PIX</span>
          <span class="pay-method-desc">Aprovação instantânea</span>
        </button>
        <button class="pay-method-btn" data-method="card" id="btnMethodCard">
          <span class="pay-method-icon">💳</span>
          <span class="pay-method-name">Cartão</span>
          <span class="pay-method-desc">Crédito ou débito</span>
        </button>
      </div>
      <button class="pix-btn-confirm" id="btnContinuarPagar" style="margin-top:16px">
        Continuar <i class="bi bi-arrow-right ms-1"></i>
      </button>
    </div>

    <!-- Steps -->
    <div class="pix-steps" id="pixSteps" style="display:none">

      <!-- STEP 1: QR Code PIX -->
      <div class="pix-step active" id="step1">
        <p class="pix-label">Escaneie o QR Code com seu banco</p>
        <div class="pix-qr-wrap">
          <canvas id="qrCanvas" width="180" height="180"></canvas>
          <div class="pix-scan-line"></div>
        </div>
        <div class="pix-key-wrap">
          <span class="pix-key-label">Chave PIX</span>
          <div class="pix-key-row">
            <span id="pixKey" class="pix-key-value">pagamentos@cineweeknd.com.br</span>
            <button class="pix-copy-btn" id="copyKey"><i class="bi bi-copy"></i></button>
          </div>
        </div>
        <div class="pix-amount">
          Total: <strong>R$ <?= number_format($total, 2, ',', '.') ?></strong>
        </div>
        <div class="pix-timer-wrap">
          <span class="pix-timer-label">QR expira em</span>
          <span class="pix-timer" id="pixTimer">05:00</span>
        </div>
        <button class="pix-btn-confirm" id="btnSimPago">
          <i class="bi bi-check-circle me-2"></i>Já fiz o pagamento
        </button>
      </div>

      <!-- STEP 1b: Cartão -->
      <div class="pix-step" id="step1card">
        <p class="pix-label">Dados do Cartão <span style="color:#8b7fb5;font-size:.78rem">(simulação)</span></p>
        <div class="card-form">
          <div class="card-preview" id="cardPreview">
            <div class="card-preview-chip">▤</div>
            <div class="card-preview-number" id="cardPreviewNum">•••• •••• •••• ••••</div>
            <div class="card-preview-bottom">
              <div>
                <div class="card-preview-label">TITULAR</div>
                <div class="card-preview-value" id="cardPreviewName">SEU NOME</div>
              </div>
              <div style="text-align:right">
                <div class="card-preview-label">VALIDADE</div>
                <div class="card-preview-value" id="cardPreviewExp">MM/AA</div>
              </div>
            </div>
          </div>
          <div class="card-field-wrap">
            <label class="card-label">Número do Cartão</label>
            <input type="text" class="card-input" id="cardNumber" placeholder="0000 0000 0000 0000" maxlength="19" inputmode="numeric">
          </div>
          <div class="card-field-wrap">
            <label class="card-label">Nome no Cartão</label>
            <input type="text" class="card-input" id="cardName" placeholder="NOME COMPLETO">
          </div>
          <div style="display:flex;gap:12px">
            <div class="card-field-wrap" style="flex:1">
              <label class="card-label">Validade</label>
              <input type="text" class="card-input" id="cardExp" placeholder="MM/AA" maxlength="5" inputmode="numeric">
            </div>
            <div class="card-field-wrap" style="flex:1">
              <label class="card-label">CVV</label>
              <input type="text" class="card-input" id="cardCvv" placeholder="•••" maxlength="3" inputmode="numeric">
            </div>
          </div>
          <button class="pix-btn-confirm" id="btnPagarCartao">
            <i class="bi bi-lock-fill me-2"></i>Pagar R$ <?= number_format($total, 2, ',', '.') ?>
          </button>
        </div>
      </div>

      <!-- STEP 2: Verificando -->
      <div class="pix-step" id="step2">
        <div class="pix-checking">
          <div class="pix-spinner"></div>
          <p class="pix-checking-text">Verificando pagamento<span class="pix-dots"></span></p>
          <p class="pix-checking-sub">Aguarde alguns instantes</p>
        </div>
      </div>

      <!-- STEP 3: Aprovado! -->
      <div class="pix-step" id="step3">
        <div class="pix-success">
          <div class="pix-success-icon">✓</div>
          <h3 class="pix-success-title">Pagamento Aprovado!</h3>
          <p class="pix-success-sub">Seu ingresso está sendo gerado…</p>
          <!-- Simulação de e-mail -->
          <div class="pix-email-anim" id="emailAnim">
            <div class="email-envelope">✉️</div>
            <p class="email-text">Comprovante enviado para<br><strong><?= e($_SESSION['user_email'] ?? 'seu e-mail') ?></strong></p>
          </div>
        </div>
      </div>

    </div><!-- /pix-steps -->
  </div>
</div>

<style>
/* ── Overlay ── */
.pix-overlay {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(5,3,15,.85);
    backdrop-filter: blur(6px);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none;
    transition: opacity .3s;
}
.pix-overlay.open { opacity: 1; pointer-events: all; }

/* ── Modal ── */
.pix-modal {
    background: var(--dark-2);
    border: 1px solid var(--border);
    border-radius: 24px;
    width: 100%; max-width: 420px;
    padding: 0;
    box-shadow: 0 30px 80px rgba(0,0,0,.7), 0 0 0 1px rgba(168,85,247,.15);
    transform: translateY(24px) scale(.97);
    transition: transform .35s cubic-bezier(.34,1.56,.64,1);
    overflow: hidden;
}
.pix-overlay.open .pix-modal { transform: translateY(0) scale(1); }

/* ── Header ── */
.pix-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--border);
}
.pix-logo { font-family: var(--font-display); font-size: 1.3rem; font-weight: 700; color: #f0eaff; }
.pix-logo span { color: var(--orange); }
.pix-close {
    background: var(--dark-4); border: 1px solid var(--border);
    color: var(--text-muted); border-radius: 8px;
    width: 32px; height: 32px; cursor: pointer;
    font-size: .85rem; transition: all .2s;
    display: flex; align-items: center; justify-content: center;
}
.pix-close:hover { background: var(--dark-5); color: #fff; }

/* ── Steps ── */
.pix-step { display: none; padding: 24px; }
.pix-step.active { display: block; }

/* ── QR ── */
.pix-label { text-align: center; color: var(--text-muted); font-size: .88rem; margin-bottom: 16px; font-weight: 600; }
.pix-qr-wrap {
    position: relative; width: 196px; height: 196px;
    margin: 0 auto 20px;
    border: 3px solid var(--neon-cyan, #00e5ff);
    border-radius: 16px; padding: 6px;
    background: #fff;
    box-shadow: 0 0 24px rgba(0,229,255,.25);
    overflow: hidden;
}
.pix-qr-wrap canvas { border-radius: 10px; width: 180px; height: 180px; }
.pix-scan-line {
    position: absolute; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, transparent, #00e5ff, transparent);
    animation: scanLine 2s ease-in-out infinite;
    box-shadow: 0 0 8px #00e5ff;
}
@keyframes scanLine {
    0%   { top: 10px; opacity: 1; }
    50%  { opacity: .6; }
    100% { top: 185px; opacity: 1; }
}

/* ── Chave ── */
.pix-key-wrap { background: var(--dark-3); border: 1px solid var(--border); border-radius: 12px; padding: 12px 16px; margin-bottom: 14px; }
.pix-key-label { font-size: .72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: .8px; margin-bottom: 4px; }
.pix-key-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.pix-key-value { font-size: .85rem; color: var(--neon-cyan, #00e5ff); font-weight: 700; word-break: break-all; }
.pix-copy-btn {
    background: var(--dark-4); border: 1px solid var(--border);
    color: var(--text-muted); border-radius: 8px; padding: 4px 10px;
    cursor: pointer; font-size: .85rem; flex-shrink: 0; transition: all .2s;
}
.pix-copy-btn:hover { border-color: var(--neon-cyan,#00e5ff); color: var(--neon-cyan,#00e5ff); }

/* ── Amount & Timer ── */
.pix-amount { text-align: center; font-size: 1rem; color: var(--text-secondary); margin-bottom: 14px; }
.pix-amount strong { color: var(--orange); font-size: 1.3rem; }
.pix-timer-wrap { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 20px; }
.pix-timer-label { font-size: .78rem; color: var(--text-muted); font-weight: 600; }
.pix-timer { font-family: var(--font-display); font-size: 1.1rem; color: #fff; background: var(--dark-4); padding: 2px 12px; border-radius: 8px; border: 1px solid var(--border); letter-spacing: 2px; }
.pix-timer.urgent { color: #ef4444; border-color: #ef4444; animation: timerPulse .8s ease-in-out infinite; }
@keyframes timerPulse { 0%,100%{opacity:1} 50%{opacity:.5} }

/* ── Confirm button ── */
.pix-btn-confirm {
    width: 100%; padding: 14px;
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff; border: none; border-radius: 12px;
    font-size: 1rem; font-weight: 700; cursor: pointer;
    transition: all .2s; display: flex; align-items: center; justify-content: center;
}
.pix-btn-confirm:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,185,129,.4); }

/* ── Step 2: Verificando ── */
.pix-checking { text-align: center; padding: 20px 0; }
.pix-spinner {
    width: 60px; height: 60px; margin: 0 auto 20px;
    border: 4px solid var(--dark-5);
    border-top-color: var(--purple);
    border-radius: 50%;
    animation: spin .8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.pix-checking-text { font-size: 1.1rem; font-weight: 700; color: #f0eaff; margin-bottom: 6px; }
.pix-checking-sub { font-size: .85rem; color: var(--text-muted); }
.pix-dots::after {
    content: '';
    animation: dots 1.4s steps(4, end) infinite;
}
@keyframes dots {
    0%   { content: ''; }
    25%  { content: '.'; }
    50%  { content: '..'; }
    75%  { content: '...'; }
    100% { content: ''; }
}


/* ── Seletor de método ── */
.pay-method-selector { padding: 20px 24px; }
.pay-method-btns { display: flex; gap: 10px; margin-bottom: 4px; }
.pay-method-btn {
    flex: 1; padding: 14px 10px; border-radius: 14px;
    border: 2px solid var(--border); background: var(--dark-3);
    cursor: pointer; text-align: center; transition: all .2s;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
}
.pay-method-btn:hover { border-color: var(--purple); background: rgba(168,85,247,.06); }
.pay-method-btn.active { border-color: var(--purple); background: rgba(168,85,247,.1); box-shadow: 0 0 16px rgba(168,85,247,.2); }
.pay-method-icon { font-size: 1.6rem; }
.pay-method-name { font-weight: 800; color: #f0eaff; font-size: .88rem; }
.pay-method-desc { font-size: .7rem; color: #8b7fb5; font-weight: 600; }

/* ── Card form ── */
.card-form { display: flex; flex-direction: column; gap: 14px; }
.card-preview {
    background: linear-gradient(135deg, #1e1040, #2d1a5a);
    border: 1px solid rgba(168,85,247,.3);
    border-radius: 14px; padding: 18px 20px;
    min-height: 120px; display: flex; flex-direction: column; justify-content: space-between;
    box-shadow: 0 8px 24px rgba(0,0,0,.4);
}
.card-preview-chip { font-size: 1.4rem; color: #ffe94d; }
.card-preview-number { font-family: 'Courier New', monospace; font-size: 1.1rem; color: #f0eaff; letter-spacing: 3px; text-align: center; margin: 8px 0; }
.card-preview-bottom { display: flex; justify-content: space-between; align-items: flex-end; }
.card-preview-label { font-size: .6rem; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: 1px; }
.card-preview-value { font-size: .82rem; color: #f0eaff; font-weight: 700; letter-spacing: 1px; }
.card-field-wrap { display: flex; flex-direction: column; gap: 5px; }
.card-label { font-size: .75rem; color: #8b7fb5; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; }
.card-input {
    background: var(--dark-3); border: 1px solid var(--border);
    border-radius: 10px; padding: 10px 14px;
    color: #f0eaff; font-size: .92rem; font-weight: 600;
    transition: border-color .2s; outline: none; width: 100%;
}
.card-input:focus { border-color: var(--purple); box-shadow: 0 0 0 3px rgba(168,85,247,.15); }
.card-input::placeholder { color: #5e5580; }

/* ── Step 3: Sucesso ── */
.pix-success { text-align: center; padding: 10px 0 6px; }
.pix-success-icon {
    width: 72px; height: 72px; margin: 0 auto 16px;
    background: linear-gradient(135deg, #059669, #10b981);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 2rem; color: #fff;
    animation: popIn .5s cubic-bezier(.34,1.56,.64,1);
    box-shadow: 0 0 30px rgba(16,185,129,.5);
}
@keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
@keyframes shake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-6px)} 75%{transform:translateX(6px)} }
.pix-success-title { font-family: var(--font-display); font-size: 1.6rem; color: #f0eaff; margin-bottom: 6px; }
.pix-success-sub { color: var(--text-muted); font-size: .88rem; margin-bottom: 20px; }

/* ── E-mail animation ── */
.pix-email-anim {
    background: var(--dark-3); border: 1px solid var(--border);
    border-radius: 14px; padding: 16px 20px;
    display: flex; align-items: center; gap: 14px;
    animation: slideUp .5s ease .3s both;
}
@keyframes slideUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
.email-envelope {
    font-size: 2rem;
    animation: envelopeFloat 1.2s ease-in-out infinite;
}
@keyframes envelopeFloat {
    0%,100% { transform: translateY(0) rotate(-4deg); }
    50%      { transform: translateY(-6px) rotate(4deg); }
}
.email-text { font-size: .88rem; color: var(--text-secondary); text-align: left; margin: 0; line-height: 1.5; }
.email-text strong { color: #f0eaff; }
</style>

<script>
(function(){
    const modal     = document.getElementById('pixModal');
    const btnAbrir  = document.getElementById('btnAbrirPix');
    const btnClose  = document.getElementById('pixClose');
    const btnPago   = document.getElementById('btnSimPago');
    const timerEl   = document.getElementById('pixTimer');
    const form      = document.getElementById('checkoutForm');
    const selector  = document.getElementById('payMethodSelector');
    const stepsWrap = document.getElementById('pixSteps');

    let timerInterval = null;
    let currentMethod = 'pix';

    // ── Seletor de método ─────────────────────────────────
    document.querySelectorAll('.pay-method-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.pay-method-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentMethod = btn.dataset.method;
        });
    });

    document.getElementById('btnContinuarPagar')?.addEventListener('click', () => {
        selector.style.display = 'none';
        stepsWrap.style.display = 'block';
        form.querySelector('[name="payment_method"]').value = currentMethod;
        if (currentMethod === 'pix') {
            showStep(1);
            timerEl.classList.remove('urgent');
            drawFakeQR();
            startTimer(300);
        } else {
            showStep('1card');
        }
    });

    // ── Cartão: máscaras e preview ────────────────────────
    const cardNumber   = document.getElementById('cardNumber');
    const cardName     = document.getElementById('cardName');
    const cardExp      = document.getElementById('cardExp');
    const cardCvv      = document.getElementById('cardCvv');
    const previewNum   = document.getElementById('cardPreviewNum');
    const previewName  = document.getElementById('cardPreviewName');
    const previewExp   = document.getElementById('cardPreviewExp');

    cardNumber?.addEventListener('input', function() {
        let v = this.value.replace(/\D/g,'').slice(0,16);
        this.value = v.replace(/(.{4})/g,'$1 ').trim();
        previewNum.textContent = (this.value || '•••• •••• •••• ••••').padEnd(19, '•').slice(0,19);
    });
    cardName?.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        previewName.textContent = this.value || 'SEU NOME';
    });
    cardExp?.addEventListener('input', function() {
        let v = this.value.replace(/\D/g,'').slice(0,4);
        if (v.length > 2) v = v.slice(0,2) + '/' + v.slice(2);
        this.value = v;
        previewExp.textContent = this.value || 'MM/AA';
    });
    cardCvv?.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g,'').slice(0,3);
    });

    document.getElementById('btnPagarCartao')?.addEventListener('click', () => {
        const num  = cardNumber?.value.replace(/\s/g,'');
        const name = cardName?.value.trim();
        const exp  = cardExp?.value;
        const cvv  = cardCvv?.value;
        if (num.length < 16 || !name || exp.length < 5 || cvv.length < 3) {
            // Shake inválido
            document.querySelector('.card-form').style.animation = 'shake .3s ease';
            setTimeout(() => document.querySelector('.card-form').style.animation = '', 350);
            return;
        }
        showStep(2);
        setTimeout(() => { showStep(3); setTimeout(() => form.submit(), 2800); }, 2200);
    });

    // ── Gera QR code fake (grid de quadradinhos) ──────────
    function drawFakeQR() {
        const canvas = document.getElementById('qrCanvas');
        const ctx    = canvas.getContext('2d');
        const size   = 180;
        const cells  = 25;
        const cell   = size / cells;

        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, size, size);

        // Padrão pseudo-aleatório determinístico
        const seed = 'CINEWEEKND';
        let h = 0;
        for (let i = 0; i < seed.length; i++) h = (Math.imul(31, h) + seed.charCodeAt(i)) | 0;

        function rand() { h = (Math.imul(1664525, h) + 1013904223) | 0; return (h >>> 0) / 4294967296; }

        ctx.fillStyle = '#000000';
        for (let r = 0; r < cells; r++) {
            for (let c = 0; c < cells; c++) {
                // Cantos de posição (7x7)
                const inCorner =
                    (r < 7 && c < 7) ||
                    (r < 7 && c >= cells - 7) ||
                    (r >= cells - 7 && c < 7);
                if (inCorner) {
                    const lr = r % (cells - 7 < r ? r - (cells - 7) : r);
                    const lc = c % (cells - 7 < c ? c - (cells - 7) : c);
                    // borda externa
                    const onBorder = (r <= 6 && (c === 0 || c === 6)) ||
                                     (c <= 6 && (r === 0 || r === 6)) ||
                                     (r >= cells-7 && r <= cells-1 && (c === 0 || c === 6)) ||
                                     (c <= 6 && r >= cells-7) ||
                                     (r <= 6 && c >= cells-7);
                    if (onBorder) ctx.fillRect(c * cell, r * cell, cell, cell);
                    else if ((r >= 2 && r <= 4 && c >= 2 && c <= 4) ||
                             (r >= 2 && r <= 4 && c >= cells-5 && c <= cells-3) ||
                             (r >= cells-5 && r <= cells-3 && c >= 2 && c <= 4)) {
                        ctx.fillRect(c * cell, r * cell, cell, cell);
                    }
                } else {
                    if (rand() > 0.48) ctx.fillRect(c * cell, r * cell, cell - .5, cell - .5);
                }
            }
        }
    }

    // ── Timer PIX ─────────────────────────────────────────
    function startTimer(seconds) {
        clearInterval(timerInterval);
        let remaining = seconds;
        function update() {
            const m = String(Math.floor(remaining / 60)).padStart(2, '0');
            const s = String(remaining % 60).padStart(2, '0');
            timerEl.textContent = `${m}:${s}`;
            if (remaining <= 60) timerEl.classList.add('urgent');
            if (remaining <= 0) { clearInterval(timerInterval); timerEl.textContent = '00:00'; }
            remaining--;
        }
        update();
        timerInterval = setInterval(update, 1000);
    }

    // ── Mostrar step ──────────────────────────────────────
    function showStep(n) {
        document.querySelectorAll('.pix-step').forEach(s => s.classList.remove('active'));
        document.getElementById('step' + n).classList.add('active');
    }

    // ── Abrir modal ───────────────────────────────────────
    btnAbrir?.addEventListener('click', () => {
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        // Mostra seletor de método primeiro
        selector.style.display = 'block';
        stepsWrap.style.display = 'none';
        // Reset método
        document.querySelectorAll('.pay-method-btn').forEach(b => b.classList.toggle('active', b.dataset.method === 'pix'));
        currentMethod = 'pix';
    });

    // ── Fechar modal ──────────────────────────────────────
    function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        clearInterval(timerInterval);
        // Reset to method selector on next open
        setTimeout(() => {
            selector.style.display = 'block';
            stepsWrap.style.display = 'none';
        }, 350);
    }
    btnClose?.addEventListener('click', closeModal);
    modal?.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    // ── Copiar chave ──────────────────────────────────────
    document.getElementById('copyKey')?.addEventListener('click', function() {
        navigator.clipboard?.writeText('pagamentos@cineweeknd.com.br').catch(() => {});
        this.innerHTML = '<i class="bi bi-check-lg"></i>';
        this.style.color = '#10b981';
        setTimeout(() => { this.innerHTML = '<i class="bi bi-copy"></i>'; this.style.color = ''; }, 2000);
    });

    // ── Simular pagamento ─────────────────────────────────
    btnPago?.addEventListener('click', () => {
        clearInterval(timerInterval);
        showStep(2);

        // Simula verificação (2.2s) → aprovado → submete form
        setTimeout(() => {
            showStep(3);
            // Após mostrar o "e-mail enviado" por 2.5s, submete o pedido
            setTimeout(() => {
                form?.submit();
            }, 2800);
        }, 2200);
    });
})();
</script>


<!-- ═══════════════════════════════════════════════════════ -->
<!-- MODAL DE ESCOLHA DE ASSENTO -->
<!-- ═══════════════════════════════════════════════════════ -->
<div id="seatModal" class="seat-modal-overlay" style="display:none">
  <div class="seat-modal-box">
    <div class="seat-modal-header">
      <div>
        <div class="label-gold mb-1">Escolha seu assento</div>
        <div class="seat-modal-movie" id="seatModalMovie"></div>
      </div>
      <button class="seat-modal-close" id="seatModalClose">✕</button>
    </div>

    <!-- Tela -->
    <div class="cinema-screen-wrap mt-3 mb-1">
      <div class="cinema-screen-sm">TELA</div>
      <div class="cinema-screen-glow-sm"></div>
    </div>

    <!-- Mapa -->
    <div class="seat-map-modal" id="seatMapModal"></div>

    <!-- Legenda -->
    <div class="seat-legend-modal">
      <div class="seat-legend-item"><div class="sdemo free"></div> Disponível</div>
      <div class="seat-legend-item"><div class="sdemo taken"></div> Ocupado</div>
      <div class="seat-legend-item"><div class="sdemo selected"></div> Selecionado</div>
    </div>

    <!-- Confirmar -->
    <div class="seat-modal-footer">
      <div class="seat-modal-chosen">
        Assento selecionado: <strong id="seatModalChosen" style="color:var(--orange)">—</strong>
      </div>
      <button class="btn-cine" id="seatModalConfirm" disabled>
        <i class="bi bi-check-lg me-1"></i>Confirmar
      </button>
    </div>
  </div>
</div>

<style>
/* ── Botão escolher assento ── */
.btn-choose-seat {
  background: rgba(168,85,247,.12);
  border: 1px solid rgba(168,85,247,.35);
  border-radius: 7px; padding: 2px 10px;
  font-size: .72rem; font-weight: 700;
  color: #c4b5fd; cursor: pointer;
  transition: all .15s; white-space: nowrap;
}
.btn-choose-seat:hover { background: rgba(168,85,247,.25); color: #f0eaff; }
.btn-choose-seat-alert {
  background: rgba(255,95,31,.12);
  border-color: rgba(255,95,31,.4);
  color: #ff8c50;
  animation: seatPulse 2s ease-in-out infinite;
}
.btn-choose-seat-alert:hover { background: rgba(255,95,31,.25); color: #fff; }
@keyframes seatPulse {
  0%,100% { box-shadow: 0 0 0 0 rgba(255,95,31,.3); }
  50%      { box-shadow: 0 0 0 5px rgba(255,95,31,0); }
}

/* ── Modal overlay ── */
.seat-modal-overlay {
  position: fixed; inset: 0; z-index: 9000;
  background: rgba(0,0,0,.75);
  backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
  padding: 16px;
  animation: fadeIn .2s ease;
}
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
.seat-modal-box {
  background: var(--dark-2);
  border: 1px solid rgba(168,85,247,.3);
  border-radius: 20px; padding: 24px;
  width: 100%; max-width: 520px;
  max-height: 90vh; overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,.7);
  animation: slideUp .25s ease;
}
@keyframes slideUp { from{transform:translateY(30px);opacity:0} to{transform:translateY(0);opacity:1} }

.seat-modal-header { display:flex; justify-content:space-between; align-items:flex-start; }
.seat-modal-movie  { font-weight:700; color:#f0eaff; font-size:1rem; }
.seat-modal-close  {
  background:none; border:none; color:#8b7fb5;
  font-size:1.1rem; cursor:pointer; padding:4px 8px;
  border-radius:6px; transition: all .15s;
}
.seat-modal-close:hover { background:var(--dark-4); color:#f0eaff; }

/* ── Mini tela ── */
.cinema-screen-sm {
  display:inline-block;
  background:linear-gradient(180deg,rgba(168,85,247,.3),rgba(168,85,247,.03));
  border:1.5px solid rgba(168,85,247,.45); border-bottom:none;
  border-radius:3px 3px 50% 50%/3px 3px 14px 14px;
  padding:5px 60px 10px;
  font-size:.62rem; font-weight:800; letter-spacing:3px;
  color:rgba(168,85,247,.75); text-transform:uppercase;
}
.cinema-screen-glow-sm {
  height:1px; margin:0 auto; width:140px;
  background:linear-gradient(90deg,transparent,rgba(168,85,247,.5),transparent);
  box-shadow:0 0 12px 3px rgba(168,85,247,.2);
}

/* ── Mapa no modal ── */
.seat-map-modal { display:flex; flex-direction:column; align-items:center; gap:7px; margin:14px 0; }
.seat-row-m { display:flex; align-items:center; gap:5px; }
.seat-row-lbl { width:18px; font-size:.65rem; color:#7c6fa0; font-weight:700; text-align:center; flex-shrink:0; }
.seat-row-gap-m { width:14px; flex-shrink:0; }
.seat-m {
  width:34px; height:30px; border-radius:7px 7px 4px 4px;
  cursor:pointer; border:1.5px solid transparent;
  transition:all .12s; position:relative;
  display:flex; align-items:center; justify-content:center;
  font-size:.62rem; font-weight:700;
}
.seat-m::before { content:''; position:absolute; top:-5px; left:4px; right:4px; height:5px; border-radius:3px 3px 0 0; }
.seat-m.free    { background:rgba(30,25,50,.9); border-color:rgba(100,80,180,.45); color:rgba(140,110,220,.7); }
.seat-m.free::before { background:rgba(80,60,160,.4); }
.seat-m.free:hover   { background:rgba(168,85,247,.22); border-color:var(--purple); transform:translateY(-2px) scale(1.08); box-shadow:0 4px 10px rgba(168,85,247,.3); }
.seat-m.taken   { background:rgba(180,30,30,.25); border-color:rgba(220,50,50,.5); color:rgba(220,80,80,.5); cursor:not-allowed; }
.seat-m.taken::before { background:rgba(180,30,30,.35); }
.seat-m.selected { background:linear-gradient(135deg,#ff5f1f,#ff8c00); border-color:#ff5f1f; color:#fff; transform:translateY(-2px) scale(1.1); box-shadow:0 4px 14px rgba(255,95,31,.45); }
.seat-m.selected::before { background:#ff4500; }

/* ── Legenda modal ── */
.seat-legend-modal { display:flex; justify-content:center; gap:16px; flex-wrap:wrap; margin-bottom:16px; }
.seat-legend-item  { display:flex; align-items:center; gap:6px; font-size:.72rem; color:#8b7fb5; }
.sdemo { width:22px; height:20px; border-radius:5px 5px 3px 3px; border:1.5px solid transparent; }
.sdemo.free     { background:rgba(30,25,50,.9); border-color:rgba(100,80,180,.45); }
.sdemo.taken    { background:rgba(180,30,30,.25); border-color:rgba(220,50,50,.5); }
.sdemo.selected { background:linear-gradient(135deg,#ff5f1f,#ff8c00); border-color:#ff5f1f; }

/* ── Footer modal ── */
.seat-modal-footer {
  display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
  border-top:1px solid var(--border); padding-top:14px; margin-top:4px;
}
.seat-modal-chosen { font-size:.85rem; color:#c4b5fd; font-weight:600; }
#seatModalConfirm:disabled { opacity:.4; cursor:not-allowed; }
</style>

<script>
// ── Dados de assentos no modal ────────────────────────────
const M_ROWS = ['A','B','C','D','E','F','G'];
const M_COLS = 8;
let modalKey      = null;
let modalSeat     = null;
let modalExisting = null;  // assento já salvo

function getOccupiedModal(cartKey, taken) {
    // Seed baseada na key do carrinho (consistente)
    let seed = 0;
    for (let i = 0; i < cartKey.length; i++) seed = seed * 31 + cartKey.charCodeAt(i);
    seed = Math.abs(seed);

    const all = [];
    M_ROWS.forEach(r => { for(let c=1;c<=M_COLS;c++) all.push(r+c); });
    for (let i = all.length-1; i > 0; i--) {
        seed = (seed * 1103515245 + 12345) & 0x7fffffff;
        const j = seed % (i+1);
        [all[i], all[j]] = [all[j], all[i]];
    }
    return new Set(all.slice(0, Math.min(taken || 12, all.length - 5)));
}

function renderModalMap(cartKey, currentSeat) {
    const map      = document.getElementById('seatMapModal');
    const occupied = getOccupiedModal(cartKey, 12);
    map.innerHTML  = '';
    modalSeat      = currentSeat || null;

    // Remove assento atual dos ocupados (a pessoa pode manter o dela)
    if (currentSeat) occupied.delete(currentSeat);

    M_ROWS.forEach(row => {
        const rowEl = document.createElement('div');
        rowEl.className = 'seat-row-m';

        const lbl = document.createElement('div');
        lbl.className = 'seat-row-lbl';
        lbl.textContent = row;
        rowEl.appendChild(lbl);

        for (let col = 1; col <= M_COLS; col++) {
            if (col === 5) {
                const g = document.createElement('div');
                g.className = 'seat-row-gap-m';
                rowEl.appendChild(g);
            }
            const id   = row + col;
            const s    = document.createElement('div');
            const occ  = occupied.has(id);
            const sel  = (id === currentSeat);
            s.className    = 'seat-m ' + (occ ? 'taken' : sel ? 'selected' : 'free');
            s.dataset.sid  = id;
            s.textContent  = col;
            s.title        = 'Assento ' + id;
            if (!occ) s.addEventListener('click', () => pickModalSeat(s, id));
            rowEl.appendChild(s);
        }
        map.appendChild(rowEl);
    });
    updateModalConfirm();
}

function pickModalSeat(el, id) {
    document.querySelectorAll('.seat-m.selected').forEach(s => s.classList.replace('selected','free'));
    el.classList.replace('free','selected');
    modalSeat = id;
    updateModalConfirm();
}

function updateModalConfirm() {
    document.getElementById('seatModalChosen').textContent = modalSeat || '—';
    document.getElementById('seatModalConfirm').disabled   = !modalSeat;
}

// ── Abre modal ─────────────────────────────────────────────
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-choose-seat');
    if (!btn) return;
    e.preventDefault();

    modalKey      = btn.dataset.key;
    modalExisting = btn.dataset.seat || null;

    // Nome do filme (pega da célula da tabela)
    const row      = btn.closest('tr');
    const nameEl   = row?.querySelector('.cart-item-name');
    document.getElementById('seatModalMovie').textContent = nameEl?.textContent || 'Filme';

    renderModalMap(modalKey, modalExisting);
    document.getElementById('seatModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
});

// ── Fecha modal ────────────────────────────────────────────
function closeModal() {
    document.getElementById('seatModal').style.display = 'none';
    document.body.style.overflow = '';
    modalKey = null; modalSeat = null;
}
document.getElementById('seatModalClose').addEventListener('click', closeModal);
document.getElementById('seatModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeModal();
});
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

// ── Confirma assento via AJAX ──────────────────────────────
document.getElementById('seatModalConfirm').addEventListener('click', async () => {
    if (!modalSeat || !modalKey) return;

    const btn  = document.getElementById('seatModalConfirm');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const res  = await fetch(window.APP_URL + '/cart/seat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ key: modalKey, seat: modalSeat, _csrf_token: csrf })
        });
        const data = await res.json();

        if (data.success) {
            // Atualiza o botão na tabela
            const seatBtn = document.querySelector(`.btn-choose-seat[data-key="${modalKey}"]`);
            if (seatBtn) {
                seatBtn.dataset.seat = modalSeat;
                // Atualiza o texto ao lado
                const typeDiv = seatBtn.closest('.cart-item-type');
                if (typeDiv) {
                    // Troca botão de alerta por badge + trocar
                    seatBtn.classList.remove('btn-choose-seat-alert');
                    seatBtn.style.animation = 'none';
                    seatBtn.style.fontSize = '.68rem';
                    seatBtn.style.color = '#8b7fb5';
                    seatBtn.innerHTML = '(trocar)';

                    // Adiciona/atualiza span de assento
                    let badge = typeDiv.querySelector('.seat-badge-inline');
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'seat-badge-inline';
                        badge.style.cssText = 'color:var(--orange);font-weight:700;margin-right:3px';
                        typeDiv.insertBefore(badge, seatBtn);
                        typeDiv.insertBefore(document.createTextNode(' · '), badge);
                    }
                    badge.textContent = '🪑 Assento ' + modalSeat;
                }
            }
            closeModal();
            // Toast de confirmação
            if (typeof showToast === 'function') showToast('Assento ' + modalSeat + ' reservado!', 'success');
        }
    } catch(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Confirmar';
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
