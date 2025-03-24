<?php
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "file_uploads: " . ini_get('file_uploads') . "<br>";

?>

<form method="post" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Video Title" required>
    <input type="file" name="video" accept="video/*" required>
    <button type="submit">Upload</button>
</form>

