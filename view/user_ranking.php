<ol>
	<?php foreach ($users as $user): ?>
	<li><a href="<?php echo $h->pageUrl('User:' . $user->getName()) ?>"><?php echo $user->getName() ?></a> - <?php echo $user->kudos ?> Kudos</li>
	<?php endforeach ?>
</ol>