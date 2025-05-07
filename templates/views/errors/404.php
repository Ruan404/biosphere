<?php
$title = "404 error";
$error = htmlspecialchars($data["error"] ?? "Ressource was not found");
?>

<p><?= $error ?></p>