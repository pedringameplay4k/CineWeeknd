<?php /** @var array $genres, bool $editing */ ?>
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Título *</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Preço (R$) *</label>
        <input type="number" name="price" class="form-control" step="0.01" min="0" required>
    </div>
    <div class="col-12">
        <label class="form-label">Sinopse</label>
        <textarea name="synopsis" class="form-control" rows="3"></textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Diretor</label>
        <input type="text" name="director" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Gênero</label>
        <select name="genre_id" class="form-select">
            <option value="">— Nenhum —</option>
            <?php foreach ($genres as $g): ?>
            <option value="<?= $g['id'] ?>"><?= e($g['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Elenco (separado por vírgula)</label>
        <input type="text" name="cast_list" class="form-control" placeholder="Ator A, Ator B, …">
    </div>
    <div class="col-md-3">
        <label class="form-label">Ano</label>
        <input type="number" name="release_year" class="form-control" min="1888" max="2099">
    </div>
    <div class="col-md-3">
        <label class="form-label">Duração (min)</label>
        <input type="number" name="duration_min" class="form-control" min="1">
    </div>
    <div class="col-md-3">
        <label class="form-label">Nota (0–10)</label>
        <input type="number" name="rating" class="form-control" step="0.1" min="0" max="10">
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check mb-2">
            <input type="checkbox" name="is_featured" id="is_featured_<?= $editing??false?'edit':'add' ?>" class="form-check-input">
            <label class="form-check-label" for="is_featured_<?= $editing??false?'edit':'add' ?>">Destaque</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">URL do Trailer</label>
        <input type="url" name="trailer_url" class="form-control" placeholder="https://youtube.com/watch?v=…">
    </div>
    <div class="col-12">
        <label class="form-label">
            <i class="bi bi-play-circle me-1" style="color:#a855f7"></i>
            Link do Filme <span style="color:#8b7fb5;font-weight:400;font-size:.8rem">(liberado após pagamento — aceita Vimeo ou Google Drive)</span>
        </label>
        <input type="url" name="gdrive_url" class="form-control" placeholder="https://vimeo.com/… ou https://drive.google.com/file/d/…">
        <div class="form-text text-muted" style="font-size:.75rem">
            💡 Vimeo: cole o link direto do vídeo &nbsp;·&nbsp; Google Drive: compartilhe como "Qualquer pessoa com o link"
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Imagem do Poster</label>
        <input type="file" name="poster" class="form-control" accept="image/jpeg,image/png,image/webp">
    </div>
</div>
