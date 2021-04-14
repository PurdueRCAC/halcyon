// Only define the Halcyon namespace if not defined.
if (typeof(Halcyon) === 'undefined') {
	var Halcyon = {};
}

/**
 * Check if an element has the specified class name
 *
 * @param   el         The element to test
 * @param   className  The class to test for
 * @return  bool
 */
Halcyon.hasClass = function (el, className) {
	return el.classList ? el.classList.contains(className) : new RegExp('\\b' + className + '\\b').test(el.className);
}

/**
 * Add a class to an element
 *
 * @param   el         The element to add the class to
 * @param   className  The class to add
 * @return  bool
 */
Halcyon.addClass = function (el, className) {
	if (el.classList) {
		el.classList.add(className);
	} else if (!Halcyon.hasClass(el, className)) {
		el.className += ' ' + className;
	}
}

/**
 * Remove a class from an element
 *
 * @param   el         The element to remove the class from
 * @param   className  The class to remove
 * @return  bool
 */
Halcyon.removeClass = function (el, className) {
	if (el.classList) {
		el.classList.remove(className);
	} else {
		el.className = el.className.replace(new RegExp('\\b' + className + '\\b', 'g'), '');
	}
}

/**
 * Attach event handlers
 */
document.addEventListener('DOMContentLoaded', function () {
	var i;

	// Open/close endpoint blocks
	var summary = document.getElementsByClassName('opblock-summary');
	for (i = 0; i < summary.length; i++) {
		summary[i].addEventListener('click', function (e) {
			e.preventDefault();

			if (Halcyon.hasClass(this.parentNode, 'is-open')) {
				Halcyon.removeClass(this.parentNode, 'is-open');
			} else {
				Halcyon.addClass(this.parentNode, 'is-open');
			}
		});
	}

	// Open/close doc folders
	var nodes = document.getElementsByClassName('node');
	for (i = 0; i < nodes.length; i++) {
		nodes[i].addEventListener('click', function (e) {
			e.preventDefault();

			if (Halcyon.hasClass(this.parentNode, 'active')) {
				Halcyon.removeClass(this.parentNode, 'active');
			} else {
				// De-activate any other menu items and sections
				var nds = document.getElementsByClassName('node');
				for (i = 0; i < nds.length; i++) {
					Halcyon.removeClass(nds[i].parentNode, 'active');
				}
				var eps = document.getElementsByClassName('docs-collection');
				for (i = 0; i < eps.length; i++) {
					Halcyon.addClass(eps[i], 'hide');
				}

				Halcyon.addClass(this.parentNode, 'active');

				// Set URL hash
				window.location.hash = this.getAttribute('href');

				// Show the targetted section
				var section = document.querySelector(this.getAttribute('href'));
				Halcyon.removeClass(section, 'hide');

				// Close the menu if open (mobile)
				var menu = this.parentNode.parentNode;
				if (Halcyon.hasClass(menu, 'active')) {
					Halcyon.removeClass(menu, 'active');
				}
			}
		});
	}

	// Open/close menu
	var menu = document.getElementsByClassName('navbar-toggle');
	for (i = 0; i < menu.length; i++) {
		menu[i].addEventListener('click', function (e) {
			e.preventDefault();

			var menu = document.querySelector(this.getAttribute('href'));

			if (Halcyon.hasClass(menu, 'active')) {
				Halcyon.removeClass(menu, 'active');
			} else {
				Halcyon.addClass(menu, 'active');
			}
		});
	}

	// Activate section and menu if URL contains a hash
	var hash = window.location.hash;
	if (hash) {
		if (hash.indexOf('-') !== -1) {
			hash = hash.split('-')[0];
		}
		var section = document.querySelector(hash);

		if (section) {
			Halcyon.removeClass(section, 'hide');
		}

		var nds = document.getElementsByClassName('node');
		for (i = 0; i < nds.length; i++) {
			if (nds[i].getAttribute('href') == hash) {
				Halcyon.addClass(nds[i].parentNode, 'active');
				break;
			}
		}
	}
});
