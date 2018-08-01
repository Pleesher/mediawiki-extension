$(function () {
	$(document).on('click', 'a[data-confirm]', function(e) {
		if (!confirm($(this).data('confirm'))) {
			$(this).data('prevented', '1');
			e.preventDefault();
		}
	});

	$(document).on('click', 'a[data-redirect]', function(e) {
		e.preventDefault();

		if ($(this).data('prevented') === '1') {
			$(this).removeData('prevented');
			return;
		}

		var redirectUrl = $(this).data('redirect');

		$(this).addClass('pleesher-loading');
		$.get($(this).prop('href')).done(function(result) {
			$(this).removeClass('pleesher-loading');
			if (redirectUrl === 'self') {
				window.location.reload();
			}
			else {
				window.location = $(this).data('redirect');
			}
		});
	});

	mw.loader.using('toastr').then(function() {
		$.getJSON(mw.util.wikiScript('api') + '?action=pleesher.check_achievements', {format: 'json'}).success(function () {
			$.getJSON(mw.util.wikiScript('api') + '?action=pleesher.notifications', {format: 'json'}).success(function (result) {
				if (!jQuery.isEmptyObject(result.notifications)) {
					var event_ids = [];
					for (var i in result.notifications) {
						var notification = result.notifications[i];
						toastr.options.timeOut = 12000;
						toastr.success(
							'<a href="' + mw.util.getUrl('Special:Achievements') + '#goal-' + notification.goal.code + '">' + result.notifications[i].goal.title + '</a>',
							mw.msg('pleesher.achievement_unlocked'));
						event_ids.push(notification.id);
					}
					$.getJSON(mw.util.wikiScript('api') + '?action=pleesher.mark_notifications_read', {
						event_ids: event_ids.join('|'),
						format: 'json'
					});
				}
			});
		});
	});
});