<?php
// Envoi de la requête HTTP
$url = "http://10.44.71.175/currentdata";
$response = json_decode(file_get_contents($url), true);

// Vérification si la réponse est valide
if (!$response) {
    echo "Erreur : impossible de récupérer les données.";
    exit;
}
?>

<!-- CSS pour les widgets -->
<style>
   .widget {
        background-color: #f7f7f7;
        border: 1px solid #ddd;
        padding: 10px;
        margin: 10px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

   .widget-header {
        background-color: #333;
        color: #fff;
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

   .widget-content {
        padding: 20px;
    }

   .widget-value {
        font-size: 24px;
        font-weight: bold;
    }

   .widget-unit {
        font-size: 18px;
        color: #666;
    }

   .progress-indicator {
        width: 100%;
        height: 20px;
        background-color: #ddd;
        border-radius: 10px;
        overflow: hidden;
    }

   .progress-bar {
        height: 100%;
        background-color: #337ab7;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
    }
</style>

<!-- Affichage des données -->
<div class="widget">
    <div class="widget-header">Dernières données météo</div>
    <div class="widget-content">
        <div class="widget-row">
            <span class="widget-label">Date/Heure</span>
            <span class="widget-value"><?= date("Y-m-d H:i:s", $response["timestamp"])?></span>
        </div>
        <div class="widget-row">
            <span class="widget-label">Température</span>
            <span class="widget-value"><?= number_format($response["temperature"], 2)?></span>
            <span class="widget-unit">°C</span>
            <div class="progress-indicator">
                <div class="progress-bar" style="width: <?= ($response["temperature"] - 15) * 100 / (35 - 15)?>%;">
                    <?= number_format($response["temperature"], 2)?> °C
                </div>
            </div>
        </div>
        <div class="widget-row">
            <span class="widget-label">Humidité</span>
            <span class="widget-value"><?= $response["humidity"]?></span>
            <span class="widget-unit">%</span>
            <div class="progress-indicator">
                <div class="progress-bar" style="width: <?= $response["humidity"] * 100 / 100?>;">
                    <?= $response["humidity"]?> %
                </div>
            </div>
        </div>
        <div class="widget-row">
            <span class="widget-label">Pression</span>
            <span class="widget-value"><?= $response["pressure"]?></span>
            <span class="widget-unit">hPa</span>
        </div>
        <div class="widget-row">
            <span class="widget-label">Vitesse du vent</span
