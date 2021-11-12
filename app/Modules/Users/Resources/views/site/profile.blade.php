@extends('layouts.master')

@php
$active = $sections->firstWhere('active', '=', true);
$paths = app('pathway')->names();
$title = end($paths);
$title = $title ?: ($active ? str_replace(['<span class="badge pull-right">', '</span>'], ['(', ')'], $active['name']) : trans('users::users.my accounts'));
@endphp

@push('scripts')
<script src="{{ asset('modules/users/js/site.js?v=' . filemtime(public_path() . '/modules/users/js/site.js')) }}"></script>
@if (auth()->user()->can('manage users'))
<script src="{{ asset('modules/resources/js/roles.js?v=' . filemtime(public_path() . '/modules/resources/js/roles.js')) }}"></script>
@endif
@endpush

@section('title'){{ $title }}@stop

@section('content')

@include('users::site.admin', ['user' => $user])

<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<h2>
		{{ $user->name }}
		@if (auth()->user()->can('manage users') && $user->isOnline())
			<span class="badge badge-success">Online</span>
		@endif
	</h2>

	<div class="qlinks">
		<ul class="nav flex-column profile-menu">
			<li class="nav-item<?php if (!$active) { echo ' active'; } ?>">
				<a class="nav-link<?php if (!$active) { echo ' active'; } ?>" href="{{ auth()->user()->id != $user->id ? route('site.users.account', ['u' => $user->id]) : route('site.users.account') }}">{{ trans('users::users.my accounts') }}</a>
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
			<div class="row">
				<div class="col-md-9">
					<h2>{{ trans('users::users.my accounts') }}</h2>
				</div>
				<div class="col-md-3 text-right">
					@if (auth()->user()->id == $user->id)
						<a class="btn btn-outline-secondary" href="{{ route('site.users.account.request') }}">{{ trans('users::users.request access') }}</a>
					@endif
				</div>
			</div>

			<?php
			$managedgroups = $user->groups()
				->whereIsManager()
				->get();

			if (count($managedgroups)):
				$groups = array();

				foreach ($managedgroups as $groupmembership):
					if (!$groupmembership->group):
						continue;
					endif;
					if ($groupmembership->group->pendingMembersCount > 0):
						$groups[] = $groupmembership->group;
					endif;
				endforeach;

				if (count($groups)):
					?>
					<div class="alert alert-warning">
						<p>
							The following groups have pending membership requests:
						</p>
						<ul>
							<?php foreach ($groups as $group): ?>
								<li>
									<a href="{{ route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $group->id, 'subsection' => 'members']) }}">{{ $group->name }}</a> <span class="badge badge-warning">{{ $group->pendingMembersCount }}</span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php
				endif;
			endif;
			?>

			<div class="card panel panel-default mb-3">
				<div class="card-header panel-heading">
					Profile
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
										<time datetime="{{ $user->getUserUsername()->datecreated }}">{{ $user->getUserUsername()->datecreated->format('M d, Y') }}</time>
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
										<time datetime="{{ $user->datelastseen }}">{{ $user->datelastseen->format('M d, Y @ h:i a') }}</time>
									@else
										{{ trans('global.unknown') }}
									@endif
								</span>
							</dd>
						</div>
					@else
					<dl class="row">
					@endif
						<div class="col-md-6 mb-2">
							<dt>Username</dt>
							<dd><span class="text-muted">{{ $user->username }}</span></dd>
						</div>
						<div class="col-md-6 mb-2">
							<dt>Department</dt>
							<dd><span class="text-muted">{!! $user->department ? e($user->department) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span></dd>
						</div>
						<div class="col-md-6 mb-2">
							<dt>Title</dt>
							<dd><span class="text-muted">{!! $user->title ? e($user->title) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span></dd>
						</div>
						<div class="col-md-6 mb-2">
							<dt>Campus</dt>
							<dd><span class="text-muted">{!! $user->campus ? e($user->campus) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span></dd>
						</div>
						<div class="col-md-6 mb-2">
							<dt>Phone</dt>
							<dd><span class="text-muted">{!! $user->phone ? e($user->phone) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span></dd>
						</div>
						<div class="col-md-6 mb-2">
							<dt>Building</dt>
							<dd><span class="text-muted">{!! $user->building ? e($user->building) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span></dd>
						</div>
						<div class="col-md-6 mb-2">
							<dt>Email</dt>
							<dd><span class="text-muted">{{ $user->email }}</span></dd>
						</div>
						<div class="col-md-6 mb-2">
							<dt>Room</dt>
							<dd><span class="text-muted">{!! $user->roomnumber ? e($user->roomnumber) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span></dd>
						</div>
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
														if (preg_match("/csh$/", $user->loginShell)):
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

			<?php
			/*$queues = $user->queues()
				->whereIsPending()
				->get();

			if (count($queues)):
			?>
			<div class="card panel panel-default">
				<div class="card-header panel-heading">
					Requests
				</div>
				<ul class="list-group list-group-flush">
					<?php
					// Owner groups
					$memberships = $user->groups()
						->where('groupid', '>', 0)
						->whereIsManager()
						->get();

					$q = array();
					foreach ($memberships as $membership):
						$group = $membership->group;

						$unixgroups = $group->unixGroups->pluck('longname')->toArray();
						?>
						<li class="list-group-item">
							<div class="card-title panel-head">
								<div class="row">
									<div class="col-md-6">
										<strong><a href="{{ route('site.users.account.section.show', ['section' => 'groups', 'id' => $group->id]) }}">{{ $group->name }}</a></strong>
									</div>
									<div class="col-md-6 text-right">
										<span class="badge badge-success">{{ $membership->type->name }}</span>
									</div>
								</div>
							</div>
							<div class="card-text panel-body">
								@foreach ($group->queues as $queue)
									<div class="row">
										<div class="col-md-6">
											<strong class="text-muted">Queue</strong>: {{ $queue->name }}
										</div>
										<div class="col-md-6">
											<strong class="text-muted">Resource</strong>: {{ $queue->subresource->name }}
										</div>
									</div>
								@endforeach
								@if (!empty($unixgroups))
									<strong class="text-muted">Unix Groups</strong>: {{ implode(', ', $unixgroups) }}
								@endif
							</div>
						</li>
						<?php
					endforeach;
					
					$queues = $user->queues()
						//->where('groupid', '>', 0)
						//->whereIn('membertype', [1, 4])
						->whereIsPending()
						//->whereNotIn('id', $q)
						->get();

					foreach ($queues as $qu):
						if ($qu->isMember() && $qu->trashed()):
							continue;
						endif;

						$queue = $qu->queue;

						if (!$queue || $queue->trashed()):
							continue;
						endif;

						if (!$queue->scheduler || $queue->scheduler->trashed()):
							continue;
						endif;

						$group = $queue->group;

						if (!$group || !$group->id):
							continue;
						endif;

						$unixgroups = $group->unixGroups->pluck('longname')->toArray();
						?>
						<li class="list-group-item">
							<div class="row">
								<div class="col-md-6">
									<strong><a href="{{ route('site.users.account.section.show', ['section' => 'groups', 'id' => $group->id]) }}">{{ $group->name }}</a></strong>
								</div>
								<div class="col-md-6 text-right">
									@if ($qu->trashed())
										<span class="badge badge-danger">{{ trans('users::users.removed') }}</span>
									@elseif ($qu->membertype == 4)
										<span class="badge badge-warning">{{ $qu->type->name }}</span>
									@else
										<span class="badge badge-secondary">{{ $qu->type->name }}</span>
									@endif
								</div>
							</div>
							<div class="card-text panel-body">
								<div class="row">
									<div class="col-md-6">
										<strong class="text-muted">Queue</strong>: {{ $queue->name }}
									</div>
									<div class="col-md-6">
										<strong class="text-muted">Resource</strong>: {{ $queue->subresource->name }}
									</div>
								</div>
								@if (!empty($unixgroups))
									<strong class="text-muted">Unix Groups</strong>: {{ implode(', ', $unixgroups) }}
								@endif
							</div>
						</li>
						<?php
					endforeach;
					?>
				</ul>
			</div>
		<?php endif;*/ ?>

		<?php
		// Owner groups
		$memberships = $user->groups()
			->where('groupid', '>', 0)
			->whereIsManager()
			->get();

		$ids = array();
		$allqueues = array();
		foreach ($memberships as $membership):
			$group = $membership->group;

			$queues = $group->queues;

			foreach ($queues as $queue):
				$ids[] = $queue->id;

				if (!$queue || $queue->trashed()):
					continue;
				endif;

				if (!$queue->scheduler || $queue->scheduler->trashed()):
					continue;
				endif;

				$queue->status = 'member';

				$allqueues[] = $queue;
			endforeach;
		endforeach;

		$queues = $user->queues()
			->whereNotIn('queueid', $ids)
			->get();

		foreach ($queues as $qu):
			if ($qu->trashed()):
				continue;
			endif;

			$queue = $qu->queue;

			if (!$queue || $queue->trashed()):
				continue;
			endif;

			if (!$queue->scheduler || $queue->scheduler->trashed()):
				continue;
			endif;

			$group = $queue->group;

			if (!$group || !$group->id):
				continue;
			endif;

			if ($qu->isPending()):
				$queue->status = 'pending';
			else:
				$queue->status = 'member';
			endif;

			$allqueues[] = $queue;
		endforeach;

		if (count($allqueues)):
			?>
			<div class="card panel panel-default">
				<div class="card-header panel-heading">
					Queues
				</div>
				<div class="card-body">
					<table class="table table-hover">
						<caption class="sr-only">Queues</caption>
						<thead>
							<tr>
								<th scope="col">Queue</th>
								<th scope="col">Resource</th>
								<th scope="col">Group</th>
								<th scope="col">Status</th>
							</tr>
						</thead>
						<tbody>
						<?php
						foreach ($allqueues as $queue):
							$group = $queue->group;
							?>
							<tr>
								<td>
									{{ $queue->name }}
								</td>
								<td>
									{{ $queue->resource ? $queue->resource->name : '' }}
								</td>
								<td>
									<a href="{{ route('site.users.account.section.show', ['section' => 'groups', 'id' => $group->id, 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}">{{ $group->name }}</a>
								</td>
								<td>
								@if ($queue->status == 'pending')
									<span class="badge badge-warning">Pending</span>
								@else
									<span class="badge badge-success">Member</span>
								@endif
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
		endif;
		?>

		@if (auth()->user()->can('manage users'))
			<div class="card panel panel-default session mb-3">
				<div class="card-header panel-heading">
					<div class="row">
						<div class="col-md-9">
							Resources
							<a href="#roles_help" class="help icn tip" title="Roles Help">
								<span class="fa fa-question-circle" aria-hidden="true"></span> Help
							</a>
							<div id="roles_help" class="dialog-help" title="Roles">
								<p>Role status may be compiled from various external sources. Available statuses are:</p>
								<ul>
									<li><span class="badge badge-secondary">No Role</span></li>
									<li><span class="badge badge-info">Role Pending</span></li>
									<li><span class="badge badge-success">Role Ready</span></li>
									<li><span class="badge badge-danger">Removal Pending</span></li>
								</ul>
							</div>
						</div>
						<div class="col-md-3 text-right">
							<a href="#manage_roles_dialog" id="manage_roles" data-membertype="1" class="btn btn-sm">
								<span class="fa fa-pencil" aria-hidden="true"></span> Manage Roles
							</a>
						</div>
					</div>
				</div>
				<div class="card-body panel-body">
					<?php
					// Gather roles
					$resources = App\Modules\Resources\Models\Asset::query()
						->where('rolename', '!=', '')
						//->where('retired', '=', 0)
						->where('listname', '!=', '')
						->orderBy('name', 'asc')
						->get();
					?>

					<table class="table table-hover" id="roles" data-api="{{ route('api.resources.index', ['limit' => 100]) }}">
						<caption class="sr-only">Roles</caption>
						<thead>
							<tr>
								<th scope="col">Resource</th>
								<th scope="col">Group</th>
								<th scope="col">Shell</th>
								<th scope="col">PI</th>
								<th scope="col">Status</th>
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
									<span class="fa fa-exclamation-triangle" aria-hidde="true"></span>
									<span class="sr-only">Loading...</span>
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>

					<div id="manage_roles_dialog" data-id="{{ $user->id }}" title="Manage Access" class="roles-dialog">
						<form method="post" action="{{ route('site.users.account') }}">
							<div class="form-group">
								<label for="role">Resource <span class="required">*</span></label>
								<select id="role" class="form-control" data-id="{{ $user->id }}" required data-api="{{ route('api.resources.members.create') }}">
									<option value="">(Select Resource)</option>
									@foreach ($resources as $resource)
										<option value="{{ $resource->id }}" data-api="{{ route('api.resources.members.read', ['id' => $resource->id . '.' . $user->id]) }}">{{ $resource->name }}</option>
									@endforeach
								</select>
							</div>

							<div class="hide" id="role_table">
								<div class="form-group">
									<label for="role_status">Status</label>
									<input type="text" disabled="disabled" class="form-control" id="role_status" />
								</div>
								<div class="form-group">
									<label for="role_group">Group</label>
									<input id="role_group" type="text" class="form-control" />
								</div>
								<div class="form-group">
									<label for="role_shell">Shell</label>
									<!-- <input id="role_shell" type="text" class="form-control" /> -->
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
										if (preg_match("/csh$/", $user->loginShell)):
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
									<label for="role_pi">PI</label>
									<input id="role_pi" type="text" class="form-control" />
								</div>
								<div class="form-group">
									<button id="role_add" class="btn btn-success role-add hide" data-id="{{ $user->id }}" data-api="{{ route('api.resources.members.create') }}">Add Role</button>
									<button id="role_modify" class="btn btn-success role-add hide" data-id="{{ $user->id }}">Modify Role</button>
									<button id="role_delete" class="btn btn-danger role-delete hide" data-id="{{ $user->id }}">Delete Role</button>
								</div>

								<div id="role_errors" class="alert alert-danger hide"></div>
							</div>
						</form>
					</div>
				</div>
			</div>
		@endif
		</div><!-- / .contentInner -->
		<?php
	endif;
	?>
</div>
@stop
