@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/messages/js/admin.js') }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('messages::messages.module name'),
		route('admin.messages.index')
	)
	->append(
		trans('messages::messages.types'),
		route('admin.messages.types')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit messages'))
		{!! Toolbar::save(route('admin.messages.types.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.messages.types.cancel'))
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('messages::messages.module name') }}: {{ trans('messages::messages.types') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.messages.types.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

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
		<div class="col-md-12">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label class="form-label" for="field-name">{{ trans('messages::messages.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" required maxlength="24" value="{{ $row->name }}" />
					<span class="invalid-feedback">{{ trans('messages::messages.errors.invalid name') }}</span>
				</div>

				<div class="form-group">
					<label class="form-label" for="field-classname">{{ trans('messages::messages.classname') }}:</label>
					<input type="text" name="fields[classname]" id="field-classname" class="form-control" maxlength="24" value="{{ $row->classname }}" />
				</div>

				<div class="form-group">
					<label class="form-label" for="field-resourceid">{{ trans('messages::messages.resource') }}:</label>
					<select name="fields[resourceid]" id="field-resourceid" class="form-control">
						<option value="0">{{ trans('global.none') }}</option>
						@foreach ($resources as $res)
							<option value="{{ $res->id }}"<?php if ($row->resourceid == $res->id): echo ' selected="selected"'; endif;?>>{{ str_repeat('- ', $res->level) . $res->name }}</option>
						@endforeach
					</select>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop