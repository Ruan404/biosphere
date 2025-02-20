<?php
use App\Auth\Auth;
use App\Database\Database;

$title = "connexion à biosphère";
$error = false;
if (!empty($_POST)) {
    $auth = new Auth(Database::getPDO());

    $user = $auth->login($_POST['pseudo'], $_POST['password']);

    if ($user) {
        header('Location: '.$router->url('home'));
        exit();
    }
    $error = true;
}
?>
<div class="sign-ctn">
    <div class="sub-ctn">
        <h1>Se connecter à Biosphère</h1>
        <?php
        if ($error) {
            echo "<div class='error'>pseudo ou  mot de passe indirect</div>";
        }
        ?>
    </div>
    <form method="POST">
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
            <a class="secondary-btn" href="/signup">S'inscrire</a>
        </div>
    </form>
</div>