// Only define the Halcyon namespace if not defined.
if (typeof(Halcyon) === 'undefined') {
	var Halcyon = {};
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

			if (this.parentNode.classList.contains('is-open')) {
				this.parentNode.classList.remove('is-open');
			} else {
				this.parentNode.classList.add('is-open');
			}
		});
	}

	// Open/close doc folders
	var nodes = document.getElementsByClassName('node');
	for (i = 0; i < nodes.length; i++) {
		nodes[i].addEventListener('click', function (e) {
			e.preventDefault();

			if (this.parentNode.classList.contains('active')) {
				this.parentNode.classList.remove('active');
			} else {
				// De-activate any other menu items and sections
				var nds = document.getElementsByClassName('node');
				for (i = 0; i < nds.length; i++) {
					nds[i].parentNode.classList.remove('active');
				}
				var eps = document.getElementsByClassName('docs-collection');
				for (i = 0; i < eps.length; i++) {
					eps[i].classList.add('hide');
				}

				this.parentNode.classList.add('active');

				// Set URL hash
				window.location.hash = this.getAttribute('href');

				// Show the targetted section
				var section = document.querySelector(this.getAttribute('href'));
				section.classList.remove('hide');

				// Close the menu if open (mobile)
				var menu = this.parentNode.parentNode;
				if (menu.classList.contains('active')) {
					menu.classList.remove('active');
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

			if (menu.classList.contains('active')) {
				menu.classList.remove('active');
			} else {
				menu.classList.add('active');
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
			var eps = document.getElementsByClassName('docs-collection');
			for (i = 0; i < eps.length; i++) {
				eps[i].classList.add('hide');
			}
			section.classList.remove('hide');
		}

		var nds = document.getElementsByClassName('node');
		for (i = 0; i < nds.length; i++) {
			if (nds[i].getAttribute('href') == hash) {
				nds[i].parentNode.classList.add('active');
				break;
			}
		}
	}
});
