<?php $pageTitle = 'Dashboard'; require __DIR__ . '/_header.php'; ?>

<h1 class="page-heading">Dashboard <span>Visão Geral</span></h1>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-widget">
            <div class="stat-widget-icon"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-widget-num"><?= number_format($stats['users']) ?></div>
                <div class="stat-widget-label">Total de Usuários</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-widget">
            <div class="stat-widget-icon"><i class="bi bi-film"></i></div>
            <div>
                <div class="stat-widget-num"><?= number_format($stats['movies']) ?></div>
                <div class="stat-widget-label">Filmes Ativos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-widget">
            <div class="stat-widget-icon"><i class="bi bi-bag-check"></i></div>
            <div>
                <div class="stat-widget-num"><?= number_format($stats['orders']) ?></div>
                <div class="stat-widget-label">Total de Pedidos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-widget">
            <div class="stat-widget-icon"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="stat-widget-num">R$<?= number_format($stats['revenue'], 0, ',', '.') ?></div>
                <div class="stat-widget-label">Receita</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="<?= APP_URL ?>/admin/add-movie" class="admin-quick-action">
            <span style="font-size:1.8rem">🎬</span>
            <div>
                <div style="font-weight:700;color:#f0eaff">Adicionar Filme via TMDB</div>
                <div style="font-size:.78rem;color:#8b7fb5">Busca, poster e dados automáticos</div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= APP_URL ?>/admin/movies" class="admin-quick-action">
            <span style="font-size:1.8rem">📋</span>
            <div>
                <div style="font-weight:700;color:#f0eaff">Gerenciar Filmes</div>
                <div style="font-size:.78rem;color:#8b7fb5">Editar, ativar ou remover</div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= APP_URL ?>/admin/combos" class="admin-quick-action">
            <span style="font-size:1.8rem">🍿</span>
            <div>
                <div style="font-weight:700;color:#f0eaff">Gerenciar Combos</div>
                <div style="font-size:.78rem;color:#8b7fb5">Preços e disponibilidade</div>
            </div>
        </a>
    </div>
</div>

<!-- Buscar Posters -->
<div class="admin-poster-fetch mb-4" id="posterFetchWrap">
    <?php
    $missingPosters = (int)(getDB()->query("SELECT COUNT(*) FROM movies WHERE poster IS NULL OR poster = '' OR poster = 'default.jpg'")->fetchColumn());
    ?>
    <?php if ($missingPosters > 0): ?>
    <div style="background:rgba(255,233,77,.06);border:1px solid rgba(255,233,77,.2);border-radius:14px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
        <div>
            <div style="font-weight:700;color:#ffe94d">🖼️ <?= $missingPosters ?> filme(s) sem pôster</div>
            <div style="font-size:.78rem;color:#8b7fb5;margin-top:2px">Busca automática via TMDB</div>
        </div>
        <button class="btn-cine" id="btnFetchPosters" style="padding:.5rem 1.2rem;font-size:.85rem">
            Buscar Pôsteres Agora
        </button>
    </div>
    <div id="posterFetchLog" style="margin-top:10px;font-size:.8rem;color:#8b7fb5"></div>
    <?php else: ?>
    <div style="background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.2);border-radius:14px;padding:12px 20px;font-size:.85rem;color:#10b981;font-weight:600">
        ✅ Todos os filmes têm pôster
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('btnFetchPosters')?.addEventListener('click', async function() {
    const btn = this;
    const log = document.getElementById('posterFetchLog');
    btn.disabled = true;

    async function fetchBatch() {
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Buscando…';
        const res  = await fetch('<?= APP_URL ?>/admin/fetch-posters?limit=5', {
            headers:{'X-Requested-With':'XMLHttpRequest'}
        });
        const data = await res.json();

        let html = '';
        if (data.updated.length) html += '✅ Encontrados: ' + data.updated.join(', ') + '<br>';
        if (data.failed.length)  html += '⚠️ Não encontrados: ' + data.failed.join(', ') + '<br>';
        log.innerHTML = html + `<span style="color:#ffe94d">${data.remaining} restantes</span>`;

        if (data.remaining > 0) {
            btn.innerHTML = 'Buscar Mais (' + data.remaining + ')';
            btn.disabled = false;
        } else {
            btn.innerHTML = '✅ Concluído!';
            btn.style.background = 'linear-gradient(135deg,#059669,#10b981)';
            log.innerHTML += '<br><span style="color:#10b981">Todos os pôsteres encontrados!</span>';
        }
    }

    fetchBatch();
});
</script>

<style>
.admin-quick-action {
    display:flex;align-items:center;gap:14px;
    background:var(--dark-2);border:1px solid var(--border);
    border-radius:14px;padding:16px 20px;
    text-decoration:none;transition:all .2s;
}
.admin-quick-action:hover {
    border-color:var(--purple);
    transform:translateY(-2px);
    box-shadow:0 6px 20px rgba(168,85,247,.15);
}
</style>

<div class="page-heading" style="font-size:1.2rem">Pedidos Recentes</div>
<div class="admin-table">
    <table class="table mb-0">
        <thead><tr><th>#</th><th>Cliente</th><th>Total</th><th>Status</th><th>Data</th></tr></thead>
        <tbody>
            <?php foreach ($recentOrders as $o): ?>
            <tr>
                <td>#<?= str_pad($o['id'], 6, '0', STR_PAD_LEFT) ?></td>
                <td><?= e($o['user_name']) ?></td>
                <td class="text-gold fw-bold">R$ <?= number_format($o['total'], 2, ',', '.') ?></td>
                <td><span class="badge-<?= e($o['status']) ?>"><?= match($o['status']) { 'confirmed' => 'Confirmado', 'pending' => 'Pendente', 'cancelled' => 'Cancelado', default => ucfirst($o['status']) } ?></span></td>
                <td><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
