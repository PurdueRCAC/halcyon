
jQuery(document).ready(function($){
	if (!$('html').hasClass('has-notice')) {
		$('html').addClass('has-notice');
	}

	$('.notice .close').on('click', function(e) {
		e.preventDefault();

		var id = $($(this).parent().parent()).attr('id'),
			days = $(this).attr('data-duration');

		$($(this).parent().parent()).slideUp();

		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));

		document.cookie = id + '=closed; expires=' + date.toGMTString() + ';';
	});
});