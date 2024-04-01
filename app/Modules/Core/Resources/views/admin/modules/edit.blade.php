@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('core::modules.module name'),
		route('admin.modules.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit modules'))
		{!! Toolbar::save(route('admin.modules.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.modules.index'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('core::modules.module name') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.modules.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="row">
		<div class="col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-name" class="form-label">{{ trans('core::modules.name') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="name" id="field-name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $row->name }}" />
					<span class="invalid-feedback">{{ trans('core::modules.invalid.name') }}</span>
				</div>

				<div class="form-group">
					<label for="field-folder" class="form-label">{{ trans('core::modules.folder') }}</label>
					<input type="text" name="folder" id="field-folder" class="form-control{{ $errors->has('folder') ? ' is-invalid' : '' }}" maxlength="250" value="{{ $row->folder }}" />
					<span class="invalid-feedback">{{ trans('core::modules.invalid.folder') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-enabled" class="form-label">{{ trans('core::modules.state') }}</label>
					<select class="form-control" name="enabled" id="field-enabled"<?php if ($row->protected) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
						<option value="1"<?php if ($row->enabled == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
						<option value="0"<?php if ($row->enabled == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
					</select>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" value="{{ $row->id }}" />

	@csrf
</form>
@stop
