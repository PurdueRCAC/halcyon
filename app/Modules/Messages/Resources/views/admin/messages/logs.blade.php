@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/messages/js/admin.js?v=' . filemtime(public_path() . '/modules/messages/js/admin.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('messages::messages.module name'),
		route('admin.messages.index')
	)
	->append(
		trans('messages::messages.logs')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('admin messages'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('messages')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
	{{ trans('messages::messages.module name') }}: {{ trans('messages::messages.logs') }}
@stop

@section('content')

@component('messages::admin.submenu')
	logs
@endcomponent

<form action="{{ route('admin.messages.logs') }}" method="post" name="adminForm" id="adminForm" class="form-inlin">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-10">
			</div>
			<div class="col-md-2 text-right">
				<div class="input-group">
				<label class="sr-only" for="filter_lines">{{ trans('messages::messages.tail limit') }}</label>
				<select name="lines" id="filter_lines" class="form-control filter filter-submit">
					<option value="10"<?php if ($filters['lines'] == 10): echo ' selected="selected"'; endif;?>>50</option>
					<option value="20"<?php if ($filters['lines'] == 20): echo ' selected="selected"'; endif;?>>50</option>
					<option value="30"<?php if ($filters['lines'] == 30): echo ' selected="selected"'; endif;?>>30</option>
					<option value="40"<?php if ($filters['lines'] == 40): echo ' selected="selected"'; endif;?>>40</option>
					<option value="50"<?php if ($filters['lines'] == 50): echo ' selected="selected"'; endif;?>>50</option>
					<option value="60"<?php if ($filters['lines'] == 60): echo ' selected="selected"'; endif;?>>60</option>
					<option value="70"<?php if ($filters['lines'] == 70): echo ' selected="selected"'; endif;?>>70</option>
					<option value="80"<?php if ($filters['lines'] == 80): echo ' selected="selected"'; endif;?>>80</option>
					<option value="90"<?php if ($filters['lines'] == 90): echo ' selected="selected"'; endif;?>>90</option>
					<option value="100"<?php if ($filters['lines'] == 100): echo ' selected="selected"'; endif;?>>100</option>
				</select>
				<span class="input-group-append">
					<span class="input-group-text">{{ trans('messages::messages.lines') }}</span>
				</span>
				</div>
			</div>
		</div>
	</fieldset>

	<div class="card mb-4">
		@if ($err)
			<div class="alert alert-danger">
				{{ $err }}
			</div>
		@endif

		@if ($results)
			<pre><code>{!! $results !!}</code></pre>
		@else
			<div class="card-body text-center">
				<div>{{ trans('global.no records found') }}</div>
			</div>
		@endif
	</div>

	@csrf
</form>

@stop