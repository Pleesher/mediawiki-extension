<?php foreach ($goals_by_category as $category => $goals): ?>
<h2><?php echo $category ?></h2>
<?php echo PleesherExtension::render('goals', [
	'user' => $user,
	'goals' => $goals
]) ?>
<?php endforeach ?>

<?php echo PleesherExtension::render('_pleesher_ad') ?>