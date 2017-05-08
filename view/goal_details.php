<?php echo PleesherExtension::render('goal', [
	'user' => isset($user) ? $user : null,
	'goal' => $goal
]) ?>

<?php if ($goal->description): ?>
<h2><?php echo $h->text('pleesher.goal.description.title') ?></h2>
<p><?php echo nl2br($goal->description) ?></p>
<?php endif ?>

<?php if (count($achievers) > 0): ?>
<h2><?php echo $h->text('pleesher.goal.achievers.title') ?></h2>
<ul>
	<?php foreach ($achievers as $achiever): ?>
	<li><a href="<?php echo $h->pageUrl('User:' . $achiever->getName()) ?>"><?php echo $achiever->getName() ?></a></li>
	<?php endforeach ?>
</ul>
<?php endif ?>