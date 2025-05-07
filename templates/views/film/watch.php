<?php
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

$style = "film";
$description = "visionner une vidéo du biosphère";
$title = "regarder une vidéo";

$filePath = "";

if($data){
    $filePath = explode($_ENV['UPLOAD_DIR'], $data['file_path'])[1];
}
?>

<div class="video-container">
    <video controls>
        <source src="/stream/<?= urlencode($filePath) ?>" >
        Your browser does not support the video tag.
    </video>
</div>
<script>
    controller = new AbortController();
</script>