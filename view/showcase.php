<div class="pleesher-showcase">
	<?php foreach ($goals as $showcased_goal): ?>
	<div class="pleesher-showcase-item">
		<div class="pleesher-showcase-item-icon"><?php if ($removable && !PleesherExtension::isDisabled()): ?>
		[ <a data-redirect="self" href="<?php echo $h->actionUrl('pleesher.showcase_achievement', ['goal_id' => $showcased_goal->id, 'remove' => 1]) ?>">X</a> ]
		<?php endif ?></div>
		<div class="pleesher-showcase-item-name"><?php echo $showcased_goal->title ?></div>
	</div>
	<?php endforeach ?>
</div>