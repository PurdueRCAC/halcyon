/*
Scripts for the home page
*/

document.addEventListener('DOMContentLoaded', function () {
	window.addEventListener('scroll', function () {
		var y = window.pageYOffset;

		document.querySelectorAll('.hero').forEach(function(el){
			el.style.backgroundPosition = 'right -' + (y / 3) + 'px';
		});
	});
});
