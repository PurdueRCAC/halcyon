
/* global $ */ // jquery.js

document.addEventListener('DOMContentLoaded', function () {
	var importorders = document.getElementById('import-orders');
	if (importorders) {
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

	if ($('.datatable').length) {
		$.fn.dataTable.render.ellipsis = function (cutoff) {
			return function (data, type) { //data, type, row
				return type === 'display' && data.length > cutoff ? data.substr(0, cutoff) + '…' : data;
			}
		};

		$('.datatable').DataTable({
			pageLength: 20,
			pagingType: 'numbers',
			info: false,
			ordering: false,
			lengthChange: false,
			scrollX: true,
			//autoWidth: false,
			language: {
				searchPlaceholder: "Filter rows...",
				search: "_INPUT_",
			},
			fixedColumns: {
				leftColumns: 1
			},
			columnDefs: [{
				targets: [-1, -2],
				render: $.fn.dataTable.render.ellipsis(14)
			}],
			initComplete: function () {
				$($.fn.dataTable.tables(true)).css('width', '100%');
			}
		});
	}
});
