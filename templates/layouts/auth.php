<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content='bienvenu dans le biosphere'>
    <title><?= htmlspecialchars($title ?? 'biosphère') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/sign.css" />
</head>

<body>
    <?= $content ?>
</body>
</html>