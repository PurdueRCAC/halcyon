@extends('layouts.master')

@section('title')
	{{ trans('user::auth.register') }} | @parent
@stop

@section('content')
<div class="container-fluid">
	<div class="row align-items-center justify-content-center">
		<div class="card register">

			<div class="card-header register-header">
				<h2 class="card-title">{{ trans('users::auth.register') }}</h2>
			</div>

			<div class="card-body register-body">
				<form method="post" action="{{ route('register.post') }}">
					<div class="form-group has-feedback {{ $errors->has('name') ? ' has-error has-feedback' : '' }}">
						<label for="register-name" class="sr-only visually-hidden">{{ trans('users::auth.name') }}</label>
						<input type="text" name="name" id="register-name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" autofocus placeholder="{{ trans('users::auth.name') }}" value="{{ old('name') }}">
						{!! $errors->first('name', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
					</div>
					<div class="form-group has-feedback {{ $errors->has('username') ? ' has-error has-feedback' : '' }}">
						<label for="register-username" class="sr-only visually-hidden">{{ trans('users::auth.username') }}</label>
						<input type="text" name="username" id="register-username" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" autofocus placeholder="{{ trans('users::auth.username') }}" value="{{ old('username') }}">
						{!! $errors->first('username', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
					</div>
					<div class="form-group has-feedback {{ $errors->has('email') ? ' has-error has-feedback' : '' }}">
						<label for="register-email" class="sr-only visually-hidden">{{ trans('users::auth.email') }}</label>
						<input type="email" name="email" id="register-email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required placeholder="{{ trans('users::auth.email') }}" value="{{ old('email') }}">
						{!! $errors->first('email', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
					</div>
					<div class="form-group has-feedback {{ $errors->has('password') ? ' has-error has-feedback' : '' }}">
						<label for="register-password" class="sr-only visually-hidden">{{ trans('users::auth.password') }}</label>
						<input type="password" name="password" id="register-password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required placeholder="{{ trans('users::auth.password') }}">
						{!! $errors->first('password', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
					</div>
					<div class="form-group has-feedback {{ $errors->has('password_confirmation') ? ' has-error has-feedback' : '' }}">
						<label for="register-password_confirmation" class="sr-only visually-hidden">{{ trans('users::auth.password confirmation') }}</label>
						<input type="password" name="password_confirmation" id="register-password_confirmation" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required placeholder="{{ trans('users::auth.password confirmation') }}">
						{!! $errors->first('password_confirmation', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
					</div>

					<div class="form-group text-center">
						<button type="submit" class="btn btn-primary btn-block btn-flat">{{ trans('users::auth.register') }}</button>
					</div>

					@csrf
				</form>

				<p class="text-center"><a href="{{ route('login') }}">{{ trans('users::auth.i already have an account') }}</a></p>
			</div>

		</div>
	</div>
</div>
@stop
