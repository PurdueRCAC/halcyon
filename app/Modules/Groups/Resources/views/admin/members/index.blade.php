@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var btnnew = document.getElementById('toolbar-plus');
	if (btnnew) {
		btnnew.setAttribute('data-toggle', 'modal');
		btnnew.setAttribute('data-target', '#new-member');

		btnnew.addEventListener('click', function (e) {
			e.preventDefault();
		});
	}

	var addmembers = document.getElementById("users");
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
				}).catch(function (err) {
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
			document.getElementById('add-member').disabled = false;
		});
	}

	var addmem = document.getElementById('add-member');
	if (addmem) {
		addmem.addEventListener('click', function(e) {
			e.preventDefault();

			var url = this.getAttribute('data-api');
			var group = document.getElementById('group').value;
			var type = document.getElementById('membertype').value;
			var name = '';
			var processed = 0;

			var users = addmembers.value.split(',');
			users.forEach(function (el) {
				name = el;

				fetch(url, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					},
					body: JSON.stringify({
						groupid: group,
						userid: el.getAttribute('data-value'),
						membertype: type
					})
				})
				.then(function (response) {
					if (response.ok) {
						Halcyon.message('success', 'Added ' + name);
						processed++;
						return;
					}
					return response.json().then(function (data) {
						var msg = data.message;
						msg = (typeof msg === 'object' ? Object.values(msg).join('<br />') : msg);
						throw msg;
					});
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
					processed++;
				});
			});

			if (processed == users.length) {
				location.reload();
			}
		});
	}
});
</script>
@endpush

@php
app('pathway')
	->append(
		trans('groups::groups.module name'),
		route('admin.groups.index')
	)
	->append(
		trans('groups::groups.groups'),
		route('admin.groups.index')
	)
	->append(
		$group->name,
		route('admin.groups.edit', ['id' => $group->id])
	)
	->append(
		trans('groups::groups.members'),
		route('admin.groups.members', ['group' => $group->id])
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete groups'))
		{!! Toolbar::deleteList('', route('admin.groups.members.delete', ['group' => $group->id])) !!}
	@endif

	@if (auth()->user()->can('create groups'))
		{!! Toolbar::addNew(route('admin.groups.members.create', ['group' => $group->id])) !!}
	@endif

	@if (auth()->user()->can('admin groups'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('groups')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('groups::groups.module name') }}: {{ $group->name }}: Members
@stop

@section('content')
<form action="{{ route('admin.groups.members', ['group' => $group->id]) }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>

				<button type="submit" class="btn btn-secondary sr-only">{{ trans('search.submit') }}</button>

				<input type="hidden" name="group" value="{{ $filters['group'] }}" />
				<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
				<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />
			</div>
			<div class="col col-md-8 filter-select text-right">
				<label class="sr-only" for="filter-state">{{ trans('groups::groups.state') }}</label>
				<select name="state" id="filter-state" class="form-control filter filter-submit">
					<option value="*">{{ trans('groups::groups.all states') }}</option>
					<option value="active"<?php if ($filters['state'] == 'active') { echo ' selected="selected"'; } ?>>{{ trans('global.active') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed') { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter-type">{{ trans('groups::groups.membership type') }}</label>
				<select name="type" id="filter-type" class="form-control filter filter-submit">
					<option value="0">{{ trans('groups::groups.select membership type') }}</option>
					<?php foreach ($types as $type): ?>
						<option value="<?php echo $type->id; ?>"<?php if ($filters['type'] == $type->id) { echo ' selected="selected"'; } ?>>{{ $type->name }}</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<input type="hidden" name="group" id="group" value="{{ $group->id }}" autocomplete="off" />
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ $group->name }} &rsaquo; trans('groups::groups.members')</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('edit groups'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('groups::groups.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('groups::groups.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{{ trans('groups::groups.username') }}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('groups::groups.joined'), 'datecreated', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('groups::groups.last visit'), 'datelastseen', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('groups::groups.type'), 'membertype', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr<?php if (($row->user && $row->user->trashed()) || $row->trashed()) { echo ' class="trashed"'; } ?>>
				@if (auth()->user()->can('edit groups'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-5">
					@if (($row->user && $row->user->trashed()) || $row->trashed())
						<span class="fa fa-trash text-danger" aria-hidden="true"></span>
					@endif
					{{ $row->id }}
				</td>
				<td>
					@if ($row->user && $row->user->trashed())
						<span class="fa fa-exclamation-triangle text-warning" data-tip="{{ trans('groups::groups.user account removed') }}">{{ trans('groups::groups.user account removed') }}</span>
					@endif
					@if (auth()->user()->can('edit users'))
						<a href="{{ route('admin.users.edit', ['id' => $row->userid]) }}">
					@endif
							{{ $row->user ? $row->user->name : trans('global.unknown') . ': ' . $row->userid }}
					@if (auth()->user()->can('edit users'))
						</a>
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit users'))
						<a href="{{ route('admin.users.edit', ['id' => $row->userid]) }}">
					@endif
							{{ $row->user ? $row->user->username : trans('global.unknown') }}
					@if (auth()->user()->can('edit users'))
						</a>
					@endif
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->datecreated)
							<time datetime="{{ $row->datecreated->toDateTimeLocalString() }}">{{ $row->datecreated }}</time>
						@else
							<span class="never">{{ trans('global.never') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->datelastseen)
							<time datetime="{{ $row->datelastseen->toDateTimeLocalString() }}">{{ $row->datelastseen }}</time>
						@else
							<span class="never">{{ trans('global.never') }}</span>
						@endif
					</span>
				</td>
				<td>
					@if (($row->user && $row->user->trashed()) || $row->trashed())
						{{ $row->type->name }}
					@else
						<?php
						$cls = ($row->membertype == 1) ? 'btn-success' : 'btn-warning';
						$cls = ($row->membertype != 3) ? $cls : 'btn-danger';
						?>
						@if (auth()->user()->can('edit groups'))
							<select name="membertype_{{ $row->id }}" data-api="{{ route('api.groups.members.update', ['id' => $row->id]) }}" class="form-control form-control-sm membertype">
								@foreach ($types as $type)
									@if ($type->id == 1 || $type->id == 2 || $type->id == 3)
										<option value="{{ $type->id }}"<?php if ($row->membertype == $type->id) { echo ' selected="selected"'; } ?>>{{ $type->name }}</option>
									@endif
								@endforeach
							</select>
						@else
							{{ $type->name }}
						@endif
					<!-- <div class="btn-group btn-group-sm dropdown" role="group" aria-label="Group membership type">
						<button type="button" class="btn btn-secondary {{ $cls }} dropdown-toggle" id="btnGroupDrop{{ $row->id }}" title="{{ trans('groups::groups.membership type') }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							{{ $row->type->name }}
						</button>
						@if (auth()->user()->can('edit groups'))
							<ul class="dropdown-menu" aria-labelledby="btnGroupDrop{{ $row->id }}">
								@foreach ($types as $type)
									@if ($type->id != $row->membertype && ($type->id == 1 || $type->id == 2 || $type->id == 3))
										<li class="dropdown-item">
											<a class="grid-action" data-id="cb{{ $i }}" href="{{ route('admin.groups.members.edit', ['group' => $row->groupid, 'id' => $row->id, 'type' => $type->id]) }}">{{ $type->name }}</a>
										</li>
									@endif
								@endforeach
							</ul>
						@endif
					</div> -->
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
		</div>
	</div>
	{{ $rows->render() }}
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif

	<script id="new-fieldofscience-row" type="text/x-handlebars-template">
		<tr class="list-group-item" <?php echo '{{ row.id }}'; ?>>
			<td>
				<?php if (auth()->user()->can('edit groups')): ?>
					<span class="form-check"><input type="checkbox" name="id[]" id="cb<?php echo '{{ i }}'; ?>" value="<?php echo '{{ row.id }}'; ?>" class="form-check-input checkbox-toggle" /><label for="cb<?php echo '{{ i }}'; ?>"></label></span>
				<?php endif; ?>
			</td>
			<td class="priority-5">
				<?php echo '{{#if ((row.user && $row.user.deleted_at) || row.dateremoved)}}'; ?>
					<span class="fa fa-trash text-danger" aria-hidden="true"></span>
				<?php echo '{{/if}}'; ?>
				<?php echo '{{ row.id }}'; ?>
			</td>
			<td>
				<?php echo '{{#if (row.user && $row.user.deleted_at)}}'; ?>
					<span class="fa fa-exclamation-triangle text-warning" data-tip="{{ trans('groups::groups.user account removed') }}">{{ trans('groups::groups.user account removed') }}</span>
				<?php echo '{{/if}}'; ?>
				<?php if (auth()->user()->can('edit users')): ?>
					<a href="<?php echo '{{row.user.route}}'; ?>">
				<?php endif; ?>
						<?php echo '{{row.user ? row.user.name : "' . trans('global.unknown') . ': " + row.userid }}'; ?>
				<?php if (auth()->user()->can('edit users')): ?>
					</a>
				<?php endif; ?>
			</td>
			<td>
				<?php if (auth()->user()->can('edit users')): ?>
					<a href="<?php echo '{{row.user.route}}'; ?>">
				<?php endif; ?>
						<?php echo '{{row.user ? row.user.username : ' . trans('global.unknown') . '}}'; ?>
				<?php if (auth()->user()->can('edit users')): ?>
					</a>
				<?php endif; ?>
			</td>
		</tr>
	</script>

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
</form>

<div class="modal modal-help" id="new-member" tabindex="-1" aria-labelledby="new-member-title" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
		<form id="form_{{ $group->id }}" action="{{ route('admin.groups.members.create', ['group' => $group->id]) }}" method="post" class="modal-content shadow-sm">
			<div class="modal-header">
				<div class="modal-title" id="new-member-title">Add users to {{ $group->name }}</div>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="field-users">{{ trans('groups::groups.add users') }}</label>
					<input type="text" name="users" id="users" class="form-control form-users" data-api="{{ route('api.users.index') }}" data-group="{{ $group->id }}" placeholder="Username, email address, etc." value="" />
				</div>

				<div class="form-group">
					<label for="field-membertype">{{ trans('groups::groups.member type') }}</label>
					<select name="membertype" id="membertype" class="form-control">
						@foreach ($types as $type)
							@if ($type->id == 1 || $type->id == 2 || $type->id == 3)
								<option value="{{ $type->id }}">{{ $type->name }}</option>
							@endif
						@endforeach
					</select>
				</div>
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<input type="button" disabled="disabled" id="add-member" class="btn btn-success"
							data-group="{{ $group->id }}"
							data-api="{{ route('api.groups.members.create') }}"
							data-api-unixgroupusers="{{ route('api.unixgroups.members.create') }}"
							data-api-queueusers="{{ route('api.queues.users.create') }}"
							value="{{ trans('global.button.save') }}" />
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
@stop
