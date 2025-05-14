<div class="container">
    <!-- Sidebar with management tabs -->
    <sidebar-tab class="sidebar-ctn">
        <button slot="trigger" class="tab-btn shadow-btn" id="toggle-btn">Actions</button>

        <button slot="menu" class='sidebar-menu-button' onclick="showTab('users')">Gestion des utilisateurs</button>
        <button slot="menu" class='sidebar-menu-button' onclick="showTab('topics')">Gestion des topics</button>
        <button slot="menu" class='sidebar-menu-button' onclick="showTab('podcasts')">Gestion des podcasts</button>
        <button slot="menu" class='sidebar-menu-button' onclick="showTab('films')">Gestion des films</button>
    </sidebar-tab>
    <!-- Tab content head -->
    <template id="table-users-head">
        <h3>Utilisateurs</h3>
        <div class="buttons-ctn">
            <button class="shadow-btn bulk-action" data-action="delete_users" type="button">Supprimer</button>
        </div>
    </template>

    <template id="table-topics-head">
        <h3>Topics</h3>
        <div class="buttons-ctn">
            <button type="button" class="shadow-btn bulk-action" data-action="delete_topics">Supprimer</button>
            <button type="button" class="primary-btn" id="addTopicBtn" onclick="showPopuForm()">Ajouter un
                topic</button>
        </div>
        <!-- Add Topic Form -->
        <div class="form-popup">
            <form onsubmit="(event)=>sendForm(event)" method="POST" class="form-popup-ctn">
                <input type="hidden" name="action" value="add_topic">
                <div class="form-field">
                    <label for="topic">Nom du topic</label>
                    <input type="text" id="topic" name="slug" placeholder="Nom du topic" required>
                </div>
                <div class="buttons-ctn">
                    <button type="submit" class="primary-btn">Ajouter</button>
                    <button type="button" class="shadow-btn" onclick='hidePopupForm()'>Annuler</button>
                </div>
            </form>
        </div>
    </template>

    <template id="table-films-head">
        <h3>Gestion des films</h3>
        <div class="buttons-ctn">
            <button type="button" class="shadow-btn bulk-action" data-action="delete_films">Supprimer</button>
            <a class="primary-btn" href="/admin/film/upload">Ajouter un film</a>
        </div>
    </template>
    <template id="table-podcasts-head">
        <h3>Gestion des podcasts</h3>
    </template>

    <!-- Tab content -->
    <div class="tab-content">
        <div class="tab-content-head">
        </div>
        <div class="table-container" id="ctn-display">
            <table class="data-table">
                <thead></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>