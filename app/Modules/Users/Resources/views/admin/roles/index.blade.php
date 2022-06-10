@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/config/js/config.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		trans('users::users.roles')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('admin'))
		{!! Toolbar::save(route('admin.users.roles.update')) !!}
	@endif

	{!!
		Toolbar::cancel(route('admin.users.roles.cancel'));
		Toolbar::spacer();
	!!}

	@if (auth()->user()->can('delete users.roles'))
		{!! Toolbar::deleteList('', route('admin.users.roles.delete')) !!}
	@endif

	@if (auth()->user()->can('create users.roles'))
		{!! Toolbar::addNew(route('admin.users.roles.create')) !!}
	@endif

	{!! Toolbar::help('users::admin.help.roles') !!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('users::users.module name') }}: {{ trans('access.permissions') }}
@stop

@section('content')

@component('users::admin.submenu')
	@if (request()->segment(3) == 'levels')
		levels
	@else
		roles
	@endif
@endcomponent

<form action="{{ route('admin.users.roles.update') }}" method="post" name="adminForm" id="adminForm">

	<div id="permissions-sliders" class="pane-sliders">
		<div id="permissions-rules">
			<?php
			$curLevel = 0;
			$canEdit = auth()->user()->can('edit users.roles');

			foreach ($roles as $i => $role)
			{
				$difLevel = $role->level - $curLevel;
				$canCalculateSettings = ($role->parent_id);
				?>
				<div class="pane-toggler title">
					@if ($canEdit)
						<span class="form-check stop-propagation"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $role->value }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif

					<span class="level">{!! str_repeat('|&mdash; ', $curLevel = $role->level) !!}</span>

					@if ($canEdit)
						<a class="stop-propagation" href="{{ route('admin.users.roles.edit', ['id' => $role->value]) }}">
							{{ $role->text }}
						</a>
					@else
						{{ $role->text }}
					@endif

					<span class="badge badge-secondary">{{ number_format($role->maps_count) }}</span>
				</div>
				<div class="pane-slider">
					<div class="pane-slider content pane-hide">

						<table class="table table-hover role-rules">
							<caption class="sr-only">{{ $role->text }}</caption>
							<thead>
								<tr>
									<th class="actions" id="actions-th{{ $role->value }}" scope="col">
										<span class="acl-action">{{ trans('access.rules.action') }}</span>
									</th>
									<th class="settings" id="settings-th{{ $role->value }}" scope="col">
										<span class="acl-action">{!! trans('access.rules.select setting') !!}</span>
									</th>
									@if ($canCalculateSettings)
										<th id="aclactionth{{ $role->value }}" scope="col">
											<span class="acl-action">{!! trans('access.rules.calculated setting') !!}</span>
										</th>
									@endif
								</tr>
							</thead>
							<tbody>
							<?php
							foreach ($actions[$section] as $name => $action)
							{
								$action['name'] = $name;
								$inheritedRule = App\Halcyon\Access\Gate::checkRole($role->value, $action['name'], $assetId);

								// Get the actual setting for the action for this role.
								$assetRule = $assetRules->allow($action['name'], $role->value);
								?>
								<tr>
									<td headers="actions-th{{ $role->value }}">
										<label data-toggle="tooltip" for="permissions_{{ $action['name'] . '_' . $role->value }}" title="{{ htmlspecialchars(trans($action['title']) . '::' . trans($action['description']), ENT_COMPAT, 'UTF-8') }}">{{ trans($action['title']) }}</label>
									</td>
									<td headers="settings-th{{ $role->value }}">
										<select class="form-control" name="permissions[{{ $action['name'] . '][' . $role->value }}]" id="permissions_{{ $action['name'] . '_' . $role->value }}" title="{{ trans('access.rules.select allow deny role', ['title' => trans($action['title']), 'role' => trim($role->text)]) }}">
											<option value=""{{ ($assetRule === null ? ' selected="selected"' : '') }}>&#x2193; {{ trans(empty($role->parent_id) ? 'access.rules.not set' : 'access.rules.inherited') }}</option>
											<option value="1"{{ ($assetRule === true ? ' selected="selected"' : '') }}>&#10003; {{ trans('access.rules.allowed') }}</option>
											<option value="0"{{ ($assetRule === false ? ' selected="selected"' : '') }}>&#x00D7; {{ trans('access.rules.denied') }}</option>
										</select>
										@if (($assetRule === true) && ($inheritedRule === false))
											&#160; {{ trans('access.rules.conflict') }}
										@endif
									</td>
								@if ($canCalculateSettings)
									<td headers="aclactionth{{ $role->value }}">
									@if (App\Halcyon\Access\Gate::checkRole($role->value, 'admin', $assetId) !== true)
										@if ($inheritedRule === null)
											<span class="badge badge-warning">{{ trans('access.rules.not allowed') }}</span>
										@elseif ($inheritedRule === true)
											<span class="badge badge-success">{{ trans('access.rules.allowed') }}</span>
										@elseif ($inheritedRule === false)
											@if ($assetRule === false)
												<span class="badge badge-danger">{{ trans('access.rules.not allowed') }}</span>
											@else
												<span class="badge badge-danger"><span class="fa fa-lock"></span> {{ trans('access.rules.not allowed locked') }}</span>
											@endif
										@endif
									@else
										@if ($action['name'] === 'admin')
											<span class="badge badge-success">{{ trans('access.rules.allowed') }}</span>
										@elseif ($inheritedRule === false)
											<span class="badge badge-danger"><span class="fa fa-lock"> {{ trans('access.rules.not allowed admin conflict') }}</span></span>
										@else
											<span class="badge badge-success"><span class="fa fa-lock"> {{ trans('access.rules.allowed admin') }}</span></span>
										@endif
									@endif
									</td>
								@endif
								</tr>
								<?php
							}
							?>
							</tbody>
						</table>

					</div>
				</div>
				<?php
			}
			?>
		</div>
		<div class="rule-notes">
			{!! trans('access.rules.setting notes') !!}
		</div>
	</div>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="id" value="{{ $assetId }}" />

	@csrf
</form>
@stop