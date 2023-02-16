/**
 * List of resources
 *
 * @var  {array}
 */
var resourceArray = [];

/**
 * Toggle resource
 *
 * @param   {string}  name
 * @return  {void}
 */
function toggleResource(name) {
	// Remove resource if found.
	for (var i = 0; i < resourceArray.length; i++) {
		if (resourceArray[i] == name) {
			resourceArray.splice(i, 1);
			return;
		}
	}

	// Otherwise; add resource.
	resourceArray.push(name);
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.rssCustomize').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var txt = this.innerHTML;
			this.innerHTML = this.getAttribute('data-txt');
			this.setAttribute('data-txt', txt);

			document.querySelectorAll('.rssCheckbox').forEach(function (item) {
				item.classList.toggle('d-none');
			});
			document.querySelectorAll('.rssCheckboxInfo').forEach(function (item) {
				item.classList.toggle('d-none');
			});
			document.querySelectorAll('.customRSS').forEach(function (item) {
				item.classList.toggle('d-none');
			});
		});
	});

	document.querySelectorAll('.rssCheckbox').forEach(function (el) {
		el.addEventListener('change', function () {
			toggleResource(this.value);

			var url = "";
			for (var i = 0; i < resourceArray.length; i++) {
				url += resourceArray[i];
				if (i != resourceArray.length - 1) {
					url += ",";
				}
			}

			if (resourceArray.length == 0) {
				url = "#";
			}

			document.getElementById('customRSS').href = url;
		});
	});
});
