@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/users/css/users.css?v=' . filemtime(public_path() . '/modules/users/css/users.css')) }}" />
@endpush

@php
app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		trans('users::users.users')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete users'))
		{!! Toolbar::deleteList('', route('admin.users.delete')) !!}
	@endif

	@if (auth()->user()->can('create users'))
		{!! Toolbar::addNew(route('admin.users.create')) !!}
	@endif

	@if (auth()->user()->can('admin users'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('users');
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
	{{ trans('users::users.users') }}
@stop

@section('content')

@component('users::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.users.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-xs-12 col-sm-3 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-xs-12 col-sm-9 text-right filter-select">
				<label class="sr-only" for="filter-state">{{ trans('users::users.state') }}:</label>
				<select name="state" id="filter-state" class="form-control filter filter-submit">
					<option value="*">{{ trans('users::users.all states') }}</option>
					<option value="enabled"<?php if ($filters['state'] == 'enabled'): echo ' selected="selected"'; endif;?>>{{ trans('users::users.status enabled') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('users::users.status trashed') }}</option>
				</select>

				<label class="sr-only" for="filter-role_id">{{ trans('users::users.usergroup') }}:</label>
				<select name="role_id" id="filter-role_id" class="form-control filter filter-submit">
					<option value="0">{{ trans('users::users.all roles') }}</option>
					<?php foreach (App\Modules\Users\Helpers\Admin::getAccessRoles() as $role): ?>
						<option value="{{ $role->value }}"<?php if ($filters['role_id'] == $role->value): echo ' selected="selected"'; endif;?>>{{ $role->text }}</option>
					<?php endforeach; ?>
				</select>

				<label class="sr-only" for="filter-range">{{ trans('users::users.registration date') }}:</label>
				<select name="range" id="filter-range" class="form-control filter filter-submit">
					<option value="">{{ trans('users::users.select registration date') }}</option>
					<?php foreach (App\Modules\Users\Helpers\Admin::getRangeOptions() as $value => $text): ?>
						<option value="{{ $value }}"<?php if ($filters['range'] == $value): echo ' selected="selected"'; endif;?>>{{ $text }}</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('users::users.users') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('users::users.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('users::users.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('users::users.username'), 'username', $filters['order_dir'], $filters['order']) !!}
				</th>
				@if (auth()->user()->can('admin'))
				<th scope="col" colspan="2" class="priority-3 nowrap">
				@else
				<th scope="col" class="priority-3 nowrap">
				@endif
					{{ trans('users::users.roles') }}
				</th>
				<th scope="col" class="priority-3">{{ trans('users::users.status') }}</th>
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('users::users.last visit'), 'last_visit', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<?php
			$canEdit   = auth()->user()->can('edit users');
			$canChange = auth()->user()->can('edit.state users');

			// If this group is super admin and this user is not super admin, $canEdit is false
			if (!auth()->user()->can('admin') && App\Halcyon\Access\Gate::check($row->id, 'admin')):
				$canEdit   = false;
				$canChange = false;
			endif;

			if (!$row->surname && !$row->given_name):
				$bits = explode(' ', $row->name);

				$row->surname = array_pop($bits);

				if (count($bits) >= 1):
					$row->given_name = array_shift($bits);
				endif;

				if (count($bits) >= 1):
					$row->middle_name = implode(' ', $bits);
				endif;
			endif;

			$row->name = $row->given_name . ($row->middle_name ? ' ' . $row->middle_name : '') . ' ' . $row->surname;

			$groups = array();
			foreach ($row->roles as $role):
				$groups[] = $role->role->title; //$accessgroups->seek($agroup->group_id)->title;
			endforeach;
			$row->role_names = implode('<br />', $groups);

			$incomplete = false;
			$authenticator = 'database';
			/*if (substr($row->email, -8) == '@invalid'):
				$authenticator = trans('global.unknown');
				if ($lnk = App\Halcyon\Auth\Link::find_by_id(abs($row->username))):
					$domain = App\Halcyon\Auth\Domain::find_by_id($lnk->auth_domain_id);
					$authenticator = $domain->authenticator;
				endif;
				$incomplete = true;
			endif;*/
			?>
			<tr<?php if ($row->isTrashed()) { echo ' class="trashed"'; } ?>>
				<td>
					@if ($canEdit)
						{!! Html::grid('id', $i, $row->id) !!}
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if ($canEdit)
						<a href="{{ route('admin.users.edit', ['id' => $row->id]) }}">
							{{ $row->name }}
						</a>
					@else
						{{ $row->name }}
					@endif
				</td>
				<td class="priority-4">
					@if ($canChange)
						<a href="{{ route('admin.users.edit', ['id' => $row->id]) }}">
					@endif
						@if ($row->username)
							{{ $row->username }}
						@else
							<span class="unknown">{{ trans('global.none') }}</span>
						@endif
					@if ($canChange)
						</a>
					@endif
				</td>
				@if ($canChange)
				<td class="text-center priority-3">
						<a class="permissions glyph icon-settings tip" href="{{ route('admin.users.debug', ['id' => $row->id]) }}" data-tip="{{ trans('users::users.debug user') }}">
							{{ trans('users::users.debug user') }}
						</a>
				</div>
				@endif
				<td class="priority-3">
					@if (substr_count($row->role_names, "\n") > 1)
						<span class="hasTip" title="{{ trans('users::users.roles') . '::' . $row->role_names }}">{{ trans('users::users.roles') }}</span>
					@else
						{!! $row->role_names !!}
					@endif
				</td>
				<td class="priority-4">
					@if ($row->isTrashed())
						<span class="badge badge-danger">
							{{ trans('users::users.status trashed') }}
						</span>
					@else
						<span class="badge badge-success">
							{{ trans('users::users.status enabled') }}
						</span>
					@endif
				</td>
				<td class="priority-6">
					@if (!$row->hasVisited())
						<span class="never">{{ trans('global.never') }}</span>
					@else
						<time datetime="{{ $row->last_visit->format('Y-m-dTh:i:s') }}">
							@if ($row->last_visit->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
								{{ $row->last_visit->diffForHumans() }}
							@else
								{{ $row->last_visit->format('Y-m-d') }}
							@endif
						</time>
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop