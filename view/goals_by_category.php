<?php foreach ($goals_by_category as $category => $goals): ?>
<h2><?php echo $h->text($h->dynPrefix() . '.goal_category.' . $category) ?></h2>
<?php echo PleesherExtension::render('goals', [
	'user' => $user,
	'goals' => $goals
]) ?>
<?php endforeach ?>