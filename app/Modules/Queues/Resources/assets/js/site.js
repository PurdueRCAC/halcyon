/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function() {
	var tabs = document.querySelectorAll('.nav-tabs a');

	for (i = 0; i < tabs.length; i++)
	{
		tabs[i].addEventListener('click', function (event) {
			event.preventDefault();

			NEWSToggle(this.getAttribute('href').replace('#', ''));
		});
	}
});
