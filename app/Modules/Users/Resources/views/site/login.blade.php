@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/users/css/login.css?v=' . filemtime(public_path() . '/modules/users/css/login.css')) }}" />
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

			<form method="post" action="{{ route('login.post') }}" class="card-body">
				<div class="form-group has-feedback {{ $errors->has('username') ? ' has-error' : '' }}">
					<label for="login-username">{{ trans('users::auth.username or email') }}</label>
					<input type="username" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" autofocus name="username" id="login-username" value="{{ old('username')}}">
					<span class="glyphicon glyphicon-envelope form-control-feedback"></span>
					{!! $errors->first('username', '<span class="help-block">:message</span>') !!}
				</div>

				<div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
					<a class="float-right" href="{{ route('password.forgot') }}">{{ trans('users::auth.forgot password') }}</a>
					<label for="login-password">{{ trans('users::auth.password') }}</label>
					<input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" id="login-password" value="{{ old('password')}}">
					<span class="glyphicon glyphicon-lock form-control-feedback"></span>
					{!! $errors->first('password', '<span class="help-block">:message</span>') !!}
				</div>
				
				<div class="row">
					<div class="col-md-8">
						<div class="checkbox icheck">
							<label for="login-remember_me">
								<input type="checkbox" name="remember_me" id="login-remember_me"> {{ trans('users::auth.remember me') }}
							</label>
						</div>
					</div>
					<div class="col-md-4">
						<button type="submit" class="btn btn-primary btn-block btn-flat">
							{{ trans('users::auth.login') }}
						</button>
					</div>
				</div>

				@if (config('module.users.allow_registration', true))
					<p><a href="{{ route('register')}}" class="text-center">{{ trans('users::auth.register')}}</a></p>
				@endif

				@csrf
			</form>
		</div>
	</div>
</div>
@stop
