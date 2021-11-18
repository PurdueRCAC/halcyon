@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('resources::resources.module name'),
		route('admin.resources.index')
	)
	->append(
		trans('resources::resources.batchsystems'),
		route('admin.resources.batchsystems')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit resources.batchsystems'))
		{!! Toolbar::save(route('admin.resources.batchsystems.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.resources.batchsystems.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('resources.name') !!}: {{ trans('resources::assets.batchsystems') }}: <?php echo $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create'); ?>
@stop

@section('content')
<form action="{{ route('admin.resources.batchsystems.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="row">
		<div class="col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-name">{{ trans('resources::assets.name') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" required maxlength="20" value="{{ $row->name }}" />
					<span class="invalid-feedback">{{ trans('resources::assets.invalid.name') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col-md-5">
			@include('history::admin.history')
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop