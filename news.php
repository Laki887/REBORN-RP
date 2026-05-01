<?php
require_once 'config.php';
$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    http_response_code(404);
    echo "<h1>Новость не найдена</h1>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($news['title']) ?> — Закрытый Сектор</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="index.php" class="logo">Закрытый Сектор</a>
            <a href="index.php" class="back-link">← Назад к новостям</a>
        </div>
    </header>

    <main class="container article-container">
        <article class="full-news">
            <?php if ($news['image']): ?>
                <img src="uploads/<?= $news['image'] ?>" alt="<?= htmlspecialchars($news['title']) ?>" class="full-news-image">
            <?php endif; ?>
            <span class="news-category"><?= htmlspecialchars($news['category']) ?></span>
            <h1><?= htmlspecialchars($news['title']) ?></h1>
            <div class="news-meta"><?= date('d.m.Y H:i', strtotime($news['created_at'])) ?></div>
            <div class="news-content">
                <?= nl2br(htmlspecialchars($news['content'])) ?>
            </div>
        </article>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Закрытый Сектор</p>
        </div>
    </footer>
</body>
</html>