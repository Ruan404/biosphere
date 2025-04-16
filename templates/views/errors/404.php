<?php
$title = "404 error";
$error = htmlspecialchars($data["error"]) ?? "ressource was not found";
?>

<div>
   <?= $error ?>
</div>