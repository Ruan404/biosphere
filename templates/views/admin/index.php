<?php
use App\Helpers\Text;


$style = "admin";
$users = $data['users'] ?? [];
$topics = $data['topics'] ?? [];
$podcasts = $data['podcasts'] ?? [];
$films = $data['films'] ?? [];
?>

<div class="container">
    <div class="tab-admin">
        <button class="tab-btn shadow-btn" onclick="showTabNav()">Actions</button>
    </div>
    <div class="tabs">
        <button class="close-btn icon-btn" onclick="hideTabNav()">close</button>
      <div class="tabs-list">
        <button class="tab-btn" onclick="showTab('users')">Gestion des utilisateurs</button>
        <button class="tab-btn" onclick="showTab('topics')">Gestion des topics</button>
        <button class="tab-btn" onclick="showTab('podcasts')">Gestion des podcasts</button>
        <button class="tab-btn" onclick="showTab('films')">Gestion des films</button>
      </div>
    </div>
    
    <!-- Gestion des utilisateurs -->
    <div class="tab-content" id="users">
        <div class="tab-content-head"> <h3>Utilisateurs</h3></div>
         <div class="table-container">
             <table>
                 <thead>
                     <tr>
                         <th>Utilisateur</th>
                         <th>Rôle</th>
                         <th colspan="2">Actions</th>
                     </tr>
                 </thead>
                 <tbody>
                     <?php foreach ($users as $user): ?>
                         <tr>
                             <td><?= htmlspecialchars($user->pseudo) ?></td>
                             <td><?= htmlspecialchars($user->role) ?></td>
                             <td>
                                 <form action="admin/action" method="POST">
                                     <input type="hidden" name="action" value="delete_user">
                                     <input type="hidden" name="pseudo" value="<?= $user->pseudo ?>">
                                     <button type="submit" class="delete-btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">Supprimer</button>
                                 </form>
                             </td>
                             <td>
                                 <form action="admin/action" method="POST">
                                     <input type="hidden" name="action" value="promote_user">
                                     <input type="hidden" name="pseudo" value="<?= $user->pseudo ?>">
                                     <button type="submit" class="promote-btn">Promouvoir</button>
                                 </form>
                             </td>
                         </tr>
                     <?php endforeach ?>
                 </tbody>
             </table>
         </div>
     </div>


    <!-- Gestion des topics -->
    <div class="tab-content" id="topics">
        <div class="tab-content-head">
            <h3>Topics</h3>
            <!-- Bouton qui va afficher le formulaire d'ajout de topic -->
            <button type="button" class="primary-btn" id="addTopicBtn" onclick="showAddTopicForm()">Ajouter un topic</button>
            
            <!-- Formulaire d'ajout de topic caché au départ -->
            <div id="addTopicForm" style="display:none; margin-top: 10px;">
                <form action="admin/action" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="add_topic">
                    <input type="text" name="topic" placeholder="Nom du topic" required>
                    <button type="submit" class="primary-btn">Ajouter</button>
                </form>
            </div>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Topic</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topics as $topic): ?>
                        <tr>
                            <td><?= Text::removeUnderscore($topic->name) ?></td>
                            <td>
                                <!-- Delete Topic Form -->
                                <form action = "admin/action" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_topic">
                                    <input type="hidden" name="topic" value="<?= $topic->name ?>"> <!-- Assuming topic name can be used for action -->
                                    <button type="submit" class="delete-btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce topic ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Gestion des podcasts -->
    <div class="tab-content" id="podcasts">
        <div class="tab-content-head"> <h3>Gestion des podcasts</h3> <button class="primary-btn" onclick="window.location.href='admin/Set_Admin.php?action=add_podcast'">Ajouter un podcast</button>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($podcasts as $podcast): ?>
                        <tr>
                            <td><?= htmlspecialchars($podcast->title) ?></td>
                            <td>
                                <!-- Delete Podcast Form -->
                                <form action = "admin/action" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_podcast">
                                    <input type="hidden" name="podcast" value="<?= $podcast->title ?>"> <!-- Assuming title can be used for action -->
                                    <button type="submit" class="delete-btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce podcast ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Gestion des films -->
    <div class="tab-content" id="films">
        <div class="tab-content-head"><h3>Gestion des films</h3>             <button class="primary-btn" onclick="window.location.href='admin/Set_Admin.php?action=add_film'">Ajouter un film</button>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($films as $film): ?>
                        <tr>
                            <td><?= htmlspecialchars($film->title) ?></td>
                            <td>
                                <!-- Delete Film Form -->
                                <form action = "admin/action" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_film">
                                    <input type="hidden" name="film" value="<?= $film->title ?>"> <!-- Assuming title can be used for action -->
                                    <button type="submit" class="delete-btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce film ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Fonction pour afficher les différents onglets
    function showTab(tabName) {
        // Supprime la classe active de tous les contenus
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        // Ajoute la classe active à l'onglet sélectionné
        document.getElementById(tabName).classList.add('active');
    
        // Met à jour les boutons de tabs
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('current'));
        document.querySelector(`[onclick="showTab('${tabName}')"]`).classList.add('current');
    }
    
    // Afficher l'onglet des utilisateurs par défaut au chargement
    document.addEventListener("DOMContentLoaded", function () {
        showTab('users');
    });
    

    // Afficher l'onglet des utilisateurs par défaut au chargement
    showTab('users');

	var tabs = document.querySelector('.tabs')

	function showTabNav() {
		tabs.classList.add('show');
		document.body.classList.add('black-mask')
	}
	function hideTabNav() {
		tabs.classList.remove('show')

		document.body.classList.remove('black-mask')
	}

    // Fonction pour afficher le formulaire d'ajout de topic
    function showAddTopicForm() {
        var form = document.getElementById("addTopicForm");
        var button = document.getElementById("addTopicBtn");
        
        // Afficher le formulaire
        form.style.display = "block";
        
        // Cacher le bouton
        button.style.display = "none";
    }

</script>