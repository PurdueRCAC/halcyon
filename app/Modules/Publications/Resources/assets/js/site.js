
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

	var sels = new Array(), sel;
	var tag = document.getElementById('field-tags');
	if (tag) {
		sel = new TomSelect(tag, {
			valueField: 'slug',
			labelField: 'slug',
			searchField: ['name', 'slug'],
			plugins: ['remove_button'],
			persist: false,
			// Fetch remote data
			load: function (query, callback) {
				var url = tag.getAttribute('data-api') + '?api_token=' + document.querySelector('meta[name="api-token"]').getAttribute('content') + '&search=' + encodeURIComponent(query);

				fetch(url)
					.then(response => response.json())
					.then(json => {
						callback(json.data);
					}).catch(() => {
						callback();
					});
			}
		});
		sel.on('change', function () {
			if (tag.classList.contains('filter-submit')) {
				tag.closest('form').submit();
			}
		});
		sels.push(sel);
	}

	document.querySelectorAll('.btn-delete').forEack(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			if (confirm(el.getAttribute('data-confirm'))) {
				fetch(el.getAttribute('data-api'), {
					method: 'DELETE',
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					}
				})
					.then(function (response) {
						if (response.ok) {
							window.location.reload(true);
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
						alert(error);
					});
			}
		});
	});

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
					filelist.innerHTML = msg + filelist.innerHTML;
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
					//label.textContent = files.length > 1
					//	? (input.getAttribute('data-multiple-caption') || '').replace('{count}', files.length)
					//	: files[0].name;
				}/*,
				triggerFormSubmit = function () {
					var event = document.createEvent('HTMLEvents');
					event.initEvent('submit', true, false);
					form.dispatchEvent(event);
				}*/;

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
					//showFiles(droppedFiles);
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

});
