<?php

namespace App\Auth;

use App\User\User;
use App\User\UserService;
use App\Exceptions\BadRequestException;

use Casbin\Enforcer;
use CasbinAdapter\Database\Adapter;
use App\Core\Database;

class AuthService
{
    private UserService $userService;

    private Enforcer $routeEnforcer;
    private Enforcer $permissionEnforcer;

    public function __construct()
    {
        $this->userService = new UserService();

        $config = Database::getDBConfig();
        $adapter = Adapter::newAdapter($config);

        // Load Casbin enforcers with separate models
        $this->routeEnforcer = new Enforcer(__DIR__ . '/../Core/route_model.conf', $adapter);
        $this->permissionEnforcer = new Enforcer(__DIR__ . '/../Core/permission_model.conf', $adapter);
    }

    public function login(User $loginUser): ?User
    {
        $user = $this->userService->getUserByPseudo($loginUser->pseudo);

        if (!$user || !(password_verify($loginUser->mdp, $user->mdp) || sha1($loginUser->mdp) === $user->mdp)) {
            throw new BadRequestException("mauvais pseudo ou mot de passe");
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['auth'] = $user->id;
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->pseudo;
        $_SESSION['role'] = $user->role;
        $_SESSION['avatar'] = $user->image;

        return $user;
    }

    public function signup(User $signupUser)
    {
        $user = $this->userService->getUserByPseudo($signupUser->pseudo);
        if ($user !== null) {
            throw new BadRequestException("Pseudo déjà utilisé");
        }

        // Récupérer la première lettre du pseudo en majuscule
        $firstLetter = strtoupper(substr($signupUser->pseudo, 0, 1));

        // Générer le chemin vers l'avatar par défaut
        $avatarPath = "/images/avatars/" . $firstLetter . ".png";

        // Assigner ce chemin à la propriété image de l'utilisateur
        $signupUser->image = $avatarPath;

        // Créer l'utilisateur avec cette image par défaut
        return $this->userService->createUser($signupUser);
    }

    public static function logout()
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Check route access using route enforcer.
     */
    public function canAccessRoute(object $sub, string $path, string $method): bool
    {
        return $this->routeEnforcer->enforce($sub, $path, strtoupper($method));
    }

    /**
     * Check if the subject can perform a given action on a resource.
     */
    public function canPerform(object $sub, string $resource, string $action): bool
    {
        return $this->permissionEnforcer->enforce($sub, $resource, $action);
    }

    /**
     * Get a list of permissions the given role has on a resource.
     */
    public function getPermissions(string $role, string $resource = 'message'): array
    {
        $actions = ['delete_own', 'delete_any'];
        $sub = (object) ['Role' => $role];
        $permissions = [];

        foreach ($actions as $action) {
            if ($this->permissionEnforcer->enforce($sub, $resource, $action)) {
                $permissions[] = "{$action}_{$resource}";
            }
        }

        return $permissions;
    }
}
