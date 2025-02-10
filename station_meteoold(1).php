<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "biosphere";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Requête SQL pour récupérer les données
$sql = "SELECT dateTime, temperature, humidity, pressure, windSpeed, windDirection, uvIntensity, uvIndex, lux FROM donnees_station_meteo";
$result = $conn->query($sql);

// Vérification si la requête a réussi
if (!$result) {
    die("Erreur de requête : " . $conn->error);
}

// Affichage des données
echo "<table border='1'>";
echo "<tr><th>Date/Heure</th><th>Température (°C)</th><th>Humidité (%)</th><th>Pression (hPa)</th><th>Vitesse du vent (m/s)</th><th>Direction du vent</th><th>Intensité UV</th><th>Index UV</th><th>Lux</th></tr>";

while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row["dateTime"] . "</td>";
    echo "<td>" . $row["temperature"] . "</td>";
    echo "<td>" . $row["humidity"] . "</td>";
    echo "<td>" . $row["pressure"] . "</td>";
    echo "<td>" . $row["windSpeed"] . "</td>";
    echo "<td>" . $row["windDirection"] . "</td>";
    echo "<td>" . $row["uvIntensity"] . "</td>";
    echo "<td>" . $row["uvIndex"] . "</td>";
    echo "<td>" . $row["lux"] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Fermeture de la connexion
$conn->close();
?>
