<?php
$pageTitle = 'Filmes';
$category  = $_GET['category'] ?? '';
$order     = $_GET['order'] ?? '';
require __DIR__ . '/../layouts/header.php';


?>

<?php if (empty($filters) || (empty($filters['search']) && empty($filters['genre_id']) && empty($category))): ?>

<!-- ═══════════════════ HERO COM THREE.JS ═══════════════════ -->
<section class="hero-section" id="hero-section">
    <!-- Canvas Three.js – chuva de frames -->
    <canvas id="hero-canvas"></canvas>
    <div class="hero-bg"></div>

    <div class="container py-5 hero-content" style="position:relative;z-index:2">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="hero-eyebrow fade-up">Cinema do jeito que a gente gosta!</div>
                <h1 class="hero-title fade-up fade-up-delay-1">
                    Seu final de semana <span class="accent">Começa aqui</span>
                </h1>
                <p class="hero-subtitle fade-up fade-up-delay-2">
                    Mais de 100 filmes. Clássicos, nacionais, Oscar, besteirols e muito mais.
                </p>
                <div class="hero-actions fade-up fade-up-delay-3">
                    <a href="#catalogo" class="btn-hero-primary scroll-to-catalog">
                        <i class="bi bi-play-circle-fill"></i> Ver Filmes
                    </a>
                    <a href="<?= APP_URL ?>/movies/aleatorio" class="btn-hero-secondary">
                        <i class="bi bi-shuffle"></i> 🎲 Aleatório
                    </a>
                </div>
                <div class="stats-bar fade-up">
                    <div class="stat-item"><div class="stat-num">100+</div><div class="stat-label">Filmes</div></div>
                    <div class="stat-item"><div class="stat-num">5</div><div class="stat-label">Combos</div></div>
                    <div class="stat-item"><div class="stat-num">4K</div><div class="stat-label">Qualidade</div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Three.js: Chuva de frames de filme -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function() {
    const canvas = document.getElementById('hero-canvas');
    const section = document.getElementById('hero-section');

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, 1, 0.1, 200);
    camera.position.z = 30;

    // Paleta neon do tema
    const COLORS = [0xa855f7, 0xff5f1f, 0x00ffff, 0xffe94d, 0xff2d78];

    // Cada "frame" é um retângulo fino com borda neon
    const FRAME_COUNT = 55;
    const frames = [];

    function makeFrame() {
        const w = 2.2 + Math.random() * 2.8;
        const h = w * 1.45;
        // Borda neon (EdgesGeometry)
        const geo = new THREE.PlaneGeometry(w, h);
        const edges = new THREE.EdgesGeometry(geo);
        const color = COLORS[Math.floor(Math.random() * COLORS.length)];
        const mat = new THREE.LineBasicMaterial({
            color,
            transparent: true,
            opacity: 0.18 + Math.random() * 0.32,
        });
        const mesh = new THREE.LineSegments(edges, mat);

        // Posição inicial espalhada
        mesh.position.set(
            (Math.random() - 0.5) * 70,
            20 + Math.random() * 40,   // começa acima da tela
            (Math.random() - 0.5) * 20 - 5
        );
        mesh.rotation.z = (Math.random() - 0.5) * 0.4;

        mesh.userData = {
            speed:  0.04 + Math.random() * 0.10,
            drift:  (Math.random() - 0.5) * 0.008,
            rotZ:   (Math.random() - 0.5) * 0.004,
            pulse:  Math.random() * Math.PI * 2,
            pulseS: 0.008 + Math.random() * 0.012,
            origOpacity: mat.opacity,
        };

        scene.add(mesh);
        frames.push(mesh);
        return mesh;
    }

    for (let i = 0; i < FRAME_COUNT; i++) {
        const f = makeFrame();
        // Distribuir verticalmente ao início
        f.position.y = (Math.random() - 0.5) * 60;
    }

    // Scanlines horizontais (linhas finas)
    const SCAN_COUNT = 18;
    const scanlines = [];
    for (let i = 0; i < SCAN_COUNT; i++) {
        const geo = new THREE.BufferGeometry().setFromPoints([
            new THREE.Vector3(-50, 0, 0),
            new THREE.Vector3(50, 0, 0)
        ]);
        const mat = new THREE.LineBasicMaterial({
            color: 0xa855f7,
            transparent: true,
            opacity: 0.03 + Math.random() * 0.06,
        });
        const line = new THREE.Line(geo, mat);
        line.position.y = (Math.random() - 0.5) * 50;
        line.position.z = -8;
        line.userData = { speed: 0.01 + Math.random() * 0.03 };
        scene.add(line);
        scanlines.push(line);
    }

    function resize() {
        const w = section.clientWidth;
        const h = section.clientHeight;
        renderer.setSize(w, h);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    }
    resize();
    window.addEventListener('resize', resize);

    // Mouse parallax suave
    let mx = 0, my = 0;
    section.addEventListener('mousemove', e => {
        mx = (e.clientX / section.clientWidth  - 0.5) * 2;
        my = (e.clientY / section.clientHeight - 0.5) * 2;
    });

    let t = 0;
    function animate() {
        requestAnimationFrame(animate);
        t += 0.016;

        // Camera parallax
        camera.position.x += (mx * 2 - camera.position.x) * 0.03;
        camera.position.y += (-my * 1.5 - camera.position.y) * 0.03;

        frames.forEach(f => {
            const d = f.userData;
            f.position.y -= d.speed;
            f.position.x += d.drift;
            f.rotation.z += d.rotZ;

            // Pulso de opacidade
            d.pulse += d.pulseS;
            f.material.opacity = d.origOpacity * (0.7 + 0.3 * Math.sin(d.pulse));

            // Reset quando sai pela base
            if (f.position.y < -35) {
                f.position.y = 30 + Math.random() * 10;
                f.position.x = (Math.random() - 0.5) * 70;
                f.rotation.z = (Math.random() - 0.5) * 0.4;
            }
        });

        // Scanlines caindo
        scanlines.forEach(s => {
            s.position.y -= s.userData.speed;
            if (s.position.y < -30) s.position.y = 30;
        });

        renderer.render(scene, camera);
    }
    animate();
})();
</script>

<!-- ═══════════════════ DESTAQUES ═══════════════════ -->
<?php if (!empty($featured)): ?>
<section class="py-5">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Destaques<span class="dot">.</span></h2>
                <p class="text-muted small mb-0">Ranqueados por nota + popularidade</p>
            </div>
            <a href="#catalogo" class="section-link scroll-to-catalog">
                Ver todos <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="row g-3">
            <?php foreach (array_slice($featured, 0, 10) as $i => $f): ?>
            <div class="col-6 col-md-4 col-lg-2dot4 fade-up" style="position:relative">
                <div class="rank-badge">#<?= $i + 1 ?></div>
                <?php $movie = $f; include __DIR__ . '/../components/movie-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php endif; ?>

<!-- ═══════════════════ CATÁLOGO COM AJAX ═══════════════════ -->
<section class="py-4" id="catalogo">
    <div class="container">

        <!-- Tabs de categoria (AJAX) -->
        <div class="category-tabs-wrap mb-4">
            <div class="category-tabs" id="cat-tabs">
                <?php
                $tabs = [
                    ''           => ['emoji'=>'','icon'=>'bi-grid-3x3-gap','label'=>'Todos'],
                    'national'   => ['emoji'=>'🇧🇷','icon'=>'','label'=>'Nacionais'],
                    'besteirol'  => ['emoji'=>'😂','icon'=>'','label'=>'Besteirol'],
                    'oscar'      => ['emoji'=>'🏆','icon'=>'','label'=>'Oscar'],
                    'bestseller' => ['emoji'=>'📚','icon'=>'','label'=>'Best-sellers'],
                ];
                foreach ($tabs as $val => $tab): ?>
                <button class="cat-tab <?= $category === $val ? 'active' : '' ?>"
                        data-cat="<?= $val ?>">
                    <?php if ($tab['icon']): ?><i class="bi <?= $tab['icon'] ?>"></i><?php endif; ?>
                    <?php if ($tab['emoji']): ?><?= $tab['emoji'] ?> <?php endif; ?>
                    <?= $tab['label'] ?>
                </button>
                <?php endforeach; ?>

                <a href="<?= APP_URL ?>/movies/aleatorio" class="cat-tab cat-tab-random ms-auto">
                    <i class="bi bi-shuffle"></i> 🎲 Aleatório
                </a>
            </div>
        </div>

        <!-- Header do catálogo -->
        <div class="section-header mb-3">
            <h2 class="section-title" id="catalog-title">
                Todos os Filmes<span class="dot">.</span>
            </h2>
            <span class="text-muted small" id="catalog-count"><?= $total ?> filmes</span>
        </div>

        <!-- Busca + ordenação -->
        <div class="filters-bar mb-4" id="filters-bar">
            <div class="filter-search-wrap">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="search-input" class="filter-search"
                       placeholder="Buscar filmes, diretores…"
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <select id="genre-select" class="filter-select">
                <option value="">Todos os Gêneros</option>
                <?php foreach ($genres as $genre): ?>
                <option value="<?= $genre['id'] ?>" <?= ($filters['genre_id'] ?? '') == $genre['id'] ? 'selected' : '' ?>>
                    <?= e($genre['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <select id="order-select" class="filter-select">
                <option value="">Ordenar por</option>
                <option value="rating"     <?= $order==='rating'     ?'selected':'' ?>>⭐ Melhor Nota</option>
                <option value="popularity" <?= $order==='popularity' ?'selected':'' ?>>🔥 Popularidade</option>
                <option value="year"       <?= $order==='year'       ?'selected':'' ?>>📅 Mais Recente</option>
                <option value="az"         <?= $order==='az'         ?'selected':'' ?>>🔤 A → Z</option>
            </select>
            <button class="btn-filter" id="search-btn"><i class="bi bi-search me-1"></i>Buscar</button>
            <button class="btn btn-ghost-light" id="clear-btn" style="display:none">Limpar</button>
        </div>

        <!-- Grid (atualizado via AJAX) -->
        <div id="catalog-container">
            <?php if (empty($movies)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🎬</div>
                <div class="empty-state-title">Nenhum filme encontrado</div>
                <p class="empty-state-desc">Tente outra categoria ou busca.</p>
            </div>
            <?php else: ?>
            <div class="row g-3" id="movies-grid">
                <?php foreach ($movies as $movie): ?>
                <div class="col-6 col-md-4 col-lg-3 col-xl-2 fade-in-card">
                    <?php include __DIR__ . '/../components/movie-card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if ($pages > 1): ?>
            <div class="cine-pagination mt-4">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a href="#" data-page="<?= $i ?>"
                   class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- Combo banner -->
<section class="py-5 mt-2">
    <div class="container">
        <div class="row align-items-center py-4 px-4"
             style="background:var(--dark-3);border:1px solid var(--border);border-radius:20px">
            <div class="col-md-8 mb-3 mb-md-0">
                <p class="label-gold mb-1">Não esqueça</p>
                <h3 class="section-title mb-1">Adicione um Combo<span class="dot">.</span></h3>
                <p class="text-muted mb-0">Pipoca, refrigerante, nachos — noite de cinema de verdade.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= APP_URL ?>/cart" class="btn-cine d-inline-flex align-items-center gap-2">
                    <i class="bi bi-cup-straw"></i> Ver Combos
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════ ESTILOS ═══════════════════ -->
<style>
/* Hero canvas */
#hero-canvas {
    position: absolute;
    inset: 0;
    width: 100% !important;
    height: 100% !important;
    z-index: 1;
    pointer-events: none;
}

/* Rank badges */
.col-lg-2dot4{width:20%;}
@media(max-width:992px){.col-lg-2dot4{width:33.333%;}}
@media(max-width:576px){.col-lg-2dot4{width:50%;}}
.rank-badge{
    position:absolute;top:8px;left:20px;z-index:10;
    background:linear-gradient(135deg,#f59e0b,#ef4444);
    color:#fff;font-weight:800;font-size:.72rem;
    padding:2px 8px;border-radius:99px;
    box-shadow:0 2px 8px rgba(0,0,0,.5);
}

/* Category tabs */
.category-tabs-wrap{
    border-bottom:1px solid var(--border);
    padding-bottom: 2px;
}
.category-tabs{
    display:flex;gap:6px;flex-wrap:wrap;
    padding: 4px 0 10px;
}
.cat-tab{
    padding:7px 16px;border-radius:10px;
    border:1px solid var(--border);
    background:var(--dark-3);color:var(--text-muted);
    font-size:.82rem;font-weight:600;
    cursor:pointer;transition:all .22s;
    white-space:nowrap;
    text-decoration:none;
    display:inline-flex;align-items:center;gap:5px;
}
.cat-tab:hover{
    border-color:var(--purple);
    color:var(--purple);
    background:rgba(168,85,247,.08);
}
.cat-tab.active{
    background:var(--purple);
    border-color:var(--purple);
    color:#fff;
    box-shadow:0 0 16px rgba(168,85,247,.4);
}
.cat-tab-random{border-color:var(--neon-cyan,#00e5ff);color:var(--neon-cyan,#00e5ff);}
.cat-tab-random:hover{background:rgba(0,229,255,.08);}

/* Skeleton loader */
.skeleton-card{
    background:var(--dark-3);
    border-radius:12px;
    aspect-ratio:2/3;
    animation:skeleton-pulse 1.4s ease-in-out infinite;
}
@keyframes skeleton-pulse{
    0%,100%{opacity:.4;}
    50%{opacity:.9;}
}

/* Fade-in para cards carregados via AJAX */
.fade-in-card{
    animation: fadeInUp .35s ease both;
}
@keyframes fadeInUp{
    from{opacity:0;transform:translateY(14px);}
    to{opacity:1;transform:translateY(0);}
}

/* Loading spinner no container */
.catalog-loading{
    display:flex;align-items:center;justify-content:center;
    min-height:280px;gap:12px;color:var(--text-muted);
    font-size:.9rem;
}
.catalog-loading::before{
    content:'';
    width:28px;height:28px;
    border:3px solid var(--dark-5);
    border-top-color:var(--purple);
    border-radius:50%;
    animation:spin .7s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg);}}
</style>

<!-- ═══════════════════ JAVASCRIPT ═══════════════════ -->
<script>
(function(){
    const APP  = window.APP_URL || '';
    const container = document.getElementById('catalog-container');
    const titleEl   = document.getElementById('catalog-title');
    const countEl   = document.getElementById('catalog-count');
    const clearBtn  = document.getElementById('clear-btn');
    const searchInput = document.getElementById('search-input');
    const genreSelect = document.getElementById('genre-select');
    const orderSelect = document.getElementById('order-select');

    let currentCat  = '<?= e($category) ?>';
    let currentPage = 1;
    let debounceTimer;

    const catLabels = {
        '':           'Todos os Filmes',
        'national':   '🇧🇷 Filmes Nacionais',
        'besteirol':  '😂 Besteirol',
        'oscar':      '🏆 Vencedores do Oscar',
        'bestseller': '📚 Best-sellers Adaptados',
    };

    // ── Fetch via AJAX ──────────────────────────────
    async function loadMovies(opts = {}) {
        const params = new URLSearchParams();
        params.set('ajax', '1');
        if (opts.category) params.set('category', opts.category);
        if (opts.search)   params.set('search',   opts.search);
        if (opts.genre)    params.set('genre',     opts.genre);
        if (opts.order)    params.set('order',     opts.order);
        if (opts.page > 1) params.set('page',      opts.page);

        // Skeleton
        container.innerHTML = '<div class="catalog-loading">Carregando filmes…</div>';

        try {
            const res  = await fetch(`${APP}/movies?${params}`);
            const data = await res.json();
            container.innerHTML = data.html;
            countEl.textContent = data.total + ' filmes';

            // Bind paginação injetada
            bindPagination();

            // Scroll suave até o catálogo (só se veio de clique na tab)
            if (opts.scrollTo) {
                document.getElementById('catalogo')
                    .scrollIntoView({behavior:'smooth', block:'start'});
            }
        } catch(e) {
            container.innerHTML = '<p class="text-muted text-center py-5">Erro ao carregar. Tente novamente.</p>';
        }
    }

    // ── Bind cliques de paginação injetada ─────────
    function bindPagination() {
        container.querySelectorAll('.page-btn[data-page]').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                currentPage = parseInt(btn.dataset.page);
                doLoad(false);
            });
        });
    }

    // ── Dispara load com estado atual ──────────────
    function doLoad(scrollTo = true) {
        const hasFilters = searchInput.value || genreSelect.value || currentCat;
        clearBtn.style.display = hasFilters ? 'inline-block' : 'none';

        // Atualiza título
        const base = catLabels[currentCat] || 'Todos os Filmes';
        titleEl.innerHTML = base + '<span class="dot">.</span>';

        loadMovies({
            category: currentCat,
            search:   searchInput.value.trim(),
            genre:    genreSelect.value,
            order:    orderSelect.value,
            page:     currentPage,
            scrollTo,
        });
    }

    // ── Tabs ────────────────────────────────────────
    document.querySelectorAll('#cat-tabs .cat-tab[data-cat]').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('#cat-tabs .cat-tab[data-cat]')
                    .forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            currentCat  = tab.dataset.cat;
            currentPage = 1;
            doLoad(true);
        });
    });

    // ── Busca com debounce ──────────────────────────
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => { currentPage=1; doLoad(false); }, 420);
    });
    document.getElementById('search-btn').addEventListener('click', () => {
        currentPage = 1; doLoad(false);
    });

    // ── Selects ─────────────────────────────────────
    genreSelect.addEventListener('change', () => { currentPage=1; doLoad(false); });
    orderSelect.addEventListener('change', () => { currentPage=1; doLoad(false); });

    // ── Limpar ──────────────────────────────────────
    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        genreSelect.value = '';
        orderSelect.value = '';
        currentCat  = '';
        currentPage = 1;
        document.querySelectorAll('#cat-tabs .cat-tab[data-cat]')
                .forEach(t => t.classList.toggle('active', t.dataset.cat === ''));
        doLoad(false);
    });

    // ── Scroll suave "Ver Filmes" / "Ver todos" ─────
    document.querySelectorAll('.scroll-to-catalog').forEach(el => {
        el.addEventListener('click', e => {
            const t = document.getElementById('catalogo');
            if (t) { e.preventDefault(); t.scrollIntoView({behavior:'smooth',block:'start'}); }
        });
    });

    // Auto-scroll se URL tem #catalogo
    if (window.location.hash === '#catalogo') {
        const el = document.getElementById('catalogo');
        if (el) setTimeout(() => el.scrollIntoView({behavior:'smooth',block:'start'}), 200);
    }

    // Bind paginação inicial (renderizada pelo PHP)
    bindPagination();
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
