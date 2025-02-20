<?php
    $title = "déconnexion à biosphère";
    use App\Auth\Auth;

    Auth::logout();

    header('Location: '.$router->url('login'));
    exit();
