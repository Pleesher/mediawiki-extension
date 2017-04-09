$(function() {
	$('a[data-confirm]').on('click', function(e) {
		if (!confirm($(this).data('confirm'))) {
			$(this).data('prevented', '1');
			e.preventDefault();
		}
	});
	
	$('a[data-redirect]').on('click', function(e) {
		e.preventDefault();
		
		if ($(this).data('prevented') == '1') {
			$(this).removeData('prevented');
			return;
		}
		
		var redirectUrl = $(this).data('redirect');
		
		$(this).addClass('pleesher-loading');
		$.get($(this).prop('href')).done(function(result) {
			$(this).removeClass('pleesher-loading');
			if (redirectUrl == 'self') {
				window.location.reload();
			}
			else {
				window.location = $(this).data('redirect');
			}
		});
	});
});