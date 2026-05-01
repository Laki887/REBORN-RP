<?php
require_once '../config.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();
if (!$news) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? 'Общее');
    $imageName = $news['image'];

    // Обработка загрузки нового изображения
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            // Удаляем старое изображение
            if ($imageName) @unlink('../uploads/' . $imageName);
            $imageName = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $imageName);
        } else {
            $error = 'Недопустимый формат файла.';
        }
    }

    // Удаление изображения по флажку
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        if ($imageName) @unlink('../uploads/' . $imageName);
        $imageName = '';
    }

    if (!$error && $title && $content) {
        $stmt = $pdo->prepare("UPDATE news SET title=?, content=?, image=?, category=? WHERE id=?");
        $stmt->execute([$title, $content, $imageName, $category, $id]);
        $success = 'Изменения сохранены!';
        // Обновляем данные для отображения
        $news['title'] = $title;
        $news['content'] = $content;
        $news['category'] = $category;
        $news['image'] = $imageName;
    } else {
        if (!$title || !$content) $error = 'Заполните заголовок и текст.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование новости</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-wrapper">
        <nav class="admin-nav">
            <div class="nav-brand">Админ-панель</div>
            <div class="nav-links">
                <a href="dashboard.php">← Назад к списку</a>
                <a href="logout.php" class="btn btn-outline btn-sm">Выход</a>
            </div>
        </nav>

        <main class="admin-content">
            <h1>Редактирование: <?= htmlspecialchars($news['title']) ?></h1>

            <?php if ($error): ?>
                <div class="alert error"><?= $error ?></div>
            <?php elseif ($success): ?>
                <div class="alert success"><?= $success ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="news-form">
                <div class="form-group">
                    <label>Заголовок</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($news['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Категория</label>
                    <input type="text" name="category" value="<?= htmlspecialchars($news['category']) ?>">
                </div>
                <div class="form-group">
                    <label>Текущее изображение</label>
                    <?php if ($news['image']): ?>
                        <div class="current-image">
                            <img src="../uploads/<?= $news['image'] ?>" style="max-width:200px; max-height:150px;"><br>
                            <label><input type="checkbox" name="remove_image" value="1"> Удалить изображение</label>
                        </div>
                    <?php else: ?>
                        <p>Нет изображения</p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Заменить изображение (необязательно)</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Текст новости</label>
                    <textarea name="content" rows="12" required><?= htmlspecialchars($news['content']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
            </form>
        </main>
    </div>
</body>
</html>