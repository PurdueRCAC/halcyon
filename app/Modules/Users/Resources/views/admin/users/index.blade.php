@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/users/css/users.css') }}" />
@stop

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
					<option value="*">{{ trans('users::users.select state') }}</option>
					<option value="enabled"<?php if ($filters['state'] == 'enabled'): echo ' selected="selected"'; endif;?>>{{ trans('users::users.status enabled') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('users::users.status trashed') }}</option>
				</select>

				<label class="sr-only" for="filter-role_id">{{ trans('users::users.usergroup') }}:</label>
				<select name="role_id" id="filter-role_id" class="form-control filter filter-submit">
					<option value="0">{{ trans('users::users.select role') }}</option>
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
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('users::users.email'), 'email', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-3 nowrap">{{ trans('users::users.roles') }}</th>
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
			$authenticator = 'hub';
			/*if (substr($row->email, -8) == '@invalid'):
				$authenticator = trans('global.unknown');
				if ($lnk = App\Halcyon\Auth\Link::find_by_id(abs($row->username))):
					$domain = App\Halcyon\Auth\Domain::find_by_id($lnk->auth_domain_id);
					$authenticator = $domain->authenticator;
				endif;
				$incomplete = true;
			endif;*/
			?>
			<tr>
				<td>
					<?php if ($canEdit) : ?>
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					<?php endif; ?>
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					<?php if ($canEdit) : ?>
						<a href="{{ route('admin.users.edit', ['id' => $row->id]) }}">
							{{ $row->name }}
						</a>
					<?php else : ?>
						{{ $row->name }}
					<?php endif; ?>
				</td>
				<td>
					<?php if ($canEdit) : ?>
						<a href="{{ route('admin.users.edit', ['id' => $row->id]) }}">
							{{ $row->username }}
						</a>
					<?php else : ?>
						{{ $row->username }}
					<?php endif; ?>
				</td>
				<td class="priority-4">
					<?php if ($canChange) : ?>
						<a href="{{ route('admin.users.edit', ['id' => $row->id]) }}">
					<?php endif; ?>
						@if ($row->email)
							{{ $row->email }}
						@else
							<span class="unknown">{{ trans('global.none') }}</span>
						@endif
					<?php if ($canChange) : ?>
						</a>
					<?php endif; ?>
				</td>
				<td class="center priority-3">
					<?php if ($canChange) : ?>
						<a class="permissions glyph icon-settings tip" href="{{ route('admin.users.debug', ['id' => $row->id]) }}" data-tip="{{ trans('users::users.debug user') }}">
							{{ trans('users::users.debug user') }}
						</a> &nbsp;
					<?php endif; ?>
					<?php if (substr_count($row->role_names, "\n") > 1) : ?>
						<span class="hasTip" title="{{ trans('users::users.roles') . '::' . $row->role_names }}">{{ trans('users::users.roles') }}</span>
					<?php else : ?>
						{!! $row->role_names !!}
					<?php endif; ?>
				</td>
				<td class="priority-4">
					@if ($row->isTrashed())
						<span class="badge state trashed">
							{{ trans('users::users.status trashed') }}
						</span>
					@else
						<span class="badge state on">
							{{ trans('users::users.status enabled') }}
						</span>
					@endif
					<?php /*if ($row->isTrashed()): ?>
						<div class="btn-group btn-group-sm dropdown user-state blocked">
							<button type="button" class="btn btn-secondary btn-danger dropdown-toggle" id="btnGroupDrop{{ $row->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								{{ trans('users::users.status blocked') }}
							</span>
							<?php if ($canChange) : ?>
								<ul class="dropdown-menu" aria-labelledby="btnGroupDrop{{ $row->id }}">
									<li class="dropdown-item">
										<a class="grid-action" data-id="cb<?php echo $i; ?>" data-task="enable" href="{{ route('admin.users.unblock', ['id' => $row->id]) }}"><i class="fa fa-check"></i> {{ trans('users::users.unblock user') }}</a>
									</li>
								</ul>
							<?php endif; ?>
						</div>
					<?php else : ?>
							<div class="btn-group btn-group-sm dropdown user-state confirmed approved enabled" role="group" aria-label="User state">
								<button type="button" class="btn btn-secondary btn-success dropdown-toggle" title="{{ trans('users::users.status approved') }}" id="btnGroupDrop{{ $row->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									{{ trans('users::users.status enabled') }}
								</button>
								<?php if ($canChange) : ?>
									<ul class="dropdown-menu" aria-labelledby="btnGroupDrop{{ $row->id }}">
										<li class="dropdown-item">
											<a class="grid-action" data-id="cb<?php echo $i; ?>" data-task="block" href="{{ route('admin.users.block', ['id' => $row->id]) }}"><i class="fa fa-ban" aria-hidden="true"></i> {{ trans('users::users.block user') }}</a>
										</li>
									</ul>
								<?php endif; ?>
							</div>
					<?php endif;*/ ?>
				</td>
				<td class="priority-6">
					<?php if (!$row->hasVisited()) : ?>
						<span class="never">{{ trans('global.never') }}</span>
					<?php else: ?>
						<time datetime="<?php echo $row->last_visit->format('Y-m-dTh:i:s'); ?>">
							@if ($row->last_visit->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
								{{ $row->last_visit->diffForHumans() }}
							@else
								{{ $row->last_visit }}
							@endif
						</time>
					<?php endif; ?>
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