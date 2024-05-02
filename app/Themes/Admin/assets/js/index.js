/* global Halcyon */ // core.js

/**
 * Toast alert
 *
 * @param {string} type
 * @param {string} message
 * @param {bool} animate
 * @param {bool} autoRemove
 * @return {void}
 */
function notify(type, message, animate, autoRemove) {
	if (typeof animate == 'undefined') {
		animate = true;
	}

	if (typeof autoRemove == 'undefined') {
		autoRemove = true;
	}

	let container = document.getElementById('toasts');

	if (!container) {
		container = document.createElement('div');
		container.id = 'toasts';
		container.classList.add('toasts');
		container.classList.add('bottomright');
		document.querySelector('body').append(container);
	}

	/*
	<!-- Example of the DOM we're creating for a notification -->
	<div class="toast" role="alert" aria-atomic="true" aria-live="assertive">
		<div class="d-flex">
			<button class="btn-close close" aria-label="Close">
				<span class="visually-hidden" aria-hidden="true">&times;</span>
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

	let node = document.createElement('div');
	node.classList.add('toast');
	node.classList.add('show');
	node.classList.add('alert-' + type);
	node.setAttribute('role', 'alert');
	node.setAttribute('aria-atomic', 'true');
	node.setAttribute('aria-live', 'assertive');

	let close = document.createElement('button');
	close.classList.add('btn-close');
	close.classList.add('close');
	close.setAttribute('aria-label', 'Close');
	close.innerHTML = '<span class="visually-hidden" aria-hidden="true">&times;</span>';
	close.addEventListener('click', function () {
		// animate when closing; then remove the DOM element entirely
		var n = this.parentNode.parentNode;
		n.remove();
	});

	var flex = document.createElement('div');
	flex.classList.add('d-flex');

	var body = document.createElement('div');
	body.classList.add('toast-body');

	var msg = document.createElement('div');
	msg.classList.add('message')
	msg.innerHTML = message;

	var pC = document.createElement('div');
	pC.classList.add('progressContainer');

	var p = document.createElement('div');
	p.classList.add('progress');

	// if animation is turned on
	if (animate === true) {
		node.classList.add("n-animate-in");
	}

	if (autoRemove === true) {
		if (animate === true) {
			//add the animation class to the notification...
			node.classList.add("n-animate");
		}
		//...and the progress bar
		p.classList.add('progress-animate');
		//ensure the node removes itself after the animation finishes
		['animationend', 'webkitAnimationEnd', 'oAnimationEnd', 'MSAnimationEnd'].forEach(function (evt) {
			node.addEventListener(evt, function () {
				this.remove();
			});
		});
	}

	body.append(msg);

	pC.append(p);

	flex.append(body);
	flex.append(close);
	node.append(flex);
	node.append(pC);

	container.append(node);
}

function getOffsetTop(element) {
	if (!element) {
		return 0;
	}
	return getOffsetTop(element.offsetParent) + element.offsetTop;
};

document.addEventListener('DOMContentLoaded', function () {
	document.getElementsByTagName('html')[0].classList.remove('no-js');

	document.querySelector('body').addEventListener('scroll', function () {
		var y = this.scrollTop,
			el = document.getElementById('toolbar-box');

		if (!el) {
			return;
		}

		if (y > 0) {
			el.classList.add('scrolled');
		} else {
			el.classList.remove('scrolled');
		}
	});

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
		var t = getOffsetTop(el) + el.offsetHeight,
			h = window.innerHeight;
		if (t > h) {
			el.classList.add('drop-up');
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
				type = el.classList.contains('alert-error') ? 'danger' : type;
				type = el.classList.contains('alert-danger') ? 'danger' : type;
				type = el.classList.contains('alert-info') ? 'info' : type;
				type = el.classList.contains('alert-success') ? 'success' : type;

				notify(type, el.innerHTML);
			});
			msg.innerHTML = '';
		}
	});

	document.dispatchEvent(new Event('renderMessages'));
});
