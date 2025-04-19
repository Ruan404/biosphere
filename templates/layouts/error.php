<!DOCTYPE html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'error occured' ?></title>
    <meta name="description" content='something went wrong'>
    <style>
        body,
        html {
            height: 100%;
        }
        *{
            margin: 0;
        }
        body {
            background: black;
            display: flex;
            color: white;
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif
        }

        main{
            width: 100%;
            margin: min(40px,  5%);
            margin-bottom: 0;
            height: fit-content;
        }
    </style>
</head>

<body>
    <main>
        <?= $content ?>
    </main>
</body>

</html>