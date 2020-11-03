@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
<script src="{{ asset('modules/users/js/users.js?v=' . filemtime(public_path() . '/modules/users/js/users.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

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
			<!-- <li><a href="#user-history">History</a></li> -->
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
					<input type="text" name="ufields[username]" id="field_username" value="{{ $user->username }}" class="form-control required<?php if ($user->id) { echo ' readonly" readonly="readonly'; } ?>" />
				</div>

				<div class="form-group">
					<label for="field-name">{{ trans('users::users.name') }}:</label><br />
					<input type="text" class="form-control<?php if ($user->sourced) { echo ' readonly" readonly="readonly'; } ?>" name="fields[name]" id="field-name" value="{{ $user->name }}" />
				</div>

				<div class="form-group">
					<label for="field-organization_id">{{ trans('users::users.organization id') }}:</label>
					<input type="text" class="form-control" name="fields[puid]" id="field-organization_id" value="{{ $user->puid }}" />
				</div>

				<div class="form-group">
					<label for="field-api_token">{{ trans('users::users.api token') }}:</label>
					<div class="row">
						<div class="col col-md-10">
							<input type="text" class="form-control readonly" readonly="readonly" name="fields[api_token]" id="field-api_token" value="{{ $user->api_token }}" />
						</div>
						<div class="col col-md-2">
							<button class="btn btn-secondary btn-apitoken">{{ trans('users::users.regenerate') }}</button>
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
						<th>{{ trans('users::users.register date') }}</th>
						<td>{{ $user->datecreated }}</td>
					</tr>
					<tr>
						<th>{{ trans('users::users.last visit date') }}</th>
						<td><?php echo !$user->getOriginal('last_visit') || $user->getOriginal('last_visit') == '0000-00-00 00:00:00' ? trans('global.never') : $user->last_visit; ?></td>
					</tr>
					@if ($user->isTrashed())
					<tr>
						<th>{{ trans('users::users.removed date') }}</th>
						<td>{{ $user->dateremoved }} ?></td>
					</tr>
					@endif
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
		<!-- <div id="user-history">
			History
		</div>/ #user-history -->
	</div><!-- / .tabs -->

	<input type="hidden" name="id" value="{{ $user->id }}" />

	@csrf
</form>
@stop