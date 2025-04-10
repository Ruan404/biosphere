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
    document.getElementById('uploadForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission
        
        var videoFile = document.getElementById('video').files[0];
        var coverFile = document.getElementById('cover').files[0];

        var chunkSize = 1024 * 1024 * 5; // 5MB per chunk
        var totalChunks = Math.ceil(videoFile.size / chunkSize);

        // Function to upload both video and cover image in one go
        function uploadFiles(chunk, chunkNumber) {
            var formData = new FormData();
            formData.append('file', chunk);
            formData.append('chunkNumber', chunkNumber);
            formData.append('totalChunks', totalChunks);
            formData.append('type', 'video'); // Indicating it's a video file
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/film/upload', true);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log('Chunk ' + chunkNumber + ' uploaded successfully');
                    if (chunkNumber === totalChunks - 1) {
                        // After all chunks are uploaded, upload the cover image
                        uploadCoverImage();
                    }
                } else {
                    console.error('Failed to upload chunk ' + chunkNumber);
                }
            };

            xhr.send(formData);
        }

        // Function to upload the cover image after video is uploaded
        function uploadCoverImage() {
            var formData = new FormData();
            formData.append('file', coverFile);
            formData.append('type', 'cover'); // Indicating it's a cover image

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/film/upload', true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log('Cover image uploaded successfully');
                } else {
                    console.error('Failed to upload cover image');
                }
            };

            xhr.send(formData);
        }

        // Start uploading the video file in chunks
        for (var i = 0; i < totalChunks; i++) {
            var start = i * chunkSize;
            var end = Math.min(start + chunkSize, videoFile.size);
            var chunk = videoFile.slice(start, end);
            uploadFiles(chunk, i);
        }
    });
</script>