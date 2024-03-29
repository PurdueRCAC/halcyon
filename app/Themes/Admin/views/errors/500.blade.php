@extends('layouts.error')

@section('title')
{{ trans('theme::admin.error 500 title') }}
@stop

@section('content')

<div class="row align-items-center h-100 w-100">
	<div class="col-6 mx-auto">
		<div id="errorbox" class="card shadow-sm">
			<div class="card-header text-center text-danger">
				<h2>500</h2>
			</div>
			<div class="card-body text-center">
				<h3 class="card-title mb-0">{{ trans('theme::admin.error 500 title') }}</h3>
				<p class="mt-0">{!! trans('theme::admin.error 500 description') !!}</p>
			</div>
		</div>
	</div>
</div>

@stop
