<?php    
session_start();

if(!isset($_SESSION['pseudo'])){
	header('Location: connexion.php');
	exit;
}

$bdd = new PDO('mysql:host=localhost;dbname=espace_membres;charset=utf8;', 'root', '');

$topic = $_GET['topic'];   // récupération du topic en fonction de l'url de la page

$lastMessageId = isset($_GET['lastMessageId'])? $_GET['lastMessageId'] : 0;   // récupère l'id du dernier msg affiché

$recupMessages = $bdd->prepare('SELECT * FROM chat WHERE topic = :topic AND id > :lastMessageId ORDER BY id ASC LIMIT 50');
$recupMessages->bindParam(':topic', $topic, PDO::PARAM_STR);
$recupMessages->bindParam(':lastMessageId', $lastMessageId, PDO::PARAM_INT);
$recupMessages->execute();

$messages = [];
if ($recupMessages->rowCount() > 0) {     //si il y a de nouveaux messages
	while($message = $recupMessages->fetch()) {
			$messages[] = [
			'id' => $message['id'],
            'pseudo' => htmlspecialchars($message['pseudo']),
            'date' => htmlspecialchars($message['date']),
            'message' => nl2br(trim(strip_tags(htmlspecialchars($message['message']))))
        ];
		}
	}
			
	echo json_encode(['messages' => $messages, 'lastMessageId' => end($messages)['id']]);
	exit;
?>
