<?php
$style = "profile";

$predefinedAvatars = $data["predefinedAvatars"];
$existingAvatar = $data["existingAvatar"];
?>
<main>
    <div class="profile-container">
        <h1 class="profile-title">Changer mon avatar</h1>
        <div class="current-avatar">
            <img id="current-avatar" src="<?= htmlspecialchars($existingAvatar) ?>" class="user-profile-img" alt="Avatar actuel">
        </div>
        <form method="POST" action="/profile" enctype="multipart/form-data" class="profile-form">
            <div class="form-group">
                <label for="avatar_upload" class="shadow-btn">Choisir un fichier</label>
                <input type="file" id="avatar_upload" name="avatar_upload" accept="image/png, image/jpeg">
            </div>
            <div class="avatar-choice-list">
                <?php foreach ($predefinedAvatars as $img): ?>
                    <label class="avatar-choice">
                        <input type="radio" name="predefined_avatar" value="<?= $img ?>" 
                            <?php if (isset($_SESSION['avatar']) && $_SESSION['avatar'] === $img): ?>checked<?php endif; ?>>
                        <img src="/avatars/<?= htmlspecialchars($img) ?>" alt="avatar">
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="shadow-btn">Enregistrer</button>
        </form>
    </div>
</main>

<script>
    const currentAvatar = document.getElementById('current-avatar');
    const fileInput = document.getElementById('avatar_upload');
    const avatarRadios = document.querySelectorAll('input[name="predefined_avatar"]');

    // When a file is uploaded
    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                currentAvatar.src = e.target.result;
            };
            reader.readAsDataURL(file);

            // Uncheck predefined avatars
            avatarRadios.forEach(r => r.checked = false);
        }
    });

    // When a predefined avatar is selected
    avatarRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.checked) {
                currentAvatar.src = `/avatars/${this.value}`;
                // Clear file input if needed
                fileInput.value = '';
            }
        });
    });
</script>
