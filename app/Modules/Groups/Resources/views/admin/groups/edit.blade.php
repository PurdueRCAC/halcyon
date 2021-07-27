@extends('layouts.master')

@push('styles')
<!-- <link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/datatables/datatables.min.css') }}" /> -->
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.css?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/handlebars/handlebars.min-v4.7.6.js?v=' . filemtime(public_path() . '/modules/core/vendor/handlebars/handlebars.min-v4.7.6.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/datatables.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/datatables.min.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/groups/js/admin.js?v=' . filemtime(public_path() . '/modules/groups/js/admin.js')) }}"></script>
<script>
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
			success: function (data) {
				window.location.reload();
			},
			error: function (xhr, ajaxOptions, thrownError) {
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
			success: function (data) {
				window.location.reload();
			},
			error: function (xhr, ajaxOptions, thrownError) {
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
	Approve: function(requests) {
		for (var request in requests) {
			UserRequests.approvepending++;

			WSPutURL(request, '{}', function(xml) {
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
	Reject: function(requests) {
		for (var request in requests) {
			UserRequests.approvepending++;

			WSDeleteURL(request, function(xml) {
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
/*
document.addEventListener('DOMContentLoaded', function() {
	var dels = document.getElementsByClassName('motd-delete');
	var i;
	for (i = 0; i < dels.length; i++)
	{
		dels[i].addEventListener('click', function(e){
			e.preventDefault();
			motd.delete(this.getAttribute('data-group'));
		});
	}

	var sets = document.getElementsByClassName('motd-set');
	for (i = 0; i < sets.length; i++)
	{
		sets[i].addEventListener('click', function(e){
			e.preventDefault();
			motd.set(this.getAttribute('data-group'));
		});
	}
});*/

	$(document).ready(function() {
		$('.reveal').on('click', function(e){
			$($(this).data('toggle')).toggleClass('hide');

			var text = $(this).data('text');
			$(this).data('text', $(this).html()); //.replace(/"/, /'/));
			$(this).html(text);
		});

		$('.motd-delete').on('click', function(e){
			e.preventDefault();
			motd.delete(this.getAttribute('data-group'));
		});

		$('.motd-set').on('click', function(e){
			e.preventDefault();
			motd.set(this.getAttribute('data-group'));
		});

		// Pending user requests
		$('.radio-toggle').on('change', function(e){
			ToggleAllRadio(parseInt($(this).val()));
			$('#submit-requests').prop('disabled', false);
		});
		$('.approve-request').on('change', function(e){
			$('#submit-requests').prop('disabled', false);
		});
		$('#submit-requests').on('click', function(e){
			e.preventDefault();

			var inputs = $('.approve-request:checked');

			if (!inputs.length) {
				alert("Must select an option for all users before continuing.");
				return;
			}

			UserRequests.approvepending = 0;

			// Loop through list and approve users. -2 so it doesnt hit the approve/deny all buttons
			for (var i = 0; i < inputs.length; i++) {
				var user = inputs[i].value.split(",")[0];
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

		//$('.tabbed').tabs();

		/*$('.add-row').on('click', function(e){
			e.preventDefault();

			var btn = $(this);

			var val = $(btn.attr('href')).val();
			if (!val) {
				return;
			}

			var container = btn.closest('ul'),
				data = {
					collegedeptid: val
				};

			$.post(btn.data('api'), data, function(result){
				var source   = $(btn.data('row')).html(),
					template = Handlebars.compile(source),
					context  = {
						"index" : container.find('li').length,
						"ancestors": result.data.ancestors, //[{name: 'foo'}, {name: 'bar'}],
						"name": val
					},
					html = template(context);

				$(html).insertBefore(container.find('li:last-child'));
			});
		});*/
		/*$('.add-fieldofscience-row').on('click', function(e){
			e.preventDefault();

			var val = $($(this).attr('href')).val();
			if (!val) {
				return;
			}

			var container = $(this).closest('ul');

			//$.post($(this).data('api'), data, function(e){
				var source   = $('#new-fieldofscience-row').html(),
					template = Handlebars.compile(source),
					context  = {
						"index" : container.find('li').length,
						"ancestors": [{name: 'foo'}, {name: 'bar'}],
						"name": val
					},
					html = template(context);

				$(html).insertBefore(container.find('li:last-child'));
			//});
		});*/
		$('.list-group').on('click', '.delete-row', function(e){
			e.preventDefault();

			var result = confirm('Are you sure you want to remove this?');

			if (result) {
				var container = $(this).closest('li');

				//$.post($(this).data('api'), data, function(e){
					container.remove();
				//});
			}
		});


		/*$('#new_group_btn').on('click', function (event) {
			event.preventDefault();

			CreateNewGroup();
		});
		$('#new_group_input').on('keyup', function (event) {
			if (event.keyCode == 13) {
				CreateNewGroup();
			}
		});

		$('#create_gitorg_btn').on('click', function (event) {
			event.preventDefault();
			CreateGitOrg($(this).data('value'));
		});

		$('.add-property').on('click', function(e){
			e.preventDefault();

			AddProperty($(this).data('prop'), $(this).data('value'));
		});
		$('.add-property-input').on('keyup', function(e){
			e.preventDefault();

			if (event.keyCode==13){
				AddProperty($(this).data('prop'), $(this).data('value'));
			}
		});*/

		$('.edit-property').on('click', function(e){
			e.preventDefault();

			var items = ['SPAN', 'INPUT', 'CANCEL', 'SAVE', 'EDIT'], item;
			for (var i = 0; i < items.length; i++)
			{
				item = $('#' + items[i] + '_' + $(this).data('prop') + '_' + $(this).data('value'));
				if (item.length) {
					item.toggleClass('hide');
				}
			}
		});

		$('.edit-property-input').on('keyup', function(event){
			if (event.keyCode == 13) {
				EditProperty($(this).data('prop'), $(this).data('value'));
			}
		});

		$('.cancel-edit-property').on('click', function(e){
			e.preventDefault();

			var items = ['SPAN', 'INPUT', 'CANCEL', 'SAVE', 'EDIT'], item;
			for (var i = 0; i < items.length; i++)
			{
				item = $('#' + items[i] + '_' + $(this).data('prop') + '_' + $(this).data('value'));
				if (item.length) {
					item.toggleClass('hide');
				}
			}
		});

		$('.save-property').on('click', function(e){
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
				error: function (xhr, ajaxOptions, thrownError) {
					//Halcyon.message('danger', xhr.response);
					//btn.find('spinner-border').toggleClass('hide');
					//btn.find('fa').toggleClass('hide');
					btn.attr('data-loading', false);
					alert(xhr.responseJSON.message);
					//console.log(xhr);
				}
			});
		});

		/*$('.create-default-unix-groups').on('click', function(e){
			e.preventDefault();
			CreateDefaultUnixGroups($(this).data('value'), $(this).data('group'));
		});*/
		$('.delete-unix-group').on('click', function(e){
			e.preventDefault();
			DeleteUnixGroup($(this).data('unixgroup'), $(this).data('value'));
		});

		$('.searchable-select').select2();

		//$('a.tab').on('shown.bs.tab', function(e){
		var inited = false;
		$('.tabs').on( "tabsactivate", function( event, ui ) {
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
							var select = $('<select class="data-col-filter" data-index="' + i + '"><option value="all">- All -</option><option value="selected">Selected</option><option value="not-selected">Not selected</option></select><br />')
								.prependTo($(column.header()));
						});

						$('.data-col-filter').on('change', function(){
							$.fn.dataTable.ext.search = [];//.pop();

							$('.data-col-filter').each(function(k, el){
								var val = $(this).val(),
								index = $(this).data('index');

								// If all records should be displayed
								if (val === 'all'){
									return;
								}

								// If selected records should be displayed
								if (val === 'selected'){
									$.fn.dataTable.ext.search.push(
										function (settings, data, dataIndex){
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
								if (val === 'not-selected'){
									$.fn.dataTable.ext.search.push(
										function (settings, data, dataIndex){
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
		});

		$('.membership-edit').on('click', function(e){
			e.preventDefault();

			$($(this).attr('href')).toggleClass('hidden');
		});

		var dialog = $(".membership-dialog").dialog({
			autoOpen: false,
			height: 'auto',
			width: 500,
			modal: true
		});

		$('.add_member').on('click', function(e){
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
			$('#addmembers').on('select2:select', function (e) {
				$('#add_member_save').prop('disabled', false);
			});
		});

		$('#add_member_save').on('click', function(e){
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

			$.each(users, function(i, userid) {
				post['userid'] = userid;

				$.ajax({
					url: btn.data('api'),
					type: 'post',
					data: post,
					dataType: 'json',
					async: false,
					success: function (data) {
						processed['users']++;

						queues.each(function(k, checkbox){
							$.ajax({
								url: btn.data('api-queueusers'),
								type: 'post',
								data: {
									'userid': userid,
									//'groupid': btn.data('group'),
									'queueid': checkbox.value,
								},
								dataType: 'json',
								async: false,
								success: function (data) {
									processed['queues']++;
									checkprocessed(processed, pending);
								},
								error: function (xhr, ajaxOptions, thrownError) {
									//Halcyon.message('danger', xhr.response);
									alert(xhr.responseJSON.message);
									processed['queues']++;
									checkprocessed(processed, pending);
								}
							});
							console.log(btn.data('api-queueusers'));
						});

						unixgroups.each(function(k, checkbox){
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
								success: function (data) {
									processed['unixgroups']++;
									checkprocessed(processed, pending);
								},
								error: function (xhr, ajaxOptions, thrownError) {
									//Halcyon.message('danger', xhr.response);
									alert(xhr.responseJSON.message);
									processed['unixgroups']++;
									checkprocessed(processed, pending);
								}
							});
							console.log(btn.data('api-unixgroupusers'));
						});
					},
					error: function (xhr, ajaxOptions, thrownError) {
						//Halcyon.message('danger', xhr.response);
						alert(xhr.responseJSON.message);
					}
				});
			});
			// Done?
		});

		// Remove user
		$('body').on('click', '.membership-remove', function(e){
			e.preventDefault();

			var row = $($(this).attr('href'));
			var boxes = row.find('input[type=checkbox]:checked');

			boxes.each(function(i, el) {
				$.ajax({
					url: $(el).data('api'),
					type: 'delete',
					dataType: 'json',
					async: false,
					success: function (data) {
					},
					error: function (xhr, ajaxOptions, thrownError) {
						if (xhr.status == 416) {
							SetError("Queue disabled for system/guest account. ACMaint Role removal must be requested manually from accounts@purdue.edu", null);
						}
						//alert(xhr.response);
					}
				});
			});

			if ($(this).data('api')) {
				$.ajax({
					url: $(this).data('api'),
					type: 'delete',
					dataType: 'json',
					async: false,
					success: function (data) {
						location.reload(true);
					},
					error: function (xhr, ajaxOptions, thrownError) {
						alert(xhr.responseJSON.message);
					}
				});
			}
		});

		$('body').on('click', '.membership-move', function(e){
			e.preventDefault();

			var parent = $($(this).attr('href'));

			parent.find('.membership-toggle').each(function(i, el){
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
					success: function (data) {
						location.reload(true);
					},
					error: function (xhr, ajaxOptions, thrownError) {
						alert(xhr.responseJSON.message);
					}
				});
			}
		});

		$('body').on('change', '.membership-toggle', function(e){
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
					success: function (data) {
					},
					error: function (xhr, ajaxOptions, thrownError) {
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
					success: function (data) {
					},
					error: function (xhr, ajaxOptions, thrownError) {
						if (xhr.status == 416) {
							alert("Queue disabled for system/guest account. ACMaint Role removal must be requested manually from accounts@purdue.edu", null);
						} else {
							alert(xhr.responseJSON.message);
						}
					}
				});
			}
		});

		$('body').on('click', '.membership-allqueues', function(e){
			e.preventDefault();

			var parent = $($(this).attr('href'));

			parent.find('.membership-toggle').each(function(i, el){
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

	function checkprocessed(processed, pending) {
		if (processed['users'] == pending['users']
		 && processed['queues'] == pending['queues']
		 && processed['unixgroups'] == pending['unixgroups']) {
			window.location.reload(true);
		}
	}
</script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('groups::groups.module name'),
		route('admin.groups.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit groups'))
		{!! Toolbar::save(route('admin.groups.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.groups.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('groups.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')


	<div class="tabs">
		<ul>
			<li>
				<a href="#group-details">{{ trans('global.details') }}</a>
			</li>
			@if ($row->id)
				<li>
					<a href="#group-members">{{ trans('groups::groups.members') }}</a>
				</li>
				@foreach ($sections as $section)
					<li>
						<a href="#group-{{ $section['route'] }}">{{ $section['name'] }}</a>
					</li>
				@endforeach
				<li>
					<a href="#group-motd">{{ trans('groups::groups.motd') }}</a>
				</li>
			@endif
		</ul>

		<div id="group-details">
			<form action="{{ route('admin.groups.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
			<div class="row">
				<div class="col col-md-7">
					<fieldset class="adminform">
						<legend>{{ trans('global.details') }}</legend>

						<div class="form-group">
							<label for="field-name">{{ trans('groups::groups.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
							<input type="text" name="fields[name]" id="field-name" class="form-control{{ $errors->has('fields.name') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $row->name }}" />
							<span class="invalid-feedback">{{ trans('groups::groups.invalid.title') }}</span>
						</div>

						<div class="row">
							<div class="col col-md-6">
								<div class="form-group">
									<label for="field-unixgroup">{{ trans('groups::groups.unix group base name') }}:</label>
									<input type="text" class="form-control input-unixgroup{{ $errors->has('fields.unixgroup') ? ' is-invalid' : '' }}" name="fields[unixgroup]" id="field-unixgroup" maxlength="10" pattern="[a-z0-9\-]+" value="{{ $row->unixgroup }}" />
									<span class="form-text text-muted">{{ trans('groups::groups.unix group base name hint') }}</span>
								</div>
							</div>
							<div class="col col-md-6">
								<div class="form-group">
									<label for="field-unixid">{{ trans('groups::groups.unix id') }}:</label>
									<input type="text" class="form-control" name="fields[unixid]" id="field-unixid" value="{{ $row->unixid }}" />
									<span class="form-text text-muted">{{ trans('groups::groups.unix group id') }}</span>
								</div>
							</div>
						</div>
					</fieldset>

					<fieldset class="adminform">
						<legend>{{ trans('groups::groups.unix groups') }}</legend>

						@if (count($row->unixGroups))
						<table class="table table-hover">
							<caption class="sr-only">{{ trans('groups::groups.unix groups') }}</caption>
							<thead>
								<tr>
									<th scope="col">{{ trans('groups::groups.id') }}</th>
									<th scope="col">{{ trans('groups::groups.unix group') }}</th>
									<th scope="col">{{ trans('groups::groups.short name') }}</th>
									<th scope="col" class="text-right">{{ trans('groups::groups.members') }}</th>
									<th scope="col" class="text-right"></th>
								</tr>
							</thead>
							<tbody>
								@foreach ($row->unixGroups as $i => $u)
									<tr id="unixgroup-{{ $u->id }}" data-id="{{ $u->id }}">
										<td>{{ $u->id }}</td>
										<td>{{ $u->longname }}</td>
										<td>{{ $u->shortname }}</td>
										<td class="text-right">{{ $u->members()->count() }}</td>
										<td class="text-right">
											<a href="#unixgroup-{{ $u->id }}" class="btn text-danger remove-unixgroup"
												data-api="{{ route('api.unixgroups.delete', ['id' => $u->id]) }}"
												data-confirm="{{ trans('groups::groups.confirm delete') }}">
												<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.trash') }}</span>
											</a>
										</td>
									</tr>
								@endforeach
								<tr class="hidden" id="unixgroup-{id}" data-id="{id}">
									<td>{id}</td>
									<td>{longname}</td>
									<td>{shortname}</td>
									<td class="text-right">0</td>
									<td class="text-right">
										<a href="#unixgroup-{id}" class="btn text-danger remove-unixgroup"
											data-api="{{ route('api.unixgroups.create') }}/{id}"
											data-confirm="{{ trans('groups::groups.confirm delete') }}">
											<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.trash') }}</span>
										</a>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td></td>
									<td colspan="3">
										<span class="input-group">
											<span class="input-group-prepend"><span class="input-group-text">{{ $row->unixgroup }}-</span></span>
											<input type="text" name="longname" id="longname" class="form-control input-unixgroup" maxlength="{{ (32 - strlen($row->unixgroup)) }}" pattern="[a-z0-9]+" placeholder="{{ strtolower(trans('groups::groups.name')) }}" />
										</span>
									</td>
									<td class="text-right">
										<a href="#longname" class="btn text-success add-unixgroup"
											data-group="{{ $row->id }}"
											data-api="{{ route('api.unixgroups.create') }}">
											<span class="fa fa-plus-circle" aria-hidden="true"></span><span class="sr-only">{{ trans('global.add') }}</span>
										</a>
									</td>
								</tr>
							</tfoot>
						</table>
						@else
							<p class="text-center"><span class="none">{{ trans('global.none') }}</span></p>
						@endif

						@if (!count($row->unixGroups))
							<div>
								<p class="text-center">
									<button class="btn btn-secondary create-default-unix-groups" data-api="{{ route('api.unixgroups.create') }}" data-group="{{ $row->id }}" data-value="{{ $row->unixgroup }}" data-all-groups="1" id="INPUT_groupsbutton_{{ $row->id }}">
										<span class="spinner-border spinner-border-sm d-none" role="status"></span> Create Default Unix Groups
									</button>
									<button class="btn btn-outline-secondary create-default-unix-groups" data-api="{{ route('api.unixgroups.create') }}" data-group="{{ $row->id }}" data-value="{{ $row->unixgroup }}" data-all-groups="0">
										<span class="spinner-border spinner-border-sm" role="status"></span> Create Base Group Only
									</button>
								</p>
								<p class="form-text">This will create default Unix groups; A base group, `apps`, and `data` group will be created. These will prefixed by the base name chosen. Once these are created, the groups and base name cannot be easily changed.</p>
							</div>
						@endif
					</fieldset>
				</div>
				<div class="col col-md-5">
					<fieldset class="adminform">
						<legend>{{ trans('groups::groups.department') }}</legend>

						<table class="table table-hover">
							<caption class="sr-only">{{ trans('groups::groups.department') }}</caption>
							<tbody>
							@foreach ($row->departments as $dept)
								<tr id="department-{{ $dept->id }}" data-id="{{ $dept->id }}">
									<td>
										<?php
										$prf = '';
										foreach ($dept->department->ancestors() as $ancestor):
											if (!$ancestor->parentid):
												continue;
											endif;

											$prf .= $ancestor->name . ' > ';
										endforeach;
										?>{{ $prf . $dept->department->name }}
									</td>
									<td class="text-right">
										<a href="#department-{{ $dept->id }}" class="btn text-danger remove-category"
											data-api="{{ route('api.groups.groupdepartments.delete', ['group' => $row->id, 'id' => $dept->id]) }}"
											data-confirm="{{ trans('groups::groups.confirm delete') }}">
											<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.trash') }}</span>
										</a>
									</td>
								</tr>
							@endforeach
								<tr class="hidden" id="department-{id}" data-id="{id}">
									<td>{name}</td>
									<td class="text-right">
										<a href="#department-{id}" class="btn text-danger remove-category"
											data-api="{{ route('api.groups.groupdepartments.create', ['group' => $row->id]) }}/{id}"
											data-confirm="{{ trans('groups::groups.confirm delete') }}">
											<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.trash') }}</span>
										</a>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td>
										<div class="form-group">
										<select name="department" id="department" data-category="collegedeptid" class="form-control searchable-select">
											<option value="0">{{ trans('groups::groups.select department') }}</option>
											@foreach ($departments as $d)
												@php
												if ($d->level == 0):
													continue;
												endif;

												$prf = '';
												foreach ($d->ancestors() as $ancestor):
													if (!$ancestor->parentid):
														continue;
													endif;

													$prf .= $ancestor->name . ' > ';
												endforeach;
												@endphp
												<option value="{{ $d->id }}">{{ $prf . $d->name }}</option>
											@endforeach
										</select>
										</div>
									</td>
									<td class="text-right">
										<a href="#department"
											class="btn text-success add-category"
											data-group="{{ $row->id }}"
											data-api="{{ route('api.groups.groupdepartments.create', ['group' => $row->id]) }}">
											<span class="fa fa-plus-circle" aria-hidden="true"></span><span class="sr-only">{{ trans('global.add') }}</span>
										</a>
									</td>
								</tr>
							</tfoot>
						</table>
					</fieldset>

					<fieldset class="adminform">
						<legend>{{ trans('groups::groups.field of science') }}</legend>

						<table class="table table-hover">
							<caption class="sr-only">{{ trans('groups::groups.field of science') }}</caption>
							<tbody>
							@foreach ($row->fieldsOfScience as $field)
								<tr id="fieldofscience-{{ $field->id }}" data-id="{{ $field->id }}">
									<td>
										<?php
										$prf = '';
										foreach ($field->field->ancestors() as $ancestor):
											if (!$ancestor->parentid):
												continue;
											endif;

											$prf .= $ancestor->name . ' > ';
										endforeach;
										?>{{ $prf . $field->field->name }}
									</td>
									<td class="text-right">
										<a href="#fieldofscience-{{ $field->id }}" class="btn text-danger remove-category"
											data-api="{{ route('api.groups.groupfieldsofscience.delete', ['group' => $row->id, 'id' => $field->id]) }}"
											data-confirm="{{ trans('groups::groups.confirm delete') }}">
											<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.trash') }}</span>
										</a>
									</td>
								</tr>
							@endforeach
								<tr class="hidden" id="fieldofscience-{id}" data-id="{id}">
									<td>{name}</td>
									<td class="text-right">
										<a href="#fieldofscience-{id}" class="btn text-danger remove-category"
											data-api="{{ route('api.groups.groupfieldsofscience.create', ['group' => $row->id]) }}/{id}"
											data-confirm="{{ trans('groups::groups.confirm delete') }}">
											<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.trash') }}</span>
										</a>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td>
										<div class="form-group">
										<select name="fieldofscience" id="fieldofscience" data-category="fieldofscienceid" class="form-control searchable-select">
											<option value="0">{{ trans('groups::groups.select field of science') }}</option>
											@foreach ($fields as $f)
												@php
												if ($f->level == 0):
													continue;
												endif;

												$prf = '';
												foreach ($f->ancestors() as $ancestor):
													if (!$ancestor->parentid):
														continue;
													endif;

													$prf .= $ancestor->name . ' > ';
												endforeach;
												@endphp
												<option value="{{ $f->id }}">{{ $prf . $f->name }}</option>
											@endforeach
										</select>
										</div>
									</td>
									<td class="text-right">
										<a href="#fieldofscience"
											class="btn text-success add-category"
											data-group="{{ $row->id }}"
											data-api="{{ route('api.groups.groupfieldsofscience.create', ['group' => $row->id]) }}">
											<span class="fa fa-plus-circle" aria-hidden="true"></span><span class="sr-only">{{ trans('global.add') }}</span>
										</a>
									</td>
								</tr>
							</tfoot>
						</table>
					</fieldset>

					<input type="hidden" name="id" value="{{ $row->id }}" />
				</div>
			</div>
			@csrf
			</form>
		</div>

		@if ($row->id)
			<div id="group-members">
				@include('groups::admin.groups.members', ['group' => $row])
			</div>

			@foreach ($sections as $section)
				<div id="group-{{ $section['route'] }}">
					{!! $section['content'] !!}
				</div>
			@endforeach

			<div id="group-motd">
				@include('groups::admin.groups.motd', ['group' => $row])
			</div>
		@endif
	</div>


@stop