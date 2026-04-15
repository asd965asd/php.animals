<?php
// templates/admin/add_post.php
require __DIR__ . '/../layout/header.php';
?>

<h2 class="mb-3">Добавить объявление</h2>
<a href="index.php?page=home" class="btn btn-secondary mb-3">← На главную</a>

<?= $messageHtml ?>

<form method="POST" action="index.php?page=add_post" enctype="multipart/form-data" class="card p-4 shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
    <div class="mb-3">
        <label class="form-label">Вид животного</label>
        <input type="text" name="type_animal" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Порода</label>
        <input type="text" name="breed_animal" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Окрас</label>
        <input type="text" name="coloring_animal" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Район</label>
        <input type="text" name="district" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Фото (загрузка файла)</label>
        <input type="file" name="image_file" class="form-control" accept="image/*" required>
        <div class="form-text">Только файл: JPG/PNG/WEBP/GIF, до 5MB.</div>
    </div>
    <div class="mb-3">
        <label class="form-label">Описание</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
    <button type="submit" class="btn btn-success">Сохранить объявление</button>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>

