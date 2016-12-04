$(function() {
	$('a[data-redirect]').on('click', function(e) {
		e.preventDefault();
		
		var redirectUrl = $(this).data('redirect');
		
		$.get($(this).prop('href')).done(function(result) {
			if (redirectUrl == 'self') {
				window.location.reload();
			}
			else {
				window.location = $(this).data('redirect');
			}
		});
	});
});