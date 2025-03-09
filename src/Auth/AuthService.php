<?php

namespace App\Auth;
use \App\User\{
    User,
    UserService
};


class AuthService
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function login(User $loginUser): ?User
    {
        $user = $this->userService->getUserByPseudo($loginUser->pseudo);

        //verify password

        if ($user == null) {
            return null;
        }

        if (sha1($loginUser->mdp) == $user->mdp) {
            /**
             * 0 ----> PHP_SESSION_DISABLED if sessions are disabled.
             * 1 ----> PHP_SESSION_NONE if sessions are enabled, but none exists.
             * 2 ----> PHP_SESSION_ACTIVE if sessions are enabled, and one exists.
             */
            if (session_status() == 1) {
                session_start();
                $_SESSION['auth'] = $user->id;
                return $user;
            }

            return $user;
        }

        return null;
    }

    public function signup(User $signupUser): bool
    {
        $result = $this->userService->createUser($signupUser);

        return $result;
    }

    /**
     * la fonction est statique pour ne pas avoir à instancier la classe
     * instancier signifie créer un nouvel objet à partir d'une classe
     * exemple : $auth = new Auth();
     */
    public static function logout()
    {
        // Initialize the session.
        // If you are using session_name("something"), don't forget it now!
        session_start();

        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();
    }

    public static function getUserSession(): ?User
    {
        
        /**
         * 0 ----> PHP_SESSION_DISABLED if sessions are disabled.
         * 1 ----> PHP_SESSION_NONE if sessions are enabled, but none exists.
         * 2 ----> PHP_SESSION_ACTIVE if sessions are enabled, and one exists.
         */

        if (session_status() == 1) {
            session_start();
        }

        elseif(empty($_SESSION['auth'])){
            return null;
        }

        $userService = new UserService();

        $user = $userService->getUserById($_SESSION['auth']);

        return $user;

    }
}