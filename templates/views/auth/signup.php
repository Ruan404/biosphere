<?php
use App\Auth\Auth;
use App\User\User;

$title = "rejoindre biosphère";
$error = false;

if (!empty($_POST)) {
  //créer un nouvel objet auth
  $auth = new Auth();

  //create a new user
  $signupUser = new User();
  $signupUser->pseudo = $_POST['pseudo'];
  $signupUser->mdp = $_POST['password'];

  //essayer d'inscrire l'utilisateur
  $result = $auth->signup($signupUser);

  if ($result == true) {
    header('Location: ' . $router->url('login'));
    exit();
  }
}
?>
<div class="sign-ctn">
  <div class="sub-ctn">
    <h1>Rejoindre Biosphère</h1>
    <?php
    if ($error) {
      echo "<div class='error'>utilisateur déjà existant</div>";
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
    <input id="btn" class="primary-btn" type="submit" name="envoi" value="S'inscrire">
  </form>
</div>