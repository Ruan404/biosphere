<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Videos</title>
</head>
<body>
    <h2>All Uploaded Videos</h2>
    <ul>
        <?php foreach ($data as $film): ?>
            <li>
                <a href="/films/watch/<?= $film['token'] ?>">
                    <?= htmlspecialchars($film['title']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
