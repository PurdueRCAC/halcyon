
document.addEventListener('DOMContentLoaded', function () {
	var alias = document.getElementById('field-alias');
	if (alias && !alias.value) {
		document.getElementById('field-name').addEventListener('keyup', function () {
			alias.value = this.value.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');
		});
		alias.addEventListener('keyup', function () {
			alias.value = this.value.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');
		});
	}

	document.querySelectorAll('.type-dependent').forEach(function (el) {
		el.classList.add('d-none');
	});

	document.querySelectorAll('[name="type_id"]').forEach(function (el) {
		el.addEventListener('change', function () {
			document.querySelectorAll('.type-dependent').forEach(function (dep) {
				dep.classList.add('d-none');
				if (dep.classList.contains('type-' + el.selectedOptions[0].getAttribute('data-alias'))) {
					dep.classList.remove('d-none');
				}
			});
		})

		document.querySelectorAll('.type-' + el.selectedOptions[0].getAttribute('data-alias')).forEach(function (dep) {
			dep.classList.remove('d-none');
		});
	});

	if (document.getElementById('upload')) {
		// feature detection for drag&drop upload
		var isAdvancedUpload = function () {
			var div = document.createElement('div');
			return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
		}();

		// applying the effect for every form
		var forms = document.querySelectorAll('.dropzone');
		Array.prototype.forEach.call(forms, function (form) {
			var input = form.querySelector('input[type="file"]'),
				//label = form.querySelector('label'),
				filelist = form.querySelector('.file-list'),
				droppedFiles = false,
				// output information
				output = function (msg) {
					filelist.innerHTML = msg + (input.getAttribute('multiple') ? filelist.innerHTML : '');
				},
				showFiles = function (files) {
					// process all File objects
					var i, f;
					for (i = 0; i < files.length; i++) {
						f = files[i];
						//parseFile(f);
						output(
							"<p>File information: <strong>" + f.name + "</strong> (" + f.size + " bytes)</p>"
						);
					}
				};

			// automatically submit the form on file select
			input.addEventListener('change', function (e) {
				showFiles(e.target.files);
			});

			// drag&drop files if the feature is available
			if (isAdvancedUpload) {
				form.classList.add('has-advanced-upload'); // letting the CSS part to know drag&drop is supported by the browser

				['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach(function (event) {
					form.addEventListener(event, function (e) {
						// preventing the unwanted behaviours
						e.preventDefault();
						e.stopPropagation();
					});
				});

				['dragover', 'dragenter'].forEach(function (event) {
					form.addEventListener(event, function () {
						form.classList.add('is-dragover');
					});
				});

				['dragleave', 'dragend', 'drop'].forEach(function (event) {
					form.addEventListener(event, function () {
						form.classList.remove('is-dragover');
					});
				});

				form.addEventListener('drop', function (e) {
					droppedFiles = e.target.files || e.dataTransfer.files; // the files that were dropped
					input.files = droppedFiles;
					showFiles(droppedFiles);
				});
			}

			// Firefox focus bug fix for file input
			input.addEventListener('focus', function () {
				input.classList.add('has-focus');
			});
			input.addEventListener('blur', function () {
				input.classList.remove('has-focus');
			});
		});
	}
});
