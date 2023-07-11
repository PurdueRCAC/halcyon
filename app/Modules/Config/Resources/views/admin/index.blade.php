@extends('layouts.master')

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