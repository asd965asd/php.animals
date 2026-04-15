<?php
require __DIR__ . '/../layout/header.php';
?>

<h2 class="mb-3">Подать заявку о пропаже животного</h2>
<a href="index.php?page=home" class="btn btn-secondary mb-3">← На главную</a>

<?= $requestMessageHtml ?>

<form method="POST" action="index.php?page=create_lost_request" enctype="multipart/form-data" class="card p-4 shadow-sm">
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
        <label class="form-label">Фото животного</label>
        <input type="file" name="image_file" class="form-control" accept="image/*" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Описание</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
    <button type="submit" class="btn btn-warning">Отправить заявку</button>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>

