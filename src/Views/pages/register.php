<?php
$pageTitle = 'Criar Conta';
require __DIR__ . '/../layouts/header.php';
?>
<style>main{background:var(--black)}</style>
<div class="auth-wrapper py-5">
    <div class="container">
        <div class="auth-card fade-up" style="max-width:480px">
            <div class="auth-logo">
                <a href="<?= APP_URL ?>/" class="text-decoration-none">
                    <span class="brand-name">Cine<span class="brand-accent">Weeknd</span></span>
                </a>
            </div>
            <h1 class="auth-title">Criar Conta</h1>
            <p class="auth-subtitle">Junte-se a milhares de apaixonados por cinema!</p>
            <form method="POST" action="<?= APP_URL ?>/register" novalidate>
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label" for="name">Nome Completo</label>
                    <input type="text" id="name" name="name" class="form-control"
                           placeholder="Seu nome" required autocomplete="name"
                           value="<?= e($_POST['name'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Endereço de E-mail</label>
                    <input type="email" id="email" name="email" class="form-control"
                           placeholder="seu@email.com" required autocomplete="email"
                           value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Senha</label>
                    <input type="password" id="password" name="password" class="form-control"
                           placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
                    <div class="form-text text-muted">Pelo menos 8 caracteres.</div>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password_confirm">Confirmar Senha</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                           placeholder="Repita sua senha" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-auth">Criar Conta</button>
            </form>
            <div class="auth-switch mt-3">
                Já tem uma conta? <a href="<?= APP_URL ?>/login">Entrar</a>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
