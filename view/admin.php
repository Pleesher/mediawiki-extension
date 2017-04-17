<?php if ($pleesher_disabled): ?>
<h3>Achievements currently disabled</h3>

<div class="pleesher-admin-block">
	<p class="pleesher-admin-block-title pleesher-admin-block-title-good"
		><a data-redirect="self" data-confirm="Are you sure?"
		href="<?php echo $h->actionUrl('pleesher.set_setting', ['key' => 'disabled', 'value' => 0]) ?>"
		>Re-enable Achievements</a></p>
	<p><strong>Use this to enable Pleesher's achievements functionality back.</strong></p>
</div>

<?php else: ?>

<h3>General</h3>

<div class="pleesher-admin-block">
	<p class="pleesher-admin-block-title"
		><a target="_blank" href="http://test.develop.pleesher.com/Liquipedia"
		>Manage Liquipedia Goals</a> - Ask PoP for website account's username/password
	<p>Use this to add or remove goals.</p>
	<p>Checker functions needs to be added to this wiki before new goals become available. See with PoP or fo-nttax.
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
		>Disable Achievements</a></p>
	<p>Use this to temporarily disable Pleesher's achievements functionality.</p>

	<p>Everything will still be available read-only (from cache)</p>
</div>
<?php endif ?>