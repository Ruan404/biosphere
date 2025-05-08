<?php
$style = "admin";
?>

<div class="container">
    <!-- Sidebar with management tabs -->
    <sidebar-tab class="sidebar-ctn">
        <button slot="trigger" class="tab-btn shadow-btn" id="toggle-btn">Actions</button>

        <button slot="menu" class='sidebar-menu-button' onclick="showTab('users')">Gestion des utilisateurs</button>
        <button slot="menu" class='sidebar-menu-button' onclick="showTab('topics')">Gestion des topics</button>
        <button slot="menu" class='sidebar-menu-button' onclick="showTab('podcasts')">Gestion des podcasts</button>
        <button slot="menu" class='sidebar-menu-button' onclick="showTab('films')">Gestion des films</button>
    </sidebar-tab>

    <!-- Tab content -->
    <div class="admin-tab-content">
        <div class="table-container">
            <h2 id="tab-title">Chargement...</h2>
            <table class="data-table">
                <thead></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<script src="/assets/js/components/Sidebar.js"></script>
<script src="/assets/js/adminPanel.js"></script>
