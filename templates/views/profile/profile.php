<?php
$style = "profile";
use App\Helpers\Text;
// Les variables $existingAvatar et $predefinedAvatars sont fournies par le contrôleur

$predefinedAvatars = $data["predefinedAvatars"];
$existingAvatar = $data["existingAvatar"];
?>
<div class="profile-container">
    <h1 class="profile-title">Changer mon avatar</h1>

    <form method="POST" action="/profile" enctype="multipart/form-data" class="profile-form">
        <div class="form-group">
            <label for="avatar_upload">Choisir une photo :</label>
            <label for="avatar_upload" class="file-label">Choisir un fichier</label>
            <span class="selected-file-name" id="file-name">Aucun fichier n’a été sélectionné</span>
            <input type="file" id="avatar_upload" name="avatar_upload" accept="image/png, image/jpeg">
        </div>
        <div class="form-group">
            <p>Ou choisir un avatar prédéfini :</p>
            <div class="avatar-choice-list">
                <?php foreach ($predefinedAvatars as $img): ?>
                    <label class="avatar-choice">
                        <input type="radio" name="predefined_avatar" value="<?= $img ?>" <?php if (isset($_SESSION['avatar']) && $_SESSION['avatar'] === $img): ?>checked<?php endif; ?>>
                        <img src="/avatars/<?= htmlspecialchars($img) ?>" alt="avatar">
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit" class="primary-btn">Enregistrer</button>
    </form>

    <div class="current-avatar">
        <p>Avatar actuel :</p>
        <img src="<?= htmlspecialchars($existingAvatar) ?>" class="user-profile-img" alt="Avatar actuel">
    </div>

    <div style="margin-top: 24px; text-align:center;">
        <a href="/" class="primary-btn">Retour à l’accueil</a>
    </div>
</div>
<script>
    document.getElementById('avatar_upload').addEventListener('change', function () {
        let fileName = this.files[0] ? this.files[0].name : "Aucun fichier n’a été sélectionné";
        document.getElementById('file-name').textContent = fileName;
    });
</script>