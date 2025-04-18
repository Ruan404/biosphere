<?php
$safeFile = $data["token"];
$style = "film";
$description = "visionner une vidéo du biosphère";
$title = "regarder une vidéo";
?>

<div class="video-container">
    <video controls>
        <source src="/stream/<?= urlencode($safeFile) ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>
</div>