/* global $ */ // jquery.js
/* global TomSelect */ // vendor/tom-select/js/tom-select.complete.min.js

/**
 * Create new group
 *
 * @return  {void}
 */
function CreateNewGroup() {
	var input = document.getElementById("new_group_input"),
		name = input.value;

	if (!name) {
		document.getElementById('new_group_action').innerHTML = 'Please enter a group name';
		return;
	}

	var post = JSON.stringify({
		'name': name,
		'userid': input.getAttribute('data-userid')
	});

	fetch(input.getAttribute('data-api'), {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
		},
		body: post
	})
		.then(function (response) {
			document.getElementById(document.getElementById('new_group_btn').getAttribute('data-indicator')).classList.add('hide');

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
			var err = document.getElementById('new_group_action');
			err.classList.remove('hide');
			err.innerHTML = error;

			document.getElementById('new_group_btn').disabled = false;
		});
}

/**
 * Unix base groups
 *
 * @const
 * @type  {array}
 */
var BASEGROUPS = Array('', 'data', 'apps');

/**
 * Create UNIX group
 *
 * @param   {integer}  num    index for BASEGROUPS array
 * @param   {string}   group
 * @return  {void}
 */
function CreateNewGroupVal(num, btn, all) {
	var group = btn.getAttribute('data-group');
	//var base = btn.data('value');

	if (typeof (all) == 'undefined') {
		all = true;
	}

	// The callback only accepts one argument, so we
	// need to compact this
	//var args = [num, group];
	var post = {
		'longname': BASEGROUPS[num],
		'groupid': group
	};

	fetch(btn.getAttribute('data-api'), {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
		},
		body: JSON.stringify(post)
	})
		.then(function (response) {
			if (response.ok) {
				num++;
				if (all && num < BASEGROUPS.length) {
					setTimeout(function () {
						CreateNewGroupVal(num, btn, all);
					}, 5000);
				} else {
					window.location.reload(true);
				}
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
			btn.querySelector('.spinner-border').classList.add('d-none');
			alert(error);
		});
}

/**
 * Reload after all pending items have been processed
 *
 * @param  {integer} processed
 * @param  {integer} pending
 * @return {void}
 */
function checkprocessed(processed, pending) {
	if (processed['users'] == pending['users']
		&& processed['queues'] == pending['queues']
		&& processed['unixgroups'] == pending['unixgroups']) {
		window.location.reload(true);
	}
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	//---------
	// Departments and Fields of Science

	if (typeof TomSelect !== 'undefined') {
		var sselects = document.querySelectorAll(".searchable-select");
		if (sselects.length) {
			sselects.forEach(function (input) {
				new TomSelect(input);
			});
		}
	}

	document.querySelectorAll('.edit-categories').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var container = document.getElementById(this.getAttribute('href').replace('#', ''));
			container.querySelectorAll('.edit-show').forEach(function (it) {
				it.classList.remove('hide');
			});
			container.querySelectorAll('.edit-hide').forEach(function (it) {
				it.classList.add('hide');
			});
		});
	});

	document.querySelectorAll('.cancel-categories').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var container = document.getElementById(this.getAttribute('href').replace('#', ''));
			container.querySelectorAll('.edit-hide').forEach(function (it) {
				it.classList.remove('hide');
			});
			container.querySelectorAll('.edit-show').forEach(function (it) {
				it.classList.add('hide');
			});
		});
	});

	document.querySelectorAll('.add-category').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var select = document.getElementById(this.getAttribute('href').replace('#', ''));
			var btn = this;

			// create new relationship
			fetch(btn.getAttribute('data-api'), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				},
				body: JSON.stringify({
					'groupid': btn.getAttribute('data-group'),
					[select.getAttribute('data-category')]: select.value
				})
			})
				.then(function (response) {
					if (response.ok) {
						return response.json();
					}
					return response.json().then(function (data) {
						var msg = data.message;
						if (typeof msg === 'object') {
							msg = Object.values(msg).join('<br />');
						}
						throw msg;
					});
				})
				.then(function (result) {
					var c = select.closest('ul');
					var li = c.querySelector('li.hide');

					if (li) {
						var template = li.cloneNode(true);

						template.classList.remove('hide');
						template.id = template.id.replace(/\{id\}/g, result.id);
						template.setAttribute('data-id', result.id);

						template.querySelectorAll('a').forEach(function (a) {
							a.setAttribute('data-api', a.getAttribute('data-api').replace(/\{id\}/g, result.id));
						});

						var content = template.innerHTML;
						content = content.replace(/\{id\}/g, result.id);
						content = content.replace(/\{name\}/g, select.options[select.selectedIndex].innerHTML);

						template.innerHTML = content;

						c.insertBefore(template, li);
					}

					select.value = 0;
				})
				.catch(function (error) {
					var tr = btn.closest('tr');
					if (tr) {
						var td = tr.querySelector('td');
						if (td) {
							var span = document.createElement("div");
							span.classList.add('text-warning');
							span.append(document.createTextNode(error));
							td.append(span);
						}
					}
					btn.classList.add('hide');
				});
		});
	});

	document.querySelector('body').addEventListener('click', function (e) {
		if (!e.target.parentNode.matches('.remove-category')) {
			return;
		}
		e.preventDefault();

		var btn = e.target.parentNode;

		var result = confirm(btn.getAttribute('data-confirm'));

		if (result) {
			var field = document.getElementById(btn.getAttribute('href').replace('#', ''));

			// delete relationship
			fetch(btn.getAttribute('data-api'), {
				method: 'DELETE',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				}
			})
				.then(function (response) {
					if (response.ok) {
						field.remove();
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

	/*document.querySelectorAll('.list-group').forEach(function (el) {
		el.addEventListener('click', function (e) {
			if (e.target.matches('.delete') || e.target.parentNode.matches('.delete-row')) {
				e.preventDefault();

				var el = e.target;
				if (e.target.parentNode.matches('.delete')) {
					el = e.target.parentNode;
				}

				if (confirm(el.getAttribute('data-confirm'))) {
					el.closest('li').remove();
				}
			}
		});
	});*/

	//---------
	// Unix Groups

	document.querySelectorAll('.input-unixgroup').forEach(function (el) {
		el.addEventListener('keyup', function () {
			var val = this.value;

			val = val.toLowerCase()
				.replace(/\s+/g, '-')
				.replace(/[^a-z0-9-]+/g, '');

			this.value = val;
		});
	});

	document.querySelectorAll('.create-default-unix-groups').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			this.setAttribute('data-loading', true);

			CreateNewGroupVal(0, this, parseInt(this.getAttribute('data-all-groups')));
		});
	});

	var longname = document.getElementById('longname');
	if (longname) {
		longname.addEventListener('change', function () {
			this.classList.remove('is-invalid');
			this.classList.remove('is-valid');

			if (this.value) {
				if (this.validity.valid) {
					this.classList.add('is-valid');
				} else {
					this.classList.add('is-invalid');
				}
			}
		});
	}

	document.querySelectorAll('.add-unixgroup').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var name = document.getElementById(this.getAttribute('href').replace('#', ''));
			var btn = this;

			name.classList.remove('is-invalid');
			name.classList.remove('is-valid');

			if (name.value && name.validity.valid) {
				name.classList.add('is-valid');
			} else {
				name.classList.add('is-invalid');
				return false;
			}

			// create new relationship
			fetch(btn.getAttribute('data-api'), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				},
				body: JSON.stringify({
					'groupid': btn.getAttribute('data-group'),
					'longname': name.value
				})
			})
				.then(function (response) {
					if (response.ok) {
						window.location.reload(true);
						return; // response.json();
					}
					return response.json().then(function (data) {
						var msg = data.message;
						if (typeof msg === 'object') {
							msg = Object.values(msg).join('<br />');
						}
						throw msg;
					});
				})
				/*.then(function (result) {
					var c = document.querySelector(btn.getAttribute('data-container'));
					var li = c.querySelector('tr.hidden');
	
					if (li) {
						var template = li.cloneNode(true);
						template.classList.remove('hidden');
	
						template.id = template.id.replace(/\{id\}/g, result.id);
						template.setAttribute('data-id', result.id);
	
						template.querySelectorAll('a').forEach(function (a) {
							a.setAttribute('data-api', a.getAttribute('data-api').replace(/\{id\}/g, result.id));
						});
	
						var content = template.innerHTML;
						content = content.replace(/\{id\}/g, result.id);
						content = content.replace(/\{longname\}/g, result.longname)
						content = content.replace(/\{shortname\}/g, result.shortname);
	
						template.innerHTML = content
						
						li.parentNode.insertBefore(template, li);
	
						var uused = document.getElementById('unix-used');
						var total = parseInt(uused.innerHTML);
						total = total + 1;
						uused.innerHTML = total;
	
						if (total >= 26) {
							btn.classList.add('disabled');
							btn.disabled = true;
						}
					}
	
					name.classList.remove('is-valid');
					name.value = '';
				})*/
				.catch(function (error) {
					name.classList.add('is-invalid');

					var err = document.querySelector(btn.getAttribute('data-error'));
					if (err) {
						err.classList.remove('hide');
						err.innerHTML = error;
					}
				});
		});
	});

	document.querySelector('body').addEventListener('click', function (e) {
		if (!e.target.parentNode.matches('.remove-unixgroup')) {
			return;
		}
		e.preventDefault();

		var btn = e.target.parentNode;
		var result = confirm(btn.getAttribute('data-confirm'));

		if (result) {
			// delete relationship
			fetch(btn.getAttribute('data-api') + '?groupid=' + btn.getAttribute('data-value'), {
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
					var tr = btn.closest('tr');
					if (tr) {
						var td = tr.querySelector('td');
						if (td) {
							var span = document.createElement("div");
							span.classList.add('text-warning');
							span.append(document.createTextNode(error));
							td.append(span);
						}
					}
					btn.classList.add('hide');
				});
		}
	});

	//---------
	// New group

	var newgroup = document.getElementById('new_group_btn');
	if (newgroup) {
		newgroup.addEventListener('click', function (e) {
			e.preventDefault();
			document.getElementById(this.getAttribute('data-indicator').replace('#', '')).classList.remove('hide');
			this.disabled = true;
			CreateNewGroup();
		});
	}

	var newgroupi = document.getElementById('new_group_input');
	if (newgroupi) {
		newgroupi.addEventListener('keyup', function (e) {
			if (e.keyCode == 13) {
				CreateNewGroup();
			}
		});
	}

	//---------
	// Group info

	document.querySelectorAll('.reveal').forEach(function (item) {
		item.addEventListener('click', function () {
			document.querySelectorAll(this.getAttribute('data-toggle')).forEach(function (el) {
				el.classList.toggle('hide');
			});

			var text = this.getAttribute('data-text');
			this.setAttribute('data-text', this.innerHTML);
			this.innerHTML = text;
		});
	});

	document.querySelectorAll('.edit-property').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var items = ['SPAN', 'INPUT', 'CANCEL', 'SAVE', 'EDIT'], item;
			for (var i = 0; i < items.length; i++) {
				item = document.getElementById(items[i] + '_' + this.getAttribute('data-prop') + '_' + this.getAttribute('data-value'));
				if (item) {
					item.classList.toggle('hide');
				}
			}
		});
	});

	document.querySelectorAll('.edit-property-input').forEach(function (el) {
		el.addEventListener('keyup', function (event) {
			if (event.keyCode == 13) {
				var row = this.closest('.row');
				if (!row) {
					return;
				}

				var btn = row.querySelector('.save-property');
				if (btn) {
					btn.dispatchEvent(new Event('click'));
				}
			}
		});
	});

	document.querySelectorAll('.cancel-edit-property').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var items = ['SPAN', 'INPUT', 'CANCEL', 'SAVE', 'EDIT'], item;
			for (var i = 0; i < items.length; i++) {
				item = document.getElementById(items[i] + '_' + this.getAttribute('data-prop') + '_' + this.getAttribute('data-value'));
				if (item) {
					item.classList.toggle('hide');
				}
			}
		});
	});

	document.querySelectorAll('.save-property').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this,
				input = document.getElementById('INPUT_' + btn.getAttribute('data-prop') + '_' + btn.getAttribute('data-value'));

			btn.setAttribute('data-loading', true);
			//btn.find('.spinner-border').toggleClass('hide');
			//btn.find('.fa').toggleClass('hide');

			var post = {};
			post[btn.getAttribute('data-prop')] = input.value;

			fetch(btn.getAttribute('data-api'), {
				method: 'PUT',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				},
				body: JSON.stringify(post)
			})
				.then(function (response) {
					if (response.ok) {
						if (btn.getAttribute('data-reload')) {
							window.location.reload(true);
							return;
						}
						return response.json();
					}
					return response.json().then(function (data) {
						var msg = data.message;
						if (typeof msg === 'object') {
							msg = Object.values(msg).join('<br />');
						}
						throw msg;
					});
				})
				.then(data => {
					var span = document.getElementById('SPAN_' + btn.getAttribute('data-prop') + '_' + btn.getAttribute('data-value'));
					if (span) {
						span.classList.toggle('hide');
						span.innerHTML = data[btn.getAttribute('data-prop')];
					}
					input.classList.toggle('hide');

					//btn.find('.spinner-border').toggleClass('hide');
					//btn.find('.fa').toggleClass('hide');
					btn.setAttribute('data-loading', false);
					btn.classList.toggle('hide');

					var cancel = document.getElementById('CANCEL_' + btn.getAttribute('data-prop') + '_' + btn.getAttribute('data-value'));
					if (cancel) {
						cancel.classList.toggle('hide');
					}
					var edit = document.getElementById('EDIT_' + btn.getAttribute('data-prop') + '_' + btn.getAttribute('data-value'));
					if (edit) {
						edit.classList.toggle('hide');
					}
				}).catch(function (err) {
					btn.setAttribute('data-loading', false);
					alert(err);
				});
		});
	});

	//---------
	// Membership

	if ($('.datatable').length) {
		$('.datatable').each(function (i, el) {
			$(el).DataTable({
				pageLength: 200,
				pagingType: 'numbers',
				paging: false,//($(el).attr('data-length') && parseInt($(el).attr('data-length')) > 200 ? true : false),
				scrollY: '50vh',
				scrollCollapse: true,
				headers: true,
				info: true,
				ordering: false,
				lengthChange: false,
				dom: "<'row'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6'" + ($(el).attr('data-length') && parseInt($(el).attr('data-length')) > 200 ? '' : 'i') + ">><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'p><'col-sm-12 col-md-7'l>>",
				scrollX: true,
				//autoWidth: false,
				language: {
					searchPlaceholder: "Filter users...",
					search: "_INPUT_",
				},
				fixedColumns: {
					leftColumns: 1
				},
				initComplete: function () {
					$($.fn.dataTable.tables(true)).css('width', '100%');

					var table = this;
					this.api().columns().every(function (i) {
						if (i < 3) {
							$('<select class="data-col-filter invisible" disabled></select><br />').prependTo($(this.header()));
							return;
						}
						var column = this;
						var select = $('<select class="data-col-filter form-contro form-contro-sm" data-index="' + i + '"><option value="all">- All -</option><option value="selected">Selected</option><option value="not-selected">Not selected</option></select><br />');
						select.prependTo($(column.header()));
					});

					$('.data-col-filter').on('change', function () {
						$.fn.dataTable.ext.search = [];//.pop();

						$('.data-col-filter').each(function () {
							var val = $(this).val(),
								index = $(this).data('index');

							// If all records should be displayed
							if (val === 'all') {
								return;
							}

							// If selected records should be displayed
							if (val === 'selected') {
								$.fn.dataTable.ext.search.push(
									function (settings, data, dataIndex) {
										var has = $(table
											.api()
											.cell(dataIndex, index)
											.node())
											.find(':checked').length;

										return has ? true : false;
									}
								);
							}

							// If selected records should not be displayed
							if (val === 'not-selected') {
								$.fn.dataTable.ext.search.push(
									function (settings, data, dataIndex) {
										var has = $(table
											.api()
											.cell(dataIndex, index)
											.node())
											.find(':checked').length;

										return has ? false : true;
									}
								);
							}
						});

						table.api().draw();
					});
				}
			});
		});

		// [!] Fix dropdowns in datatables getting cut off if there is only one row
		$(document).on('shown.bs.dropdown', '.datatable', function (e) {
			// The .dropdown container
			var $container = $(e.target);

			// Find the actual .dropdown-menu
			var $dropdown = $container.find('.dropdown-menu');
			if ($dropdown.length) {
				// Save a reference to it, so we can find it after we've attached it to the body
				$container.data('dropdown-menu', $dropdown);
			} else {
				$dropdown = $container.data('dropdown-menu');
			}

			$dropdown.css('top', ($container.offset().top + $container.outerHeight()) + 'px');
			$dropdown.css('left', $container.offset().left + 'px');
			$dropdown.css('position', 'absolute');
			$dropdown.css('display', 'block');
			$dropdown.appendTo('body');
		});

		$(document).on('hide.bs.dropdown', '.datatable', function (e) {
			// Hide the dropdown menu bound to this button
			$(e.target).data('dropdown-menu').css('display', 'none');
		});
		/*var dts = false;
		$('a.tab').on('shown.bs.tab', function(e){
			//$($.fn.dataTable.tables(true)).DataTable().columns.adjust();//.draw();
			if (dts) {
				return;
			}
			$('.datatable').DataTable({
			pageLength: 20,
			pagingType: 'numbers',
			info: false,
			ordering: false,
			lengthChange: false,
			scrollX: true,
			//autoWidth: false,
			language: {
				searchPlaceholder: "Filter users...",
				search: "_INPUT_",
			},
			fixedColumns: {
				leftColumns: 1//,
				//rightColumns: 1
			},
			initComplete: function () {
				//this.page(0).draw(true);
				dts = true;
				$($.fn.dataTable.tables(true)).css('width', '100%');
			}
			});
		});*/
	}

	document.querySelectorAll('.membership-edit').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			document.getElementById(this.getAttribute('href').replace('#', '')).classList.toggle('hidden');
		});
	});

	/*
		$('a[data-toggle="tab"]').on( 'shown.bs.tab', function (e) {
		$($.fn.dataTable.tables( true ) ).css('width', '100%');
		$($.fn.dataTable.tables( true ) ).DataTable().columns.adjust().draw();
	});
	*/

	//$('.dataTables_filter input').addClass('form-control');

	$(".membership-dialog").dialog({
		autoOpen: false,
		height: 'auto',
		width: 500,
		modal: true
	});

	// Add members
	var addmembers = document.getElementById("addmembers");
	if (addmembers) {
		var addmembersts = new TomSelect(addmembers, {
			plugins: {
				remove_button: {
					title: 'Remove this user',
				}
			},
			valueField: 'id',
			labelField: 'name',
			searchField: ['name', 'username', 'email'],
			hidePlaceholder: true,
			persist: false,
			create: true,
			load: function (query, callback) {
				var url = addmembers.getAttribute('data-api') + '?search=' + encodeURIComponent(query);

				fetch(url, {
					method: 'GET',
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					}
				})
					.then(response => response.json())
					.then(json => {
						for (var i = 0; i < json.data.length; i++) {
							if (!json.data[i].id) {
								json.data[i].id = json.data[i].username;
							}
						}
						callback(json.data);
					}).catch(function () {
						callback();
					});
			},
			render: {
				option: function (item, escape) {
					var name = item.name;
					var label = name || item.username;
					var caption = name ? item.username : null;
					return '<div>' +
						'<span class="label">' + escape(label) + '</span>' +
						(caption ? '&nbsp;<span class="caption text-muted">(' + escape(caption) + ')</span>' : '') +
						'</div>';
				},
				item: function (item) {
					return `<div data-id="${escape(item.id)}">${item.name}&nbsp;(${item.username})</div>`;
				}
			}
		});
		addmembersts.on('item_add', function () {
			document.getElementById('add_member_save').disabled = false;
		});
	}

	var imp = document.getElementById('import_member_dialog');
	if (imp) {
		/*var dialogi = $("#import_member_dialog").dialog({
			autoOpen: false,
			height: 'auto',
			width: 500,
			modal: true
		});

		$('.import_member').off('click').on('click', function (e) {
			e.preventDefault();

			dialogi.dialog("open");
		});*/

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

	var newmembertype = document.getElementById('new_membertype');
	if (newmembertype) {
		newmembertype.addEventListener('change', function () {
			var sel = this;
			if (sel.value == 2 && sel.getAttribute('data-cascade')) {
				document.querySelectorAll('.add-queue-member').forEach(function (el) {
					el.checked = true;
					const event = new Event('change');
					el.dispatchEvent(event);

					if (sel.getAttribute('data-disable')) {
						el.disabled = true;
					}
				});
				document.querySelectorAll('.add-unixgroup-member').forEach(function (el) {
					el.checked = true;
					const event = new Event('change');
					el.dispatchEvent(event);

					if (sel.getAttribute('data-disable')
						&& el.getAttribute('data-base')
						&& el.getAttribute('data-base') == el.getAttribute('id')) {
						el.disabled = true;
					}
				});
			} else {
				document.querySelectorAll('.add-queue-member').forEach(function (el) {
					if (sel.getAttribute('data-disable')) {
						el.disabled = false;
					}
				});
			}
		});
	}

	$('.add-queue-member,.add-unixgroup-member').on('change', function (e) {
		e.preventDefault();

		var bx = $(this);

		if (bx.is(':checked')) {
			if (bx.attr('data-base') && bx.attr('data-base') != bx.attr('id')) {
				$('#' + bx.attr('data-base'))
					.prop('checked', true)
					.attr('checked', 'checked')
					.trigger('change');
			}
		}
	});

	$('#add_member_save').on('click', function (e) {
		e.preventDefault();

		var btn = $(this);
		var users = $('#addmembers').val().split(',');

		$('#addmembers').removeClass('is-invalid');
		$('#add_member_error').addClass('hide').html('');

		if (!users || !users.length) {
			$('#addmembers').addClass('is-invalid');
			$('#add_member_error').removeClass('hide').html('Please specify the person(s) to add.');
			return;
		}

		btn.addClass('processing');

		var notice = null;
		var not = document.getElementById('notice');
		if (not && not.checked) {
			notice = 0;
		}
		var post = {
			'groupid': btn.data('group'),
			'userid': 0,
			'membertype': (newmembertype ? newmembertype.value : 1),
			'notice': notice
		};
		var queues = $('.add-queue-member:checked');
		var unixgroups = $('.add-unixgroup-member:checked');

		var processed = {
			users: 0,
			queues: 0,
			unixgroups: 0
		};

		var pending = {
			users: users.length,
			queues: queues.length * users.length,
			unixgroups: unixgroups.length * users.length
		};
		var errors = new Array;

		$.each(users, function (i, userid) {
			post['userid'] = userid;

			$.ajax({
				url: btn.data('api'),
				type: 'post',
				data: post,
				dataType: 'json',
				async: false,
				success: function (data) {
					processed['users']++;

					userid = data.userid;

					if (!queues.length && !unixgroups.length) {
						checkprocessed(processed, pending);
						return;
					}

					queues.each(function (k, checkbox) {
						$.ajax({
							url: btn.data('api-queueusers'),
							type: 'post',
							data: {
								'userid': userid,
								'groupid': btn.data('group'),
								'queueid': checkbox.value,
								'notice': notice
							},
							dataType: 'json',
							async: false,
							success: function () {
								processed['queues']++;
								checkprocessed(processed, pending);
							},
							error: function (xhr) {
								if (typeof xhr.responseJSON.message === 'object') {
									var lines = Object.values(xhr.responseJSON.message);
									for (var i = 0; i < lines.length; i++) {
										errors.push(lines[i]);
									}
								} else {
									errors.push(xhr.responseJSON.message);
								}

								processed['queues']++;
								checkprocessed(processed, pending);
							}
						});
					});

					unixgroups.each(function (k, checkbox) {
						$.ajax({
							url: btn.data('api-unixgroupusers'),
							type: 'post',
							data: {
								'userid': userid,
								'groupid': btn.data('group'),
								'unixgroupid': checkbox.value,
								'notice': notice
							},
							dataType: 'json',
							async: false,
							success: function () {
								processed['unixgroups']++;
								checkprocessed(processed, pending);
							},
							error: function (xhr) {
								if (typeof xhr.responseJSON.message === 'object') {
									var lines = Object.values(xhr.responseJSON.message);
									for (var i = 0; i < lines.length; i++) {
										errors.push(lines[i]);
									}
								} else {
									errors.push(xhr.responseJSON.message);
								}

								processed['unixgroups']++;
								checkprocessed(processed, pending);
							}
						});
					});
				},
				error: function (xhr) {
					if (typeof xhr.responseJSON.message === 'object') {
						var lines = Object.values(xhr.responseJSON.message);
						for (var i = 0; i < lines.length; i++) {
							errors.push(lines[i]);
						}
					} else {
						errors.push(xhr.responseJSON.message);
					}
				}
			});
		});

		// Done?
		if (errors.length) {
			btn.removeClass('processing');
			$('#add_member_error').removeClass('hide').html(errors.join('<br />'));
		}
	});

	// Remove user
	$('body').on('click', '.membership-remove', function (e) {
		e.preventDefault();

		var row = $($(this).attr('href'));
		var boxes = row.find('input[type=checkbox]:checked');
		var errors = new Array;

		var al = $($(this).closest('.card')).find('.alert');
		if (al.length) {
			al.addClass('hide').html(errors.join('<br />'));
		}

		boxes.each(function (i, el) {
			$.ajax({
				url: $(el).data('api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function () {
				},
				error: function (xhr) {
					if (xhr.status == 416) {
						errors.push("Queue disabled for system/guest account.");
					}
					if (typeof xhr.responseJSON.message === 'object') {
						var lines = Object.values(xhr.responseJSON.message);
						for (var i = 0; i < lines.length; i++) {
							errors.push(lines[i]);
						}
					} else {
						errors.push(xhr.responseJSON.message);
					}
				}
			});
		});

		if ($(this).data('api')) {
			$.ajax({
				url: $(this).data('api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function () {
					location.reload(true);
				},
				error: function (xhr) {
					if (typeof xhr.responseJSON.message === 'object') {
						var lines = Object.values(xhr.responseJSON.message);
						for (var i = 0; i < lines.length; i++) {
							errors.push(lines[i]);
						}
					} else {
						errors.push(xhr.responseJSON.message);
					}
				}
			});
		}

		if (errors.length && al.length) {
			al.removeClass('hide').html(errors.join('<br />'));
		}
	});

	$('body').on('click', '.membership-move', function (e) {
		e.preventDefault();

		/*var parent = $($(this).attr('href'));

		parent.find('.membership-toggle').each(function(i, el){
			if ($(el).is(':checked')) {
				$(el).prop('checked', false).change();
			}
		});*/

		if ($(this).attr('data-api')) {
			$.ajax({
				url: $(this).data('api'),
				type: $(this).attr('data-method'),
				data: {
					userid: $(this).data('userid'),
					membertype: $(this).data('target'),
					groupid: $('#groupid').val()
				},
				dataType: 'json',
				async: false,
				success: function () {
					location.reload(true);
				},
				error: function (xhr) {
					alert(xhr.responseJSON.message);
				}
			});
		}
	});

	$('body').on('change', '.membership-toggle', function (e) {
		e.preventDefault();

		var al = $($(this).closest('.card')).find('.alert');
		if (al.length) {
			al.addClass('hide').html('');
		}

		var bx = $(this);
		bx.parent().find('.fa').remove();

		if (bx.is(':checked')) {
			if (bx.attr('data-base') && bx.attr('data-base') != bx.attr('id')) {
				$('#' + bx.attr('data-base'))
					.prop('checked', true)
					.attr('checked', 'checked')
					.prop('disabled', true)
					.trigger('change');
			}

			var post = {
				userid: $(this).data('userid')
			};
			post['groupid'] = $('#groupid').val();
			if ($(this).hasClass('queue-toggle')) {
				post['queueid'] = bx.data('objectid');
			} else {
				post['unixgroupid'] = bx.data('objectid');
			}

			$.ajax({
				url: bx.data('api-create'),
				type: 'post',
				data: post,
				dataType: 'json',
				async: false,
				success: function (data) {
					bx.data('api', data.api);
					if (typeof data.error != 'undefined') {
						if (al.length) {
							al.removeClass('hide').html(data.error);
						}
						bx.after($('<span class="fa fa-exclamation-triangle text-warning" aria-hidden="true" title="' + data.error + '"><span class="sr-only">' + data.error + '</span></span>'));
						alert('An error occurred. Try toggling the checkbox. If issues persist, please contact help.');
					}
				},
				error: function (xhr) {
					var msg = '';

					if (xhr.status == 416) {
						msg = "Queue enabled for system/guest account.";
					} else {
						msg = xhr.responseJSON.message;
					}

					if (al.length) {
						al.removeClass('hide').html(msg);
					} else {
						alert(msg);
					}
				}
			});
		} else {
			$.ajax({
				url: bx.data('api') + '?groupid=' + $('#groupid').val(),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function () {
					// Nothing to do here
					//bx.data('api', bx.data('api-create'));
				},
				error: function (xhr) { //xhr, ajaxOptions, thrownError
					var msg = '';

					if (xhr.status == 416) {
						msg = "Queue disabled for system/guest account.";
					} else {
						msg = xhr.responseJSON.message;
					}

					bx.after($('<span class="fa fa-exclamation-triangle text-warning" aria-hidden="true" title="' + msg + '"><span class="sr-only">' + msg + '</span></span>'));

					if (al.length) {
						al.removeClass('hide').html(msg);
					} else {
						alert(msg);
					}
				}
			});
		}
	});

	$('body').on('click', '.membership-allqueues', function (e) {
		e.preventDefault();

		var parent = $($(this).attr('href'));

		parent.find('.membership-toggle').each(function (i, el) {
			if (!$(el).is(':checked')) {
				$(el).prop('checked', true).change();
			}
		});
	});

	var exp = document.getElementById('export_to_csv');
	if (exp) {
		exp.addEventListener('click', function (e) {
			e.preventDefault();
			// Get the form unique to the current tab group using its id
			var form = document.getElementById('csv_form_' + this.getAttribute('data-id'));
			/*var data = form.find('input:hidden[name=data]').val();
			// Data is json_parsed and uri decoded so convert it back
			data = JSON.parse(decodeURIComponent(data));
			// csvEscapeJSON is found in common.js and used to make the html render correctly
			data = csvEscapeJSON(JSON.stringify(data));
			// Insert data back into the form and make it submit to the php csv page. 
			form.find('input:hidden[name=data]').val(data)*/
			form.submit();
		});
	}
});
