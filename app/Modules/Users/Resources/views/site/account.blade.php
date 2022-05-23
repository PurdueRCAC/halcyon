@extends('layouts.master')

@php
$active = $sections->firstWhere('active', '=', true);
$paths = app('pathway')->names();
$title = end($paths);
$title = $title ?: ($active ? str_replace(['<span class="badge pull-right">', '</span>'], ['(', ')'], $active['name']) : trans('users::users.my accounts'));
@endphp

@push('scripts')
<script src="{{ asset('modules/users/js/site.js?v=' . filemtime(public_path() . '/modules/users/js/site.js')) }}"></script>
@endpush

@section('title'){{ $title }}@stop

@section('content')

@include('users::site.admin', ['user' => $user])
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<div class="card">
		<div class="card-header text-center bg-secondary text-white">
			@if (in_array('profile-photos', config('module.users.features', [])))
				<div class="user-avatar-container">
					<img src="{{ $user->avatar(false) }}" class="user-avatar" height="100" width="100" alt="User Image">
				</div>
			@endif
			<div class="user-name">
				<strong>{{ $user->name }}</strong>
			@if (auth()->user()->can('manage users') && $user->isOnline())
				<span class="badge badge-success">Online</span>
			@endif
			</div>
		</div>
	</div>

	<div class="qlinks">
		<ul class="nav flex-column profile-menu">
			<li class="nav-item<?php if (!$active) { echo ' active'; } ?>">
				<a class="nav-link<?php if (!$active) { echo ' active'; } ?>" href="{{ auth()->user()->id != $user->id ? route('site.users.account', ['u' => $user->id]) : route('site.users.account') }}">{{ trans('users::users.my account') }}</a>
			</li>
			@foreach ($sections as $section)
				<li class="nav-item<?php if ($section['active']) { echo ' active'; } ?>">
					<a class="nav-link" href="{{ $section['route'] }}">{!! $section['name'] !!}</a>
				</li>
			@endforeach
		</ul>
	</div>
</div>

<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<input type="hidden" name="userid" id="userid" value="{{ $user->id }}" />
	<?php
	if ($active):
		echo isset($active['content']) ? $active['content'] : '';
	else:
		?>
		<div class="contentInner">
			<h2 class="mt-0">{{ trans('users::users.my account') }}</h2>

			<div class="card panel panel-default mb-3">
				<div class="card-header panel-heading">
					<h3 class="card-title my-0">Profile</h3>
				</div>
				<div class="card-body panel-body">
					@if (auth()->user()->can('manage users'))
						@if ($user->trashed())
							<p class="alert alert-warning">This account was removed on {{ $user->dateremoved }}.</p>
						@endif

					<dl class="row">
						<div class="col-md-6 mb-2">
							<dt>Created</dt>
							<dd>
								<span class="text-muted">
									@if ($user->isCreated())
										<time datetime="{{ $user->getUserUsername()->datecreated->toDateTimeLocalString() }}">{{ $user->getUserUsername()->datecreated->format('M d, Y') }}</time>
									@else
										{{ trans('global.unknown') }}
									@endif
								</span>
							</dd>
						</div>
						<div class="col-md-6 mb-2">
							<dt>Last Visit</dt>
							<dd>
								<span class="text-muted">
									@if ($user->hasVisited())
										<time datetime="{{ $user->datelastseen->toDateTimeLocalString() }}">{{ $user->datelastseen->format('M d, Y @ h:i a') }}</time>
									@else
										{{ trans('global.unknown') }}
									@endif
								</span>
							</dd>
						</div>
					@else
					<dl class="row">
					@endif
						@php
						$fields = [
							'username' => 'Username',
							'department' => 'Department',
							'title' => 'Title',
							'campus' => 'Campus',
							'phone' => 'Phone',
							'building' => 'Building',
							'email' => 'Email',
							'room' => 'Room',
						];
						@endphp
						@foreach ($fields as $key => $name)
							@if ($val = $user->{$key})
								<div class="col-md-6 mb-2">
									<dt>{{ $name }}</dt>
									<dd><span class="text-muted">{{ $val }}</dd>
								</div>
							@endif
						@endforeach
						<div class="col-md-6 mb-2">
							<dt>
								Login Shell
								<a href="#box1_account" class="help icn tip" title="Help">
									<span class="fa fa-question-circle" aria-hidden="true"></span> Help
								</a>
							</dt>
							<dd>
								@if ($user->loginShell === false)
									<span class="alert alert-error">Failed to retrieve shell information</span>
								@else
									<span id="SPAN_loginshell" class="edit-hide text-muted">{!! $user->loginShell ? e($user->loginShell) : '<span id="SPAN_loginshell" class="edit-hide none">' . trans('global.unknown') . '</span>' !!}</span>

									@if (!preg_match("/acmaint/", $user->loginShell))
										<a href="#loginshell" id="edit-loginshell" class="edit-hide property-edit" data-prop="loginshell">
											<span class="fa fa-pencil" aria-hidden="true"></span><span class="sr-only">Edit</span>
										</a>
										<div id="loginshell" class="edit-show hide">
											<div class="form-group">
												<span class="input-group">
													<select class="form-control property-edit" id="INPUT_loginshell" data-prop="loginshell">
														<?php
														$selected = '';
														if (preg_match("/bash$/", $user->loginShell)):
															$selected = ' selected="selected"';
														endif;
														?>
														<option value="/bin/bash"<?php echo $selected; ?>>bash</option>
														<?php
														$selected = '';
														if (preg_match("/\/csh$/", $user->loginShell)):
															$selected = ' selected="selected"';
														endif;
														?>
														<option value="/bin/csh"<?php echo $selected; ?>>csh</option>
														<?php
														$selected = '';
														if (preg_match("/tcsh$/", $user->loginShell)):
															$selected = ' selected="selected"';
														endif;
														?>
														<option value="/bin/tcsh"<?php echo $selected; ?>>tcsh</option>
														<?php
														$selected = '';
														if (preg_match("/zsh$/", $user->loginShell)):
															$selected = ' selected="selected"';
														endif;
														?>
														<option value="/bin/zsh"<?php echo $selected; ?>>zsh</option>
													</select>
													<span class="input-group-append">
														<a href="{{ auth()->user()->id != $user->id ? route('site.users.account', ['u' => $user->id]) : route('site.users.account') }}" data-api="{{ route('api.users.update', ['id' => $user->id]) }}" class="btn input-group-text text-success property-save" title="Save">
															<span class="fa fa-save" aria-hidden="true"></span><span class="sr-only">Save</span>
														</a>
														<a href="#edit-loginshell" class="btn input-group-text text-danger property-cancel" title="Cancel">
															<span class="fa fa-ban" aria-hidden="true"></span><span class="sr-only">Cancel</span>
														</a>
													</span>
												</span>
											</div>
											<p>Please note it may take a few minutes for changes to be reflected.</p>
											<div class="alert alert-danger hide" id="loginshell_error"></div>
										</div>
									@endif
								@endif
								<div id="box1_account" class="dialog-help" title="Login Shell">
									<p>This is the interactive shell you are started with when logging into {{ config('app.name') }} resources. The default for new accounts is bash however you may use this to change it if desired. Supported options are <code>bash</code>, <code>tcsh</code>, and <code>zsh</code>. Once changed, it will take one to two hours for the changes to propagate to all systems.</p>
								</div>
							</dd>
						</div>
					</dl>
				</div>
			</div>

			@foreach ($parts as $part)
				{!! $part !!}
			@endforeach

			@if (config('module.users.allow_self_deletion'))
				<div class="card card-danger">
					<div class="card-header">
						<h3 class="card-title my-0">{{ trans('users::users.delete.delete account') }}</h3>
					</div>
					<div class="card-body">
						<p>{{ trans('users::users.delete.description') }}</p>
						<p>
							<a href="#confirmdelete" class="btn btn-danger" data-toggle="modal" data-target="#confirmdelete">
								{{ trans('users::users.delete.delete account') }}
							</a>
						</p>
					</div>
				</div>

				<div class="modal dialog" id="confirmdelete" tabindex="-1" aria-labelledby="confirmdelete-title" aria-hidden="true" title="{{ trans('users::users.delete.are you sure') }}">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content dialog-content shadow-sm">
							<div class="modal-header">
								<div class="modal-title" id="confirmdelete-title">{{ trans('users::users.delete.are you sure') }}</div>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body dialog-body">
								<form method="post" action="{{ route('site.users.account.delete') }}">
									<p>{{ trans('users::users.delete.warning') }}</p>
									<div class="form-group">
										<label for="confirmdelete">{!! trans('users::users.delete.confirm', ['username' => $user->name]) !!}</label>
										<input type="text" name="confirmdelete" id="confirmdelete" class="form-control" required value="" />
									</div>
									<div class="form-group">
										<input type="submit" class="btn btn-danger" value="{{ trans('users::users.delete.confirm submit') }}" />
									</div>
									@csrf
								</form>
							</div>
						</div>
					</div>
				</div>
			@endif
		</div><!-- / .contentInner -->
		<?php
	endif;
	?>
</div>
</div>
@stop
