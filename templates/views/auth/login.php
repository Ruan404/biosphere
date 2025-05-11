<?php

$title = "connexion à biosphère";
?>
<div class="sign-ctn">
    <div class="sub-ctn">
        <h1>Se connecter à Biosphère</h1>
        <?php
        if ($data['error'] ?? false) {
            echo "<div class='error'>pseudo ou  mot de passe incorrecte</div>";
        }
        ?>
    </div>
    <form method="POST">
        <input type="hidden" name="csrf_name" value="<?= $data['csrf_name'] ?>">
        <input type="hidden" name="csrf_value" value="<?= $data['csrf_value'] ?>">
        <div class="form-field-ctn">
            <div class="form-field">
                <label for="pseudo">Pseudo</label>
                <input id="pseudo" required type="text" name="pseudo" autocomplete="off"
                    placeholder="Entrez votre pseudo">
            </div>
            <div class="form-field">
                <label for="pwd">Mot de passe</label>
                <input id="pwd" required type="password" name="password" autocomplete="off"
                    placeholder="Entrez votre mot de passe">
            </div>
        </div>
        <div class="sub-ctn">
            <input class="primary-btn" type="submit" name="envoi" value="Se connecter">
            <p>Nouveau sur Biosphère ? <a class="sign-link" href="/signup">Créer un compte</a></p>
        </div>
    </form>
</div>