@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/datatables/datatables.bootstrap.min.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/handlebars/handlebars.min-v4.7.6.js') }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/datatables.bootstrap.min.js') }}"></script>
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


		$('#new_group_btn').on('click', function (event) {
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
		});
		$('.edit-property').on('click', function(e){
			e.preventDefault();

			EditProperty($(this).data('prop'), $(this).data('value'));
		});
		$('.edit-property-input').on('keyup', function(event){
			if (event.keyCode==13){
				EditProperty($(this).data('prop'), $(this).data('value'));
			}
		});
		$('.cancel-edit-property').on('click', function(e){
			e.preventDefault();

			CancelEditProperty($(this).data('prop'), $(this).data('value'));
		});
		$('.create-default-unix-groups').on('click', function(e){
			e.preventDefault();
			CreateDefaultUnixGroups($(this).data('value'), $(this).data('group'));
		});
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
				searchPlaceholder: "Filter users..."
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
console.log(queues);
console.log(unixgroups);
			$.each(users, function(i, userid) {
				post['userid'] = userid;
console.log(post);
				/*$.ajax({
					url: btn.getAttribute('data-api'),
					type: 'post',
					data: post,
					dataType: 'json',
					async: false,
					success: function (data) {
						//window.location.reload();
					},
					error: function (xhr, ajaxOptions, thrownError) {
						Halcyon.message('danger', xhr.response);
					}
				});*/

				queues.each(function(k, checkbox){
					$.ajax({
						url: checkbox.getAttribute('data-api'),
						type: 'post',
						data: {
							'userid': userid,
						},
						dataType: 'json',
						async: false,
						success: function (data) {
							//window.location.reload();
						},
						error: function (xhr, ajaxOptions, thrownError) {
							Halcyon.message('danger', xhr.response);
						}
					});
				});
			});
		});
	});
</script>
@endpush


@php
$canManage = auth()->user()->can('edit groups') || (auth()->user()->can('edit.own groups') && $group->ownerid == $user->id);
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
						<span class="badge{{ $membership->isManager() ? ' badge-success' : '' }}">{{ $membership->type->name }}</span>
					@endif
				@endif
			</div>
		</div>

		<!-- 
		@if (auth()->user()->can('manage users'))
			<div class="card panel panel-default card-admin">
				<div class="card-header panel-heading">
					Admin Options
				</div>
				<div class="card-body panel-body">
					<form method="get" action="{{ route('site.users.account.section', ['section' => 'groups']) }}">
						<div class="form-group">
							<label for="newuser">Search for someone:</label>
							<div class="input-group">
								<input type="text" name="newuser" id="newuser" class="form-control searchuser" autocorrect="off" autocapitalize="off" />
								<div id="user_results" class="searchMain usersearch_results"></div>
								<div class="input-group-addon">
									<span class="input-group-text">
										<i class="fa fa-search" aria-hidden="true" id="add_button_a"></i>
										<img src="/include/images/loading.gif" width="14" id="search_loading" alt="Loading..." class="icon" />
									</span>
								</div>
							</div>
							<span id="add_errors"></span>
						</div>
					</form>

					@if ($user->id != auth()->user()->id)
						<p>
							Showing information for "{{ $user->name }}":
						</p>
					@endif
				</div>
			</div>
		@endif
		 -->

		<div id="everything">
			<ul class="nav nav-tabs tabs">
				<li class="nav-item">
					<a href="#DIV_group-overview" id="group-overview" class="nav-link tab active activeTab">
						Overview
					</a>
				</li>
				<li class="nav-item">
					<a href="#DIV_group-members" id="group-members" class="nav-link tab">
						Members
					</a>
				</li>
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

	</div>
