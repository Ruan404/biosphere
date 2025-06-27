<?php
$style = "upload";
?>
<main>
    <div class="container">
        <div>
            <h1>Ajouter une nouvelle vidéo</h1>
        </div>
        <div class="upload-ctn">
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="tab" id="video-upload">
                    <div class="form-field-ctn">
                        <div class="form-upload-field">
                            <label for="video" aria-label="insérer un fichier vidéo">ajouter un fichier vidéo</label>
                            <input type="file" name="video" id="video" accept="video/mp4,video/mov,video/avi" />
                        </div>
                        <div class="preview-video-ctn">
                            <div class="preview-file">
                                <video id="video-preview" width="320" height="180"></video>
                            </div>
                            <div class="preview-file-info">
                                <p class="preview-file-name" id="video-preview-file-name"></p>
                                <p class="preview-file-size" id="video-preview-file-size"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab" id="image-upload">
                    <div class="form-field-ctn">
                        <div class="form-upload-field">
                            <label for="cover" aria-label="insérer un fichier image">ajouter un fichier image</label>
                            <input type="file" name="cover" id="cover" accept="image/jpeg,image/png" />
                        </div>
                        <div class="preview-image-ctn">
                            <div class="preview-file">
                                <image id="image-preview" src="#" alt="votre image" />
                            </div>
                            <div class="preview-file-info">
                                <p class="preview-file-name" id="image-preview-file-name"></p>
                                <p class="preview-file-size" id="image-preview-file-size"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab" id="details">
                    <div class="form-field-ctn">
                        <div class="form-field">
                            <label for="title">Titre</label>
                            <input type="text" value="titre" placeholder="entrez un titre" name="title" id="title"
                                required />
                        </div>
                        <div class="form-field">
                            <label for="description">Description</label>
                            <textarea id="description" placeholder="entrez une desciption" name="description"
                                required>description</textarea>
                        </div>
                    </div>
                </div>
                <button type="submit" id="submit-btn" class="primary-btn">envoyer</button>
                <div class="uploading">
                    <progress id="uploadProgress" value="0" max="100" style="width: 100%;"></progress>
                    <div id="upload-status">début du téléchargement...</div>
                </div>

            </form>
            <div class="form-progress">
                <span class="step"></span>
                <div class="nav-btn-ctn">
                    <button aria-label="prev button" id="prev-btn" class="shadow-btn">prev</button>
                    <button aria-label="next button" id="next-btn" class="shadow-btn">next</button>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="/assets/js/upload.js"></script>