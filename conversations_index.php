<?php
session_start();
$bdd = new PDO('mysql:host=localhost;dbname=espace_membres;charset=utf8;', 'root', '');
if(!isset($_SESSION['pseudo'])){
	header('Location: connexion.php');
	exit;
}
?>


<!DOCTYPE html lang="fr">
<html>
<head>
  <meta charset="UTF-8">
  <title>Menu Messages</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="navbar.css">
  <link rel="stylesheet" href="conversation_index.css">

</head>
<body>
<nav>
   <div class="navbar">
     <i class="">|||</i>
     <div class="logo"><a href="index.php">Biosphere</a></div>
     <div class="nav-links">
       <div class="sidebar-logo">
         <span class="logo-name">-</span>
         <i class="">X</i>
       </div>
       <ul class="links">
         <li><a href="le_projet.php">Le projet</a></li>
         <li><a href="conversations_index.php">Messagerie</a></li>
         <li>
           <a href="#">forum  </a>
           <i1 class="">v</i1>
           <ul class="htmlCss-sub-menu sub-menu">
             <li><a href="chat.php?topic=commentaires_de_film">Commentaires de film</a></li>
             <li><a href="chat.php?topic=petites_annonces">Petites annonces</a></li>
             <li><a href="chat.php?topic=evenements">événements</a></li>
             <li><a href="chat.php?topic=propositions_services">Services / Savoir faire</a></li>
             <li><a href="chat.php?topic=repair_cafe">Repair café</a></li>
           </ul>
         </li>
         <li>
           <a href="#">station meteo  </a>
           <i1 class="">v</i1>
           <ul class="js-sub-menu sub-menu">
             <li><a href="station_meteo_temps_reel.php">infos temps reel</a></li>
             <li><a href="station_meteo_historique.php">historique station meteo</a></li>
             <li><a href="station_meteo_infos.php">infos</a></li>
           </ul>
         </li>
         <li><a href="film.php">Films</a></li>
         <li><a href="podcast.php">Podcast</a></li>
         <li><a href="deconnexion.php">Déconnexion</a></li>
       </ul>
     </div>
   </div>
 </nav>
	<div class="presentation">
		<h1 class="contacts-header">Contacts BiosphWeb</h1>
		<?php
		$recupUser = $bdd->prepare('SELECT * FROM users WHERE id != :id');
		$recupUser->execute(['id' => $_SESSION['id']]);
		while($user = $recupUser->fetch()){
			?>
			<div class="user-card">
			<a href="conversations_privées.php?id=<?php echo $user['id']; ?>">
				<p><?php echo $user['pseudo'];?></p>
			</a>
			</div>
			<?php
		}
	?>
	</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
   // sidebar open close js code
   let navLinks = document.querySelector(".nav-links");
   let menuOpenBtn = document.querySelector(".navbar i:first-child");
   let menuCloseBtn = document.querySelector(".sidebar-logo i");
   menuOpenBtn.onclick = function() {
     navLinks.style.left = "0";
   }
   menuCloseBtn.onclick = function() {
     navLinks.style.left = "-100%";
   }
   // sidebar submenu open close js code
   let htmlcssArrow = document.querySelector(".htmlcss-arrow");
   htmlcssArrow.onclick = function() {
     navLinks.classList.toggle("show1");
   }
   let moreArrow = document.querySelector(".more-arrow");
   moreArrow.onclick = function() {
     navLinks.classList.toggle("show2");
   }
   let jsArrow = document.querySelector(".js-arrow");
   jsArrow.onclick = function() {
     navLinks.classList.toggle("show3");
   }
   // Add event listener to links li
   let linksLi = document.querySelectorAll(".links li");
   linksLi.forEach(function(li) {
     li.addEventListener("click", function() {
       this.classList.toggle("active");
     });
   });
  });
 </script>
 <!-- <script>
      // sidebar open close js code
      let navLinks = document.querySelector(".nav-links");
      let menuOpenBtn = document.querySelector(".navbar i:first-child");
      let menuCloseBtn = document.querySelector(".sidebar-logo i");
      menuOpenBtn.onclick = function() {
        navLinks.style.left = "0";
      }
      menuCloseBtn.onclick = function() {
        navLinks.style.left = "-100%";
      }
      // sidebar submenu open close js code
      let htmlcssArrow = document.querySelector(".htmlcss-arrow");
      htmlcssArrow.onclick = function() {
        navLinks.classList.toggle("show1");
      }
      let moreArrow = document.querySelector(".more-arrow");
      moreArrow.onclick = function() {
        navLinks.classList.toggle("show2");
      }
      let jsArrow = document.querySelector(".js-arrow");
      jsArrow.onclick = function() {
        navLinks.classList.toggle("show3");
      }
      // Add event listener to links li
      let linksLi = document.querySelectorAll(".links li");
      linksLi.forEach(function(li) {
        li.addEventListener("click", function() {
          this.classList.toggle("active");
        });
      });
    </script> -->
</body>
</html>
