@extends('layouts.master')

@section('content')
	<div class="error-page">
		<h2>500</h2>

		<div class="error-content">
			<h3><i class="fa fa-warning text-red"></i> {{ trans('global.error 500 title') }}</h3>
			<p>{!! trans('global.error 500 description') !!}</p>
		</div>
		<!-- /.error-content -->
	</div>
@stop
