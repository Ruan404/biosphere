<!DOCTYPE html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'biosphère' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/sign.css" />
</head>

<body>
    <main>
        <?= $content ?>
    </main>
</body>
<footer>
    <?php if (defined('DEBUG_TIME')): ?>

        temps d'affichage: <?= round(1000 * (microtime(true) - DEBUG_TIME)) ?> ms

    <?php endif ?>
</footer>
</html>