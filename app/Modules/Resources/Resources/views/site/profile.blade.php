@push('scripts')
<script src="{{ asset('modules/resources/js/roles.js?v=' . filemtime(public_path() . '/modules/resources/js/roles.js')) }}"></script>
@endpush

<div class="card mb-3">
	<div class="card-header">
		<div class="row">
			<div class="col-md-9">
				<h3 class="card-title">
					{{ trans('resources::assets.resources') }}
					<a href="#roles_help" class="help help-dialog text-info tip" title="{{ trans('resources::assets.roles help') }}">
						<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only"> {{ trans('resources::assets.roles help') }}</span>
					</a>
				</h3>
				<div id="roles_help" class="dialog-help" title="Resources">
					<p>{{ trans('resources::assets.roles explanation') }}</p>
					<ul>
						<li><span class="badge badge-secondary">{{ trans('resources::assets.no role') }}</span></li>
						<li><span class="badge badge-info">{{ trans('resources::assets.role pending') }}</span></li>
						<li><span class="badge badge-success">{{ trans('resources::assets.role ready') }}</span></li>
						<li><span class="badge badge-danger">{{ trans('resources::assets.removal pending') }}</span></li>
					</ul>
				</div>
			</div>
			<div class="col-md-3 text-right">
				@if ($user->enabled)
				<a href="#manage_roles_dialog" data-toggle="modal" id="manage_roles" data-membertype="1" class="btn btn-sm" data-tip="{{ trans('resources::assets.manage access') }}">
					<span class="fa fa-pencil" aria-hidden="true"></span> {{ trans('resources::assets.manage') }}
				</a>
				@endif
			</div>
		</div>
	</div>
	<div class="card-body">
		<table class="table table-hover" id="roles" data-api="{{ route('api.resources.index', ['limit' => 100]) }}">
			<caption class="sr-only">{{ trans('resources::assets.resource membership') }}</caption>
			<thead>
				<tr>
					<th scope="col">{{ trans('resources::assets.resource') }}</th>
					<th scope="col">{{ trans('resources::assets.group') }}</th>
					<th scope="col">{{ trans('resources::assets.shell') }}</th>
					<th scope="col">{{ trans('resources::assets.pi') }}</th>
					<th scope="col">{{ trans('resources::assets.status') }}</th>
				</tr>
			</thead>
			<tbody>
			@foreach ($resources as $resource)
				<tr>
					<td>{{ $resource->name }}</td>
					<td id="resource{{ $resource->id }}_group"></td>
					<td id="resource{{ $resource->id }}_shell"></td>
					<td id="resource{{ $resource->id }}_pi"></td>
					<td id="resource{{ $resource->id }}" data-api="{{ route('api.resources.members') }}">
						<span class="fa fa-exclamation-triangle text-warning" aria-hidde="true"></span>
						<span class="sr-only">{{ trans('global.loading') }}</span>
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
</div>

@if ($user->enabled)
<div class="modal dialog roles-dialog" id="manage_roles_dialog" tabindex="-1" aria-labelledby="manage_roles_dialog-title" aria-hidden="true" title="{{ trans('resources::assets.manage access') }}">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content dialog-content shadow-sm">
			<div class="modal-header">
				<div class="modal-title" id="manage_roles_dialog-title">{{ trans('resources::assets.manage access') }}</div>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body dialog-body">

				<form method="post" action="{{ route('site.users.account') }}">
					<div class="form-group">
						<label for="role">{{ trans('resources::assets.resource') }} <span class="required">*</span></label>
						<select id="role" class="form-control" required data-id="{{ $user->id }}" data-api="{{ route('api.resources.members.create') }}">
							<option value="">{{ trans('resources::assets.select resource') }}</option>
							@foreach ($resources as $resource)
								<option value="{{ $resource->id }}" data-api="{{ route('api.resources.members.read', ['id' => $resource->id . '.' . $user->id]) }}">{{ $resource->name }}</option>
							@endforeach
						</select>
					</div>

					<div class="hide" id="role_table">
						<div class="form-group">
							<label for="role_status">{{ trans('resources::assets.status') }}</label>
							<input type="text" disabled="disabled" class="form-control" id="role_status" />
						</div>
						<div class="form-group">
							<label for="role_group">{{ trans('resources::assets.group') }}</label>
							<input id="role_group" type="text" class="form-control" />
						</div>
						<div class="form-group">
							<label for="role_shell">{{ trans('resources::assets.shell') }}</label>
							<select class="form-control" id="role_shell">
								<option value="">{{ trans('global.none') }}</option>
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
						</div>
						<div class="form-group">
							<label for="role_pi">{{ trans('resources::assets.pi') }}</label>
							<input id="role_pi" type="text" class="form-control" />
						</div>
						<div class="form-group mb-0">
							<button id="role_add" class="btn btn-success role-add hide" data-id="{{ $user->id }}" data-api="{{ route('api.resources.members.create') }}">{{ trans('resources::assets.add role') }}</button>
							<button id="role_modify" class="btn btn-success role-add hide" data-id="{{ $user->id }}">{{ trans('resources::assets.modify role') }}</button>
							<button id="role_delete" class="btn btn-danger role-delete hide" data-id="{{ $user->id }}">{{ trans('resources::assets.delete role') }}</button>
						</div>

						<span id="role_errors" class="alert alert-warning hide"></span>
					</div>
				</form>

			</div>
		</div>
	</div>
</div>
@endif
