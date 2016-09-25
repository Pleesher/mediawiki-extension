<?php foreach ($goals as $goal): ?>
<?php echo PleesherExtension::render('goal', [
	'user' => isset($user) ? $user : null,
	'goal' => $goal,
	'actions' => isset($actions) ? $actions : []
]) ?>
<?php endforeach ?>