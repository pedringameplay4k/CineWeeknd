<?php
$pageTitle = 'Meus Pedidos';
require __DIR__ . '/../layouts/header.php';
?>
<section class="py-5">
    <div class="container">
        <h1 class="page-heading">Meus Pedidos<span class="dot">.</span></h1>
        <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🛍️</div>
            <div class="empty-state-title">Nenhum pedido ainda</div>
            <p class="empty-state-desc">Explore nossa seleção e comece sua experiência cinematográfica!</p>
            <a href="<?= APP_URL ?>/movies" class="btn-cine d-inline-block mt-3">Ver Filmes</a>
        </div>
        <?php else: ?>
        <?php foreach ($orders as $order): ?>
        <div class="order-card">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="order-id">Pedido #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></div>
                    <div class="small text-muted"><?= date('d/m/Y · H:i', strtotime($order['created_at'])) ?></div>
                </div>
                <div class="col-md-3 mt-2 mt-md-0">
                    <span class="badge-<?= e($order['status']) ?>">
                        <?= match($order['status']) { 'confirmed' => 'Confirmado', 'pending' => 'Pendente', 'cancelled' => 'Cancelado', default => ucfirst($order['status']) } ?>
                    </span>
                </div>
                <div class="col-md-3 mt-2 mt-md-0">
                    <div class="order-total">R$ <?= number_format($order['total'], 2, ',', '.') ?></div>
                    <?php if ($order['discount'] > 0): ?>
                    <div class="small" style="color:#10b981">-R$ <?= number_format($order['discount'], 2, ',', '.') ?> de desconto</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-3 mt-2 mt-md-0 text-md-end">
                    <a href="<?= APP_URL ?>/orders/<?= $order['id'] ?>" class="btn btn-ghost-light btn-sm">
                        Ver Detalhes <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <?php
            $driveItems = array_filter($orderItems[$order['id']] ?? [], fn($i) => $i['item_type'] === 'movie' && !empty($i['gdrive_url']));
            if ($driveItems && $order['status'] === 'confirmed'):
            ?>
            <div class="order-drive-links">
                <?php foreach ($driveItems as $item): ?>
                <a href="<?= e($item['gdrive_url']) ?>" target="_blank" rel="noopener" class="order-drive-btn">
                    <svg width="14" height="14" viewBox="0 0 87.3 78" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0"><path d="m6.6 66.85 3.85 6.65c.8 1.4 1.95 2.5 3.3 3.3l13.75-23.8h-27.5c0 1.55.4 3.1 1.2 4.5z" fill="#0066da"/><path d="m43.65 25-13.75-23.8c-1.35.8-2.5 1.9-3.3 3.3l-25.4 44a9.06 9.06 0 0 0 -1.2 4.5h27.5z" fill="#00ac47"/><path d="m73.55 76.8c1.35-.8 2.5-1.9 3.3-3.3l1.6-2.75 7.65-13.25c.8-1.4 1.2-2.95 1.2-4.5h-27.502l5.852 11.5z" fill="#ea4335"/><path d="m43.65 25 13.75-23.8c-1.35-.8-2.9-1.2-4.5-1.2h-18.5c-1.6 0-3.15.45-4.5 1.2z" fill="#00832d"/><path d="m59.8 53h-32.3l-13.75 23.8c1.35.8 2.9 1.2 4.5 1.2h50.8c1.6 0 3.15-.45 4.5-1.2z" fill="#2684fc"/><path d="m73.4 26.5-12.7-22c-.8-1.4-1.95-2.5-3.3-3.3l-13.75 23.8 16.15 27h27.45c0-1.55-.4-3.1-1.2-4.5z" fill="#ffba00"/></svg>
                    <span>▶ Assistir: <?= e($item['item_name']) ?></span>
                    <i class="bi bi-box-arrow-up-right" style="font-size:.7rem;margin-left:auto"></i>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if ($pages > 1): ?>
        <div class="cine-pagination">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<style>
.order-drive-links{
    margin-top:10px;padding-top:10px;
    border-top:1px solid rgba(255,255,255,.05);
    display:flex;flex-wrap:wrap;gap:8px;
}
.order-drive-btn{
    display:inline-flex;align-items:center;gap:7px;
    padding:5px 12px;border-radius:8px;font-size:.78rem;font-weight:700;
    background:rgba(52,168,83,.12);border:1px solid rgba(52,168,83,.3);
    color:#34a853;text-decoration:none;transition:all .2s;
}
.order-drive-btn:hover{background:rgba(52,168,83,.25);color:#34a853;transform:translateY(-1px)}
</style>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
