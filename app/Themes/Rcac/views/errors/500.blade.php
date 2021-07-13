@extends('layouts.master')

@section('content')
	<div class="error-page">
		<div class="error-header">
			<h2>500</h2>
		</div>
		<div class="error-body">
			<h3><span class="fa fa-warning text-red" aria-hidden="true"></span> {{ trans('global.error 500 title') }}</h3>
			<p>{!! trans('global.error 500 description') !!}</p>
		</div>
		<!-- /.error-content -->
	</div>
@stop
