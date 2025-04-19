<?php
$title = "400 error";
$error = htmlspecialchars($data["error"] ?? "Bad request");
?>

<p><?= $error ?></p>