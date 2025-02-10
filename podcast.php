<!DOCTYPE html>
<html>
<head>
  <title>Podcast #1 L'Éclosion Low-Tech - Geoffrey</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background-color: #f2f2f2;
    }
    .player {
      display: flex;
      flex-direction: column;
      align-items: center;
      background-color: #fff;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
    }
    .cover {
      width: 200px;
      height: 200px;
      object-fit: cover;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    .title {
      font-size: 24px;
      margin-bottom: 10px;
    }
    .author {
      font-size: 16px;
      margin-bottom: 20px;
    }
    audio {
      width: 100%;
    }
  </style>
</head>
<body>
  <div class="player">
    <img class="cover" src="/podcast/Podcast.jpg" alt="Podcast cover">
    <h2 class="title">Podcast #1 L'Éclosion Low-Tech - Geoffrey</h2>
    <p class="author">Plus on possède, plus on est possédé</p>
    <audio controls>
      <source src="/podcast/Podcast.mp3" type="audio/mpeg">
      Votre navigateur ne supporte pas la balise audio.
    </audio>
  </div>
</body>
</html>
