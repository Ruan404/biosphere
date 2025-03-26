<?php
use App\Helpers\Text;
use App\Admin\Set_Admin;


$style = "admin";
$users = $data['users'] ?? [];
$topics = $data['topics'] ?? [];
$podcasts = $data['podcasts'] ?? [];
$films = $data['films'] ?? [];
?>

<div class="container">
    <div class="tabs">
        <button class="tab-btn" onclick="showTab('users')">Utilisateurs</button>
        <button class="tab-btn" onclick="showTab('topics')">Topics</button>
        <button class="tab-btn" onclick="showTab('podcasts')">Podcasts</button>
        <button class="tab-btn" onclick="showTab('films')">Films</button>
    </div>

    <!-- Gestion des utilisateurs -->
    <div class="tab-content" id="users">
        <h3>Gestion des utilisateurs</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>topic</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user->pseudo) ?></td>
                            <td><?= htmlspecialchars($user->role) ?></td>
                            <td>
                                <!-- Delete User Form -->
                                <form action = "admin/action" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="pseudo" value="<?= $user->pseudo ?>">
                                    <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">Supprimer</button>
                                </form> |
                                
                                <!-- Promote User Form -->
                                <form action = "admin/action" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="promote_user">
                                    <input type="hidden" name="pseudo" value="<?= $user->pseudo ?>">
                                    <button type="submit">Promouvoir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
            <button class="primary-btn" onclick="window.location.href='Set_Admin'">Ajouter un utilisateur</button>
        </div>
    </div>

    <!-- Gestion des topics -->
    <div class="tab-content" id="topics">
        <h3>Gestion des topics</h3>
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
                                    <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce topic ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
            <button class="primary-btn" onclick="window.location.href='admin/Set_Admin.php?action=add_topic'">Ajouter un topic</button>
        </div>
    </div>

    <!-- Gestion des podcasts -->
    <div class="tab-content" id="podcasts">
        <h3>Gestion des podcasts</h3>
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
                                    <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce podcast ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
            <button class="primary-btn" onclick="window.location.href='admin/Set_Admin.php?action=add_podcast'">Ajouter un podcast</button>
        </div>
    </div>

    <!-- Gestion des films -->
    <div class="tab-content" id="films">
        <h3>Gestion des films</h3>
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
                                    <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce film ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
            <a class="primary-btn" href="/admin/film/upload">Ajouter un film</a>
        </div>
    </div>
</div>

<script>
    // Fonction pour afficher les différents onglets
    function showTab(tabName) {
        const tabs = document.querySelectorAll('.tab-content');
        tabs.forEach(tab => tab.style.display = 'none');
        document.getElementById(tabName).style.display = 'block';
    }

    // Afficher l'onglet des utilisateurs par défaut au chargement
    showTab('users');
</script>
