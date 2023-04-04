
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
					<p class="login-register mt-3 mb-0 text-muted text-center">{{ trans('users::auth.new to the site') }} <a href="{{ route('register')}}">{{ trans('users::auth.register') }}</a></p>
				@endif

				@csrf
			</form>