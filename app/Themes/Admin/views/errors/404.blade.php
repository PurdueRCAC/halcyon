@extends('layouts.master')

@section('content')
	<div id="errorbox" class="error-page">
		<h2 class="error-code">404</h2>

		<div class="error-content">
			<h3><i class="fa fa-warning text-yellow"></i> {{ trans('core::core.error 404 title') }}</h3>
			<p>{!! trans('core::core.error 404 description') !!}</p>
		</div><!-- /.error-content -->
	</div><!-- /.error-page -->
@stop
