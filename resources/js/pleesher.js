$(function() {
	$('a[data-redirect]').on('click', function(e) {
		e.preventDefault();
		
		$.get($(this).prop('href')).done(function(e) {
//			window.location = $(this).data('redirect');
		});
	});
});