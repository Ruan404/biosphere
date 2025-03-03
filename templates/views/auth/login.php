<?php
// use App\Database\Database;
use App\Auth\AuthService;
use App\User\User;

$title = "connexion à biosphère";
$error = false;
if (!empty($_POST)) {
    $auth = new AuthService();

    //create a new user
    $loginUser = new User();
    $loginUser->pseudo = $_POST['pseudo'];
    $loginUser->mdp = $_POST['password'];

    $user = $auth->login($loginUser);

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
            echo "<div class='error'>pseudo ou  mot de passe incorrecte</div>";
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
            <p>Nouveau sur Biosphère ? <a class="sign-link" href="/signup">Créer un compte</a></p>
        </div>
    </form>
</div>