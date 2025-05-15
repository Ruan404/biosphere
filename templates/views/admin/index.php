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
            <button class="icon-btn bulk-action" aria-label="supprimer un ou plusieurs utilisateurs"
                data-action="delete_users" type="button">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M3.5 6H8.53544C8.77806 4.30385 10.2368 3 12 3C13.7632 3 15.2219 4.30385 15.4646 6H20.5C20.7761 6 21 6.22386 21 6.5C21 6.77614 20.7761 7 20.5 7H3.5C3.22386 7 3 6.77614 3 6.5C3 6.22386 3.22386 6 3.5 6ZM12 4C10.7905 4 9.78164 4.85888 9.55001 6H14.45C14.2184 4.85888 13.2095 4 12 4Z" />
                    <path
                        d="M5.5 8.5C5.5 8.22386 5.27614 8 5 8C4.72386 8 4.5 8.22386 4.5 8.5V16.5C4.5 18.9853 6.51472 21 9 21H15C17.4853 21 19.5 18.9853 19.5 16.5V8.5C19.5 8.22386 19.2761 8 19 8C18.7239 8 18.5 8.22386 18.5 8.5V16.5C18.5 18.433 16.933 20 15 20H9C7.067 20 5.5 18.433 5.5 16.5V8.5Z" />
                </svg>
            </button>
        </div>
    </template>

    <template id="table-topics-head">
        <h3>Topics</h3>
        <div class="buttons-ctn">
            <button type="button" aria-label="supprimer un ou plusieurs topics" class="icon-btn bulk-action"
                data-action="delete_topics">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M3.5 6H8.53544C8.77806 4.30385 10.2368 3 12 3C13.7632 3 15.2219 4.30385 15.4646 6H20.5C20.7761 6 21 6.22386 21 6.5C21 6.77614 20.7761 7 20.5 7H3.5C3.22386 7 3 6.77614 3 6.5C3 6.22386 3.22386 6 3.5 6ZM12 4C10.7905 4 9.78164 4.85888 9.55001 6H14.45C14.2184 4.85888 13.2095 4 12 4Z" />
                    <path
                        d="M5.5 8.5C5.5 8.22386 5.27614 8 5 8C4.72386 8 4.5 8.22386 4.5 8.5V16.5C4.5 18.9853 6.51472 21 9 21H15C17.4853 21 19.5 18.9853 19.5 16.5V8.5C19.5 8.22386 19.2761 8 19 8C18.7239 8 18.5 8.22386 18.5 8.5V16.5C18.5 18.433 16.933 20 15 20H9C7.067 20 5.5 18.433 5.5 16.5V8.5Z" />
                </svg>
            </button>
            <button type="button" aria-label="ajouter un topic" class="icon-btn" id="addTopicBtn"
                onclick="showPopuForm()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12 2C12.2761 2 12.5 2.22386 12.5 2.5V11.5H21.5L21.6006 11.5098C21.8286 11.5563 22 11.7583 22 12C22 12.2417 21.8286 12.4437 21.6006 12.4902L21.5 12.5H12.5V21.5C12.5 21.7761 12.2761 22 12 22C11.7239 22 11.5 21.7761 11.5 21.5V12.5H2.5C2.22386 12.5 2 12.2761 2 12C2 11.7239 2.22386 11.5 2.5 11.5H11.5V2.5C11.5 2.22386 11.7239 2 12 2Z" />
                </svg>
            </button>
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
            <button type="button" aria-label="supprimer un ou plusieurs films" class="icon-btn bulk-action"
                data-action="delete_films">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M3.5 6H8.53544C8.77806 4.30385 10.2368 3 12 3C13.7632 3 15.2219 4.30385 15.4646 6H20.5C20.7761 6 21 6.22386 21 6.5C21 6.77614 20.7761 7 20.5 7H3.5C3.22386 7 3 6.77614 3 6.5C3 6.22386 3.22386 6 3.5 6ZM12 4C10.7905 4 9.78164 4.85888 9.55001 6H14.45C14.2184 4.85888 13.2095 4 12 4Z" />
                    <path
                        d="M5.5 8.5C5.5 8.22386 5.27614 8 5 8C4.72386 8 4.5 8.22386 4.5 8.5V16.5C4.5 18.9853 6.51472 21 9 21H15C17.4853 21 19.5 18.9853 19.5 16.5V8.5C19.5 8.22386 19.2761 8 19 8C18.7239 8 18.5 8.22386 18.5 8.5V16.5C18.5 18.433 16.933 20 15 20H9C7.067 20 5.5 18.433 5.5 16.5V8.5Z" />
                </svg>
            </button>
            <a class="icon-btn" aria-label="ajouter un film" href="/admin/film/upload">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12 2C12.2761 2 12.5 2.22386 12.5 2.5V11.5H21.5L21.6006 11.5098C21.8286 11.5563 22 11.7583 22 12C22 12.2417 21.8286 12.4437 21.6006 12.4902L21.5 12.5H12.5V21.5C12.5 21.7761 12.2761 22 12 22C11.7239 22 11.5 21.7761 11.5 21.5V12.5H2.5C2.22386 12.5 2 12.2761 2 12C2 11.7239 2.22386 11.5 2.5 11.5H11.5V2.5C11.5 2.22386 11.7239 2 12 2Z" />
                </svg>
            </a>
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