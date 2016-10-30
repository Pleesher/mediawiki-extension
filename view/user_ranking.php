<style type="text/css">
.liquigoalsTable th, .liquigoalsTable td { padding: 5px; }
</style>

<table class="liquigoalsTable">
	<thead>
	<tr>
		<th>#</th>
		<th>Name</th>
		<th>Achievements completed</th>
		<th>Kudos</th>
	</tr>
	</thead>
	<tbody>
		<?php foreach ($users as $user): ?>
		<tr>
			<td><?php echo $user->rank ?></td>
			<td><a href="<?php echo $h->pageUrl('User:' . $user->getName()) ?>"><?php echo $user->getName() ?></a></td>
			<td><?php echo $user->achievement_count ?></td>
			<td><?php echo $user->kudos ?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>