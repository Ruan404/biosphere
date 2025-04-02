<?php

use Dotenv\Dotenv;

$style = "film";
$description = "regarder une vidéo";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

$video = $data;

// Convert absolute path to relative URL
$playlistUrl = str_replace($_ENV['HLS_DIR'], '/uploads/hls/', $video['playlist_path']);

?>

<div class="film-ctn">
    <div class="film-card">
        <h2><?= htmlspecialchars($video['title']) ?></h2>
        <video id="videoPlayer" controls></video>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

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