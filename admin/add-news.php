<?php
require_once '../config.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? 'Общее');
    $imageName = '';

    // Загрузка изображения
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $error = 'Недопустимый формат файла.';
        } else {
            $imageName = uniqid() . '.' . $ext;
            $uploadPath = '../uploads/' . $imageName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $error = 'Ошибка загрузки изображения.';
            }
        }
    }

    if (!$error && $title && $content) {
        $stmt = $pdo->prepare("INSERT INTO news (title, content, image, category) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $imageName, $category]);
        $success = 'Новость успешно добавлена!';
        // Очищаем поля
        $_POST = [];
    } else {
        if (!$title || !$content) $error = 'Заполните заголовок и текст новости.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить новость</title>
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
            <h1>Добавить новость</h1>

            <?php if ($error): ?>
                <div class="alert error"><?= $error ?></div>
            <?php elseif ($success): ?>
                <div class="alert success"><?= $success ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="news-form">
                <div class="form-group">
                    <label>Заголовок</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Категория</label>
                    <input type="text" name="category" value="<?= htmlspecialchars($_POST['category'] ?? 'Общее') ?>">
                </div>
                <div class="form-group">
                    <label>Изображение (необязательно)</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Текст новости</label>
                    <textarea name="content" rows="12" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Опубликовать</button>
            </form>
        </main>
    </div>
</body>
</html>