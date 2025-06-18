<?php
namespace App\User;

echo "Début du script\n";

$folder = __DIR__ . '/public/uploads/images/avatars/';
if (!file_exists($folder)) {
    mkdir($folder, 0777, true);
    echo "Dossier créé : $folder\n";
} else {
    echo "Dossier déjà existant : $folder\n";
}

$fontPath = __DIR__ . '/arial.ttf'; // Police TTF
if (!file_exists($fontPath)) {
    die("Fichier de police introuvable : $fontPath\n");
}

function randomColorNoWhite($min = 30, $max = 200) {
    // Génère une couleur dans des tons non blancs
    return [
        rand($min, $max),
        rand($min, $max),
        rand($min, $max)
    ];
}

foreach (range('A', 'Z') as $letter) {
    $size = 100;
    $img = imagecreatetruecolor($size, $size);

    // Fond transparent
    imagesavealpha($img, true);
    imagealphablending($img, false);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);

    // Couleur de fond aléatoire
    [$r, $g, $b] = randomColorNoWhite();
    $bgColor = imagecolorallocate($img, $r, $g, $b);

    // Cercle de fond
    $center = $size / 2;
    imagefilledellipse($img, $center, $center, $size, $size, $bgColor);

    // Couleur du texte : blanc
    $white = imagecolorallocate($img, 255, 255, 255);
    $fontSize = 50;

    // Calcul de la boîte englobante
    $bbox = imagettfbbox($fontSize, 0, $fontPath, $letter);
    $textWidth = $bbox[2] - $bbox[0];
    $textHeight = $bbox[1] - $bbox[7];

    // Calcul des positions centrées
    $x = ($size - $textWidth) / 2;
    $y = ($size + $textHeight) / 2;

    // Afficher la lettre centrée
    imagettftext($img, $fontSize, 0, $x, $y, $white, $fontPath, $letter);

    // Enregistrer l'image
    imagepng($img, $folder . $letter . '.png');
    imagedestroy($img);

    echo "Image générée : $letter.png avec couleur RGB($r, $g, $b)\n";
}

echo "✅ Toutes les images ont été générées dans $folder\n";
