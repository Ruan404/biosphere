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
        <input type="text" id="title" name="title" value="Default Title" required><br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required>Default description here...</textarea><br><br>

        <progress id="uploadProgress" value="0" max="100" style="width: 100%;"></progress><br><br>
        <div id="uploadStatus" style="margin-bottom: 10px; background: #f4f4f4; padding: 16px"></div>
        <button class="primary-btn" type="submit">Upload</button>
    </form>
</div>
<script>
    document.getElementById('uploadForm').addEventListener('submit', fileUpload);


    async function fileUpload(ev) {
        ev.preventDefault();

        const videoFile = document.getElementById('video').files[0];
        const coverFile = document.getElementById('cover').files[0];
        const title = document.getElementById('title').value;
        const description = document.getElementById('description').value;
        const progressBar = document.getElementById('uploadProgress');
        const statusBox = document.getElementById('uploadStatus');

        const token = crypto.randomUUID();
        const chunkSize = 5 * 1024 * 1024; // 5MB
        const totalChunks = Math.ceil(videoFile.size / chunkSize);

        let start = 0;
        let step = 0;

        statusBox.innerHTML = ''; // Clear previous messages

        while (start < videoFile.size) {
            const chunk = videoFile.slice(start, start + chunkSize);
            try {
                const isLastChunk = (step === totalChunks - 1);
                const res = await uploadChunk(chunk, videoFile.name, token, step, totalChunks, title, description, coverFile, isLastChunk);

                if (res.message) {
                    statusBox.innerHTML = res.message;
                } else if (res.error) {
                    statusBox.innerHTML = `❌ ${res.error}`;
                    return;
                }
            } catch (err) {
                statusBox.innerHTML = `❌ Upload failed on chunk #${step}: ${err.message}`;
                return;
            }

            const percentComplete = Math.round(((step + 1) / totalChunks) * 100);
            progressBar.value = percentComplete;

            start += chunkSize;
            step += 1;
        }

        // Reset form and progress only after final chunk, assuming success
        document.getElementById('uploadForm').reset();
        progressBar.value = 0;
    }


    async function uploadChunk(chunk, filename, token, step, totalChunks, title, description, coverFile, isLastChunk, retries = 3) {
        const formData = new FormData();
        formData.append('file', chunk);
        formData.append('filename', filename);
        formData.append('token', token);
        formData.append('step', step);
        formData.append('totalChunks', totalChunks);

        if (isLastChunk) {
            formData.append('title', title);
            formData.append('description', description);
            formData.append('cover', coverFile);
        }

        try {
            const response = await fetch('/film/upload', {
                method: 'POST',
                body: formData,
            });

            const text = await response.text();
            const jsonStart = text.indexOf('{');
            const data = JSON.parse(text.slice(jsonStart));

            if (!response.ok) {
                throw new Error(data.message || `HTTP ${response.status}`);
            }

            return data;
        } catch (error) {
            if (retries > 0) {
                return uploadChunk(chunk, filename, token, step, totalChunks, title, description, coverFile, isLastChunk, retries - 1);
            } else {
                throw error;
            }
        }
    }

</script>