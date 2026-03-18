<?php
$pageTitle = 'Meu Perfil';
require __DIR__ . '/../layouts/header.php';
$avatarSrc = !empty($user['avatar']) ? UPLOAD_URL . e($user['avatar']) : null;
?>
<section class="py-5">
    <div class="container">
        <div class="profile-header fade-up">
            <?php if ($avatarSrc): ?>
            <img src="<?= $avatarSrc ?>" alt="Avatar" class="profile-avatar">
            <?php else: ?>
            <div class="profile-avatar-placeholder"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
            <?php endif; ?>
            <div>
                <div class="profile-name"><?= e($user['name']) ?></div>
                <div class="profile-email"><?= e($user['email']) ?></div>
                <?php if ($user['is_admin']): ?>
                <span class="profile-badge mt-1 d-inline-block">Admin</span>
                <?php endif; ?>
            </div>
            <div class="ms-auto d-none d-md-block text-end">
                <div class="label-gold">Membro desde</div>
                <div class="small text-secondary"><?= date('F Y', strtotime($user['created_at'])) ?></div>
            </div>
        </div>

        <ul class="nav nav-tabs-cine" id="profileTabs">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-profile">Perfil</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-security">Segurança</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-favorites">Favoritos (<?= count($favorites) ?>)</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-orders">Pedidos Recentes</a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-profile">
                <div class="profile-tab">
                    <h5 class="mb-4">Editar Perfil</h5>
                    <form method="POST" action="<?= APP_URL ?>/profile/update" enctype="multipart/form-data">
                        <?= csrfField() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome Completo</label>
                                <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">E-mail</label>
                                <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                                <div class="form-text text-muted">O e-mail não pode ser alterado.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Foto de Perfil</label>
                                <input type="file" name="avatar" class="form-control" accept="image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                        <button type="submit" class="btn-cine mt-3">Salvar Alterações</button>
                    </form>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-security">
                <div class="profile-tab">
                    <h5 class="mb-4">Alterar Senha</h5>
                    <form method="POST" action="<?= APP_URL ?>/profile/change-password" style="max-width:480px">
                        <?= csrfField() ?>
                        <div class="mb-3">
                            <label class="form-label">Senha Atual</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nova Senha</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Nova Senha</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn-cine">Atualizar Senha</button>
                    </form>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-favorites">
                <div class="profile-tab">
                    <h5 class="mb-4">Filmes Favoritos</h5>
                    <?php if (empty($favorites)): ?>
                    <div class="empty-state py-3">
                        <div class="empty-state-icon">❤️</div>
                        <div class="empty-state-title">Nenhum favorito ainda</div>
                        <p class="empty-state-desc">Curta um filme para salvá-lo aqui.</p>
                    </div>
                    <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($favorites as $movie): ?>
                        <div class="col-6 col-md-3 col-lg-2">
                            <?php include __DIR__ . '/../components/movie-card.php'; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-orders">
                <div class="profile-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Pedidos Recentes</h5>
                        <a href="<?= APP_URL ?>/orders" class="section-link">Ver todos <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <?php if (empty($orders)): ?>
                    <div class="empty-state py-3">
                        <div class="empty-state-icon">🛍️</div>
                        <div class="empty-state-title">Nenhum pedido ainda</div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <div class="order-id">Pedido #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></div>
                                <div class="small text-muted"><?= date('d/m/Y', strtotime($order['created_at'])) ?></div>
                            </div>
                            <div class="order-total">R$ <?= number_format($order['total'], 2, ',', '.') ?></div>
                            <a href="<?= APP_URL ?>/orders/<?= $order['id'] ?>" class="btn btn-ghost-light btn-sm">Detalhes</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
