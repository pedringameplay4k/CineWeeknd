<?php $pageTitle = 'Gerenciar Cupons'; require __DIR__ . '/_header.php'; ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="page-heading mb-0">Cupons <span>Gerenciamento</span></h1>
    <button class="btn-cine" data-bs-toggle="modal" data-bs-target="#addCouponModal">
        <i class="bi bi-plus me-1"></i>Criar Cupom
    </button>
</div>
<div class="admin-table">
    <table class="table mb-0">
        <thead><tr><th>Código</th><th>Tipo</th><th>Valor</th><th>Pedido Mínimo</th><th>Usos</th><th>Validade</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach ($coupons as $c): ?>
            <tr>
                <td><code class="text-gold"><?= e($c['code']) ?></code></td>
                <td><?= $c['discount_type'] === 'percent' ? 'Percentual' : 'Fixo' ?></td>
                <td><?= $c['discount_type']==='percent' ? $c['discount_value'].'%' : 'R$ '.number_format($c['discount_value'],2,',','.') ?></td>
                <td>R$ <?= number_format($c['min_order_value'],2,',','.') ?></td>
                <td><?= $c['used_count'] ?><?= $c['max_uses'] ? '/'.$c['max_uses'] : '' ?></td>
                <td><?= $c['expires_at'] ? date('d/m/Y', strtotime($c['expires_at'])) : '—' ?></td>
                <td><?= $c['is_active'] ? '<span class="badge-confirmed">Ativo</span>' : '<span class="badge-cancelled">Inativo</span>' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:var(--dark-3);border:1px solid var(--border)">
            <div class="modal-header" style="border-color:var(--border)">
                <h5 class="modal-title">Criar Cupom</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>/admin/coupons">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Código *</label>
                            <input type="text" name="code" class="form-control text-uppercase" required placeholder="VERAO20">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tipo *</label>
                            <select name="discount_type" class="form-select">
                                <option value="percent">Percentual (%)</option>
                                <option value="fixed">Fixo (R$)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Valor do Desconto *</label>
                            <input type="number" name="discount_value" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Pedido Mínimo (R$)</label>
                            <input type="number" name="min_order_value" class="form-control" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Máximo de Usos</label>
                            <input type="number" name="max_uses" class="form-control" min="1" placeholder="Ilimitado">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Validade</label>
                            <input type="datetime-local" name="expires_at" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-color:var(--border)">
                    <button type="button" class="btn btn-ghost-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-cine">Criar Cupom</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
