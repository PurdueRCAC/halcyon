@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('users::auth.register'),
		route('register')
	);
@endphp

@section('title')
	{{ trans('user::auth.register') }} | @parent
@stop

@section('content')
<div class="row">
<div class="col-md-12">
<section>
	<div class="container py-2 h-100">
		<div class="row justify-content-center align-items-center h-100">
			<div class="col-6 col-lg-9 col-xl-7">
				<div class="card card-registration">

					<div class="card-body p-4 p-md-5">
						<h2 class="card-title mt-0 pt-0 mb-4 pb-2 pb-md-0 mb-md-5">{{ trans('users::auth.register') }}</h2>

						<form method="post" action="{{ route('register.post') }}">
							<div class="form-group has-feedback {{ $errors->has('name') ? ' has-error has-feedback' : '' }}">
								<label for="register-name">{{ trans('users::auth.name') }}</label>
								<input type="text" name="name" id="register-name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" autofocus value="{{ old('name') }}">
								{!! $errors->first('name', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
							</div>

							<div class="form-group has-feedback {{ $errors->has('username') ? ' has-error has-feedback' : '' }}">
								<label for="register-username">{{ trans('users::auth.username') }}</label>
								<input type="text" name="username" id="register-username" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" value="{{ old('username') }}">
								{!! $errors->first('username', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
							</div>

							<div class="form-group has-feedback {{ $errors->has('email') ? ' has-error has-feedback' : '' }}">
								<label for="register-email">{{ trans('users::auth.email') }}</label>
								<input type="email" name="email" id="register-email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required value="{{ old('email') }}">
								{!! $errors->first('email', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
							</div>

							<div class="form-group has-feedback {{ $errors->has('password') ? ' has-error has-feedback' : '' }}">
								<label for="register-password">{{ trans('users::auth.password') }}</label>
								<input type="password" name="password" id="register-password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required />
								{!! $errors->first('password', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
							</div>

							<div class="form-group has-feedback {{ $errors->has('password_confirmation') ? ' has-error has-feedback' : '' }}">
								<label for="register-password_confirmation">{{ trans('users::auth.password confirmation') }}</label>
								<input type="password" name="password_confirmation" id="register-password_confirmation" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required />
								{!! $errors->first('password_confirmation', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
							</div>

							<div class="row mt-4 pt-2">
								<div class="col-md-4">
									<button type="submit" class="btn btn-primary btn-flat">{{ trans('users::auth.register') }}</button>
								</div>
								<div class="col-md-8 text-right">
									<p><a href="{{ route('login') }}">{{ trans('users::auth.i already have an account') }}</a></p>
								</div>
							</div>

							@csrf
						</form>
					</div>

				</div>
			</div>
		</div>
	</div>
</section>
</div>
</div>
@stop
