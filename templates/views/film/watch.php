<?php

use Dotenv\Dotenv;

$style = "film";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

$video = $data;

// Convert absolute path to relative URL
$playlistUrl = str_replace($_ENV['BASE_URL'] . $_ENV['HLS_DIR'], '/uploads/hls/', $video['playlist_path']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Video</title>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
</head>

<body>
    <div class="film-ctn">
        <div class="film-card">
            <h2><?= htmlspecialchars($video['title']) ?></h2>
            <video id="videoPlayer" controls></video>
        </div>
    </div>
    <script>
        var video = document.getElementById('videoPlayer');
        var videoSrc = "<?= $playlistUrl ?>";

        if (Hls.isSupported()) {
            var hls = new Hls();
            hls.loadSource(videoSrc);
            hls.attachMedia(video);
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            video.src = videoSrc;
        }
    </script>
</body>

</html>