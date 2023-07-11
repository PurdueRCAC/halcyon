@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tagsinput/jquery.tagsinput.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tagsinput/jquery.tagsinput.js') }}"></script>
<script src="{{ timestamped_asset('modules/groups/js/admin.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var autocompleteUsers = function(url) {
		return function(request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
				response($.map(data.data, function (el) {
					return {
						label: el.name + ' (' + el.username + ')',
						name: el.name,
						id: el.id,
					};
				}));
			});
		};
	};

	var newsuser = $(".form-users");
	if (newsuser.length) {
		newsuser.tagsInput({
			placeholder: 'Select user...',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteUsers(newsuser.attr('data-uri')),
				dataName: 'data',
				height: 150,
				delay: 100,
				minLength: 1,
				open: function (e, ui) {
					var acData = $(this).data('ui-autocomplete');

					acData
						.menu
						.element
						.find('.ui-menu-item-wrapper')
						.each(function () {
							var me = $(this);
							var regex = new RegExp('(' + acData.term + ')', "gi");
							me.html(me.text().replace(regex, '<strong>$1</strong>'));
						});
				}
			}
		});
	}

	var dialog = $(".dialog").dialog({
		autoOpen: false,
		height: 'auto',
		width: 500,
		modal: true
	});

	$('#toolbar-plus').on('click', function(e){
		e.preventDefault();

		dialog.dialog("open");
	});

	$('#add-member').on('click', function(e){
		e.preventDefault();

		var url = $(this).data('api');
		var group = document.getElementById("group").value;
		var type = document.getElementById("membertype").value;
		var name = "";
		var processed = 0;

		var users = $('.tagsinput').find('.tag');
			users.each(function(i, el) {
				name = $($(el).find('.tag-text')[0]).text();

				$.ajax({
					url: url,
					type: 'post',
					data: {
						groupid: group,
						userid: $(el).data('value'),
						membertype: type
					},
					dataType: 'json',
					async: false,
					success: function(data) {
						Halcyon.message('success', 'Added ' + name);
						processed++;
					},
					error: function(xhr, ajaxOptions, thrownError) {
						Halcyon.message('danger', 'Failed to add ' + name);
						processed++;
					}
				});
			});

		if (processed == users.length) {
			location.reload();
		}

		/*var usersdata = document.getElementById("users").value.split(',');
		for (i=0; i<usersdata.length; i++) {
			if (!usersdata[i]) {
				continue;
			}

			$.ajax({
				url: $(this).data('api'),
				type: 'post',
				data: {
					groupid: group,
					userid: usersdata[i],
					membertype: type
				},
				dataType: 'json',
				async: false,
				success: function(data) {
					Halcyon.message('success', 'added!');
				},
				error: function(xhr, ajaxOptions, thrownError) {
					Halcyon.message('danger', 'Failed to add .');
				}
			});
		}
		});*/
	});
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
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="icon-search" aria-hidden="true"></span><span class="sr-only">{{ trans('search.submit') }}</span></button></span>
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

	<div class="dialog ui-front hide" title="{{ trans('groups::groups.add member') }}">
		<h3 class="sr-only">{{ trans('groups::groups.add member') }}</h3>

		<div class="form-group">
			<label for="field-users">{{ trans('groups::groups.add users') }}</label>
			<input type="text" name="users" id="users" class="form-control form-users" data-uri="{{ url('/') }}/api/users/?api_token={{ auth()->user()->api_token }}&search=%s" value="" />
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

		<div class="form-group text-center">
			<button class="btn btn-primary" id="add-member" data-api="{{ route('api.groups.members.create') }}"><span class="icon-plus"></span> Add</button>
		</div>
	</div>

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
@stop
