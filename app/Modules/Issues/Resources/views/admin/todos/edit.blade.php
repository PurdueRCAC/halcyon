@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@stop

@section('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/issues/js/admin.js?v=' . filemtime(public_path() . '/modules/issues/js/admin.js')) }}"></script>
@stop

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('issues::issues.module name'),
		route('admin.issues.index')
	)
	->append(
		trans('issues::issues.todos'),
		route('admin.issues.todos')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);

if (auth()->user()->can('edit issues')):
	Toolbar::save(route('admin.issues.todos.store'));
endif;
Toolbar::spacer();
Toolbar::cancel(route('admin.issues.todos.cancel'));

@endphp

@section('toolbar')
	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('issues.name') !!}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.issues.todos.store') }}" method="post" name="adminForm" id="item-form" class="editform">

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

				<div class="form-group{{ $errors->has('fields.name') ? ' has-error' : '' }}">
					<label for="field-name">{{ trans('issues::issues.name') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control" required maxlength="250" value="{{ $row->name }}" />
				</div>

				<div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
					<label for="field-description">{{ trans('issues::issues.description') }}:</label>
					{!! markdown_editor('fields[description]', $row->description, ['rows' => 5]) !!}
					<span class="form-text text-muted">{{ trans('issues::issues.formatting help') }}</span>
				</div>

				<div class="form-group{{ $errors->has('recurringtimeperiodid') ? ' has-error' : '' }}">
					<label for="field-recurringtimeperiodid">{{ trans('issues::issues.recurrence') }}</label>
					<select class="form-control" name="fields[recurringtimeperiodid]" id="field-recurringtimeperiodid">
						<option value="0"<?php if (!$row->recurringtimeperiodid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
						<?php foreach (App\Halcyon\Models\Timeperiod::all() as $period): ?>
							<option value="{{ $period->id }}"<?php if ($row->recurringtimeperiodid == $period->id) { echo ' selected="selected"'; } ?>>{{ $period->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>
			</fieldset>
		</div>
		<div class="col-md-5">
			<div class="help">
				<table class="table table-bordered">
					<caption>MarkDown Quick Guide</caption>
					<thead>
						<tr>
							<th scope="col">MarkDown</th>
							<th scope="col">HTML</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>*bold*</td>
							<td><strong>bold</strong></td>
						</tr>
						<tr>
							<td>_italic_</td>
							<td><em>italic</em></td>
						</tr>
						<tr>
							<td>`code`</td>
							<td><code>code</code></td>
						</tr>
						<tr>
							<td>[a link](https//:somewhere.com)</td>
							<td><a href="https//:somewhere.com">a link</a></td>
						</tr>
						<tr>
							<td>```<br />
code<br />
block<br />
```
							</td>
							<td><pre>code
block</pre></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop
