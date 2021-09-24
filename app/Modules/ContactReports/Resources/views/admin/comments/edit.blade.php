@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/contactreports/js/admin.js?v=' . filemtime(public_path() . '/modules/contactreports/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit contactreports'))
		{!! Toolbar::save(route('admin.contactreports.comments.store', ['report' => $report->id])) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.contactreports.comments.cancel', ['report' => $report->id]));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('contactreports.name') !!} Comment: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.contactreports.comments.store', ['report' => $report->id]) }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">

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
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-comment">{{ trans('contactreports::contactreports.comment') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<textarea name="fields[comment]" id="field-comment" class="form-control{{ $errors->has('fields.comment') ? ' is-invalid' : '' }}" required rows="20" cols="40">{{ $row->comment }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<table class="meta">
				<caption>Metadata</caption>
				<tbody>
					<tr>
						<th scope="row">{{ trans('contactreports::contactreports.contactreport id') }}:</th>
						<td>
							{{ $row->contactreportid }}
							<input type="hidden" name="fields[contactreportid]" id="field-contactreportid" value="{{ $row->contactreportid }}" />
						</td>
					</tr>
					@if ($row->id)
						<tr>
							<th scope="row">{{ trans('contactreports::contactreports.id') }}:</th>
							<td>
								{{ $row->id }}
								<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
							</td>
						</tr>
						<tr>
							<th scope="row">{{ trans('contactreports::contactreports.created') }}:</th>
							<td>
								@if ($row->datetimecreated)
									{{ $row->datetimecreated }}
								@else
									{{ trans('global.unknown') }}
								@endif
							</td>
						</tr>
					@endif
				</tbody>
			</table>
		</div>
	</div>

	@csrf
</form>
@stop