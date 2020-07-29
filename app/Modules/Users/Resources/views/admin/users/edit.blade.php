@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
<script src="{{ asset('modules/users/js/users.js?v=' . filemtime(public_path() . '/modules/users/js/users.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		($user->id ? trans('global.edit') . ' #' . $user->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit users'))
		{!! Toolbar::save(route('admin.users.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.users.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('users::system.users') }}: {{ $user->id ? 'Edit: #' . $user->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.users.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.VALIDATION_FORM_FAILED') }}">

	@if ($errors->any())
		<div class="alert alert-error">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="tabs">
		<ul>
			<li><a href="#user-account">Account</a></li>
		@if (auth()->user()->can('view users.notes'))
			<li><a href="#user-notes">Notes</a></li>
		@endif
			<li><a href="#user-history">History</a></li>
		</ul>
		<div id="user-account">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				@if ($user->sourced)
					<p class="alert alert-info">{{ trans('users::users.sourced description') }}</p>
				@endif

				<div class="form-group">
					<label id="field_username-lbl" for="field_username">{{ trans('users::users.username') }} <span class="required star">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[username]" id="field_username" value="{{ $user->username }}" class="form-control required<?php if ($user->id) { echo ' readonly" readonly="readonly'; } ?>" />
				</div>

				<div class="form-group">
					<label id="field_email-lbl" for="field_email">{{ trans('users::users.email') }} <span class="required star">{{ trans('global.required') }}</span></label>
					<input type="email" name="fields[email]" class="form-control validate-email required<?php if ($user->sourced) { echo ' readonly" readonly="readonly'; } ?>" id="field_email" value="{{ $user->email }}" />
				</div>

				<!-- <fieldset>
					<legend>{{ trans('users::users.name') }}</legend> -->

				<div class="row">
					<div class="col col-md-4 form-group">
						<label for="field-given_name">{{ trans('users::users.first name') }}:</label><br />
						<input type="text" class="form-control<?php if ($user->sourced) { echo ' readonly" readonly="readonly'; } ?>" name="fields[given_name]" id="field-given_name" value="{{ $user->given_name }}" />
					</div>

					<div class="col col-md-4 form-group">
						<label for="field-middle_name">{{ trans('users::users.middle name') }}:</label><br />
						<input type="text" class="form-control<?php if ($user->sourced) { echo ' readonly" readonly="readonly'; } ?>" name="fields[middle_name]" id="field-middle_name" value="{{ $user->middle_name }}" />
					</div>

					<div class="col col-md-4 form-group">
						<label for="field-surname">{{ trans('users::users.last name') }}:</label><br />
						<input type="text" class="form-control<?php if ($user->sourced) { echo ' readonly" readonly="readonly'; } ?>" name="fields[surname]" id="field-surname" value="{{ $user->surname }}" />
					</div>
				</div>
				<!-- </fieldset> -->

				<div class="form-group">
					<label for="field-organization_id">{{ trans('users::users.organization id') }}:</label>
					<input type="text" class="form-control" name="fields[organization_id]" id="field-organization_id" value="{{ $user->organization_id }}" />
				</div>

				<div class="form-group">
					<label for="field-api_token">{{ trans('users::users.api token') }}:</label>
					<div class="row">
						<div class="col col-md-8">
							<input type="text" class="form-control readonly" readonly="readonly" name="fields[api_token]" id="field-api_token" value="{{ $user->api_token }}" />
						</div>
						<div class="col col-md-2">
							<button class="btn btn-outline-secondary">{{ trans('users::users.regenerate') }}</button>
						</div>
					</div>
					<span class="form-text text-muted">{{ trans('users::users.api token hint') }}</span>
				</div>
			</fieldset>

			<fieldset id="user-groups" class="adminform">
				<legend>{{ trans('users::users.assigned roles') }}</legend>

				<div class="form-group">
					<?php
					$roles = $user->roles
						->pluck('role_id')
						->all();

					echo App\Halcyon\Html\Builder\Access::roles('fields[newroles]', $roles, true); ?>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<table class="meta">
				<tbody>
					<tr>
						<th>{{ trans('users::users.id') }}</th>
						<td>
							{{ $user->id }}
						</td>
					</tr>
					<tr>
						<th>{{ trans('users::users.register ip') }}</th>
						<td>{{ $user->created_ip }}</td>
					</tr>
					<tr>
						<th>{{ trans('users::users.register date') }}</th>
						<td>{{ $user->created_at }}</td>
					</tr>
					<tr>
						<th>{{ trans('users::users.last visit date') }}</th>
						<td><?php echo !$user->getOriginal('last_visit') || $user->getOriginal('last_visit') == '0000-00-00 00:00:00' ? trans('global.never') : $user->last_visit; ?></td>
					</tr>
					<tr>
						<th>{{ trans('users::users.last modified date') }}</th>
						<td><?php echo !$user->getOriginal('modifiedDate') || $user->getOriginal('modifiedDate') == '0000-00-00 00:00:00' ? trans('global.never') : $user->modifiedDate; ?></td>
					</tr>
				</tbody>
			</table>

			@foreach ($user->sessions as $session)
				<div class="panel panel-default session">
					<div class="panel-body">
						<div class="session-ip">
							<strong>{{ $session->ip_address == '::1' ? 'localhost' : $session->ip_address }}</strong>
						</div>
					@if ($session->id == session()->getId())
						<div class="session-current">
							Your current session
						</div>
					@endif
					</div>
				</div>
			@endforeach

			<fieldset class="adminform">
				<legend>{{ trans('users::users.status') }}</legend>

				<!-- <div class="form-group" data-hint="{{ trans('users::users.approved user desc') }}">
					<label id="field_approved-lbl" for="field-approved">{{ trans('users::users.approved user') }}</label>
					<fieldset id="field-approved" class="radio">
						<ul>
							<li>
								<div class="form-check">
									<input class="form-check-input" type="radio" id="field-approved0" name="fields[approved]" value="0"<?php if ($user->approved == 0) { echo ' checked="checked"'; } ?> />
									<label class="form-check-label" for="field-approved0">{{ trans('global.no') }}</label>
								</div>
							</li>
							<li>
								<div class="form-check">
									<input class="form-check-input" type="radio" id="field-approved1" name="fields[approved]" value="1"<?php if ($user->approved == 1) { echo ' checked="checked"'; } ?> />
									<label class="form-check-label" for="field-approved1">{{ trans('global.yes') }}</label>
								</div>
							</li>
						</ul>
					</fieldset>
					<span class="hint">{{ trans('users::users.approved user desc') }}</span>
				</div> -->

				<div class="form-group">
					<label id="field-block-lbl" for="field-block">{{ trans('users::users.block this user') }}</label>
					<fieldset id="field-block" class="radio">
						<ul>
							<li>
								<div class="form-check">
									<input class="form-check-input" type="radio" id="field-block0" name="fields[block]" value="0"<?php if ($user->block == 0) { echo ' checked="checked"'; } ?> />
									<label class="form-check-label" for="field-block0">{{ trans('global.no') }}</label>
								</div>
							</li>
							<li>
								<div class="form-check">
									<input class="form-check-input" type="radio" id="field-block1" name="fields[block]" value="1"<?php if ($user->block == 1) { echo ' checked="checked"'; } ?> />
									<label class="form-check-label" for="field-block1">{{ trans('global.yes') }}</label>
								</div>
							</li>
						</ul>
					</fieldset>
				</div>

				<div class="form-check">
					<?php if ($user->email): ?>
						<?php if ($user->email_verified_at) { ?>
							<input class="form-check-input<?php if ($user->sourced) { echo ' readonly" readonly="readonly" disabled="disabled'; } ?>" type="checkbox" name="fields[activation]" id="activation" value="1" checked="checked" />
							<label class="form-check-label" for="activation">{{ trans('users::users.email verified at', ['datetime' => $user->email_verified_at]) }}</label>
						<?php } else { ?>
							<span class="form-text unconfirmed">{{ trans('users::users.email awaiting confirmation') }}</span>

							<input class="form-check-input" type="checkbox" name="fields[activation]" id="activation" value="1" />
							<label class="form-check-label" for="activation">{{ trans('users::users.confirm email') }}</label>

							<button class="btn">{{ trans('users::users.resend confirmation') }}</button>
						<?php } ?>
					<?php else: ?>
						<span class="form-text error">{{ trans('users::users.FIELD_EMAIL_NONE_ON_FILE') }}</span><br />

						<input class="form-check-input" type="checkbox" name="fields[activation]" id="activation" value="1" />
						<label class="form-check-label" for="activation">{{ trans('users::users.confirm email') }}</label>
					<?php endif; ?>
				</div>
			</fieldset>
				</div><!-- / .col -->
			</div><!-- / .grid -->
		</div><!-- / #user-account -->
		@if (auth()->user()->can('view users.notes'))
		<div id="user-notes">
			<?php
			$notes = $user->notes;
			if (count($notes))
			{
				foreach ($notes as $note)
				{
					?>
					<p><?php echo $note->subject; ?></p>
					<?php
				}
			}
			else
			{
				?>
				<p>No notes found.</p>
				<?php
			}
			?>
		</div><!-- / #user-notes -->
		@endif
		<div id="user-history">
			History
		</div><!-- / #user-history -->
	</div><!-- / .tabs -->

	<input type="hidden" name="id" value="{{ $user->id }}" />

	@csrf
</form>
@stop