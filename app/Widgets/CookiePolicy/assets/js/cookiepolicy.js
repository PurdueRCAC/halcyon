/**
 * Cookie Policy scripts
 */

jQuery(document).ready(function($){

	$('body').addClass('has-eprivacy-warning');

	// Add an event to close the notice
	$('.eprivacy-close').on('click', function(e) {
		e.preventDefault();

		var id = $($(this).parent().parent()).attr('id'),
			days = $(this).attr('data-duration');

		$($(this).parent().parent()).hide();

		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));

		document.cookie = id + '=acknowledged; expires=' + date.toGMTString() + ';';

		$('body').removeClass('has-eprivacy-warning');
	});

});