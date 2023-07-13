/* global $ */ // jquery.js
/* global Halcyon */ // core.js

var headers = {
	'Content-Type': 'application/json'
};

/**
 * Reload the window after all pending items are complete
 *
 * @param {array} processed 
 * @param {array} pending 
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
	headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	if ($.fn.select2) {
		$('.searchable-select').select2();
	}

	var main = document.getElementById('main');
	if (main) {
		main.addEventListener('change', function (e) {
			// User changing membertype
			if (e.target.matches('.membertype')
			|| e.target.parentNode.matches('.membertype')) {
				var tar = e.target;
				if (e.target.parentNode.matches('.membertype')) {
					tar = e.target.parentNode;
				}

				fetch(tar.getAttribute('data-api'), {
					method: 'PUT',
					headers: headers,
					body: JSON.stringify({ membertype: tar.value })
				})
				.then(function (response) {
					if (response.ok) {
						Halcyon.message('success', 'Member type updated!');
						return;
					}
					return response.json();
				})
				.then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
				});
			}
		});
	}

	//$('a.tab').on('shown.bs.tab', function(e){
	var inited = false;
	//$('.tabs').on("tabsactivate", function (event, ui) {
	if ($('.datatable').length && !inited) {
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
				leftColumns: 1
			},
			initComplete: function () {
				$($.fn.dataTable.tables(true)).css('width', '100%');

				var table = this;
				this.api().columns().every(function (i) {
					if (i < 2) {
						return;
					}
					var column = this;
					var select = $('<select class="data-col-filter" data-index="' + i + '"><option value="all">- All -</option><option value="selected">Selected</option><option value="not-selected">Not selected</option></select><br />');
					select.prependTo($(column.header()));
				});

				$('.data-col-filter').on('change', function () {
					$.fn.dataTable.ext.search = [];//.pop();

					$('.data-col-filter').each(function (k, el) {
						var val = $(el).val(),
							index = $(el).data('index');

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

		inited = true;
	}
	//});

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

	document.querySelectorAll('.membership-edit').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			document.getElementById(this.getAttribute('href').replace('#', '')).classList.toggle('hidden');
		});
	});

	$(".membership-dialog").dialog({
		autoOpen: false,
		height: 'auto',
		width: 500,
		modal: true
	});

	document.querySelectorAll('.add_member').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			$(this.getAttribute('href')).dialog("open");
			document.getElementById('new_membertype').value = this.getAttribute('data-membertype');

			var addmembers = $('#addmembers');
			addmembers.select2({
				ajax: {
					url: addmembers.data('api'),
					dataType: 'json',
					//maximumSelectionLength: 1,
					//theme: "classic",
					data: function (params) {
						var query = {
							search: params.term,
							order: 'surname',
							order_dir: 'asc'
						}

						return query;
					},
					processResults: function (data) {
						for (var i = 0; i < data.data.length; i++) {
							data.data[i].text = data.data[i].name + ' (' + data.data[i].username + ')';
						}

						return {
							results: data.data
						};
					}
				}
			});
			addmembers.on('select2:select', function () {
				document.getElementById('add_member_save').disabled = false;
			});
		});
	});

	var new_membertype = document.getElementById('new_membertype');
	if (new_membertype) {
		new_membertype.addEventListener('change', function () {
			var sel = this;
			if (sel.value == 2 && sel.getAttribute('data-cascade')) {
				document.querySelectorAll('.add-queue-member').forEach(function (el) {
					el.checked = true;
					el.dispatchEvent(new Event('change'));

					if (sel.getAttribute('data-disable')) {
						el.disabled = true;
					}
				});
				document.querySelectorAll('.add-unixgroup-member').forEach(function (bx) {
					bx.checked = true;
					bx.dispatchEvent(new Event('change'));

					if (sel.getAttribute('data-disable')
					&& bx.getAttribute('data-base')
					&& bx.getAttribute('data-base') == bx.getAttribute('id')) {
						bx.disabled = true;
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

	document.querySelectorAll('.add-queue-member,.add-unixgroup-member').forEach(function (el) {
		el.addEventListener('change', function (e) {
			e.preventDefault();

			if (this.checked) {
				if (this.getAttribute('data-base') && this.getAttribute('data-base') != this.getAttribute('id')) {
					var base = document.getElementById(this.getAttribute('data-base'));
					if (base) {
						base.checked = true;
						base.dispatchEvent(new Event('change'));
					}
				}
			}
		});
	});

	var memsave = document.getElementById('add_member_save');
	if (memsave) {
		memsave.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;
			var users = $('#addmembers').val();

			var notice = null;
			var not = document.getElementById('notice');
			if (not && not.checked) {
				notice = 0;
			}
			var post = {
				'groupid': btn.getAttribute('data-group'),
				'userid': 0,
				'membertype': document.getElementById('new_membertype').value,
				'notice': notice
			};
			var queues = document.querySelectorAll('.add-queue-member:checked');
			var unixgroups = document.querySelectorAll('.add-unixgroup-member:checked');

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

			users.forEach(function (userid) {
				post['userid'] = userid;

				fetch(btn.getAttribute('data-api'), {
					method: 'POST',
					headers: headers,
					body: JSON.stringify(post)
				})
				.then(function (response) {
					if (response.ok) {
						processed['users']++;

						queues.forEach(function (checkbox) {
							fetch(btn.getAttribute('data-api-queueusers'), {
								method: 'POST',
								headers: headers,
								body: JSON.stringify({
									'userid': userid,
									'groupid': btn.getAttribute('data-group'),
									'queueid': checkbox.value,
									'notice': notice
								})
							})
							.then(function (response) {
								if (response.ok) {
									processed['queues']++;
									checkprocessed(processed, pending);
								}
								return response.json();
							})
							.then(function (data) {
								if (typeof data.message !== 'undefined') {
									var msg = data.message;
									if (typeof msg === 'object') {
										msg = Object.values(msg).join('<br />');
									}
									throw msg;
								}
							})
							.catch(function (error) {
								Halcyon.message('danger', error);

								processed['queues']++;
								checkprocessed(processed, pending);
							});
						});

						unixgroups.forEach(function (checkbox) {
							fetch(btn.getAttribute('data-api-unixgroupusers'), {
								method: 'POST',
								headers: headers,
								body: JSON.stringify({
									'userid': userid,
									'groupid': btn.getAttribute('data-group'),
									'unixgroupid': checkbox.value,
									'notice': notice
								})
							})
							.then(function (response) {
								if (response.ok) {
									processed['unixgroups']++;
									checkprocessed(processed, pending);
								}
								return response.json();
							})
							.then(function (data) {
								if (typeof data.message !== 'undefined') {
									var msg = data.message;
									if (typeof msg === 'object') {
										msg = Object.values(msg).join('<br />');
									}
									throw msg;
								}
							})
							.catch(function (error) {
								Halcyon.message('danger', error);

								processed['unixgroups']++;
								checkprocessed(processed, pending);
							});
						});
					}
					return response.json();
				})
				.then(function (data) {
					if (typeof data.message !== 'undefined') {
						var msg = data.message;
						if (typeof msg === 'object') {
							msg = Object.values(msg).join('<br />');
						}
						throw msg;
					}
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
				});
			});
			// Done?
		});
	}

	// Listen for membership actions
	document.querySelector('body').addEventListener('click', function (e) {
		var tar;

		if (e.target.matches('.membership-remove')
		|| e.target.parentNode.matches('.membership-remove')) {
			e.preventDefault();

			tar = e.target;
			if (e.target.parentNode.matches('.membership-remove')) {
				tar = e.target.parentNode;
			}

			var row = document.getElementById(tar.getAttribute('href').replace('#', ''));
			var boxes = row.querySelectorAll('input[type=checkbox]:checked');

			boxes.forEach(function (el) {
				fetch(el.getAttribute('data-api'), {
					method: 'DELETE',
					headers: headers
				})
				.then(function (response) {
					if (!response.ok) {
						return response.json();
					}
				})
				.then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
				});
			});

			if (tar.getAttribute('data-api')) {
				fetch(tar.getAttribute('data-api'), {
					method: 'DELETE',
					headers: headers
				})
				.then(function (response) {
					if (response.ok) {
						window.location.reload(true);
						return;
					}
					return response.json();
				})
				.then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
				});
			}
		}

		if (e.target.matches('.membership-move')
		|| e.target.parentNode.matches('.membership-move')) {
			e.preventDefault();

			tar = e.target;
			if (e.target.parentNode.matches('.membership-move')) {
				tar = e.target.parentNode;
			}

			var parent = document.getElementById(tar.getAttribute('href').replace('#', ''));

			parent.guerySelectorAll('.membership-toggle').forEach(function (el) {
				if (el.checked) {
					el.checked = false;
					el.dispatchEvent(new Event('change'));
				}
			});

			if (tar.getAttribute('data-api')) {
				fetch(tar.getAttribute('data-api'), {
					method: 'POST',
					headers: headers,
					body: JSON.stringify({
						userid: this.getAttribute('data-userid'),
						membertype: this.getAttribute('data-target'),
						groupid: document.getElementById('groupid').value
					})
				})
				.then(function (response) {
					if (response.ok) {
						window.location.reload(true);
						return;
					}
					return response.json();
				})
				.then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
				});
			}
		}

		if (e.target.matches('.membership-allqueues')
		|| e.target.parentNode.matches('.membership-allqueues')) {
			e.preventDefault();

			tar = e.target;
			if (e.target.matches('.membership-allqueues')) {
				tar = e.target.parentNode;
			}

			var parent = document.getElementById(tar.getAttribute('href').replace('#', ''));

			parent.querySelectorAll('.membership-toggle').forEach(function (el) {
				if (!el.checked) {
					el.checked = true;
					el.dispatchEvent(new Event('change'));
				}
			});
		}
	});

	document.querySelector('body').addEventListener('change', function (e) {
		if (e.target.matches('.membership-toggle')
		|| e.target.parentNode.matches('.membership-toggle')) {
			e.preventDefault();

			var bx = e.target;
			if (e.target.matches('.membership-toggle')) {
				bx = e.target.parentNode;
			}

			if (bx.checked) {
				if (bx.getAttribute('data-base') && bx.getAttribute('data-base') != bx.getAttribute('id')) {
					var base = document.getElementById(bx.getAttribute('data-base'));
					if (base) {
						base.checked = true;
						base.disabled = true;
						base.dispatchEvent(new Event('change'));
					}
				}

				var post = {
					userid: bx.getAttribute('data-userid')
				};
				post['groupid'] = document.getElementById('groupid').value;
				if (this.classList.contains('queue-toggle')) {
					post['queueid'] = bx.getAttribute('data-objectid');
				} else {
					post['unixgroupid'] = bx.getAttribute('data-objectid');
				}

				fetch(bx.getAttribute('data-api-create'), {
					method: 'POST',
					headers: headers,
					body: JSON.stringify(post)
				})
				.then(function (response) {
					if (!response.ok) {
						return response.json();
					}
				})
				.then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
				});
			} else {
				fetch(bx.getAttribute('data-api'), {
					method: 'DELETE',
					headers: headers
				})
				.then(function (response) {
					if (!response.ok) {
						return response.json();
					}
				})
				.then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
				});
			}
		}
	});

	var exp = document.getElementById('export_to_csv');
	if (exp) {
		exp.addEventListener('click', function (e) {
			e.preventDefault();
			// Get the form unique to the current tab group using its id
			var form = document.getElementById('csv_form_' + this.getAttribute('data-id'));
			if (form) {
				/*var data = form.find('input:hidden[name=data]').val();
				// Data is json_parsed and uri decoded so convert it back
				data = JSON.parse(decodeURIComponent(data));
				// csvEscapeJSON is found in common.js and used to make the html render correctly
				data = csvEscapeJSON(JSON.stringify(data));
				// Insert data back into the form and make it submit to the php csv page. 
				form.find('input:hidden[name=data]').val(data)*/
				form.submit();
			}
		});
	}
});
