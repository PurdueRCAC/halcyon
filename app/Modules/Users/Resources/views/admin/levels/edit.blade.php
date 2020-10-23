@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		trans('users::users.levels'),
		route('admin.users.levels')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit users.levels'))
		{!! Toolbar::save(route('admin.users.levels.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.users.levels.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('users.name') !!}: {{ trans('users::access.levels') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.users.levels.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">
	<div class="row">
		<div class="col col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-title">{{ trans('users::access.title') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[title]" id="field-title" class="form-control required" value="{{ $row->title }}" />
					<span class="form-text text-muted">{{ trans('users::access.title desc') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('users::access.roles having access') }}</legend>

				<div class="form-group">
					<ul class="checklist usergroups">
						<?php
						$selected = $row->rules;
						$isSuperAdmin = true;

						foreach ($roles as $role):
							// If checkSuperAdmin is true, only add item if the user is superadmin or the group is not super admin
							if ($isSuperAdmin || (!App\Halcyon\Access\Access::checkGroup($role->id, 'admin'))):
								// Setup  the variable attributes.
								$eid = 'role_' . $role->id;
								// Don't call in_array unless something is selected
								$checked = '';
								if ($selected):
									$checked = in_array($role->id, $selected) ? ' checked="checked"' : '';
								endif;
								$rel = ($role->parent_id > 0) ? ' rel="role_' . $role->parent_id . '"' : '';

								$level = $role->countDescendents();
								?>
								<li>
									<div class="form-check">
										<input type="checkbox" class="form-check-input" name="fields[rules][]" value="{{ $role->id }}" id="{{ $eid }}"<?php echo $checked . $rel; ?> />
										<label class="form-check-label" for="{{ $eid }}">
											<?php echo str_repeat('<span class="gi">|&mdash;</span>', $level) . $role->title; ?>
										</label>
									</div>
								</li>
								<?php
							endif;
						endforeach;
						?>
					</ul>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop
