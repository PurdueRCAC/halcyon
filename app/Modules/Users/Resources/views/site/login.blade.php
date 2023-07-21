@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/users/css/login.css') }}" />
@endpush

@php
app('pathway')
	->append(
		trans('users::auth.login'),
		route('login')
	);
@endphp

@section('title')
	{{ trans('users::auth.login') }} | @parent
@stop

@section('content')
<div class="container-fluid">
	<div class="row align-items-center justify-content-center">
		<div class="login-box-body auth card">
			<div class="card-header">
				<h2 class="card-title">{{ trans('users::auth.login') }}</h2>
				<p class="login-box-msg">{{ trans('users::auth.sign in welcome message') }}</p>
			</div>

			<div class="card-body">
			@if (count($authenticators) > 0)
				@php
				$drivers = array_keys($authenticators);
				$driver = array_shift($drivers);
				$primary = $authenticators[$driver];
				unset($authenticators[$driver]);
				@endphp
				@include($primary['view'], ['authenticator' => $driver])

				@if (count($authenticators) > 0)
					<p class="or">Or</p>
					@foreach ($authenticators as $driver => $options)
					<div class="mb-2">
						@include($options['view'], ['authenticator' => $driver])
					</div>
					@endforeach
				@endif
			@else
				<div class="alert alert-warning">Login is disabled.</div>
			@endif
			</div>
		</div>
	</div>
</div>
@stop
