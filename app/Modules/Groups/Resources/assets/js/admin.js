/* global $ */ // jquery.js
/* global Halcyon */ // core.js
/* global WSPutURL */ // core.js
/* global WSDeleteURL */ // core.js
/* global SetError */ // common.js
/* global ERRORS */ // common.js

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
	var group = btn.data('group');
	//var base = btn.data('value');

	if (typeof (all) == 'undefined') {
		all = true;
	}

	$.ajax({
		url: btn.data('api'),
		type: 'post',
		data: {
			'longname': BASEGROUPS[num],
			'groupid': group
		},
		dataType: 'json',
		async: false,
		success: function () {
			num++;
			if (all && num < BASEGROUPS.length) {
				setTimeout(function () {
					CreateNewGroupVal(num, btn, all);
				}, 5000);
			} else {
				Halcyon.message('success', 'Item added');
				window.location.reload(true);
			}
		},
		error: function (xhr) { //xhr, ajaxOptions, thrownError
			btn.find('.spinner-border').addClass('d-none');
			Halcyon.message('danger', xhr.responseJSON.message);
		}
	});
}

var _DEBUG = true;
/**
 * Message of the Day
 */
var motd = {
	/**
	 * Set the MOTD for a group
	 *
	 * @param   {string}  group
	 * @return  {void}
	 */
	set: function (group) {
		var message = document.getElementById("MotdText_" + group);

		if (!group) {
			Halcyon.message('danger', 'No group ID provided.');
			return false;
		}

		var post = {
			'groupid': group,
			'motd': message.value
		};

		_DEBUG ? console.log('post: ' + message.getAttribute('data-api'), post) : null;

		$.ajax({
			url: message.getAttribute('data-api'),
			type: 'post',
			data: post,
			dataType: 'json',
			async: false,
			success: function () {
				window.location.reload();
			},
			error: function (xhr) {
				Halcyon.message('danger', xhr.response);
			}
		});
	},

	/**
	 * Delete the MOTD for a group
	 *
	 * @param   {string}  group
	 * @return  {void}
	 */
	delete: function (group) {
		if (!group) {
			Halcyon.message('danger', 'No group ID provided.');
			return false;
		}

		var btn = document.getElementById("MotdText_delete_" + group);

		_DEBUG ? console.log('delete: ' + btn.getAttribute('data-api')) : null;

		$.ajax({
			url: btn.getAttribute('data-api'),
			type: 'delete',
			async: false,
			success: function () {
				window.location.reload();
			},
			error: function (xhr) {
				Halcyon.message('danger', xhr.response);
			}
		});
	}
}

var UserRequests = {
	/**
	 * Pending approved requests
	 *
	 * @var  {number}
	 */
	approvepending: 0,

	/**
	 * Pending rejected requests
	 *
	 * @var  {number}
	 */
	rejectpending: 0,

	/**
	 * Approve a user request
	 *
	 * @param   {array}  requests
	 * @return  {void}
	 */
	Approve: function (requests) {
		for (var request in requests) {
			UserRequests.approvepending++;

			WSPutURL(request, '{}', function (xml) {
				if (xml.status < 400) {
					UserRequests.approvepending--;

					if (UserRequests.approvepending == 0) {
						window.location.reload(true);
					}
				} else {
					SetError(ERRORS['generic'], ERRORS['500']);
				}
			});
		}
	},

	/**
	 * Reject a user request
	 *
	 * @param   {array}  requests
	 * @return  {void}
	 */
	Reject: function (requests) {
		for (var request in requests) {
			UserRequests.approvepending++;

			WSDeleteURL(request, function (xml) {
				if (xml.status < 400) {
					UserRequests.rejectpending--;

					if (UserRequests.rejectpending == 0) {
						window.location.reload(true);
					}
				} else {
					SetError(ERRORS['generic'], ERRORS['500']);
				}
			});
		}
	}
}

/**
 * toggle accept all radio buttons
 *
 * @param   {string}  btn
 * @return  {void}
 */
function ToggleAllRadio(btn) {
	if (btn == 0) {
		$('#denyAll').prop('checked', false);
		$('.approve-value1').prop('checked', false);
	}
	else if (btn == 1) {
		$('#acceptAll').prop('checked', false);
		$('.approve-value0').prop('checked', false);
	}

	$('.approve-value' + btn).prop('checked', true);
}

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
document.addEventListener('DOMContentLoaded', function() {
	if ($.fn.select2) {
		$('.searchable-select').select2();
	}

	$('.reveal').on('click', function () {
		$($(this).data('toggle')).toggleClass('hide');

		var text = $(this).data('text');
		$(this).data('text', $(this).html());
		$(this).html(text);
	});

	$('.motd-delete').on('click', function (e) {
		e.preventDefault();
		motd.delete(this.getAttribute('data-group'));
	});

	$('.motd-set').on('click', function (e) {
		e.preventDefault();
		motd.set(this.getAttribute('data-group'));
	});

	$('#main').on('change', '.membertype', function(){
		$.ajax({
			url: $(this).data('api'),
			type: 'put',
			data: {membertype: $(this).val()},
			dataType: 'json',
			async: false,
			success: function () {
				Halcyon.message('success', 'Member type updated!');
			},
			error: function () { //xhr, ajaxOptions, thrownError
				Halcyon.message('danger', 'Failed to update member type.');
			}
		});
	});

	$('.input-unixgroup').on('keyup', function (){
		var val = $(this).val();

		val = val.toLowerCase()
			.replace(/\s+/g, '-')
			.replace(/[^a-z0-9-]+/g, '');

		$(this).val(val);
	});

	$('.create-default-unix-groups').on('click', function(e) {
		e.preventDefault();

		$(this).find('.spinner-border').removeClass('d-none');

		CreateNewGroupVal(0, $(this), parseInt($(this).data('all-groups')));
	});

	$('.add-category').on('click', function(e){
		e.preventDefault();

		var select = $($(this).attr('href'));
		var btn = $(this);

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'post',
			data: {
				'groupid' : btn.data('group'),
				[select.data('category')] : select.val()
			},
			dataType: 'json',
			async: false,
			success: function(response) {
				Halcyon.message('success', 'Item added');

				var c = select.closest('table');
				var li = c.find('tr.hidden');

				if (typeof(li) !== 'undefined') {
					var template = $(li)
						.clone()
						.removeClass('hidden');

					template
						.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
						.data('id', response.id);

					template.find('a').each(function(i, el){
						$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.id));
					});

					var content = template
						.html()
						.replace(/\{id\}/g, response.id)
						.replace(/\{name\}/g, select.find('option:selected').text());

					template.html(content).insertBefore(li);
				}

				select.val(0);
			},
			error: function(xhr) {
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	$('#main').on('click', '.remove-category', function(e){
		e.preventDefault();

		var result = confirm($(this).data('confirm'));

		if (result) {
			var field = $($(this).attr('href'));

			// delete relationship
			$.ajax({
				url: $(this).data('api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function() {
					Halcyon.message('success', 'Item removed');
					field.remove();
				},
				error: function(xhr) {
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		}
	});

	$('.add-unixgroup').on('click', function(e){
		e.preventDefault();

		var name = $($(this).attr('href'));
		var btn = $(this);

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'post',
			data: {
				'groupid' : btn.data('group'),
				'longname' : name.val()
			},
			dataType: 'json',
			async: false,
			success: function(response) {
				Halcyon.message('success', 'Item added');

				var c = name.closest('table');
				var li = c.find('tr.hidden');

				if (typeof(li) !== 'undefined') {
					var template = $(li)
						.clone()
						.removeClass('hidden');

					template
						.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
						.data('id', response.id);

					template.find('a').each(function(i, el){
						$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.id));
					});

					var content = template
						.html()
						.replace(/\{id\}/g, response.id)
						.replace(/\{longname\}/g, response.longname)
						.replace(/\{shortname\}/g, response.shortname);

					template.html(content).insertBefore(li);
				}

				name.val('');
			},
			error: function(xhr) {
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	$('#main').on('click', '.remove-unixgroup', function(e){
		e.preventDefault();

		var result = confirm($(this).data('confirm'));

		if (result) {
			var field = $($(this).attr('href'));

			// delete relationship
			$.ajax({
				url: $(this).data('api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function() {
					Halcyon.message('success', 'Item removed');
					field.remove();
				},
				error: function(xhr) {
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		}
	});

	// Pending user requests
	$('.radio-toggle').on('change', function () {
		ToggleAllRadio(parseInt($(this).val()));
		$('#submit-requests').prop('disabled', false);
	});
	$('.approve-request').on('change', function () {
		$('#submit-requests').prop('disabled', false);
	});
	$('#submit-requests').on('click', function (e) {
		e.preventDefault();

		var inputs = $('.approve-request:checked');

		if (!inputs.length) {
			alert("Must select an option for all users before continuing.");
			return;
		}

		UserRequests.approvepending = 0;

		// Loop through list and approve users. -2 so it doesnt hit the approve/deny all buttons
		for (var i = 0; i < inputs.length; i++) {
			//var user = inputs[i].value.split(",")[0];
			var approve = inputs[i].value.split(",")[1];

			if (approve == 0 && inputs[i].checked == true) {
				// Approve the user
				UserRequests.Approve(inputs[i].getAttribute('data-api').split(','));
			}
			else if (inputs[i].value.split(",")[1] == 1 && inputs[i].checked == true) {
				// Delete the request
				UserRequests.Reject(inputs[i].getAttribute('data-api').split(','));
			}
		}
	});

	$('.list-group').on('click', '.delete-row', function (e) {
		e.preventDefault();

		var result = confirm('Are you sure you want to remove this?');

		if (result) {
			var container = $(this).closest('li');

			//$.post($(this).data('api'), data, function(e){
			container.remove();
			//});
		}
	});

	/*$('.edit-property').on('click', function (e) {
		e.preventDefault();

		var items = ['SPAN', 'INPUT', 'CANCEL', 'SAVE', 'EDIT'], item;
		for (var i = 0; i < items.length; i++) {
			item = $('#' + items[i] + '_' + $(this).data('prop') + '_' + $(this).data('value'));
			if (item.length) {
				item.toggleClass('hide');
			}
		}
	});

	$('.edit-property-input').on('keyup', function (event) {
		if (event.keyCode == 13) {
			EditProperty($(this).data('prop'), $(this).data('value'));
		}
	});

	$('.cancel-edit-property').on('click', function (e) {
		e.preventDefault();

		var items = ['SPAN', 'INPUT', 'CANCEL', 'SAVE', 'EDIT'], item;
		for (var i = 0; i < items.length; i++) {
			item = $('#' + items[i] + '_' + $(this).data('prop') + '_' + $(this).data('value'));
			if (item.length) {
				item.toggleClass('hide');
			}
		}
	});

	$('.save-property').on('click', function (e) {
		e.preventDefault();

		var btn = $(this),
			input = $('#INPUT_' + btn.data('prop') + '_' + btn.data('value'));

		btn.attr('data-loading', true);
		//btn.find('.spinner-border').toggleClass('hide');
		//btn.find('.fa').toggleClass('hide');

		var post = {};
		post[btn.data('prop')] = input.val();

		$.ajax({
			url: btn.data('api'),
			type: 'put',
			data: post,
			dataType: 'json',
			async: false,
			success: function (data) {
				if (btn.data('reload')) {
					window.location.reload(true);
					return;
				}

				var span = $('#SPAN_' + btn.data('prop') + '_' + btn.data('value'));
				if (span.length) {
					span.toggleClass('hide');
					span.html(data[btn.data('prop')]);
				}
				input.toggleClass('hide');

				//btn.find('.spinner-border').toggleClass('hide');
				//btn.find('.fa').toggleClass('hide');
				btn.attr('data-loading', false);
				btn.toggleClass('hide');

				var cancel = $('#CANCEL_' + btn.data('prop') + '_' + btn.data('value'));
				if (cancel.length) {
					cancel.toggleClass('hide');
				}
				var edit = $('#EDIT_' + btn.data('prop') + '_' + btn.data('value'));
				if (edit.length) {
					edit.toggleClass('hide');
				}
			},
			error: function (xhr) {
				//Halcyon.message('danger', xhr.response);
				//btn.find('spinner-border').toggleClass('hide');
				//btn.find('fa').toggleClass('hide');
				btn.attr('data-loading', false);
				alert(xhr.responseJSON.message);
				//console.log(xhr);
			}
		});
	});

	$('.create-default-unix-groups').on('click', function(e){
		e.preventDefault();
		CreateDefaultUnixGroups($(this).data('value'), $(this).data('group'));
	});
	$('.delete-unix-group').on('click', function (e) {
		e.preventDefault();
		DeleteUnixGroup($(this).data('unixgroup'), $(this).data('value'));
	});*/

	//$('.searchable-select').select2();

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

	$('.membership-edit').on('click', function (e) {
		e.preventDefault();

		$($(this).attr('href')).toggleClass('hidden');
	});

	$(".membership-dialog").dialog({
		autoOpen: false,
		height: 'auto',
		width: 500,
		modal: true
	});

	$('.add_member').on('click', function (e) {
		e.preventDefault();

		$($(this).attr('href')).dialog("open");
		$('#new_membertype').val($(this).data('membertype'));

		$('#addmembers').select2({
			ajax: {
				url: $('#addmembers').data('api'),
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
		$('#addmembers').on('select2:select', function () {
			$('#add_member_save').prop('disabled', false);
		});
	});

	$('#add_member_save').on('click', function (e) {
		e.preventDefault();

		var btn = $(this);
		var users = $('#addmembers').val();

		var post = {
			'groupid': btn.data('group'),
			'userid': 0,
			'membertype': $('#new_membertype').val()
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

		$.each(users, function (i, userid) {
			post['userid'] = userid;

			$.ajax({
				url: btn.data('api'),
				type: 'post',
				data: post,
				dataType: 'json',
				async: false,
				success: function () {
					processed['users']++;

					queues.each(function (k, checkbox) {
						$.ajax({
							url: btn.data('api-queueusers'),
							type: 'post',
							data: {
								'userid': userid,
								'queueid': checkbox.value,
							},
							dataType: 'json',
							async: false,
							success: function () {
								processed['queues']++;
								checkprocessed(processed, pending);
							},
							error: function (xhr) {
								//Halcyon.message('danger', xhr.response);
								alert(xhr.responseJSON.message);
								processed['queues']++;
								checkprocessed(processed, pending);
							}
						});
						console.log(btn.data('api-queueusers'));
					});

					unixgroups.each(function (k, checkbox) {
						$.ajax({
							url: btn.data('api-unixgroupusers'),
							type: 'post',
							data: {
								'userid': userid,
								//'groupid': btn.data('group'),
								'unixgroupid': checkbox.value
							},
							dataType: 'json',
							async: false,
							success: function () {
								processed['unixgroups']++;
								checkprocessed(processed, pending);
							},
							error: function (xhr) {
								//Halcyon.message('danger', xhr.response);
								alert(xhr.responseJSON.message);
								processed['unixgroups']++;
								checkprocessed(processed, pending);
							}
						});
						console.log(btn.data('api-unixgroupusers'));
					});
				},
				error: function (xhr) {
					//Halcyon.message('danger', xhr.response);
					alert(xhr.responseJSON.message);
				}
			});
		});
		// Done?
	});

	// Remove user
	$('body').on('click', '.membership-remove', function (e) {
		e.preventDefault();

		var row = $($(this).attr('href'));
		var boxes = row.find('input[type=checkbox]:checked');

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
						SetError("Queue disabled for system/guest account. ACMaint Role removal must be requested manually from accounts@purdue.edu", null);
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
					alert(xhr.responseJSON.message);
				}
			});
		}
	});

	$('body').on('click', '.membership-move', function (e) {
		e.preventDefault();

		var parent = $($(this).attr('href'));

		parent.find('.membership-toggle').each(function (i, el) {
			if ($(el).is(':checked')) {
				$(el).prop('checked', false).change();
			}
		});

		if ($(this).data('api')) {
			$.ajax({
				url: $(this).data('api'),
				type: 'post',
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

		if ($(this).is(':checked')) {
			var post = {
				userid: $(this).data('userid')
			};
			if ($(this).hasClass('queue-toggle')) {
				post['groupid'] = $('#groupid').val();
				post['queueid'] = $(this).data('objectid');
			} else {
				post['unixgroupid'] = $(this).data('objectid');
			}

			$.ajax({
				url: $(this).data('api'),
				type: 'post',
				data: post,
				dataType: 'json',
				async: false,
				success: function () {
				},
				error: function (xhr) {
					if (xhr.status == 416) {
						alert("Queue enabled for system/guest account. ACMaint Role addition must be requested manually from accounts@purdue.edu", null);
					} else {
						alert(xhr.responseJSON.message);
					}
				}
			});
		} else {
			$.ajax({
				url: $(this).data('api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function () {
				},
				error: function (xhr) {
					if (xhr.status == 416) {
						alert("Queue disabled for system/guest account. ACMaint Role removal must be requested manually from accounts@purdue.edu", null);
					} else {
						alert(xhr.responseJSON.message);
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

	$('#export_to_csv').on('click', function (e) {
		e.preventDefault();
		// Get the form unique to the current tab group using its id
		var form_id = "#csv_form_" + $(this).data('id');
		var form = $(form_id);
		/*var data = form.find('input:hidden[name=data]').val();
		// Data is json_parsed and uri decoded so convert it back
		data = JSON.parse(decodeURIComponent(data));
		// csvEscapeJSON is found in common.js and used to make the html render correctly
		data = csvEscapeJSON(JSON.stringify(data));
		// Insert data back into the form and make it submit to the php csv page. 
		form.find('input:hidden[name=data]').val(data)*/
		form.submit();
	});
});