<?php
ini_set('max_execution_time', 300);
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";
echo "file_uploads: " . ini_get('file_uploads') . "<br>";

$description = "ajouter une vidéo dans le biosphère";
$title = "ajouter une vidéo";
$style = "film"
    ?>

<div>
    <h2>Ajouter une vidéo</h2>
    <form id="uploadForm" method="POST" enctype="multipart/form-data">
        <label for="video">Select Video:</label>
        <input type="file" name="video" id="video" accept="video/mp4,video/mov,video/avi" required><br><br>

        <label for="cover">Select Cover Image:</label>
        <input type="file" name="cover" id="cover" accept="image/jpeg,image/png" required><br><br>

        <button class="primary-btn" type="submit">Upload</button>
    </form>
</div>

<script>
    document.getElementById('uploadForm').addEventListener('submit', function (event) {
        event.preventDefault(); // Stop default form submission

        const videoFile = document.getElementById('video').files[0];
        const coverFile = document.getElementById('cover').files[0];
        const chunkSize = 1024 * 1024 * 5; // 5MB
        const totalChunks = Math.ceil(videoFile.size / chunkSize);

        const token = generateToken(); // Token generated client-side

        function uploadChunk(chunk, chunkNumber) {
            const formData = new FormData();
            formData.append('file', chunk);
            formData.append('chunkNumber', chunkNumber);
            formData.append('totalChunks', totalChunks);
            formData.append('type', 'video');
            formData.append('filename', videoFile.name);
            formData.append('token', token);
            formData.append('cover', coverFile);
            // Only append cover image on the last chunk
            

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/film/upload', true);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log(`Chunk ${chunkNumber} uploaded`);
                } else {
                    console.error(`Chunk ${chunkNumber} failed: `, xhr.responseText);
                }
            };

            xhr.send(formData);
        }

        for (let i = 0; i < totalChunks; i++) {
            const start = i * chunkSize;
            const end = Math.min(start + chunkSize, videoFile.size);
            const chunk = videoFile.slice(start, end);
            uploadChunk(chunk, i);
        }

        function generateToken() {
            const array = new Uint8Array(16);
            crypto.getRandomValues(array);
            return Array.from(array, b => b.toString(16).padStart(2, '0')).join('');
        }
    });
</script>