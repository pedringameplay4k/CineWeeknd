<?php
$poster = $movie['poster'] ?? '';
if (empty($poster)) {
    $posterSrc = DEFAULT_POSTER;
} elseif (str_starts_with($poster, 'http')) {
    $posterSrc = $poster;
} else {
    $posterSrc = UPLOAD_URL . e($poster);
}
$isFav = isLoggedIn() && isset($userFavorites) && in_array($movie['id'], array_column($userFavorites ?? [], 'id'));
?>
<div class="movie-card h-100">
    <div class="movie-card-poster">
        <?php if (!empty($movie['is_featured'])): ?>
        <span class="movie-badge">★ Destaque</span>
        <?php endif; ?>
        <?php if (!empty($movie['genre_name'])): ?>
        <span class="movie-badge genre" style="top:auto;bottom:.8rem;left:.8rem"><?= e($movie['genre_name']) ?></span>
        <?php endif; ?>
        <img data-src="<?= $posterSrc ?>" src="<?= $posterSrc ?>" alt="<?= e($movie['title']) ?>" loading="lazy">
        <div class="movie-card-overlay">
            <a href="<?= APP_URL ?>/movies/<?= e($movie['slug']) ?>" class="btn-overlay" title="Ver Detalhes">
                <i class="bi bi-eye"></i>
            </a>
            <?php if (isLoggedIn()): ?>
            <button class="btn-overlay fav-btn <?= $isFav ? 'active' : '' ?>"
                    data-movie-id="<?= $movie['id'] ?>" title="<?= $isFav ? 'Remover dos favoritos' : 'Adicionar aos favoritos' ?>">
                <i class="bi <?= $isFav ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
            </button>
            <?php endif; ?>
            <button class="btn-overlay" data-add-cart data-type="movie" data-id="<?= $movie['id'] ?>" title="Adicionar ao Carrinho">
                <i class="bi bi-bag-plus"></i>
            </button>
        </div>
    </div>
    <div class="movie-card-body">
        <div class="movie-card-title" title="<?= e($movie['title']) ?>"><?= e($movie['title']) ?></div>
        <div class="movie-card-meta">
            <span class="movie-year"><?= e($movie['release_year'] ?? '—') ?></span>
            <?php if ($movie['rating'] > 0): ?>
            <span class="movie-rating"><i class="bi bi-star-fill"></i> <?= e($movie['rating']) ?></span>
            <?php endif; ?>
        </div>
        <div class="movie-card-price">
            <div class="mcp-row">
                <span class="mcp-badge mcp-digital">
                    <i class="bi bi-laptop"></i>
                    R$ <?= number_format($movie['price_digital'] ?? 2.90, 2, ',', '.') ?>
                </span>
                <span class="mcp-badge mcp-cinema">
                    <i class="bi bi-buildings"></i>
                    R$ <?= number_format($movie['price_cinema'] ?? 5.90, 2, ',', '.') ?>
                </span>
            </div>
        </div>
    </div>
</div>
