<?php $pageTitle = 'Gerenciar Filmes'; require __DIR__ . '/_header.php'; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="page-heading mb-0">Filmes <span>Gerenciamento</span></h1>
    <button class="btn-cine" data-bs-toggle="modal" data-bs-target="#addMovieModal">
        <i class="bi bi-plus me-1"></i>Adicionar Filme
    </button>
</div>

<div class="admin-table">
    <table class="table mb-0">
        <thead><tr><th>Título</th><th>Gênero</th><th>Ano</th><th>Preço</th><th>Nota</th><th>Destaque</th><th>Ações</th></tr></thead>
        <tbody>
            <?php foreach ($movies as $m): ?>
            <tr>
                <td class="fw-600"><?= e($m['title']) ?></td>
                <td><?= e($m['genre_name'] ?? '—') ?></td>
                <td><?= e($m['release_year'] ?? '—') ?></td>
                <td class="text-gold">R$ <?= number_format($m['price'], 2, ',', '.') ?></td>
                <td><?= $m['rating'] > 0 ? '⭐ '.$m['rating'] : '—' ?></td>
                <td><?= $m['is_featured'] ? '<span class="badge-confirmed">Sim</span>' : '<span class="text-muted">Não</span>' ?></td>
                <td>
                    <button class="btn-admin-sm btn-edit me-1" data-bs-toggle="modal"
                            data-bs-target="#editMovieModal"
                            data-movie="<?= htmlspecialchars(json_encode($m), ENT_QUOTES) ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form method="POST" action="<?= APP_URL ?>/admin/movies/<?= $m['id'] ?>/delete" class="d-inline"
                          onsubmit="return confirm('Excluir este filme?')">
                        <?= csrfField() ?>
                        <button type="submit" class="btn-admin-sm btn-delete"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Adicionar -->
<div class="modal fade" id="addMovieModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:var(--dark-3);border:1px solid var(--border)">
            <div class="modal-header" style="border-color:var(--border)">
                <h5 class="modal-title">Adicionar Novo Filme</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>/admin/movies" enctype="multipart/form-data">
                <?= csrfField() ?>
                <div class="modal-body"><?php include __DIR__ . '/movie-form-fields.php'; ?></div>
                <div class="modal-footer" style="border-color:var(--border)">
                    <button type="button" class="btn btn-ghost-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-cine">Criar Filme</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editMovieModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:var(--dark-3);border:1px solid var(--border)">
            <div class="modal-header" style="border-color:var(--border)">
                <h5 class="modal-title">Editar Filme</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editMovieForm" enctype="multipart/form-data">
                <?= csrfField() ?>
                <div class="modal-body"><?php $editing = true; include __DIR__ . '/movie-form-fields.php'; ?></div>
                <div class="modal-footer" style="border-color:var(--border)">
                    <button type="button" class="btn btn-ghost-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-cine">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('editMovieModal').addEventListener('show.bs.modal', function(e) {
    const movie = JSON.parse(e.relatedTarget.dataset.movie);
    const form = document.getElementById('editMovieForm');
    form.action = `<?= APP_URL ?>/admin/movies/${movie.id}/update`;
    ['title','synopsis','director','cast_list','release_year','duration_min','rating','trailer_url','price'].forEach(f => {
        const el = form.elements[f]; if (el) el.value = movie[f] || '';
    });
    const genreEl = form.elements['genre_id']; if (genreEl) genreEl.value = movie.genre_id || '';
    const featEl = form.elements['is_featured']; if (featEl) featEl.checked = movie.is_featured == 1;
});
</script>

<?php require __DIR__ . '/_footer.php'; ?>
