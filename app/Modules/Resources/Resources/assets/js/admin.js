/**
 * Remove unwanted characters from roles
 *
 * @return  {void}
 */
function formatName() {
	var val = this.value;

	val = val.toLowerCase()
		.replace(/\s+/g, '_')
		.replace(/[^a-z0-9-_]+/g, '');

	this.value = val;
}

/**
 * Set subresource name based on resource and cluster
 *
 * @return  {void}
 */
function setName() {
	var resource = document.querySelector('#assoc-resourceid').selectedOptions[0].text.replace(/(- )+/, '');
	var cluster = document.getElementById('field-cluster').value;
	document.getElementById('field-name').value = resource + "-" + cluster;
}

document.addEventListener('DOMContentLoaded', function () {
	// Asset
	var name = document.getElementById('field-name');
	if (name) {
		name.addEventListener('keyup', function () {
			var val = this.value;

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9-_]+/g, '');

			var rolename = document.getElementById('field-rolename');
			if (rolename) {
				rolename.value = val;
			}
			var listname = document.getElementById('field-listname');
			if (listname) {
				listname.value = val;
			}
		});
	}

	var rolename = document.getElementById('field-rolename');
	if (rolename) {
		rolename.addEventListener('keyup', formatName);
	}
	var listname = document.getElementById('field-listname');
	if (listname) {
		listname.addEventListener('keyup', formatName);
	}

	// Subresource
	var rid = document.getElementById('assoc-resourceid');
	if (rid) {
		rid.addEventListener('change', setName);
	}
	var cluster = document.getElementById('field-cluster');
	if (cluster) {
		cluster.addEventListener('change', setName);
	}

	var nodemem = document.getElementById('field-nodemem');
	if (nodemem) {
		nodemem.addEventListener('keyup', function () {
			this.value = this.value.toUpperCase().replace(/[^0-9]{1,4}[^PTGMKB]/g, '');
		});
	}
});
