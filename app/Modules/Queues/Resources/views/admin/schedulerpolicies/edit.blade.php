@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('queues::queues.module name'),
		route('admin.queues.index')
	)
	->append(
		trans('queues::queues.scheduler policies'),
		route('admin.queues.schedulerpolicies')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit queues.schedulerpolicies'))
		{!! Toolbar::save(route('admin.queues.schedulerpolicies.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.queues.schedulerpolicies.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@php
	app('request')->merge(['hidemainmenu' => 1]);
@endphp

@section('title')
{!! config('queues.name') !!}: {{ trans('queues::queues.types') }}: {{ $row->id ? trans('queues::queues.edit') . ': #' . $row->id : trans('queues::queues.create') }}
@stop

@section('content')
<form action="{{ route('admin.queues.schedulerpolicies.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('JGLOBAL_VALIDATION_FORM_FAILED') }}">
	<div class="grid row">
		<div class="col col-md-7 span7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-name">{{ trans('queues::queues.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" value="{{ $row->name }}" />
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