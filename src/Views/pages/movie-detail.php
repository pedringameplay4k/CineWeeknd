<?php
$pageTitle = $movie['title'];
require __DIR__ . '/../layouts/header.php';
$poster = $movie['poster'] ?? '';
if (empty($poster)) $posterSrc = DEFAULT_POSTER;
elseif (str_starts_with($poster, 'http')) $posterSrc = $poster;
else $posterSrc = UPLOAD_URL . e($poster);
?>

<section class="movie-detail-hero">
    <div class="movie-detail-bg" style="background-image:url('<?= $posterSrc ?>')"></div>
    <div class="container py-5 movie-detail-content">
        <div class="row g-5 align-items-start">
            <div class="col-md-3 col-sm-5 text-center text-md-start">
                <img src="<?= $posterSrc ?>" alt="<?= e($movie['title']) ?>" class="movie-poster-large fade-up">
            </div>
            <div class="col-md-9">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb" style="--bs-breadcrumb-divider-color:var(--text-muted)">
                        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/movies" class="text-muted">Filmes</a></li>
                        <li class="breadcrumb-item active text-muted"><?= e($movie['title']) ?></li>
                    </ol>
                </nav>
                <h1 class="movie-detail-title fade-up"><?= e($movie['title']) ?></h1>
                <div class="movie-tags fade-up fade-up-delay-1">
                    <?php if (!empty($movie['genre_name'])): ?>
                    <span class="movie-tag genre"><?= e($movie['genre_name']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($movie['release_year'])): ?>
                    <span class="movie-tag"><?= e($movie['release_year']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($movie['duration_min'])): ?>
                    <span class="movie-tag"><i class="bi bi-clock me-1"></i><?= $movie['duration_min'] ?> min</span>
                    <?php endif; ?>
                    <?php if ($movie['rating'] > 0): ?>
                    <span class="movie-tag"><i class="bi bi-star-fill text-gold me-1"></i><?= $movie['rating'] ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($movie['synopsis'])): ?>
                <p class="movie-synopsis mt-3 fade-up fade-up-delay-2"><?= nl2br(e($movie['synopsis'])) ?></p>
                <?php endif; ?>
                <div class="row g-3 mt-1 fade-up fade-up-delay-2">
                    <?php if (!empty($movie['director'])): ?>
                    <div class="col-auto">
                        <div class="label-gold">Diretor</div>
                        <div class="text-primary small"><?= e($movie['director']) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($movie['cast_list'])): ?>
                    <div class="col">
                        <div class="label-gold">Elenco</div>
                        <div class="text-secondary small"><?= e($movie['cast_list']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="movie-price-box mt-4 fade-up fade-up-delay-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <div class="label-gold mb-2">Preço do Ingresso</div>
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <div class="detail-price-badge digital">
                                    <i class="bi bi-laptop me-1"></i>Online
                                    <strong>R$ <?= number_format($movie['price_digital'] ?? 2.90, 2, ',', '.') ?></strong>
                                </div>
                                <div class="detail-price-badge cinema">
                                    <i class="bi bi-buildings me-1"></i>Presencial
                                    <strong>R$ <?= number_format($movie['price_cinema'] ?? 5.90, 2, ',', '.') ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php if (!empty($movie['trailer_url'])): ?>
                            <a href="<?= e($movie['trailer_url']) ?>" target="_blank" rel="noopener"
                               class="btn-hero-secondary d-inline-flex align-items-center gap-2">
                                <i class="bi bi-play-circle"></i> Trailer
                            </a>
                            <?php endif; ?>
                            <a href="<?= APP_URL ?>/movies/<?= e($movie['slug']) ?>/sessions" class="btn-hero-primary" style="text-decoration:none">
                                <i class="bi bi-calendar-event"></i> Ver Sessões
                            </a>
                            <button class="btn-hero-secondary" data-add-cart data-type="movie" data-id="<?= $movie['id'] ?>">
                                <i class="bi bi-bag-plus"></i> Adicionar sem Sessão
                            </button>
                            <?php if (isLoggedIn()): ?>
                            <button class="btn-overlay fav-btn <?= $isFavorite ? 'active' : '' ?>"
                                    data-movie-id="<?= $movie['id'] ?>"
                                    style="width:46px;height:46px;font-size:1.1rem"
                                    title="<?= $isFavorite ? 'Remover dos favoritos' : 'Adicionar aos favoritos' ?>">
                                <i class="bi <?= $isFavorite ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <h3 class="section-title mb-4">Avaliações<span class="dot">.</span></h3>
                <?php if (empty($reviews)): ?>
                <div class="empty-state py-4">
                    <div class="empty-state-icon">💬</div>
                    <div class="empty-state-title">Sem avaliações ainda</div>
                    <p class="empty-state-desc">Seja o primeiro a compartilhar sua opinião!</p>
                </div>
                <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                <div class="order-card mb-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="profile-avatar-placeholder" style="width:36px;height:36px;font-size:1rem">
                            <?= strtoupper(substr($review['user_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="fw-600 small"><?= e($review['user_name']) ?></div>
                            <div class="movie-year"><?= date('d/m/Y', strtotime($review['created_at'])) ?></div>
                        </div>
                        <div class="ms-auto">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                            <i class="bi <?= $s <= $review['rating'] ? 'bi-star-fill text-gold' : 'bi-star text-muted' ?>" style="font-size:.85rem"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php if (!empty($review['comment'])): ?>
                    <p class="text-muted small mb-0"><?= nl2br(e($review['comment'])) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (isLoggedIn()): ?>
            <div class="col-lg-5">
                <div class="cart-summary-card">
                    <h5 class="cart-summary-title">Escrever Avaliação</h5>
                    <form method="POST" action="<?= APP_URL ?>/reviews/submit">
                        <?= csrfField() ?>
                        <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Sua Nota</label>
                            <div class="star-rating star-rating-interactive">
                                <?php for ($s = 5; $s >= 1; $s--): ?>
                                <input type="radio" name="rating" id="star<?= $s ?>" value="<?= $s ?>">
                                <label for="star<?= $s ?>"><i class="bi bi-star-fill"></i></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comentário (opcional)</label>
                            <textarea name="comment" class="form-control" rows="3" placeholder="Compartilhe sua opinião…"></textarea>
                        </div>
                        <button type="submit" class="btn-cine w-100">Enviar Avaliação</button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="col-lg-5">
                <div class="cart-summary-card text-center">
                    <div class="empty-state-icon">🔐</div>
                    <h5 class="mb-2">Entre para Avaliar</h5>
                    <p class="text-muted small mb-3">Compartilhe sua opinião com outros cinéfilos.</p>
                    <a href="<?= APP_URL ?>/login" class="btn-cine d-inline-block">Entrar</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>




<?php require __DIR__ . '/../layouts/footer.php'; ?>
