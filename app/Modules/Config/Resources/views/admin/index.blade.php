@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/config/js/config.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('config::config.module name'),
		route('admin.config')
	);
@endphp

@section('title')
{!! config('config.name') !!}
@stop

@section('content')
<form action="{{ route('admin.config') }}" method="post" name="adminForm" id="adminForm">

	Global config

	@csrf
</form>

@stop