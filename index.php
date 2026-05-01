<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новости — Закрытый Сектор</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="index.php" class="logo">Закрытый Сектор</a>
            <form action="index.php" method="get" class="search-form">
                <input type="text" name="q" placeholder="Поиск новостей..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button type="submit">🔍</button>
            </form>
        </div>
    </header>

    <main class="container">
        <div class="news-grid">
            <?php
            $search = $_GET['q'] ?? '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 9;
            $offset = ($page - 1) * $limit;

            $sql = "SELECT id, title, SUBSTRING(content, 1, 250) AS preview, image, created_at, category 
                    FROM news 
                    WHERE title LIKE :q OR content LIKE :q 
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':q', "%$search%");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $newsList = $stmt->fetchAll();

            if (empty($newsList)) {
                echo '<p class="no-news">Новостей не найдено.</p>';
            } else {
                foreach ($newsList as $item):
                    $imagePath = $item['image'] ? 'uploads/' . $item['image'] : 'https://via.placeholder.com/400x250/1e1e2f/ffffff?text=No+image';
            ?>
                <article class="news-card">
                    <a href="news.php?id=<?= $item['id'] ?>" class="news-card-image">
                        <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
                    </a>
                    <div class="news-card-content">
                        <span class="news-category"><?= htmlspecialchars($item['category']) ?></span>
                        <h2><a href="news.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a></h2>
                        <div class="news-meta"><?= date('d.m.Y', strtotime($item['created_at'])) ?></div>
                        <p><?= htmlspecialchars($item['preview']) ?>...</p>
                    </div>
                </article>
            <?php
                endforeach;
            }
            ?>
        </div>

        <?php
        // Пагинация
        $countSql = "SELECT COUNT(*) FROM news WHERE title LIKE :q OR content LIKE :q";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute([':q' => "%$search%"]);
        $total = $countStmt->fetchColumn();
        $totalPages = ceil($total / $limit);

        if ($totalPages > 1):
        ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): 
                $active = ($i === $page) ? 'active' : '';
                $query = $search ? "&q=" . urlencode($search) : "";
            ?>
                <a class="<?= $active ?>" href="?page=<?= $i . $query ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Закрытый Сектор. Все права защищены.</p>
        </div>
    </footer>
</body>
</html>