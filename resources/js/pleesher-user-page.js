$(function() {
	$('[data-user-page-contents]').each(function() {
		var $outputArea = $(this);
		$.getJSON(mw.util.wikiScript('api') + '?action=pleesher.get_user_page_output&username=' + $(this).data('user-page-contents'), {format: 'json'}).success(function(result) {
			$outputArea.html(result.output);
		});
	});
});