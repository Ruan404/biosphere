<?php
use App\Helpers\Text;
$style = "chat";
$topics = $data['topics'] ?? [];

?>

<div class="container">
	<div class="sidebar-ctn">
		<sidebar-tab>
			<button slot="trigger" class="tab-btn shadow-btn" id="toggle-btn">Topics</button>
			<?php foreach ($topics as $topic): ?>
					<a slot="menu" class='sidebar-menu-button' href="<?= '/chat/' . $topic->name ?>"><?= Text::removeUnderscore($topic->name) ?></a>
				<?php endforeach ?>
		</sidebar-tab>

	</div>
	<div class="messages">
		<div class="no-topic">aucun topic sélectionné</div>
	</div>
</div>
<script src="/assets/js/components/Sidebar.js"></script>