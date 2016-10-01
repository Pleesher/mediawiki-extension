<?php echo $user->getName() ?> has <?php echo $user->kudos ?> Kudos !

<h2>Unlocked achievements</h2>
<AchievementList user="<?php echo $user->getName() ?>" />

<h2>Closest Achievements</h2>
<?php foreach (PleesherExtension::getClosestAchievements($user->getId(), 3) as $goal): ?>
<Goal code="<?php echo $goal->code ?>" perspective="<?php echo $user->getName() ?>" />
<br>
<?php endforeach ?>
