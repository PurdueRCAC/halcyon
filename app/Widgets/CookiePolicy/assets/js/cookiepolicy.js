/**
 * Cookie Policy scripts
 */

document.addEventListener('DOMContentLoaded', function () {

	let bdy = document.querySelector('body');
	bdy.classList.add('has-eprivacy-warning');

	// Add an event to close the notice
	document.querySelectorAll('.eprivacy-close').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			let id = this.getAttribute('data-target'),
				days = this.getAttribute('data-duration'),
				date = new Date();

			date.setTime(date.getTime()+(days*24*60*60*1000));

			document.cookie = id + '=acknowledged; expires=' + date.toGMTString() + ';';

			bdy.classList.remove('has-eprivacy-warning');

			document.getElementById(id).remove();
		});
	});

});