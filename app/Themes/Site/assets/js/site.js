/* global $ */  // jquery.js

if (typeof (Halcyon) === 'undefined') {
	var Halcyon = {
		config: {}
	};
}

/**
 * Get a config value
 *
 * @param   {string}  key
 * @param   {mixed}   def
 * @return  {mixed}
 */
function config(key, def) {
	var result = key.split('.').reduce(function (obj, i, def) {
		return obj[i];
	}, Halcyon.config);
	if (typeof (result) === 'undefined') {
		result = def;
	}
	return result;
}

// define a couple global variables
if (typeof(base_url) === 'undefined') {
	var base_url = document.querySelector('meta[name="base-url"]').getAttribute('content');
}
var ROOT_URL = base_url + "/api/";

document.addEventListener('DOMContentLoaded', function () {
	var html = document.querySelector('html');
	html.classList.remove('no-js');
	html.classList.add('js');

	$('.editicon').tooltip({
		position: {
			my: 'center bottom',
			at: 'center top'
		},
		// When moving between hovering over many elements quickly, the tooltip will jump around
		// because it can't start animating the fade in of the new tip until the old tip is
		// done. Solution is to disable one of the animations.
		hide: false,
		items: "img[alt]",
		content: function () {
			return $(this).attr('alt');
		}
	});

	$('.tip').tooltip({
		position: {
			my: 'center bottom',
			at: 'center top'
		},
		// When moving between hovering over many elements quickly, the tooltip will jump around
		// because it can't start animating the fade in of the new tip until the old tip is
		// done. Solution is to disable one of the animations.
		hide: false
	});

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
			'Authorization': 'Bearer ' + $('meta[name="api-token"]').attr('content'),
		}
	});

	$('.date-pick').datepicker({ dateFormat: 'yy-mm-dd' });

	$('.navbar .dropdown').hover(
		function () {
			$(this).find('.dropdown-menu').first().stop(true, true).delay(10).slideDown();
		},
		function () {
			$(this).find('.dropdown-menu').first().stop(true, true).delay(10).slideUp();
		}
	);

	document.querySelectorAll('.navbar .dropdown > a').forEach(function (el) {
		el.addEventListener('click', function () {
			location.href = this.href;
		});
	});

	// Show a character counter for inputs with a maxlength
	document.querySelectorAll('[maxlength]').forEach(function (el) {
		if (el.getAttribute('data-counter') && el.getAttribute('data-counter') == 'false') {
			return;
		}
		var container = document.createElement('span');
		container.classList.add('char-counter-wrap');

		var counter = document.createElement('span');
		counter.classList.add('char-counter');

		if (el.getAttribute('id') != '') {
			counter.setAttribute('id', el.getAttribute('id') + '-counter');
		}

		if (el.parentNode.classList.contains('input-group')) {
			el.parentNode.insertBefore(container, el);
			container.appendChild(el);
			container.appendChild(counter);
		} else {
			el.parentNode.insertBefore(container, el);
			container.appendChild(el);
			container.appendChild(counter);
		}
		counter.innerHTML = el.value.length + ' / ' + el.getAttribute('maxlength');

		el.addEventListener('focus', function () {
			var container = this.closest('.char-counter-wrap');
			if (container) {
				container.classList.add('char-counter-focus');
			}
		});
		el.addEventListener('blur', function () {
			var container = this.closest('.char-counter-wrap');
			if (container) {
				container.classList.remove('char-counter-focus');
			}
		});
		el.addEventListener('keyup', function () {
			var chars = this.value.length;
			var counter = document.getElementById(this.getAttribute('id') + '-counter');
			if (counter) {
				counter.innerHTML = chars + ' / ' + this.getAttribute('maxlength');
			}
		});
	});
});
