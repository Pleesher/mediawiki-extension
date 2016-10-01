<?php echo $user->getName() ?> has <?php echo $user->kudos ?> Kudos !

<h2>Unlocked achievements</h2>
<AchievementList user="<?php echo $user->getName() ?>" />

<?php if (count($closest_achievements) > 0): ?>
<h2>Closest Achievements</h2>
<?php foreach ($closest_achievements as $goal): ?>
<Goal code="<?php echo $goal->code ?>" perspective="<?php echo $user->getName() ?>" />
<?php endforeach ?>
<?php endif ?>
