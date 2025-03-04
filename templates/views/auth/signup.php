<?php
$title = "rejoindre biosphère";
$error = $data['error'] ?? false;
?>
<div class="sign-ctn">
  <div class="sub-ctn">
    <h1>Rejoindre Biosphère</h1>
    <?php
    if ($error) {
      echo "<div class='error'>le pseudo est indisponible</div>";
    }
    ?>
  </div>
  <form method="POST">
    <div class="form-field-ctn">
      <div class="form-field">
        <label for="pseudo">Pseudo</label>
        <input id="pseudo" required type="text" name="pseudo" autocomplete="off" placeholder="Entrez un pseudo">
      </div>
      <div class="form-field">
        <label for="pwd">Mot de passe</label>
        <input id="pwd" required type="password" name="password" autocomplete="off"
          placeholder="Entrez un mot de passe">
      </div>
    </div>
    <div class="sub-ctn">
      <input id="btn" class="primary-btn" type="submit" name="envoi" value="S'inscrire">
      <p>Déjà un compte ? <a class="sign-link" href="/login">Se connecter</a></p>
    </div>
  </form>
</div>