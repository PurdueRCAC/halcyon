@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		trans('users::users.roles'),
		route('admin.users.roles')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit users'))
		{!! Toolbar::save(route('admin.users.roles.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.users.roles.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('users.name') !!}: {{ trans('users::access.roles') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.users.roles.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-title">{{ trans('users::access.title') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input class="form-control" required type="text" name="fields[title]" id="field-title" maxlength="100" value="{{ $row->title }}" />
				</div>

				<div class="form-group">
					<label for="field-parent_id">{{ trans('users::access.parent') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<select name="fields[parent_id]" id="field-parent_id" class="form-control" required>
						<?php
						foreach ($options as $option):
							//if (auth()->user()->can('admin') || (!App\Halcyon\Access\Gate::checkGroup($option->id, 'admin'))):
								$level = $option->countDescendents();
								?>
								<option value="{{ $option->id }}"<?php if ($option->id == $row->parent_id) { echo ' selected="selected"'; } ?>><?php echo str_repeat('- ', $level) . $option->title; ?></option>
								<?php
							//endif;
						endforeach;
						?>
					</select>
					<span class="form-text">{{ trans('users::access.parent desc') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<input type="hidden" name="id" value="{{ $row->id }}" />
		</div>
	</div>

	@csrf
</form>
@stop
