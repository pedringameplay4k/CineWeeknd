<?php $pageTitle = 'Gerenciar Combos'; require __DIR__ . '/_header.php'; ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="page-heading mb-0">Combos <span>Gerenciamento</span></h1>
    <button class="btn-cine" data-bs-toggle="modal" data-bs-target="#addComboModal">
        <i class="bi bi-plus me-1"></i>Adicionar Combo
    </button>
</div>
<div class="admin-table">
    <table class="table mb-0">
        <thead><tr><th>Nome</th><th>Descrição</th><th>Preço</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach ($combos as $c): ?>
            <tr>
                <td class="fw-600"><?= e($c['name']) ?></td>
                <td class="text-muted"><?= e(substr($c['description']??'', 0, 60)) ?>…</td>
                <td class="text-gold">R$ <?= number_format($c['price'], 2, ',', '.') ?></td>
                <td><?= $c['is_active'] ? '<span class="badge-confirmed">Ativo</span>' : '<span class="badge-cancelled">Inativo</span>' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="modal fade" id="addComboModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:var(--dark-3);border:1px solid var(--border)">
            <div class="modal-header" style="border-color:var(--border)">
                <h5 class="modal-title">Adicionar Novo Combo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>/admin/combos">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Preço (R$) *</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="modal-footer" style="border-color:var(--border)">
                    <button type="button" class="btn btn-ghost-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-cine">Criar Combo</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
