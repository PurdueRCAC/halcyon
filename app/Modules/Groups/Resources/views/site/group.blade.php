@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap.min.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/handlebars/handlebars.min-v4.7.6.js') }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/groups/js/site.js?v=' . filemtime(public_path() . '/modules/groups/js/site.js')) }}"></script>
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
	 * Approvet a user request
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
			var inputs = $('.approve-request:checked');

			if (!inputs.length) {
				alert("Must select an option for all users before continuing.");
				return;
			}

			UserRequests.approvepending = 0;

			// Loop through list and approve users. -2 so it doesnt hit the approve/deny all buttons
			for (var i = 0; i < inputs.length - 2; i++) {
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

			//EditProperty($(this).data('prop'), $(this).data('value'));
			/*$('#SPAN_' + $(this).data('prop') + '_' + $(this).data('value')).toggleClass('hide');
			$('#INPUT_' + $(this).data('prop') + '_' + $(this).data('value')).toggleClass('hide');
			$('#CANCEL_' + $(this).data('prop') + '_' + $(this).data('value')).toggleClass('hide');
			$('#SAVE_' + $(this).data('prop') + '_' + $(this).data('value')).toggleClass('hide');
			$('#EDIT_' + $(this).data('prop') + '_' + $(this).data('value')).toggleClass('hide');*/
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

			//CancelEditProperty($(this).data('prop'), $(this).data('value'));
			/*$('#SPAN_' + $(this).data('prop') + '_' + $(this).data('value')).toggleClass('hide');
			$('#INPUT_' + $(this).data('prop') + '_' + $(this).data('value')).toggleClass('hide');
			$('#CANCEL_' + $(this).data('prop') + '_' + $(this).data('value')).toggleClass('hide');
			$('#SAVE_' + $(this).data('prop') + '_' + $(this).data('value')).toggleClass('hide');
			$('#EDIT_' + $(this).data('prop') + '_' + $(this).data('value')).toggleClass('hide');*/
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

		$('.datatable').DataTable({
			pageLength: 20,
			pagingType: 'numbers',
			info: false,
			ordering: false,
			lengthChange: false,
			scrollX: true,
			autoWidth: false,
			language: {
				searchPlaceholder: "Filter users...",
				search: "_INPUT_",
			},
			fixedColumns: {
				leftColumns: 1//,
				//rightColumns: 1
			},
			/*lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],*/
			initComplete: function () {
				//this.page(0).draw(true);
				$($.fn.dataTable.tables(true)).css('width', '100%');
				//$($.fn.dataTable.tables( true ) ).DataTable().columns.adjust();//.draw();
				/*this.api().columns().every(function (i) {
					var column = this;
					var select = $('<select data-index="' + i + '"><option value=""></option></select>')
						.appendTo($(column.footer()).empty());

					column.data().unique().sort().each(function (d, j) {
						select.append('<option value="'+d+'">'+d+'</option>');
					});
				});

				var table = this;

				$(table.api().table().container()).on('change', 'tfoot select', function () {
					var val = $.fn.dataTable.util.escapeRegex(
						$(this).val()
					);

					table.api()
						.column($(this).data('index'))
						.search(val ? '^'+val+'$' : '', true, false)
						.draw();
				});*/
			}
		});
		$('a.tab').on('shown.bs.tab', function(e){
			$($.fn.dataTable.tables(true)).DataTable().columns.adjust();//.draw();
		});

		$('.membership-edit').on('click', function(e){
			e.preventDefault();

			$($(this).attr('href')).toggleClass('hidden');
		});

		/*
		 $('a[data-toggle="tab"]').on( 'shown.bs.tab', function (e) {
			$($.fn.dataTable.tables( true ) ).css('width', '100%');
			$($.fn.dataTable.tables( true ) ).DataTable().columns.adjust().draw();
		});
		*/

		//$('.dataTables_filter input').addClass('form-control');

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
					type: 'put',
					data: {
						membertype: $(this).data('target')
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
$canManage = auth()->user()->can('edit groups') || (auth()->user()->can('edit.own groups') && $group->isManager(auth()->user()));
@endphp

	<div class="contentInner">
		<div class="row">
			<div class="col-md-9">
				<h2>{{ $group->name }}</h2>
			</div>
			<div class="col-md-3 text-right">
				@if ($membership)
					@if ($membership->isTrashed())
						<span class="badge badge-danger">{{ trans('users::users.removed') }}</span>
					@elseif ($membership->membertype == 4)
						<span class="badge badge-warning">{{ $membership->type->name }}</span>
					@else
						<span class="badge {{ $membership->isManager() ? 'badge-success' : 'badge-secondary' }}">{{ $membership->type->name }}</span>
					@endif
				@endif
			</div>
		</div>

		<div id="everything">
			<ul class="nav nav-tabs tabs">
				<li class="nav-item">
					<a href="#DIV_group-overview" id="group-overview" class="nav-link tab active activeTab">
						Overview
					</a>
				</li>
			@if ($canManage)
				<li class="nav-item">
					<a href="#DIV_group-members" id="group-members" class="nav-link tab">
						Members
					</a>
				</li>
			@endif
			@foreach ($sections as $section)
				<li class="nav-item">
					<a href="#DIV_group-{{ $section['route'] }}" id="group-{{ $section['route'] }}" class="nav-link tab">{{ $section['name'] }}</a>
				</li>
			@endforeach
			@if ($canManage)
				<li class="nav-item">
					<a href="#DIV_group-motd" id="group-motd" class="nav-link tab">
						Notices
					</a>
				</li>
				<li class="nav-item">
					<a href="#DIV_group-history" id="group-history" class="nav-link tab">
						History
					</a>
				</li>
			@endif
			</ul>

			<input type="hidden" id="groupid" value="{{ $group->id }}" />
			<input type="hidden" id="HIDDEN_property_{{ $group->id }}" value="{{ $group->id }}" />
			<!-- <div class="tabMain" id="tabMain"> -->

				<div id="DIV_group-overview">
					@include('groups::site.group.overview', ['group' => $group])
				</div><!-- / #group-overview -->

				<div id="DIV_group-members" class="stash">
					@include('groups::site.group.members', ['group' => $group])
				</div><!-- / #group-members -->

			@foreach ($sections as $section)
				<div id="DIV_group-{{ $section['route'] }}" class="stash">
					{{ $section['content'] }}
				</div>
			@endforeach

			@if ($canManage)
				<div id="DIV_group-motd" class="stash">
					@include('groups::site.group.motd', ['group' => $group])
				</div><!-- / #group-motd -->

				<div id="DIV_group-history" class="stash">
					@include('groups::site.group.history', ['group' => $group])
				</div><!-- / #group-history -->
			@endif

			<!--</div>-->
		</div><!-- / #everything -->

	</div><!-- / .contentInner -->
