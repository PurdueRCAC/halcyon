/**
 * Remove unwanted characters from roles
 *
 * @return  {void}
 */
function formatName() {
	let val = this.value;

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
	const resource = document.querySelector('#assoc-resourceid').selectedOptions[0].text.replace(/(- )+/, '');
	const cluster = document.getElementById('field-cluster').value;
	document.getElementById('field-name').value = resource + "-" + cluster;
}

document.addEventListener('DOMContentLoaded', function () {
	// Asset
	const rolename = document.getElementById('field-rolename');
	if (rolename) {
		rolename.addEventListener('keyup', formatName);
	}
	const listname = document.getElementById('field-listname');
	if (listname) {
		listname.addEventListener('keyup', formatName);
	}

	const name = document.getElementById('field-name');
	if (name) {
		name.addEventListener('keyup', function () {
			let val = this.value;

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9-_]+/g, '');

			//var rolename = document.getElementById('field-rolename');
			if (rolename) {
				rolename.value = val;
			}
			//var listname = document.getElementById('field-listname');
			if (listname) {
				listname.value = val;
			}
		});
	}

	const type = document.getElementById('field-resourcetype');
	if (type) {
		type.addEventListener('change', function () {
			document.querySelectorAll('.type-dependent').forEach(function (el) {
				if (el.classList.contains('type-' + type.value)) {
					el.classList.remove('d-none');
				} else {
					el.classList.add('d-none');
				}
			});
		});
	}

	// Subresource
	const rid = document.getElementById('assoc-resourceid');
	if (rid) {
		rid.addEventListener('change', setName);
	}
	const cluster = document.getElementById('field-cluster');
	if (cluster) {
		cluster.addEventListener('change', setName);
	}

	const nodemem = document.getElementById('field-nodemem');
	if (nodemem) {
		nodemem.addEventListener('keyup', function () {
			this.value = this.value.toUpperCase().replace(/[^0-9]{1,4}[^PTGMKB]/g, '');
		});
	}
});
