@extends('layouts.master')

@section('content')
	<div class="error-page">
		<div class="error-header">
			<h2>403</h2>
		</div>
		<div class="error-body">
			<h3><span class="fa fa-warning text-yellow" aria-hidden="true"></span> {{ trans('global.error 403 title') }}</h3>

			<p>Sorry, but you do not have permission to access this page.</p>

			<p>If you feel this is in error please contact <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.</p>
		</div>
		<!-- /.error-content -->
	</div>
@stop
