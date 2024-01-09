@extends('layouts.login')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/users/css/providers.css') }}" />
@endpush

@section('title')
	{{ trans('users::auth.login') }} | @parent
@stop

@section('content')
	<div class="login-box-body auth">
		<?php /*<div class="login-instructions instructions">{{ trans('users::auth.choose method') }}</div>
			<div class="options">
				<?php
				$results = Event::dispatch('loginOptions', [$return, 'admin']);
				echo implode("\n", $results);
				?>
			</div>
		</div>*/ ?>
		<form method="post" action="{{ route('login.post') }}">
			<div class="form-group has-feedback {{ $errors->has('email') ? ' has-error' : '' }}">
				<label for="login-email">{{ trans('users::auth.email') }}</label>
				<input type="email" class="form-control" autofocus name="email" id="login-email" value="{{ old('email')}}">
				<span class="form-control-feedback"></span>
				{!! $errors->first('email', '<span class="help-block">:message</span>') !!}
			</div>

			<div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
				<label for="login-password">{{ trans('users::auth.password') }}</label>
				<input type="password" class="form-control" name="password" id="login-password" value="{{ old('password')}}">
				<span class="form-control-feedback"></span>
				{!! $errors->first('password', '<span class="help-block">:message</span>') !!}
			</div>

			<div class="row">
				<div class="col col-xs-8">
					<div class="form-check">
						<input type="checkbox" name="remember_me" id="login-remember_me" class="form-check-input">
						<label for="login-remember_me" class="form-check-label">{{ trans('users::auth.remember me') }}</label>
					</div>
				</div>
				<div class="col col-xs-4">
					<button type="submit" class="btn btn-primary">
						{{ trans('users::auth.login') }}
					</button>
				</div>
			</div>
			@csrf
		</form>
	</div>
@stop
