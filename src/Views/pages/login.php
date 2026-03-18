<?php
$pageTitle = 'Entrar';
require __DIR__ . '/../layouts/header.php';
?>
<style>main{background:var(--black)}</style>
<div class="auth-wrapper py-5">
    <div class="container">
        <div class="auth-card fade-up">
            <div class="auth-logo">
                <a href="<?= APP_URL ?>/" class="text-decoration-none">
                    <span class="brand-name">Cine<span class="brand-accent">Weeknd</span></span>
                </a>
            </div>
            <h1 class="auth-title">Bem-vindo de volta!</h1>
            <p class="auth-subtitle">Entre na sua conta para continuar.</p>
            <form method="POST" action="<?= APP_URL ?>/login" novalidate>
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label" for="email">Endereço de E-mail</label>
                    <input type="email" id="email" name="email" class="form-control"
                           placeholder="seu@email.com" required autocomplete="email"
                           value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0" for="password">Senha</label>
                        <a href="#" class="small text-gold">Esqueceu a senha?</a>
                    </div>
                    <input type="password" id="password" name="password" class="form-control"
                           placeholder="••••••••" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-auth">Entrar</button>
            </form>
            <div class="auth-divider">ou</div>
            <div class="auth-switch">
                Não tem uma conta? <a href="<?= APP_URL ?>/register">Criar conta</a>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
