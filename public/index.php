<?php
//charger le fichier d'auto loadidng de composer
require '../vendor/autoload.php'; 

define('DEBUG_TIME', microtime(true));

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

//créer un router à partir de la classe Router
$router = new \App\Router(dirname(__DIR__) . '/templates'); 

/**
 * 1. défini la route d'accès à la page home 
 * 2. défini la route d'accès à la page podcast
 * 3. vérifie que l'url rentrée dans la barre de recherche correspond à celles définies plus haut
 */
$router
    ->get('/', '/home/index', 'home')
    ->get('/podcast', '/podcast/index', 'podcast')

    //chat
    ->get('/chat/[*:slug]', '/chat/index', 'topic')
    ->get('/chat', '/chat/index', 'chat')
    ->post('/chat[*:slug]', '/chat/index', 'new message')

    //film
    ->get('/films', '/film/index', 'film')
    ->get('/film/details/[*:slug]', '/film/api/details', 'film details')
    ->get('/films/[*:slug]', '/film/show', 'show a film')

    //connexion
    ->get('/login', '/auth/login', 'login')
    ->post('/login', '/auth/login')

    //inscription
    ->get('/signup', '/auth/signup', 'signup')
    ->post('/signup', '/auth/signup')
    ->run();
