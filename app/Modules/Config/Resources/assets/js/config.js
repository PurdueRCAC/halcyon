
jQuery(document).ready(function($){
	$('#permissions-rules').accordion({
		heightStyle: 'content',
		collapsible: true,
		active: false
	});
	$('#permissions-rules .stop-propagation').on('click', function(e) {
		e.stopPropagation();
	});
});
