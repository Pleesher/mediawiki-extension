$(function () {
	$.getJSON(mw.util.wikiScript('api') + '?action=pleesher.check_achievements', {format: 'json'}).success(function () {
		$.getJSON(mw.util.wikiScript('api') + '?action=pleesher.notifications', {format: 'json'}).success(function (result) {
			if (!jQuery.isEmptyObject(result.notifications)) {
				var event_ids = [];
				for (var i in result.notifications) {
					var notification = result.notifications[i];
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

	$('[data-user-page-contents]').each(function() {
		var $outputArea = $(this);
		$.getJSON(mw.util.wikiScript('api') + '?action=pleesher.get_user_page_output&username=' + $(this).data('user-page-contents'), {format: 'json'}).success(function(result) {
			$outputArea.html(result.output);
		});
	});
});