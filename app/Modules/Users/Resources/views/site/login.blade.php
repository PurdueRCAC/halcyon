@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/Users/css/login.css') }}" />
@stop

@section('title')
	{{ trans('users::auth.login') }} | @parent
@stop

@section('content')
<div class="container-fluid">
	<div class="row align-items-center justify-content-center">
		<div class="login-box-body auth">
			<p class="login-box-msg">{{ trans('users::auth.sign in welcome message') }}</p>

			<?php
			//$results = Event::dispatch('loginOptions', [$return]);
			//echo implode("\n", $results);
			?>

			<form method="post" action="<?php echo route('login.post'); ?>">
				<div class="form-group has-feedback {{ $errors->has('email') ? ' has-error' : '' }}">
					<label for="login-email">{{ trans('users::auth.email') }}</label>
					<input type="email" class="form-control" autofocus name="email" id="login-email" value="{{ old('email')}}">
					<span class="glyphicon glyphicon-envelope form-control-feedback"></span>
					{!! $errors->first('email', '<span class="help-block">:message</span>') !!}
				</div>
				<div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
					<label for="login-password">{{ trans('users::auth.password') }}</label>
					<input type="password" class="form-control" name="password" id="login-password" value="{{ old('password')}}">
					<span class="glyphicon glyphicon-lock form-control-feedback"></span>
					{!! $errors->first('password', '<span class="help-block">:message</span>') !!}
				</div>
				<div class="row">
					<div class="col-xs-8">
						<div class="checkbox icheck">
							<label for="login-remember_me">
								<input type="checkbox" name="remember_me" id="login-remember_me"> {{ trans('users::auth.remember me') }}
							</label>
						</div>
					</div>
					<div class="col-xs-4">
						<button type="submit" class="btn btn-primary btn-block btn-flat">
							{{ trans('users::auth.login') }}
						</button>
					</div>
				</div>
				@csrf
			</form>

			<a href="{{ route('reset')}}">{{ trans('users::auth.forgot password') }}</a><br>
			@if (config('user.allow_registration'))
				<a href="{{ route('register')}}" class="text-center">{{ trans('users::auth.register')}}</a>
			@endif
		</div>
	</div>
</div>
@stop
