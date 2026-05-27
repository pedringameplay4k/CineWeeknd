<?php require __DIR__ . '/../layouts/header.php'; ?>

<section class="py-5">
<div class="container" style="max-width:880px">

  <a href="<?= APP_URL ?>/movies/<?= e($movie['slug']) ?>" class="text-muted small mb-4 d-inline-flex align-items-center gap-1">
    <i class="bi bi-arrow-left"></i> Voltar ao filme
  </a>

  <!-- Hero do filme -->
  <div class="screening-hero mb-4">
    <?php
    $posterVal = $movie['poster'] ?? '';
    $posterSrc = empty($posterVal) ? DEFAULT_POSTER : (str_starts_with($posterVal, 'http') ? $posterVal : UPLOAD_URL . $posterVal);
    ?>
    <img src="<?= e($posterSrc) ?>" class="screening-poster" alt="<?= e($movie['title']) ?>">
    <div class="screening-info">
      <div class="label-gold mb-1">Escolha como assistir</div>
      <h1 class="section-title mb-2"><?= e($movie['title']) ?><span class="dot">.</span></h1>
      <div class="d-flex gap-3 flex-wrap" style="font-size:.82rem;color:var(--text-muted)">
        <span><i class="bi bi-clock me-1"></i><?= $movie['duration_min'] ?? '—' ?> min</span>
        <span><i class="bi bi-star-fill me-1 text-gold"></i><?= $movie['rating'] ?></span>
      </div>
      <!-- Preços -->
      <div class="price-badges mt-3">
        <div class="price-badge digital">
          <i class="bi bi-laptop me-1"></i>Digital
          <span class="price-val">R$ <?= number_format($movie['price_digital'] ?? 2.90, 2, ',', '.') ?></span>
        </div>
        <div class="price-badge cinema">
          <i class="bi bi-buildings me-1"></i>Presencial
          <span class="price-val">R$ <?= number_format($movie['price_cinema'] ?? 5.90, 2, ',', '.') ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- TABS: Digital vs Cinema -->
  <div class="mode-tabs mb-4">
    <button class="mode-tab active" data-mode="digital">
      <div class="mode-tab-icon">💻</div>
      <div class="mode-tab-label">Assistir Online</div>
      <div class="mode-tab-desc">Em casa, no seu dispositivo</div>
      <div class="mode-tab-price">a partir de R$ <?= number_format($movie['price_digital'] ?? 2.90, 2, ',', '.') ?></div>
    </button>
    <button class="mode-tab" data-mode="cinema">
      <div class="mode-tab-icon">🎭</div>
      <div class="mode-tab-label">Ir ao Cinema</div>
      <div class="mode-tab-desc">Escolha o shopping e horário</div>
      <div class="mode-tab-price">a partir de R$ <?= number_format($movie['price_cinema'] ?? 5.90, 2, ',', '.') ?></div>
    </button>
  </div>

  <!-- ══ PAINEL DIGITAL ══════════════════════════════════════ -->
  <?php
  // Encontra a primeira sessão digital disponível para auto-seleção
  $firstDigital = null;
  foreach ($screeningsDigital as $_date => $_slots) {
      foreach ($_slots as $_s) {
          if (strtotime($_s['starts_at']) >= time()) { $firstDigital = $_s; break 2; }
      }
  }
  ?>
  <div id="panelDigital">
    <div class="mode-info-bar digital-bar mb-4">
      <i class="bi bi-info-circle me-2"></i>
      Filme disponível imediatamente após a compra. Assista quando e onde quiser, sem precisar sair de casa.
      <strong>Combos não estão disponíveis no modo digital</strong> — apenas em sessões presenciais.
    </div>

    <?php if ($firstDigital): ?>
    <input type="hidden" id="digitalScreeningId" value="<?= $firstDigital['id'] ?>">
    <input type="hidden" id="digitalPrice" value="<?= $movie['price_digital'] ?? 2.90 ?>">
    <div class="digital-access-box">
      <div class="digital-access-icon">🎬</div>
      <div>
        <div class="digital-access-title">Acesso Imediato</div>
        <div class="digital-access-sub">Após a compra o link do filme é liberado instantaneamente</div>
      </div>
    </div>
    <?php else: ?>
    <div class="empty-state"><div class="empty-state-icon">💻</div><div class="empty-state-title">Sem sessões digitais disponíveis</div></div>
    <?php endif; ?>
  </div>

  <!-- ══ PAINEL CINEMA ════════════════════════════════════════ -->
  <div id="panelCinema" style="display:none">
    <div class="mode-info-bar cinema-bar mb-4">
      <i class="bi bi-info-circle me-2"></i>
      Sessões presenciais incluem direito a <strong>combos de pipoca e bebida</strong>. Escolha o shopping e o horário desejado.
    </div>

    <?php if (empty($screeningsCinema)): ?>
    <div class="empty-state"><div class="empty-state-icon">🎭</div><div class="empty-state-title">Sem sessões presenciais disponíveis</div></div>
    <?php else: ?>

    <!-- Filtro por Shopping -->
    <div class="step-label mb-2"><span class="step-num">1</span> Escolha o shopping</div>
    <div class="venue-tabs mb-4" id="venueTabs">
      <button class="venue-tab active" data-venue-filter="all">Todos</button>
      <?php
      $venuesSeen = [];
      foreach ($screeningsCinema as $slots) {
          foreach ($slots as $s) {
              $vn = $s['venue_name'] ?? '';
              if ($vn && !in_array($vn, $venuesSeen)) {
                  $venuesSeen[] = $vn;
                  echo "<button class='venue-tab' data-venue-filter='" . e($vn) . "'>" . e($vn) . "</button>";
              }
          }
      }
      ?>
    </div>

    <div class="step-label mb-3"><span class="step-num">2</span> Escolha o horário</div>
    <?php foreach ($screeningsCinema as $date => $slots): ?>
    <?php $ts=strtotime($date);$dn=$ptDays[date('l',$ts)]??date('l',$ts);$mn=$ptMonths[date('F',$ts)]??date('F',$ts);
    $lbl=($date===date('Y-m-d')?"📅 Hoje":($date===date('Y-m-d',strtotime('+1 day'))?"📅 Amanhã":"📅 {$dn}")).", ".date('d',$ts)." de {$mn}"; ?>
    <div class="screening-day mb-4" data-date="<?= $date ?>">
      <div class="screening-date-label"><?= $lbl ?></div>
      <div class="screening-slots">
        <?php foreach ($slots as $s):
          $past=$past2=strtotime($s['starts_at'])<time();
          $sold=($s['seats_left']??0)<=0;
          $dis=$past||$sold;
          $vn=$s['venue_name']??'';
        ?>
        <button class="slot-btn slot-cinema <?= $dis?'disabled':'' ?>" <?= $dis?'disabled':'' ?>
                data-mode="cinema"
                data-screening="<?= $s['id'] ?>"
                data-time="<?= date('H:i',strtotime($s['starts_at'])) ?>"
                data-date="<?= date('d/m/Y',strtotime($s['starts_at'])) ?>"
                data-room="<?= e($s['room']) ?>"
                data-venue="<?= e($vn) ?>"
                data-capacity="<?= $s['capacity'] ?>"
                data-taken="<?= $s['seats_taken'] ?>"
                data-price="<?= $movie['price_cinema'] ?? 5.90 ?>">
          <div class="slot-venue"><?= e($vn) ?></div>
          <div class="slot-time"><?= date('H:i',strtotime($s['starts_at'])) ?></div>
          <div class="slot-room"><?= e($s['room']) ?></div>
          <div class="slot-seats">
            <?php if($sold): ?><span class="slot-sold">Esgotado</span>
            <?php elseif($past): ?><span class="slot-past">Encerrada</span>
            <?php else: ?><span><?= $s['seats_left'] ?> lugares</span><?php endif; ?>
          </div>
        </button>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- ══ PASSO 2: Mapa de assentos (só cinema) ════════════════ -->
  <div id="seatMapSection" style="display:none">
    <div class="step-label mt-4 mb-3">
      <span class="step-num" id="seatStepNum">3</span> Escolha seu assento
      <span class="step-session-info" id="stepSessionInfo"></span>
    </div>
    <div class="cinema-screen-wrap">
      <div class="cinema-screen">TELA</div>
      <div class="cinema-screen-glow"></div>
    </div>
    <div class="seat-map" id="seatMap"></div>
    <div class="seat-legend">
      <div class="seat-legend-item"><div class="seat-demo free"></div> Disponível</div>
      <div class="seat-legend-item"><div class="seat-demo taken"></div> Ocupado</div>
      <div class="seat-legend-item"><div class="seat-demo selected"></div> Selecionado</div>
    </div>
  </div>

  <!-- ══ CARD DE CONFIRMAÇÃO ══════════════════════════════════ -->
  <div class="screening-confirm-card" id="confirmCard" style="display:none">
    <div class="confirm-card-inner">
      <div class="confirm-card-info">
        <div class="label-gold mb-1">Resumo do ingresso</div>
        <div class="confirm-movie"><?= e($movie['title']) ?></div>
        <div class="confirm-details mt-1">
          <span id="confirmDate"></span> · <span id="confirmTime"></span>
        </div>
        <div class="confirm-meta mt-1" id="confirmMeta"></div>
        <div class="confirm-seat mt-1" id="confirmSeatRow" style="display:none">
          <i class="bi bi-grid-3x3-gap me-1"></i>
          Assento: <strong id="confirmSeat" style="color:var(--orange)">—</strong>
        </div>
      </div>
      <div class="confirm-card-action">
        <div class="confirm-price" id="confirmPrice">—</div>
        <?php if (isLoggedIn()): ?>
        <form method="POST" action="<?= APP_URL ?>/cart/movie" id="sessionCartForm">
          <?= csrfField() ?>
          <input type="hidden" name="movie_id"     value="<?= $movie['id'] ?>">
          <input type="hidden" name="screening_id" id="selectedScreeningId" value="">
          <input type="hidden" name="seat"         id="selectedSeat"        value="">
          <input type="hidden" name="mode"         id="selectedMode"        value="">
          <input type="hidden" name="price"        id="selectedPrice"       value="">
          <button type="submit" class="btn-cine w-100" id="btnAddCart" disabled>
            <i class="bi bi-bag-plus me-2"></i>Adicionar ao Carrinho
          </button>
        </form>
        <?php else: ?>
        <a href="<?= APP_URL ?>/login" class="btn-cine d-block text-center">
          <i class="bi bi-lock me-2"></i>Entre para Comprar
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
</section>

<style>
/* ── Hero ── */
.screening-hero{display:flex;gap:20px;align-items:flex-start}
.screening-poster{width:90px;height:130px;object-fit:cover;border-radius:12px;border:2px solid var(--border);flex-shrink:0}

/* ── Price badges ── */
.price-badges{display:flex;gap:10px;flex-wrap:wrap}
.price-badge{display:flex;align-items:center;gap:6px;padding:6px 14px;border-radius:10px;font-size:.78rem;font-weight:700}
.price-badge.digital{background:rgba(139,92,246,.15);border:1px solid rgba(139,92,246,.35);color:#c4b5fd}
.price-badge.cinema{background:rgba(255,95,31,.12);border:1px solid rgba(255,95,31,.35);color:#ffa07a}
.price-val{font-size:.92rem;font-weight:800}

/* ── Mode tabs ── */
.mode-tabs{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.mode-tab{background:var(--dark-3);border:2px solid var(--border);border-radius:16px;padding:18px 16px;cursor:pointer;text-align:center;transition:all .2s;position:relative;overflow:hidden}
.mode-tab::before{content:'';position:absolute;inset:0;opacity:0;transition:opacity .2s}
.mode-tab.digital-active::before{background:radial-gradient(circle at top,rgba(139,92,246,.15),transparent 70%);opacity:1}
.mode-tab.cinema-active::before{background:radial-gradient(circle at top,rgba(255,95,31,.15),transparent 70%);opacity:1}
.mode-tab.active,.mode-tab:hover{transform:translateY(-2px)}
.mode-tab.active[data-mode="digital"]{border-color:#7c3aed;box-shadow:0 0 0 1px #7c3aed,0 8px 24px rgba(124,58,237,.25)}
.mode-tab.active[data-mode="cinema"]{border-color:#ff5f1f;box-shadow:0 0 0 1px #ff5f1f,0 8px 24px rgba(255,95,31,.25)}
.mode-tab-icon{font-size:1.8rem;margin-bottom:6px}
.mode-tab-label{font-weight:800;font-size:1rem;color:#f0eaff;margin-bottom:3px}
.mode-tab-desc{font-size:.72rem;color:#8b7fb5;margin-bottom:8px}
.mode-tab-price{font-size:.78rem;font-weight:700;color:var(--orange)}

/* ── Info bar ── */
.mode-info-bar{padding:10px 16px;border-radius:10px;font-size:.82rem;line-height:1.5}
.digital-bar{background:rgba(139,92,246,.08);border:1px solid rgba(139,92,246,.25);color:#c4b5fd}
.cinema-bar{background:rgba(255,95,31,.08);border:1px solid rgba(255,95,31,.25);color:#ffa07a}

/* ── Venue filter tabs ── */
.venue-tabs{display:flex;flex-wrap:wrap;gap:8px}
.venue-tab{background:var(--dark-3);border:1.5px solid var(--border);border-radius:8px;padding:6px 14px;font-size:.75rem;font-weight:700;color:#8b7fb5;cursor:pointer;transition:all .15s;white-space:nowrap}
.venue-tab:hover,.venue-tab.active{background:rgba(255,95,31,.12);border-color:rgba(255,95,31,.4);color:#ffa07a}

/* ── Step ── */
.step-label{display:flex;align-items:center;gap:10px;font-weight:700;font-size:1rem;color:#f0eaff;margin-bottom:16px}
.step-num{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;font-size:.82rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.step-session-info{font-size:.78rem;color:#8b7fb5;font-weight:400}

/* ── Date label ── */
.screening-date-label{font-size:.78rem;font-weight:800;color:var(--orange);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px}

/* ── Slots ── */
.screening-slots{display:flex;flex-wrap:wrap;gap:10px}
.slot-btn{background:var(--dark-3);border:1.5px solid var(--border);border-radius:12px;padding:10px 14px;cursor:pointer;min-width:110px;text-align:center;transition:all .18s}
.slot-btn.slot-digital:hover:not(.disabled){border-color:#7c3aed;background:rgba(124,58,237,.1);transform:translateY(-2px);box-shadow:0 6px 18px rgba(124,58,237,.2)}
.slot-btn.slot-cinema:hover:not(.disabled){border-color:var(--orange);background:rgba(255,95,31,.1);transform:translateY(-2px);box-shadow:0 6px 18px rgba(255,95,31,.2)}
.slot-btn.selected{box-shadow:0 0 0 2px var(--purple)}
.slot-btn.slot-cinema.selected{box-shadow:0 0 0 2px var(--orange)}
.slot-btn.disabled{opacity:.4;cursor:not-allowed}
.slot-venue{font-size:.62rem;color:#ff8c50;font-weight:800;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px}
.slot-time{font-family:var(--font-display);font-size:1.25rem;color:#f0eaff;font-weight:700}
.slot-room{font-size:.65rem;color:#8b7fb5;text-transform:uppercase;letter-spacing:.8px;font-weight:700;margin:2px 0}
.slot-seats{font-size:.72rem;color:#10b981;font-weight:700}
.slot-sold{color:#ef4444}.slot-past{color:#5e5580}

/* ── Tela cinema ── */
.cinema-screen-wrap{text-align:center;margin-bottom:22px}
.cinema-screen{display:inline-block;background:linear-gradient(180deg,rgba(168,85,247,.35),rgba(168,85,247,.05));border:2px solid rgba(168,85,247,.5);border-bottom:none;border-radius:4px 4px 60% 60%/4px 4px 20px 20px;padding:8px 80px 14px;font-size:.72rem;font-weight:800;letter-spacing:3px;color:rgba(168,85,247,.8);text-transform:uppercase}
.cinema-screen-glow{height:2px;margin:0 auto;width:200px;background:linear-gradient(90deg,transparent,rgba(168,85,247,.6),transparent);box-shadow:0 0 18px 4px rgba(168,85,247,.25)}

/* ── Assentos ── */
.seat-map{display:flex;flex-direction:column;align-items:center;gap:9px;margin-bottom:24px}
.seat-row{display:flex;align-items:center;gap:7px}
.seat-row-label{width:22px;font-size:.75rem;color:#7c6fa0;font-weight:700;text-align:center;flex-shrink:0}
.seat-row-gap{width:22px;flex-shrink:0}
.seat{width:38px;height:34px;border-radius:8px 8px 5px 5px;cursor:pointer;border:1.5px solid transparent;transition:all .15s;position:relative;display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:700}
.seat::before{content:'';position:absolute;top:-6px;left:5px;right:5px;height:6px;border-radius:4px 4px 0 0}
.seat.free{background:rgba(30,25,50,.9);border-color:rgba(100,80,180,.45);color:rgba(140,110,220,.7)}
.seat.free::before{background:rgba(80,60,160,.45)}
.seat.free:hover{background:rgba(168,85,247,.22);border-color:var(--purple);transform:translateY(-3px) scale(1.1);box-shadow:0 5px 14px rgba(168,85,247,.35)}
.seat.taken{background:rgba(180,30,30,.25);border-color:rgba(220,50,50,.5);color:rgba(220,80,80,.6);cursor:not-allowed}
.seat.taken::before{background:rgba(180,30,30,.4)}
.seat.selected{background:linear-gradient(135deg,#ff5f1f,#ff8c00);border-color:#ff5f1f;color:#fff;transform:translateY(-3px) scale(1.12);box-shadow:0 5px 18px rgba(255,95,31,.5)}
.seat.selected::before{background:#ff4500}
.seat-legend{display:flex;justify-content:center;gap:20px;flex-wrap:wrap;margin-bottom:24px}
.seat-legend-item{display:flex;align-items:center;gap:7px;font-size:.78rem;color:#8b7fb5}
.seat-demo{width:22px;height:20px;border-radius:4px 4px 3px 3px;border:1.5px solid transparent}
.seat-demo.free{background:rgba(30,25,50,.9);border-color:rgba(100,80,180,.45)}
.seat-demo.taken{background:rgba(180,30,30,.25);border-color:rgba(220,50,50,.5)}
.seat-demo.selected{background:linear-gradient(135deg,#ff5f1f,#ff8c00);border-color:#ff5f1f}

/* ── Digital access box ── */
.digital-access-box{display:flex;align-items:center;gap:18px;background:rgba(139,92,246,.1);border:1.5px solid rgba(139,92,246,.3);border-radius:14px;padding:20px 22px;margin-bottom:8px}
.digital-access-icon{font-size:2rem;flex-shrink:0}
.digital-access-title{font-weight:800;color:#f0eaff;font-size:1rem;margin-bottom:4px}
.digital-access-sub{font-size:.8rem;color:#a78bfa}

/* ── Card confirmar ── */
.screening-confirm-card{position:sticky;bottom:20px;z-index:100;background:var(--dark-2);border:1px solid rgba(168,85,247,.35);border-radius:18px;padding:20px 24px;box-shadow:0 8px 40px rgba(0,0,0,.6);animation:slideUp .3s ease}
@keyframes slideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.confirm-card-inner{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap}
.confirm-card-action{min-width:200px;text-align:center}
.confirm-movie{font-family:var(--font-display);font-size:1.1rem;color:#f0eaff;font-weight:700}
.confirm-details,.confirm-meta,.confirm-seat{font-size:.82rem;color:#8b7fb5}
.confirm-price{font-family:var(--font-display);font-size:1.4rem;color:var(--orange);font-weight:700;margin-bottom:10px}
#btnAddCart:disabled{opacity:.45;cursor:not-allowed}
@media(max-width:600px){.mode-tabs{grid-template-columns:1fr}.venue-tabs{overflow-x:auto;flex-wrap:nowrap}}
</style>

<script>
const ROWS=['A','B','C','D','E','F','G'],COLS=8;
let selectedSeat=null,currentMode='digital';

function getOccupied(screeningId,taken){
    const all=[];
    ROWS.forEach(r=>{for(let c=1;c<=COLS;c++)all.push(r+c);});
    let seed=screeningId*9301+49297;
    for(let i=all.length-1;i>0;i--){
        seed=(seed*1103515245+12345)&0x7fffffff;
        const j=seed%(i+1);[all[i],all[j]]=[all[j],all[i]];
    }
    return new Set(all.slice(0,Math.min(taken,all.length-5)));
}

function renderMap(screeningId,taken){
    const map=document.getElementById('seatMap');
    const occ=getOccupied(screeningId,taken);
    map.innerHTML='';selectedSeat=null;updateBtn();
    ROWS.forEach(row=>{
        const rowEl=document.createElement('div');rowEl.className='seat-row';
        const lbl=document.createElement('div');lbl.className='seat-row-label';lbl.textContent=row;rowEl.appendChild(lbl);
        for(let col=1;col<=COLS;col++){
            if(col===5){const g=document.createElement('div');g.className='seat-row-gap';rowEl.appendChild(g);}
            const id=row+col,s=document.createElement('div');
            s.className='seat '+(occ.has(id)?'taken':'free');s.dataset.sid=id;s.textContent=col;s.title='Assento '+id;
            if(!occ.has(id))s.addEventListener('click',()=>pickSeat(s,id));
            rowEl.appendChild(s);
        }
        map.appendChild(rowEl);
    });
}

function pickSeat(el,id){
    document.querySelectorAll('.seat.selected').forEach(s=>s.classList.replace('selected','free'));
    el.classList.replace('free','selected');selectedSeat=id;updateBtn();
    document.getElementById('confirmSeat').textContent=id;
}

function updateBtn(){
    const btn=document.getElementById('btnAddCart');
    const inp=document.getElementById('selectedSeat');
    if(inp) inp.value=selectedSeat||'';
    if(btn){
        if(currentMode==='digital') btn.disabled=false;
        else btn.disabled=!selectedSeat;
    }
    const seatRow=document.getElementById('confirmSeatRow');
    if(seatRow) seatRow.style.display=(currentMode==='cinema')?'block':'none';
}

// ── Auto-preenche confirmCard para modo digital ───────────────
function activateDigital(){
    const sid=document.getElementById('digitalScreeningId')?.value;
    const price=parseFloat(document.getElementById('digitalPrice')?.value||'2.90');
    if(!sid) return;
    document.getElementById('selectedScreeningId').value=sid;
    document.getElementById('selectedMode').value='digital';
    document.getElementById('selectedPrice').value=price;
    document.getElementById('confirmDate').textContent='Acesso Imediato';
    document.getElementById('confirmTime').textContent='Online';
    document.getElementById('confirmPrice').textContent='R$ '+price.toFixed(2).replace('.',',');
    document.getElementById('confirmMeta').innerHTML='<i class="bi bi-laptop me-1"></i>Modo Online';
    document.getElementById('confirmCard').style.display='block';
    updateBtn();
}

// ── Tabs de modo ─────────────────────────────────────────────
document.querySelectorAll('.mode-tab').forEach(tab=>{
    tab.addEventListener('click',function(){
        document.querySelectorAll('.mode-tab').forEach(t=>t.classList.remove('active'));
        this.classList.add('active');
        currentMode=this.dataset.mode;
        document.getElementById('panelDigital').style.display=currentMode==='digital'?'block':'none';
        document.getElementById('panelCinema').style.display=currentMode==='cinema'?'block':'none';
        document.getElementById('seatMapSection').style.display='none';
        document.querySelectorAll('.slot-btn').forEach(b=>b.classList.remove('selected'));
        document.getElementById('selectedMode').value=currentMode;
        selectedSeat=null;
        if(currentMode==='digital'){
            activateDigital();
        } else {
            document.getElementById('confirmCard').style.display='none';
        }
    });
});

// ── Filtro por shopping ───────────────────────────────────────
document.querySelectorAll('.venue-tab').forEach(tab=>{
    tab.addEventListener('click',function(){
        document.querySelectorAll('.venue-tab').forEach(t=>t.classList.remove('active'));
        this.classList.add('active');
        const filter=this.dataset.venueFilter;
        document.querySelectorAll('.slot-cinema').forEach(s=>{
            if(filter==='all'||s.dataset.venue===filter) s.style.display='';
            else s.style.display='none';
        });
    });
});

// ── Clique nos slots ─────────────────────────────────────────
document.querySelectorAll('.slot-btn:not(.disabled)').forEach(btn=>{
    btn.addEventListener('click',function(){
        document.querySelectorAll('.slot-btn').forEach(b=>b.classList.remove('selected'));
        this.classList.add('selected');

        const sid=parseInt(this.dataset.screening);
        const mode=this.dataset.mode;
        const price=parseFloat(this.dataset.price)||2.90;
        const priceStr='R$ '+price.toFixed(2).replace('.',',');

        document.getElementById('confirmDate').textContent=this.dataset.date;
        document.getElementById('confirmTime').textContent=this.dataset.time;
        document.getElementById('confirmPrice').textContent=priceStr;
        document.getElementById('selectedScreeningId').value=sid;
        document.getElementById('selectedMode').value=mode;
        document.getElementById('selectedPrice').value=price;
        document.getElementById('stepSessionInfo').textContent='— '+this.dataset.time;

        // Meta info
        const meta=document.getElementById('confirmMeta');
        if(mode==='cinema'&&this.dataset.venue){
            meta.innerHTML='<i class="bi bi-buildings me-1"></i>'+this.dataset.venue+' · '+this.dataset.room;
        } else {
            meta.innerHTML='<i class="bi bi-laptop me-1"></i>Modo Online';
        }

        document.getElementById('confirmCard').style.display='block';
        selectedSeat=null;

        if(mode==='cinema'){
            const taken=parseInt(this.dataset.taken||'0');
            renderMap(sid,taken);
            document.getElementById('seatMapSection').style.display='block';
            document.getElementById('seatStepNum').textContent='3';
            setTimeout(()=>document.getElementById('seatMapSection').scrollIntoView({behavior:'smooth',block:'start'}),100);
        } else {
            document.getElementById('seatMapSection').style.display='none';
            document.getElementById('selectedSeat').value='';
        }
        updateBtn();
    });
});

// Init mode
document.getElementById('selectedMode').value='digital';
activateDigital();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
