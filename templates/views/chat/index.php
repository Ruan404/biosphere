<?php
use App\Helpers\Text;
$style = "chat";
$topics = $data['topics'] ?? [];

?>

<main>
	<div class="container">
		<sidebar-tab class="sidebar-ctn">
			<button slot="trigger" class="tab-btn shadow-btn" id="toggle-btn">Topics</button>
			<?php foreach ($topics as $topic): ?>
				<a slot="menu" class='sidebar-menu-button'
					href="<?= '/chat/' . $topic->name ?>"><?= Text::removeUnderscore($topic->name) ?></a>
			<?php endforeach ?>
		</sidebar-tab>
		<div class="messages">
			<div class="no-topic">aucun topic sélectionné</div>
		</div>
	</div>
</main>
<script src="/assets/js/components/Sidebar.js"></script>