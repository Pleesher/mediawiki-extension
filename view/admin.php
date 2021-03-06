<?php if ($pleesher_disabled): ?>
<h3>Achievements currently disabled</h3>

<div class="pleesher-admin-block">
	<p class="pleesher-admin-block-title pleesher-admin-block-title-good"
		><a data-redirect="self" data-confirm="Are you sure?"
		href="<?php echo $h->actionUrl('pleesher.set_setting', ['key' => 'disabled', 'value' => 0]) ?>"
		>Re-enable achievements</a></p>
	<p><strong>Use this to re-enable Pleesher's achievements functionality.</strong></p>
</div>

<?php else: ?>

<h3>General</h3>

<?php // FIXME: the following block should be in the LP-specific extension ?>
<div class="pleesher-admin-block">
	<p class="pleesher-admin-block-title"
		><a target="_blank" href="https://pleesher.com/Liquipedia"
		>Manage Liquipedia Goals</a> - Ask PoP for website account's username/password
	<p>Use this to add or remove goals.</p>
	<p>Checker functions needs to be added to this wiki before new goals become available. See with PoP or FO-nTTaX.
</div>

<div class="pleesher-admin-block">
	<p class="pleesher-admin-block-title"
		><a data-redirect="self" data-confirm="Are you sure this is required?"
		href="<?php echo $h->actionUrl('pleesher.refresh_cache', ['refresh' => 'goals']) ?>"
		>Refresh goals cache</a></p>
	Use this after adding/changing a goal from the Pleesher.com admin (see above).
</div>

<h3>Advanced</h3>

<div class="pleesher-admin-block">
	<p class="pleesher-admin-block-title">Refresh specific user's cache</p>
	Use this when something seems wrong with speficic users. It will refresh all cache related to them.

	<form method="get" action="<?php echo $h->baseActionUrl() ?>" style="margin-top:10px">
		<p>
			<select name="user_name">
				<?php foreach ($users as $user): ?>
				<option value="<?php echo $user->getName() ?>"><?php echo $user->getName() ?></option>
				<?php endforeach ?>
			</select>
			<input type="hidden" name="action" value="pleesher.refresh_cache" />
			<input type="hidden" name="refresh" value="all" />
			<input type="submit" value="Refresh" />
		</p>
	</form>

	<p><strong>This is faster than below actions. Use it whenever possible when the issue doesn't look global.</strong></p>
</div>

<div class="pleesher-admin-block">
	<p class="pleesher-admin-block-title"
		><a data-redirect="self" data-confirm="Are you sure this is required?"
		href="<?php echo $h->actionUrl('pleesher.refresh_cache', ['refresh' => 'achievements']) ?>"
		>Refresh achievements cache</a></p>
	Use this only when either:
	<ul>
		<li>The way to check an existing achievement has been changed</li>
		<li>Something seems wrong with some people's achievements (progress, status, etc.)</li>
	</ul>
	<p><strong>This will recompute achievements for all users, and will take quite some time.</strong></p>
</div>

<div class="pleesher-admin-block">
	<p class="pleesher-admin-block-title"
		><a data-redirect="self" data-confirm="Are you sure this is required?"
		href="<?php echo $h->actionUrl('pleesher.refresh_cache', ['refresh' => 'users']) ?>"
		>Refresh users cache</a></p>
	Use this if anyone's Kudos seems wrong (in general it should never be used, but it may be useful in case of a bug).
</div>

<div class="pleesher-admin-block">
	<p class="pleesher-admin-block-title"
		><a data-redirect="self" data-confirm="Are you sure this is required?"
		href="<?php echo $h->actionUrl('pleesher.refresh_cache', ['refresh' => 'all']) ?>"
		>Refresh whole cache</a></p>
	<p>Use this to remove all cache and fetch/check everything again.</p>
	<p><strong>Use with caution: this will compute everything from scratch for every user, and will take quite some time.</strong></p>
</div>

<div class="pleesher-admin-block">
	<p class="pleesher-admin-block-title pleesher-admin-block-title-bad"
		><a data-redirect="self" data-confirm="Are you sure this is required?"
		href="<?php echo $h->actionUrl('pleesher.set_setting', ['key' => 'disabled', 'value' => 1]) ?>"
		>Disable achievements</a></p>
	<p>Use this to temporarily disable Pleesher's achievements functionality.</p>

	<p>Some data will still be available read-only (from cache), when available.</p>
</div>
<?php endif ?>