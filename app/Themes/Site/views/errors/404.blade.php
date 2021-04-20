@extends('layouts.master')

@section('content')
	<div class="error-page">
		<div class="error-header">
			<h2>404</h2>
		</div>
		<div class="error-body">
			<h3><i class="fa fa-warning text-yellow"></i> {{ trans('global.error 404 title') }}</h3>
			<p>{!! trans('global.error 404 description') !!}</p>
		</div>
	</div>
@stop
