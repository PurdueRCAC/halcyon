/* global $ */  // jquery.js

$(document).ready(function() {
	$(window).on('scroll', function(){
		// what the y position of the scroll is
		var y = $(window).scrollTop();
		//$('.hero').css('background-position', 'right ' + ((y / $(window).height()) * 100) + 'px top 0');
		$('.hero').css('background-position', 'right -' + (y / 3) + 'px')
	});
});
