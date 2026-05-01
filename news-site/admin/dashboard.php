<?php
require_once '../config.php';

// Проверка авторизации
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Удаление новости (если передан параметр delete)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Удаляем картинку из папки
    $stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $news = $stmt->fetch();
    if ($news && $news['image']) {
        @unlink('../uploads/' . $news['image']);
    }
    $pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
    header('Location: dashboard.php?msg=deleted');
    exit;
}

// Получение всех новостей
$stmt = $pdo->query("SELECT id, title, created_at, category FROM news ORDER BY created_at DESC");
$newsList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель управления</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-wrapper">
        <nav class="admin-nav">
            <div class="nav-brand">Админ-панель</div>
            <div class="nav-links">
                <a href="dashboard.php" class="active">Новости</a>
                <a href="add-news.php" class="btn btn-sm">+ Добавить</a>
                <a href="logout.php" class="btn btn-outline btn-sm">Выход</a>
            </div>
        </nav>

        <main class="admin-content">
            <h1>Управление новостями</h1>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="alert success">Новость удалена</div>
            <?php endif; ?>

            <table class="news-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Заголовок</th>
                        <th>Категория</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($newsList as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['title']) ?></td>
                        <td><?= htmlspecialchars($item['category']) ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($item['created_at'])) ?></td>
                        <td class="actions">
                            <a href="edit-news.php?id=<?= $item['id'] ?>" class="btn-icon" title="Редактировать">✏️</a>
                            <a href="?delete=<?= $item['id'] ?>" class="btn-icon delete" onclick="return confirm('Удалить новость?')" title="Удалить">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($newsList)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px;">Новостей пока нет</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>