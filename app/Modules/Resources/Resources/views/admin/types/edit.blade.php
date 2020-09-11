@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('resources::resources.module name'),
		route('admin.resources.index')
	)
	->append(
		trans('resources::resources.types'),
		route('admin.resources.types')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit resources.types'))
		{!! Toolbar::save(route('admin.resources.types.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.resources.types.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('resources.name') !!}: {{ trans('resources::assets.types') }}: <?php echo $row->id ? trans('resources::assets.edit') . ': #' . $row->id : trans('resources::assets.create'); ?>
@stop

@section('content')
<form action="{{ route('admin.resources.types.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('JGLOBAL_VALIDATION_FORM_FAILED') }}">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-name">{{ trans('resources::assets.FIELD_NAME') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" maxlength="20" value="{{ $row->name }}" />
				</div>

				<div class="form-group">
					<label for="field-description">{{ trans('resources::assets.description') }}:</label>
					<textarea name="fields[description]" id="field-description" rows="5" cols="45" class="form-control">{{ $row->description }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5 span5">
			@include('history::admin.history')
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop