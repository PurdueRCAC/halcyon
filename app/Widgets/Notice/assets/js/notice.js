
document.addEventListener('DOMContentLoaded', function () {
	if (!document.querySelector('html').classList.contains('has-notice')) {
		document.querySelector('html').classList.add('has-notice');
	}

	document.querySelectorAll('.notice .close').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var id = this.parentNode.getAttribute('id'),
				days = this.getAttribute('data-duration');

			this.parentNode.parentNode.classList.add('d-none');

			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));

			document.cookie = id + '=closed; expires=' + date.toGMTString() + ';';
		});
	});
});