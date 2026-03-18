<?php
/**
 * CineWeeknd — Admin: Adicionar Filme via TMDB
 * Acesse: /admin/add-movie
 */
requireAdmin();
require_once APP_ROOT . '/src/Services/TMDBService.php';
$pageTitle = 'Adicionar Filme';
require __DIR__ . '/../../Views/layouts/header.php';
?>

<section class="py-5">
<div class="container" style="max-width:900px">

  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <h1 class="section-title mb-0">Adicionar Filme<span class="dot">.</span></h1>
    <a href="<?= APP_URL ?>/admin" class="btn btn-ghost-light">
      <i class="bi bi-arrow-left me-1"></i> Painel Admin
    </a>
  </div>

  <!-- Busca -->
  <div class="tmdb-search-box mb-4">
    <div class="tmdb-search-icon">🎬</div>
    <div class="tmdb-search-inner">
      <label class="label-gold mb-2">Buscar filme no TMDB</label>
      <div class="d-flex gap-2">
        <input type="text" id="tmdbQuery" class="form-control dark-input"
               placeholder="Ex: Inception, Duna, Parasita…"
               autocomplete="off">
        <button class="btn-cine px-4" id="btnSearch">
          <i class="bi bi-search me-1"></i>Buscar
        </button>
      </div>
    </div>
  </div>

  <!-- Resultados TMDB -->
  <div id="tmdbResults" class="tmdb-results mb-5" style="display:none"></div>

  <!-- Formulário de importação (aparece ao selecionar filme) -->
  <div id="importForm" class="import-form-card" style="display:none">
    <h3 class="label-gold mb-3">Configurar e Importar</h3>
    <div class="import-preview mb-4" id="importPreview"></div>

    <div class="row g-3">
      <input type="hidden" id="selectedTmdbId">
      <div class="col-md-4">
        <label class="card-label">Preço (R$)</label>
        <input type="number" id="importPrice" class="card-input" value="24.90" step="0.10" min="0">
      </div>
      <div class="col-md-4">
        <label class="card-label">Em destaque?</label>
        <select id="importFeatured" class="card-input">
          <option value="0">Não</option>
          <option value="1">Sim</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="card-label">Categoria especial</label>
        <select id="importCategory" class="card-input">
          <option value="">Nenhuma</option>
          <option value="is_oscar">🏆 Oscar</option>
          <option value="is_national">🇧🇷 Nacional</option>
          <option value="is_bestseller">🔥 Best-seller</option>
          <option value="is_besteirol">😂 Besteirol</option>
        </select>
      </div>
    </div>

    <button class="btn-cine w-100 py-3 mt-4 fw-bold" id="btnImport">
      <i class="bi bi-cloud-download me-2"></i>Importar Filme para o Banco
    </button>
  </div>

  <!-- Feedback -->
  <div id="importFeedback" style="display:none" class="mt-3"></div>

</div>
</section>

<style>
.dark-input {
    background:var(--dark-3)!important;border:1px solid var(--border)!important;
    color:#f0eaff!important;border-radius:10px;padding:10px 14px;
}
.dark-input::placeholder{color:#5e5580}
.dark-input:focus{border-color:var(--purple)!important;box-shadow:0 0 0 3px rgba(168,85,247,.15)!important;outline:none}

.tmdb-search-box {
    display:flex;gap:16px;align-items:flex-start;
    background:var(--dark-2);border:1px solid var(--border);
    border-radius:18px;padding:24px;
}
.tmdb-search-icon{font-size:2.5rem;flex-shrink:0;margin-top:24px}
.tmdb-search-inner{flex:1}

.tmdb-results{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.tmdb-card {
    background:var(--dark-2);border:2px solid var(--border);
    border-radius:14px;overflow:hidden;cursor:pointer;
    transition:all .2s;
}
.tmdb-card:hover{border-color:var(--purple);transform:translateY(-3px);box-shadow:0 8px 24px rgba(168,85,247,.2)}
.tmdb-card.selected{border-color:var(--purple);box-shadow:0 0 0 3px rgba(168,85,247,.25)}
.tmdb-card img{width:100%;aspect-ratio:2/3;object-fit:cover;display:block;background:var(--dark-4)}
.tmdb-card-info{padding:10px 12px}
.tmdb-card-title{font-weight:700;color:#f0eaff;font-size:.85rem;margin-bottom:3px;
                 white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tmdb-card-meta{font-size:.75rem;color:#8b7fb5}
.tmdb-card-rating{color:#ffe94d;font-weight:700}
.tmdb-no-poster{aspect-ratio:2/3;background:var(--dark-4);display:flex;align-items:center;
                justify-content:center;font-size:3rem}

.import-form-card{
    background:var(--dark-2);border:1px solid var(--border);
    border-radius:18px;padding:28px;
}
.import-preview{display:flex;gap:16px;align-items:flex-start}
.import-preview img{width:80px;border-radius:10px;flex-shrink:0}
.import-preview-info h4{font-family:var(--font-display);font-size:1.2rem;color:#f0eaff;margin:0 0 4px}
.import-preview-info p{font-size:.82rem;color:#8b7fb5;margin:0;
                       display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}

.card-label{font-size:.75rem;color:#8b7fb5;font-weight:700;text-transform:uppercase;
            letter-spacing:.8px;display:block;margin-bottom:5px}
.card-input{
    background:var(--dark-3);border:1px solid var(--border);
    border-radius:10px;padding:10px 14px;color:#f0eaff;
    font-size:.92rem;font-weight:600;width:100%;
    transition:border-color .2s;outline:none;
}
.card-input:focus{border-color:var(--purple)}
</style>

<script>
let selectedMovie = null;

// ── Busca TMDB ────────────────────────────────────────────
async function searchTMDB() {
    const query = document.getElementById('tmdbQuery').value.trim();
    if (!query) return;

    const btn = document.getElementById('btnSearch');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled  = true;

    const res  = await fetch(`<?= APP_URL ?>/admin/tmdb-search?q=` + encodeURIComponent(query), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();

    btn.innerHTML = '<i class="bi bi-search me-1"></i>Buscar';
    btn.disabled  = false;

    const wrap = document.getElementById('tmdbResults');
    wrap.style.display = 'grid';
    wrap.innerHTML = '';

    if (!data.length) {
        wrap.innerHTML = '<p class="text-muted">Nenhum resultado encontrado.</p>';
        return;
    }

    data.forEach(m => {
        const card = document.createElement('div');
        card.className = 'tmdb-card';
        card.dataset.id = m.tmdb_id;
        card.innerHTML = `
            ${m.poster_url
                ? `<img src="${m.poster_url}" alt="${m.title}" loading="lazy">`
                : `<div class="tmdb-no-poster">🎬</div>`}
            <div class="tmdb-card-info">
                <div class="tmdb-card-title">${m.title}</div>
                <div class="tmdb-card-meta">
                    ${m.year} · <span class="tmdb-card-rating">⭐ ${m.rating.toFixed(1)}</span>
                </div>
            </div>`;
        card.addEventListener('click', () => selectMovie(m, card));
        wrap.appendChild(card);
    });
}

// ── Seleciona filme ────────────────────────────────────────
function selectMovie(m, card) {
    document.querySelectorAll('.tmdb-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    selectedMovie = m;

    document.getElementById('selectedTmdbId').value = m.tmdb_id;
    document.getElementById('importPreview').innerHTML = `
        <img src="${m.poster_url || ''}" alt="" onerror="this.style.display='none'">
        <div class="import-preview-info">
            <h4>${m.title} <small style="color:#8b7fb5;font-size:.75rem">(${m.year})</small></h4>
            <p>${m.overview || 'Sem sinopse disponível.'}</p>
            <div style="margin-top:8px;font-size:.8rem;color:#ffe94d">⭐ ${m.rating.toFixed(1)} · TMDB ID: ${m.tmdb_id}</div>
        </div>`;

    document.getElementById('importForm').style.display = 'block';
    document.getElementById('importForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Importa filme ──────────────────────────────────────────
document.getElementById('btnImport')?.addEventListener('click', async () => {
    const tmdbId   = document.getElementById('selectedTmdbId').value;
    const price    = document.getElementById('importPrice').value;
    const featured = document.getElementById('importFeatured').value;
    const category = document.getElementById('importCategory').value;
    const csrf     = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const btn      = document.getElementById('btnImport');

    if (!tmdbId) return;

    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Importando…';

    const res  = await fetch(`<?= APP_URL ?>/admin/tmdb-import`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ tmdb_id: tmdbId, price, featured, category, _csrf_token: csrf })
    });
    const data = await res.json();

    const fb = document.getElementById('importFeedback');
    fb.style.display = 'block';

    if (data.success) {
        fb.innerHTML = `<div style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);border-radius:12px;padding:16px 20px;color:#10b981;font-weight:700">
            ✅ ${data.message} <a href="<?= APP_URL ?>/movies/${data.slug}" target="_blank" style="color:#10b981;margin-left:8px">Ver filme →</a>
        </div>`;
        btn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Importado!';
        btn.style.background = 'linear-gradient(135deg,#059669,#10b981)';
    } else {
        fb.innerHTML = `<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:12px;padding:16px 20px;color:#ef4444;font-weight:700">
            ❌ ${data.message}
        </div>`;
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-download me-2"></i>Tentar novamente';
    }
});

// ── Enter na busca ─────────────────────────────────────────
document.getElementById('tmdbQuery')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') searchTMDB();
});
document.getElementById('btnSearch')?.addEventListener('click', searchTMDB);
</script>

<?php require __DIR__ . '/../../Views/layouts/footer.php'; ?>
