@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/dashboard/css/dashboard.css?v=' . filemtime(public_path() . '/modules/dashboard/css/dashboard.css')) }}" />
@stop

@section('title')
{!! config('dashboard.name') !!}
@stop

@section('content')
<div class="contianer-fluid width-100">
	<div class="row">
		<div class="col-md-12 hero">
			@widget('cpanelhero')
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			@widget('icon')
		</div>
		<div class="col-md-6">
			@widget('cpanel')
		</div>
	</div>
</div>
@stop