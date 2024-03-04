@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/users/css/users.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/users/js/users.js') }}"></script>
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

	if (auth()->user()->can('edit.state users')):
		Toolbar::publishList(route('admin.users.enable'), 'users::users.enable');
		Toolbar::unpublishList(route('admin.users.disable'), 'users::users.disable');
		Toolbar::spacer();
	endif;

	if (auth()->user()->can('delete users')):
		Toolbar::deleteList('', route('admin.users.delete'));
	endif;

	if (auth()->user()->can('create users')):
		Toolbar::addNew(route('admin.users.create'));
	endif;

	if (auth()->user()->can('admin users')):
		Toolbar::spacer();
		Toolbar::preferences('users');
	endif;

	Toolbar::help('users::admin.help.users');
@endphp

@section('toolbar')
	{!! Toolbar::render() !!}
@stop

@section('title')
	{{ trans('users::users.users') }}
@stop

@section('content')

@component('users::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.users.index') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-xs-12 col-sm-3 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" name="search" enterkeyhint="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
			</div>
			<div class="col col-xs-12 col-sm-9 text-right filter-select">
				<label class="sr-only" for="filter-state">{{ trans('users::users.state') }}:</label>
				<select name="state" id="filter-state" class="form-control filter filter-submit">
					<option value="*">{{ trans('users::users.all states') }}</option>
					<option value="enabled"<?php if ($filters['state'] == 'enabled'): echo ' selected="selected"'; endif;?>>{{ trans('users::users.status enabled') }}</option>
					<option value="disabled"<?php if ($filters['state'] == 'disabled'): echo ' selected="selected"'; endif;?>>{{ trans('users::users.status disabled') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('users::users.status trashed') }}</option>
				</select>

				<label class="sr-only" for="filter-role_id">{{ trans('users::users.usergroup') }}:</label>
				<select name="role_id" id="filter-role_id" class="form-control filter filter-submit">
					<option value="0">{{ trans('users::users.all roles') }}</option>
					<?php foreach ($roles as $role): ?>
						<option value="{{ $role->id }}"<?php if ($filters['role_id'] == $role->id): echo ' selected="selected"'; endif;?>>{{ str_repeat('- ', $role->level) . $role->title }}</option>
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

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
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
				@foreach ($extraFieldKeys as $extraKey)
				<th scope="col">
					{!! Html::grid('sort', $extraKey, $extraKey, $filters['order_dir'], $filters['order']) !!}
				</th>
				@endforeach
				<th scope="col" class="priority-3 nowrap"<?php /*if (auth()->user()->can('admin')) { echo ' colspan="2"'; }*/ ?>>
					{{ trans('users::users.roles') }}
				</th>
				<th scope="col" class="priority-3">{{ trans('users::users.status') }}</th>
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('users::users.last visit'), 'datelastseen', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$canDelete = auth()->user()->can('delete users');
		$canChange = auth()->user()->can('edit.state users');
		?>
		@foreach ($rows as $i => $row)
			<?php
			/*$canDelete = auth()->user()->can('delete users');
			$canChange = auth()->user()->can('edit.state users');

			// If this group is super admin and this user is not super admin, edit is false
			if (!auth()->user()->can('admin') && App\Halcyon\Access\Gate::check($row->id, 'admin')):
				$canDelete = false;
				$canChange = false;
			endif;*/

			$groups = array();
			foreach ($row->roles as $role):
				$r = $roles->where('id', '=', $role->role_id)->first();
				if ($r):
					$groups[] = $r->title; //$role->role->title; //$accessgroups->seek($agroup->group_id)->title;
				endif;
			endforeach;
			$row->role_names = implode('<br />', $groups);

			$incomplete = false;
			$authenticator = 'database';
			?>
			<tr<?php if ($row->trashed()) { echo ' class="trashed"'; } ?>>
				<td>
					@if ($canDelete || $canChange)
						{!! Html::grid('id', $i, $row->id) !!}
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					<a href="{{ route('admin.users.show', ['id' => $row->id]) }}">
						{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
					</a>
				</td>
				<td>
					<a href="{{ route('admin.users.show', ['id' => $row->id]) }}">
						@if ($row->username)
							{!! App\Halcyon\Utility\Str::highlight(e($row->username), $filters['search']) !!}
						@else
							<span class="text-muted unknown">{{ trans('global.none') }}</span>
						@endif
					</a>
				</td>
				@foreach ($extraFieldKeys as $extraIndex => $extraKey)
				<td>
					{{ $row[$extraKey] ?? '' }}
				</td>
				@endforeach

				<?php /*@if ($canChange)
				<td class="text-center priority-3">
						<a class="permissions tip" href="{{ route('admin.users.debug', ['id' => $row->id]) }}" data-tip="{{ trans('users::users.debug user') }}">
							<span class="fa fa-cog" aria-hidden="true"></span>
							<span class="sr-only">{{ trans('users::users.debug user') }}</span>
						</a>
				</div>
				@endif*/ ?>
				<td class="priority-3">
					@if (substr_count($row->role_names, "\n") > 1)
						<span class="hasTip" title="{{ trans('users::users.roles') . '::' . $row->role_names }}">{{ trans('users::users.roles') }}</span>
					@else
						{!! $row->role_names !!}
					@endif
				</td>
				<td class="priority-3">
					@if ($row->trashed())
						<span class="badge badge-danger">
							{{ trans('users::users.status trashed') }}
						</span>
					@elseif ($row->enabled)
						<span class="badge badge-success">
							{{ trans('users::users.status enabled') }}
						</span>
					@else
						<span class="badge badge-warning">
							{{ trans('users::users.status disabled') }}
						</span>
					@endif
				</td>
				<td class="priority-6">
					@if (!$row->hasVisited())
						<span class="text-muted never">{{ trans('global.never') }}</span>
					@else
						<time datetime="{{ $row->last_visit->toDateTimeLocalString() }}">
							@if ($row->last_visit->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
								{{ $row->last_visit->diffForHumans() }}
							@else
								{{ $row->last_visit->format('Y-m-d') }}
							@endif
						</time>
					@endif
					@if ($row->isOnline())
						<span class="badge badge-success">{{ trans('global.online') }}</span>
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

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
</form>

@stop