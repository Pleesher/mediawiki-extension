<?php echo $user->getName() ?> has <?php echo $user->kudos ?> Kudos !

<h2>Unlocked achievements</h2>
<AchievementList user="<?php echo $user->getName() ?>" />
