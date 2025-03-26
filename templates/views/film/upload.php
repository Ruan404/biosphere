<?php
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "file_uploads: " . ini_get('file_uploads') . "<br>";

$description = "ajouter une vidéo dans le biosphère";
$title = "ajouter une vidéo";
$style = "film"
    ?>
<div>
    <h2>Ajouter une vidéo</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="video">Select Video:</label>
        <input type="file" name="video" id="video" accept="video/mp4,video/mov,video/avi" required><br><br>

        <label for="cover">Select Cover Image:</label>
        <input type="file" name="cover" id="cover" accept="image/jpeg,image/png" required><br><br>

        <button class="primary-btn" type="submit">Upload</button>
    </form>
</div>