<?php
// templates/admin/edit_post.php
require __DIR__ . '/../layout/header.php';
?>

<h2 class="mb-3">Редактировать объявление #<?= (int)$post['id'] ?></h2>
<a href="index.php?page=home" class="btn btn-secondary mb-3">← На главную</a>

<?= $messageHtml ?>

<form method="POST" action="index.php?page=edit_post&id=<?= (int)$post['id'] ?>" enctype="multipart/form-data" class="card p-4 shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">

    <div class="mb-3">
        <label class="form-label">Вид животного</label>
        <input type="text" name="type_animal" class="form-control" value="<?= h($post['type_animal']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Порода</label>
        <input type="text" name="breed_animal" class="form-control" value="<?= h($post['breed_animal']) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Окрас</label>
        <input type="text" name="coloring_animal" class="form-control" value="<?= h($post['coloring_animal']) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Район</label>
        <input type="text" name="district" class="form-control" value="<?= h($post['district']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Текущее фото</label>
        <div class="mb-2">
            <?php $preview = !empty($post['image_url']) ? $post['image_url'] : 'https://via.placeholder.com/300x200?text=Нет+фото'; ?>
            <img src="<?= h($preview) ?>" alt="preview" style="max-width:220px;max-height:160px;object-fit:cover;" class="rounded border">
        </div>
        <label class="form-label">Загрузить новое фото</label>
        <input type="file" name="image_file" class="form-control" accept="image/*">
        <div class="form-text">Если файл не выбран, останется текущее фото.</div>
    </div>
    <div class="mb-3">
        <label class="form-label">Описание</label>
        <textarea name="description" class="form-control" rows="3"><?= h($post['description']) ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
    <a href="index.php?page=home" class="btn btn-link">Отмена</a>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>

