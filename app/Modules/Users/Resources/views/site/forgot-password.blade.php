@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('users::auth.forgot password'),
		route('password.forgot')
	);
@endphp

@section('title')
	{{ trans('user::auth.forgot password') }} | @parent
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

						<h2 class="card-title mt-0 pt-0 mb-4 pb-2 pb-md-0 mb-md-5">{{ trans('users::auth.forgotten password') }}</h2>

						<div class="mb-4">
							{{ trans('users::auth.forgotten password instructions') }}
						</div>

						<form method="post" action="{{ route('password.email') }}">

							<div class="form-group has-feedback {{ $errors->has('email') ? ' has-error has-feedback' : '' }}">
								<label for="remind-email">{{ trans('users::auth.email') }}</label>
								<input type="email" name="email" id="remind-email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required value="{{ old('email') }}">
								{!! $errors->first('email', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
							</div>

							<div class="text-center mt-4 pt-2">
								<button type="submit" class="btn btn-primary btn-flat">{{ trans('users::auth.send reset link') }}</button>
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
