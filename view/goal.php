<?php if (!isset($actions)) $actions = [] ?>

<article id="goal-<?php echo $goal->code ?>" style="background-color:gold; padding:0 5px 5px 5px; margin:5px;">
	<h1><a href="<?php echo $h->pageUrl('Special:AchievementDetails/' . $h->slugifyUrlId($goal->id, $goal->title)) ?>"><?php echo htmlspecialchars($goal->title) ?></a>
	- <span class="kudos"><?php echo $goal->kudos ?> Kudos</span>
	<?php if (!empty($goal->achieved)): ?>
	<span style="color:green; margin-left:10px">✓</span>
	<?php elseif (isset($goal->progress)): ?>
	<progress title="<?php echo $goal->progress->current ?> / <?php echo $goal->progress->target ?>" max="<?php echo $goal->progress->target ?>"
		value="<?php echo $goal->progress->current ?>" style="margin-left:10px"></progress>
	<?php endif ?>
	</h1>

	<?php echo htmlspecialchars($goal->short_description) ?>

	<?php if (isset($user) && $goal->achieved && in_array('revoke', $actions) && !PleesherExtension::isDisabled()): ?>
	<a data-redirect="self" href="<?php echo $h->actionUrl('pleesher.revoke_achievement', ['user_name' => $user->getName(), 'goal_id' => $goal->id, 'duration' => 'forever']) ?>"
		data-confirm="<?php echo $h->text('pleesher.revoke_achievement.confirm') ?>">Revoke</a>
	<?php endif ?>
</article>