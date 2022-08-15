/* global $ */ // jquery.js
/* global jQuery */ // jquery.js
/* global Halcyon */ // core.js

/*
USAGE:

	$.growl(title, msg);

OPTIONS:

	animate: Animate the slide in/out of the message
	autoRemove: Automatically temove the message after a period of time
*/

(function ($) {
	$.growl = function (type, message, animate, autoRemove) {
		notify(type, message, animate, autoRemove);
	}
	$.growl.version = "1.0.2";

	function create(rebuild) {
		var instance = document.getElementById('toasts');

		if (!instance || rebuild) {
			instance = $(jQuery.growl.settings.dockTemplate)
				.attr('id', 'toasts')
				.addClass('toasts')
				.addClass(jQuery.growl.settings.position);
			$('body').append(instance);
		} else {
			instance = $(instance);
		}

		return instance;
	}

	function notify(type, message, animate, autoRemove) {
		var container = create();

		/*
		<!-- Example of the DOM we're creating for a notification -->
		<div class="toast" role="alert" aria-atomic="true" aria-live="assertive">
			<div class="d-flex">
				<button class="btn-close close" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<div class="toast-body">
					<div class="message">
						Message here
					</div>
				</div>
			</div>
			<div class="progressContainer">
				<div class="progress"></div>
			</div>
		</div>
		*/
		var node = $('<div/>')
			.addClass('toast')
			.addClass('alert-' + type)
			.attr('role', 'alert')
			.attr('aria-atomic', 'true')
			.attr('aria-live', 'assertive');

		var close = $('<button/>')
			.addClass('btn-close')
			.addClass('close')
			.attr('aria-label', 'Close')
			//.attr('data-bs-dismiss', 'toast')
			.html('<span aria-hidden="true">&times;</span>');

		var flex = $('<div/>')
			.addClass('d-flex');

		var body = $('<div/>')
			.addClass('toast-body');

		var msg = $('<div/>')
			.addClass('message')
			.html(message);

		// add close-notification click functionality
		close.off('click').on('click', function () {
			// animate when closing; then remove the DOM element entirely
			var n = $(this).parent().parent();
			n.animate({ left: '-=50px', opacity: "0" }, "fast", function () { n.remove(); });
		});

		var pC = $('<div/>')
			.addClass('progressContainer');

		var p = $('<div/>')
			.addClass('progress');

		if (typeof animate == 'undefined') {
			animate = jQuery.growl.settings.autoRemove;
		}

		if (typeof autoRemove == 'undefined') {
			autoRemove = jQuery.growl.settings.autoRemove;
		}

		//if animation is turned on
		if (animate === true) {
			node.addClass("n-animate-in");
		}

		if (autoRemove === true) {
			if (animate === true) {
				//add the animation class to the notification...
				node.addClass("n-animate");
				//...and the progress bar
				p.addClass('progress-animate');
				//ensure the node removes itself after the animation finishes
				node.on('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', function () { $(this).remove(); });
			} else {
				//...and the progress bar
				p.addClass('progress-animate');
				//ensure the node removes itself after the progress-bar animation finishes
				node.on('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', function () { $(this).remove(); });
			}
		}

		body.append(msg);

		pC.append(p);

		flex.append(body);
		flex.append(close);
		node.append(flex);
		node.append(pC);

		container.append(node);
	}

	// default settings
	$.growl.settings = {
		dockTemplate: '<div></div>',
		position: 'bottomright',
		animate: true,
		autoRemove: true,
		noticeElement: function (el) {
			$.growl.settings.noticeTemplate = $(el);
		}
	};
})(jQuery);

document.addEventListener('DOMContentLoaded', function () {
	document.getElementsByTagName('html')[0].classList.remove('no-js');

	document.querySelectorAll('.hamburger').forEach(function (item) {
		item.addEventListener('click', function (e) {
			e.preventDefault();

			var body = document.getElementsByTagName('body')[0];
			var mode = body.classList.contains('menu-open') ? 'closed' : 'open';

			body.classList.toggle('menu-open');

			fetch(btn.getAttribute('data-api'), {
				method: 'PUT',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				},
				body: JSON.stringify({
					facets: {
						'theme.admin.menu': mode
					}
				})
			})
				.then(function (response) {
					if (response.ok) {
						return;
					}
					return response.json().then(function (data) {
						var msg = data.message;
						if (typeof msg === 'object') {
							msg = Object.values(msg).join('<br />');
						}
						throw msg;
					});
				})
				.catch(function (error) {
					console.error('Error:', error);
				});
		});
	});

	// Mobile device fix
	/*document.querySelectorAll('#toolbar ul').forEach(function(el) {
		el.addEventListener('click', function(e) {
			this.parentNode.classList.toggle('active');
		});
	});*/

	document.querySelectorAll('.main-navigation li.node>a').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			this.parentNode.classList.toggle('active');
		});
	});

	document.querySelectorAll('.node>ul').forEach(function (el) {
		var node = $(el);
		var t = node.offset().top + node.height(),
			h = $(window).height();
		if (t > h) {
			node.addClass('drop-up');
		}
	});

	// Light/dark mode
	document.getElementById('mode').addEventListener('click', function (e) {
		e.preventDefault();

		var btn = this,
			mode = btn.getAttribute('data-mode');

		fetch(btn.getAttribute('data-api'), {
			method: 'PUT',
			headers: {
				'Content-Type': 'application/json',
				'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
			},
			body: JSON.stringify({
				facets: {
					'theme.admin.mode': mode
				}
			})
		})
			.then(function (response) {
				if (response.ok) {
					document.getElementsByTagName('html')[0].setAttribute('data-mode', mode);

					btn.setAttribute('data-mode', mode == 'dark' ? 'light' : 'dark');
					return;
				}
				return response.json().then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				});
			})
			.catch(function (error) {
				console.log(error);
				Halcyon.error(btn.getAttribute('data-error'));
			});
	});

	// Display system messages in Growl-like way
	document.addEventListener('renderMessages', function () {
		var msg = document.getElementById('system-messages');
		if (msg && msg.innerHTML.replace(/\s+/, '') != '') {
			msg.querySelectorAll('.alert').forEach(function (el) {
				var type = '';
				type = el.classList.contains('alert-warning') ? 'warning' : type;
				type = el.classList.contains('alert-danger') ? 'danger' : type;
				type = el.classList.contains('alert-info') ? 'info' : type;
				type = el.classList.contains('alert-success') ? 'success' : type;

				$.growl(type, el.innerHTML);
			});
			msg.innerHTML = '';
		}
	});

	document.dispatchEvent(new Event('renderMessages'));
});
