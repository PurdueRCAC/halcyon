@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('users::auth.register'),
		route('register')
	);
@endphp

@section('title')
	{{ trans('users::auth.register') }} | @parent
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
						@if (!$invite && config('module.users.invite_only'))
							<p class="alert alert-warning">Registration is by invitation only.</p>
						@else
						<form method="post" action="{{ route('register.post') }}">
							<div class="form-group {{ $errors->has('name') ? ' has-error has-feedback' : '' }}">
								<label for="register-name">{{ trans('users::auth.name') }}</label>
								<input type="text" name="name" id="register-name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" autofocus value="{{ old('name') }}">
								{!! $errors->first('name', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
							</div>

							<div class="form-group {{ $errors->has('username') ? ' has-error has-feedback' : '' }}">
								<label for="register-username">{{ trans('users::auth.username') }}</label>
								<input type="text" name="username" id="register-username" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" value="{{ old('username') }}">
								{!! $errors->first('username', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
							</div>

							<div class="form-group {{ $errors->has('email') ? ' has-error has-feedback' : '' }}">
								<label for="register-email">{{ trans('users::auth.email') }}</label>
								<input type="email" name="email" id="register-email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required value="{{ old('email') }}">
								{!! $errors->first('email', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
							</div>

							<div class="form-group {{ $errors->has('password') ? ' has-error has-feedback' : '' }}">
								<label for="register-password">{{ trans('users::auth.password') }}</label>
								<input type="password" name="password" id="register-password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required />
								{!! $errors->first('password', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
							</div>

							<div class="form-group {{ $errors->has('password_confirmation') ? ' has-error has-feedback' : '' }}">
								<label for="register-password_confirmation">{{ trans('users::auth.password confirmation') }}</label>
								<input type="password" name="password_confirmation" id="register-password_confirmation" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required />
								{!! $errors->first('password_confirmation', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
							</div>
							@if (count($extraFields))
								@foreach ($extraFields as $field)
									@if ($field->type == 'radio')
										<fieldset>
											<legend>{{ $field->name }}</legend>
											@foreach ($field->options as $option)
											<div class="form-group mb-1 {{ $errors->has('extras.' . $field->name) ? ' has-error has-feedback' : '' }}">
												<div class="form-check">
													<input type="radio" name="extras[{{$field->name}}]" id="extra-{{ $field->name }}-{{ str_replace([' ', '.', '"'], '', $option) }}" 
														class="form-check-input{{ $errors->has('extras.' . $field->name) ? ' is-invalid' : '' }}" value="{{ $option }}" />
													<label for="extra-{{ $field->name }}-{{ str_replace([' ', '.', '"'], '', $option) }}" class="form-check-label">{{ $option }}</label>
												</div>
											</div>
											@endforeach
										</fieldset>
									@else
										<div class="form-group {{ $errors->has('extras.' . $field->name) ? ' has-error has-feedback' : '' }}">
											<label for="extra-{{ $field->name }}">{{ $field->name }}</label>
											@if ($field->type == 'textarea')
												<textarea name="extras[{{$field->name}}]" id="extra-{{ $field->name }}" 
													class="form-control{{ $errors->has('extras.' . $field->name) ? ' is-invalid' : '' }}" {{ $field->required ? 'required' : ''}} col="45" rows="3"></textarea>
											@elseif ($field->type == 'select')
												<select name="extras[{{$field->name}}]" id="extra-{{ $field->name }}" 
													class="form-control{{ $errors->has('extras.' . $field->name) ? ' is-invalid' : '' }}" {{ $field->required ? 'required' : ''}}>
													<option value>Select an option...</option>
													@foreach ($field->options as $option)
														<option value="{{$option}}">{{$option}}</option>
													@endforeach
												</select>
											@else
												<input type="{{$field->type}}" name="extras[{{$field->name}}]" id="extra-{{ $field->name }}" 
													class="form-control{{ $errors->has('extras.' . $field->name) ? ' is-invalid' : '' }}" {{ $field->required ? 'required' : ''}}/>
											@endif
											{!! $errors->first('extras.' . $field->name, '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
										</div>
									@endif
								@endforeach
							@endif

							@if ($pageid = config('module.users.terms'))
								<?php
								$page = App\Modules\Pages\Models\Page::find($pageid);
								?>
								@if ($page)
								<div class="form-group has-feedback {{ $errors->has('terms') ? ' has-error has-feedback' : '' }}">
									<div class="form-check">
										<input type="checkbox" name="terms" id="register-terms" value="1" class="form-check-input{{ $errors->has('terms') ? ' is-invalid' : '' }}" required />
										<label for="register-terms" class="form-check-label">{!! trans('users::auth.terms confirmation', ['url' => route('page', ['uri' => $page->path]), 'title' => $page->title]) !!}</label>
									</div>
									{!! $errors->first('terms', '<span class="form-text text-danger invalid-feedback">:message</span>') !!}
								</div>

								<div class="modal dialog" id="termscontent" tabindex="-1" aria-labelledby="termscontent-title" aria-hidden="true">
									<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
										<div class="modal-content dialog-content shadow-sm">
											<div class="modal-header">
												<div class="modal-title" id="termscontent-title">{{ $page->title }}</div>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
											</div>
											<div class="modal-body dialog-body">
												{!! $page->content !!}
											</div>
										</div>
									</div>
								</div>
								@endif
							@endif

							<input type="hidden" name="token" value="{{ $invite ? $invite->token : '' }}" />

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
						@endif
					</div>

				</div>
			</div>
		</div>
	</div>
</section>
</div>
</div>
@stop
