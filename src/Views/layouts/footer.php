</main>

<!-- FOOTER -->
<footer class="cine-footer mt-auto">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4">
                <div class="footer-brand mb-3">
                    <span class="brand-icon">🎬</span>
                    <span class="brand-name">Cine<span class="brand-accent">Weeknd</span></span>
                </div>
                <p class="footer-tagline">Um cinema digital feito para relaxar, rir e curtir <br> filmes que não precisam ser levados tão a sério...</p>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="footer-heading">Descobrir</h6>
                <ul class="footer-links">
                    <li><a href="<?= APP_URL ?>/movies">Todos os filmes</a></li>
                    <li><a href="<?= APP_URL ?>/movies?genre=1">Ação</a></li>
                    <li><a href="<?= APP_URL ?>/movies?genre=2">Drama</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="footer-heading">Conta</h6>
                <ul class="footer-links">
                    <li><a href="<?= APP_URL ?>/login">Login</a></li>
                    <li><a href="<?= APP_URL ?>/register">Registrar</a></li>
                    <li><a href="<?= APP_URL ?>/orders">Minhas Compras</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="footer-heading">A Experiência</h6>
                <p class="small text-muted">Escolha um filme, pegue a pipoca e prepare-se para dar boas risadas.
O CineWeeknd foi feito com ❤️ para quem ama cinema e diversão.</p>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <p class="mb-0 small text-muted">© <?= date('Y') ?> <?= APP_NAME ?>. Todos os direitos reservados.</p>
            <p class="mb-0 small text-muted"></p>
        </div>
    </div>
</footer>

<!-- Main JS (Bootstrap already loaded in header with defer) -->
<script src="<?= APP_URL ?>/assets/js/main.js"></script>

</body>
</html>
