<?php 
session_start();

if(!isset($_SESSION['mdp']) || empty($_SESSION['mdp'])){
  header('Location: connexion.php');
  exit;
}
?>

<!DOCTYPE html lang="fr">
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Station Météo</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f9f9f9;
      color: #333;
    }
    header {
      display: flex;
      justify-content: space-around;
      align-items: center;
      padding: 20px;
    }
    nav a {
      margin: 0 10px;
      text-transform: uppercase;
      text-decoration: none;
      color: #333;
    }
    main {
      display: flex;
      justify-content: space-around;
      align-items: center;
      padding: 20px;
    }
    h1 {
      font-size: 2.5em;
      text-transform: uppercase;
      letter-spacing: 2px;
    }
    p {
      font-size: 1.2em;
      line-height: 1.5em;
      text-align: justify;
      text-justify: inter-character;
    }
    .container {
      width: 70%;
      background-color: #fff;
      border: 1px solid #ddd;
      padding: 20px;
      overflow-y: auto;
      max-height: 80vh;
    }
    table {
      border-collapse: collapse;
      width: 100%;
    }
    th, td {
      border: 1px solid black;
      padding: 8px;
      text-align: left;
    }
    th {
      background-color: #4CAF50;
      color: white;
    }
    .export-button {
      position: absolute;
      top: 0;
      right: 0;
      background-color: #4CAF50;
      color: #fff;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <h1>Biosphère Experience</h1>
      <h2>Bienvenue dans le biosphWeb, <?php echo $_SESSION['pseudo']; ?>!</h2>
    </div>
    <nav>
      <a href="podcast.php">Podcast</a>
      <a href="conversations_index.php">Messagerie</a>
      <a href="chat.php">Forum</a>
      <a href="film.php">Films</a>
      <a href="connexion.php">Connexion</a>
      <a href="deconnexion.php">Déconnexion</a>
      <br><br>
      <a href="etatbaterrie.html">Niveau : batterie</a>
      <a href="station_meteo.php">historique station meteo</a>
      <a href="station_meteo_tempsReel.php">station meteo</a>
    </nav>
  </header>
  <main>
    <div class="container">
      <button class="export-button" onclick='exportToCSV()'>Exporter au format CSV</button>
      <?php
      // Connexion à la base de données
      $servername = "localhost";
      $username = "root";
      $password = "";
      $dbname = "biosphere";

      $conn = new mysqli($servername, $username, $password, $dbname);

      // Vérification de la connexion
      if ($conn->connect_error) {
        die("Erreur de connexion : ". $conn->connect_error);
      }

      // Requête SQL pour récupérer les données
      $sql = "SELECT dateTime, temperature, humidity, pressure, windSpeed, windDirection, uvIntensity, uvIndex, lux FROM donnees_station_meteo";
      $result = $conn->query($sql);

      // Vérification si la requête a réussi
      if (!$result) {
        die("Erreur de requête : ". $conn->error);
      }

      // Affichage des données
      echo "<table border='1'>";
      echo "<tr><th>Date/Heure</th><th>Température (°C)</th><th>Humidité (%)</th><th>Pression (hPa)</th><th>Vitesse du vent (m/s)</th><th>Direction du vent</th><th>Intensité UV</th><th>Index UV</th><th>Lux</th></tr>";

      while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>". $row["dateTime"]. "</td>";
        echo "<td>". $row["temperature"]. "</td>";
        echo "<td>". $row["humidity"]. "</td>";
        echo "<td>". $row["pressure"]. "</td>";
        echo "<td>". $row["windSpeed"]. "</td>";
        echo "<td>". $row["windDirection"]. "</td>";
        echo "<td>". $row["uvIntensity"]. "</td>";
        echo "<td>". $row["uvIndex"]. "</td>";
        echo "<td>". $row["lux"]. "</td>";
        echo "</tr>";
      }

      echo "</table>";

      // Fermeture de la connexion
      $conn->close();
     ?>
    </div>
  </main>
  <script>
    function exportToCSV() {
      var csv = '';
      var rows = document.querySelectorAll('table tr');
      for (var i = 0; i < rows.length; i++) {
        var row = [];
        var cols = rows[i].querySelectorAll('td, th');
        for (var j = 0; j < cols.length; j++) {
          row.push(cols[j].textContent);
        }
        csv += row.join(',') + '\n';
      }
      var csvBlob = new Blob([csv], { type: 'text/csv' });
      var link = document.createElement('a');
      link.href = URL.createObjectURL(csvBlob);
      link.download = 'donnees_station_meteo.csv';
      link.click();
    }
  </script>
</body>
</html>
