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
    <form id="uploadForm" enctype="multipart/form-data">
        <label for="video">Select Video:</label>
        <input type="file" name="video" id="video" accept="video/mp4,video/mov,video/avi" required><br><br>

        <label for="cover">Select Cover Image:</label>
        <input type="file" name="cover" id="cover" accept="image/jpeg,image/png" required><br><br>

        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required><br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea><br><br>

        <progress id="uploadProgress" value="0" max="100" style="width: 100%;"></progress><br><br>

        <button class="primary-btn" type="submit">Upload</button>
    </form>
</div>

<script>
    document.getElementById('uploadForm').addEventListener('submit', function (event) {
        event.preventDefault();

         
        const videoFile = document.getElementById('video').files[0];
        const coverFile = document.getElementById('cover').files[0];
        console.log(coverFile);
        const title = document.getElementById('title').value;
        const description = document.getElementById('description').value;
        const progressBar = document.getElementById('uploadProgress');

        if (!videoFile || !coverFile || !title || !description) {
            alert('Please fill in all fields.');
            return;
        }

        const token = crypto.randomUUID(); // or custom generator
        const chunkSize = 5 * 1024 * 1024; // 5MB
        const totalChunks = Math.ceil(videoFile.size / chunkSize);

        function uploadChunk(chunk, chunkNumber) {
            const formData = new FormData();
            formData.append('file', chunk);
            formData.append('chunkNumber', chunkNumber);
            formData.append('totalChunks', totalChunks);
            formData.append('filename', videoFile.name);
            formData.append('token', token);

            // Include metadata and cover only on the last chunk
            if (chunkNumber === totalChunks - 1) {
                formData.append('title', title);
                formData.append('description', description);
                formData.append('cover', coverFile);
            }

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/film/upload', true);

            xhr.upload.onprogress = function (e) {
                if (e.lengthComputable) {
                    const totalUploaded = chunkNumber * chunkSize + e.loaded;
                    const percentComplete = Math.min((totalUploaded / videoFile.size) * 100, 100);
                    progressBar.value = percentComplete;
                }
            };

            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log(`Chunk ${chunkNumber} uploaded`);
                    if (chunkNumber + 1 < totalChunks) {
                        sendNextChunk(chunkNumber + 1);
                    } else {
                        alert('Upload complete!');
                    }
                } else {
                    alert(`Upload failed on chunk ${chunkNumber}`);
                }
            };

            xhr.onerror = function () {
                alert('An error occurred during upload.');
            };

            xhr.send(formData);
        }

        function sendNextChunk(chunkNumber) {
            const start = chunkNumber * chunkSize;
            const end = Math.min(start + chunkSize, videoFile.size);
            const chunk = videoFile.slice(start, end);
            uploadChunk(chunk, chunkNumber);
        }

        sendNextChunk(0); // Start upload
    });
</script>