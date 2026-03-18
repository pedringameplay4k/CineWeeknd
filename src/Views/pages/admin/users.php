<?php $pageTitle = 'Gerenciar Usuários'; require __DIR__ . '/_header.php'; ?>
<h1 class="page-heading">Usuários <span>Gerenciamento</span></h1>
<div class="admin-table">
    <table class="table mb-0">
        <thead><tr><th>#</th><th>Nome</th><th>E-mail</th><th>Função</th><th>Status</th><th>Cadastro</th></tr></thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td class="fw-600"><?= e($u['name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><?= $u['is_admin'] ? '<span class="badge-confirmed">Admin</span>' : '<span class="text-muted">Usuário</span>' ?></td>
                <td><?= $u['is_active'] ? '<span class="badge-confirmed">Ativo</span>' : '<span class="badge-cancelled">Inativo</span>' ?></td>
                <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
