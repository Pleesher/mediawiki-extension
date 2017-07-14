<p><?php echo $user->getName() ?> has <?php echo $user->kudos ?> Kudos</p>
<p><?php echo $user->getName() ?> has completed <?php echo $achievement_count ?> achievement(s) out of <?php echo $goal_count ?></p>

<h2>Unlocked achievements</h2>
<?php echo PleesherExtension::viewAchievements(null, ['user' => $user->getName()], null, null); ?>

<?php if (count($closest_achievements) > 0): ?>
<h2>Achievements in progress</h2>
<?php foreach ($closest_achievements as $goal): ?>
	<?php echo PleesherExtension::viewGoal(null, ['code' => $goal->code, 'perspective' => $user->getName()], null, null); ?>
<?php endforeach ?>
<?php endif ?>

<?php echo PleesherExtension::render('_pleesher_ad') ?>