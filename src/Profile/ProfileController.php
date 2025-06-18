<?php
namespace App\Profile;

use App\Helpers\Text;
use App\Attributes\Route;
use App\Core\Database;
use PDO;
use PDOException;

class ProfileController
{
    private $targetDir;
    private $predefinedAvatars;

    public function __construct()
    {
        $this->targetDir = __DIR__ . '/../../public/uploads/images/avatars';
        if (!is_dir($this->targetDir)) {
            mkdir($this->targetDir, 0777, true);
        }
        $this->targetDir = realpath($this->targetDir);
        if ($this->targetDir !== false) {
            $this->targetDir .= DIRECTORY_SEPARATOR;
        } else {
            throw new \Exception("Le dossier avatars n'existe pas ou le chemin est incorrect.");
        }

        $this->predefinedAvatars = [
            'homme1.png', 'homme2.png', 'femme1.png', 'femme2.png', 'homme3.png', 'femme3.png',
            'homme4.png', 'femme4.png', 'homme5.png', 'femme5.png'
        ];
    }

    #[Route(path: "/profile", method: "GET")]
    public function index()
    {
        if (!isset($_SESSION["username"])) {
            header("Location: /login");
            exit();
        }
        $username = $_SESSION["username"];

        // Nom de l'avatar stocké en session, ou [lettre].png par défaut
        $avatarFilename = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : (Text::getFirstStr($username) . '.png');

        // Chemin absolu pour filemtime
        $avatarFile = $_SERVER['DOCUMENT_ROOT'] . '/uploads/images/avatars/' . $avatarFilename;
        $version = file_exists($avatarFile) ? filemtime($avatarFile) : time();
        $existingAvatar = '/uploads/images/avatars/' . $avatarFilename . '?v=' . $version;

        $predefinedAvatars = $this->predefinedAvatars;
        $style = "profile";
        require __DIR__ . '/../../templates/views/profile/profile.php';
    }

    #[Route(path: "/profile/avatar", method: "POST")]
    public function handleAvatarChange()
    {
        if (!isset($_SESSION["username"], $_SESSION["user_id"])) {
            header("Location: /login");
            exit();
        }

        $username = $_SESSION["username"];
        $userId = $_SESSION["user_id"];

        if (isset($_FILES['avatar_upload']) &&
            $_FILES['avatar_upload']['error'] !== UPLOAD_ERR_OK &&
            $_FILES['avatar_upload']['error'] !== UPLOAD_ERR_NO_FILE) {
            $this->redirectWithError("Erreur lors de l'upload du fichier (code " . $_FILES['avatar_upload']['error'] . ")");
        }

        // Si upload perso, on stocke le nom unique dans la session
        if (!empty($_FILES['avatar_upload']['tmp_name'])) {
            $fileType = mime_content_type($_FILES['avatar_upload']['tmp_name']);
            $allowedTypes = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/jpg' => 'jpg'];
            if (array_key_exists($fileType, $allowedTypes)) {
                $extension = $allowedTypes[$fileType];
                $uniqueFileName = 'avatar_' . $userId . '.' . $extension;
                $finalFile = $this->targetDir . $uniqueFileName;
                // Supprimer l'ancien avatar perso s'il existe (évite les fichiers orphelins)
                $this->deleteOldCustomAvatar($userId, $extension);

                if (!move_uploaded_file($_FILES['avatar_upload']['tmp_name'], $finalFile)) {
                    $this->redirectWithError("Erreur lors de l'enregistrement du fichier (vérifier les droits du dossier)");
                }
                $_SESSION['avatar'] = $uniqueFileName;
                $this->updateUserAvatar($username, $uniqueFileName);
            } else {
                $this->redirectWithError("Fichier non supporté");
            }
        }
        // Si avatar prédéfini choisi
        elseif (!empty($_POST['predefined_avatar'])) {
            $chosen = basename($_POST['predefined_avatar']);
            if (in_array($chosen, $this->predefinedAvatars)) {
                $_SESSION['avatar'] = $chosen;
                $this->updateUserAvatar($username, $chosen);
                // Optionnel : on pourrait supprimer l'ancien avatar perso ici si tu veux nettoyer
            } else {
                $this->redirectWithError("Avatar prédéfini non valide");
            }
        }

        header("Location: /profile");
        exit();
    }

    private function redirectWithError($msg)
    {
        header("Location: /profile?error=" . urlencode($msg));
        exit();
    }

    // Mise à jour de l'avatar en base de données
    private function updateUserAvatar($username, $avatarFilename)
    {
        try {
            $pdo = Database::getPDO();
            $stmt = $pdo->prepare("UPDATE users SET image = ? WHERE pseudo = ?");
            $stmt->execute([$avatarFilename, $username]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'avatar : " . $e->getMessage());
        }
    }

    // Supprime l'ancien avatar personnalisé (optionnel mais recommandé pour éviter l'accumulation de fichiers)
    private function deleteOldCustomAvatar($userId, $currentExt)
    {
        $pattern = $this->targetDir . 'avatar_' . $userId . '.*';
        foreach (glob($pattern) as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext !== $currentExt && file_exists($file)) {
                @unlink($file);
            }
        }
    }
}