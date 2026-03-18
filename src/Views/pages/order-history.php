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
<?php require __DIR__ . '/../layouts/footer.php'; ?>
