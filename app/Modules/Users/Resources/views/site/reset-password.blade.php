@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('users::auth.reset'),
		route('password.reset')
	);
@endphp

@section('title')
	{{ trans('user::auth.reset') }} | @parent
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

						<form method="post" action="{{ route('password.update') }}">

							<input type="hidden" name="token" value="{{ $request->input('token') }}">
							<input type="hidden" name="email" value="{{ $request->input('email') }}">

							<div class="form-group has-feedback {{ $errors->has('password') ? ' has-error has-feedback' : '' }}">
								<label for="register-password">{{ trans('users::auth.password') }}</label>
								<input type="password" name="password" id="register-password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required autofocus />
								{!! $errors->first('password', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
							</div>

							<div class="form-group has-feedback {{ $errors->has('password_confirmation') ? ' has-error has-feedback' : '' }}">
								<label for="register-password_confirmation">{{ trans('users::auth.password confirmation') }}</label>
								<input type="password" name="password_confirmation" id="register-password_confirmation" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required />
								{!! $errors->first('password_confirmation', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
							</div>

							<div class="text-center mt-4 pt-2">
								<button type="submit" class="btn btn-primary btn-flat">{{ trans('users::auth.reset') }}</button>
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
