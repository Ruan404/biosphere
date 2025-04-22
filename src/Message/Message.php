<?php

namespace App\Message;

use App\Auth\AuthService;

class Message
{
    private $timezone;
    
    public int $message_id = 0;
    public string $pseudo = "";
    public string $date = "";
    public string $content = "";
    public string $options = "";

    public function __construct(string $pseudo = "", string $date = "")
    {
        if ($pseudo) {
            $this->pseudo = $pseudo;
        }
        if ($date) {
            $this->date = $date;
        }
        // Vérifier si la session n'est pas active, puis la démarrer
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        // On suppose que $_SESSION['username'] est défini dès que la session est active
        $this->options = $this->getOptions($this->pseudo === $_SESSION['username'] || ($_SESSION["role"] ?? "") === "admin");
    }

    public function setMessageId(int $message_id): void
    {
        $this->message_id = $message_id;
    }

    public function setContent(string $content): void
    {
        // Nettoyage du contenu pour l'affichage
        $this->content = nl2br(rtrim(strip_tags(htmlspecialchars_decode(trim($content)))));
    }

    /**
     * Génère le HTML contenant les options disponibles pour ce message.
     * Utilise le message_id pour le bouton de suppression.
     */
    private function getOptions(bool $canDelete): string
    {
        if ($canDelete) {
            return "
            <div class='options-ctn'>
                <div class='options'>
                    <button class='option-btn' onclick='deleteMessage({$this->message_id})'>supprimer</button>
                </div>
                <button class='options-btn'>
                    <svg width='24' height='24' viewBox='0 0 24 24' fill='currentColor' xmlns='http://www.w3.org/2000/svg'>
                        <rect x='11' y='5' width='2' height='2' rx='1'/>
                        <rect x='11' y='11' width='2' height='2' rx='1'/>
                        <rect x='11' y='17' width='2' height='2' rx='1'/>
                    </svg>
                </button>
            </div>
        ";
        }
        return "";
    }
}
?>
