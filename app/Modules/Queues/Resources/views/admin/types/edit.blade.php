@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('queues::queues.module name'),
		route('admin.queues.index')
	)
	->append(
		trans('queues::queues.types'),
		route('admin.queues.types')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit queues.types'))
		{!! Toolbar::save(route('admin.queues.types.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.queues.types.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('queues::queues.module name') }}: {{ trans('queues::queues.types') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.queues.types.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
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
					<label for="field-name">{{ trans('queues::queues.name') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control{{ $errors->has('fields.name') ? ' is-invalid' : '' }}" maxlength="20" required value="{{ $row->name }}" />
					<span class="invalid-feedback">{{ $errors->first('fields.name') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col-md-5">
			<table class="meta table table-bordered">
				<caption class="sr-only visually-hidden">{{ trans('global.metadata') }}</caption>
				<tbody>
					<tr>
						<th scope="row">{{ trans('queues::queues.queues') }}</th>
						<td class="text-right">{{ number_format($row->queues()->count()) }}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop