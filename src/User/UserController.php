<?php
namespace App\User;

use App\Entities\Layout;
use App\File\FileService;
use App\Helpers\Text;
use App\Attributes\Route;
use App\Core\Database;
use App\User\UserService;
use Exception;
use GuzzleHttp\Psr7\Response;
use PDO;
use PDOException;
use function App\Helpers\view;

class UserController
{
    private $predefinedAvatars;
    private $fileService;
    private $userService;

    public function __construct()
    {
        $this->fileService = new FileService();
        $this->userService = new UserService();

        $this->predefinedAvatars = [
            'homme1.png',
            'homme2.png',
            'femme1.png',
            'femme2.png',
            'homme3.png',
            'femme3.png',
            'homme4.png',
            'femme4.png',
            'homme5.png',
            'femme5.png'
        ];
    }

    #[Route(path: "/profile", method: "GET")]
    public function index()
    {
        $username = $_SESSION["username"];

        $existingAvatar = $_SESSION['avatar'];
        $predefinedAvatars = $this->predefinedAvatars;

        return view(view: "profile/profile", data: ["predefinedAvatars" => $predefinedAvatars, "existingAvatar" => $existingAvatar]);

    }

    #[Route(path: "/profile", method: "POST")]
    public function handleAvatarChange()
    {
        try {
            $username = $_SESSION["username"];

            // Si upload perso, on stocke le nom unique dans la session
            if (!empty($_FILES['avatar_upload']["tmp_name"])) {
                $avatarFile = $_FILES['avatar_upload'] ?? [];
                $this->fileService->validate(["png", "jpeg", "jpg"], $avatarFile['name']);
                $saveFile = $this->fileService->save("avatars/", $avatarFile['name'], $avatarFile['tmp_name']);
                $_SESSION['avatar'] = $saveFile;
                $this->userService->updateUserAvatar($username, $saveFile);
            }

            // Si avatar prédéfini choisi
            elseif (!empty($_POST['predefined_avatar'])) {
                $chosen = basename($_POST['predefined_avatar']);
                $previous = $_SESSION['avatar'];
                if (in_array($chosen, $this->predefinedAvatars)) {
                    $_SESSION['avatar'] = "/avatars/" . $chosen;
                    $this->userService->updateUserAvatar($username, "/avatars/" . $chosen);

                }
            }

            return new Response(301, ["location" => "/profile"]);
        } catch (HttpExceptionInterface) {
            header('Location: /profile');
            exit();
        } catch (Exception $e) {
            error_log("Profile image modification error: " . $e->getMessage());
        }
    }
}