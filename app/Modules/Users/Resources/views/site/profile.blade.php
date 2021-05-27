@extends('layouts.master')

@php
$active = $sections->firstWhere('active', '=', true);
@endphp

@if (auth()->user()->can('manage users'))
@push('scripts')
<script src="{{ asset('modules/resources/js/roles.js?v=' . filemtime(public_path() . '/modules/resources/js/roles.js')) }}"></script>
@endpush
@endif

@section('title'){{ ($active ? str_replace(['<span class="badge pull-right">', '</span>'], ['(', ')'], $active['name']) : trans('users::users.my accounts')) }}@stop

@section('content')

<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('users::site.admin', ['user' => $user])

	<h2>{{ $user->name }}</h2>

	<div class="qlinks">
		<ul class="dropdown-menu">
			<li<?php if (!$active) { echo ' class="active"'; } ?>>
				<a href="{{ auth()->user()->id != $user->id ? route('site.users.account', ['u' => $user->id]) : route('site.users.account') }}">{{ trans('users::users.my accounts') }}</a>
			</li>
			@foreach ($sections as $section)
				<li<?php if ($section['active']) { echo ' class="active"'; } ?>>
					<a href="{{ $section['route'] }}">{!! $section['name'] !!}</a>
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

			<div class="card panel panel-default mb-3">
				<div class="card-header panel-heading">
					Profile
				</div>
				<div class="card-body panel-body">
					@if (auth()->user()->can('manage users'))
						@if ($user->isTrashed())
							<p class="alert alert-warning">This account was removed on {{ $user->dateremoved }}.</p>
						@endif

						<div class="row">
							<div class="col-md-6">
								<p>
									<strong>Created</strong><br />
									<span class="text-muted">{{ $user->datecreated && $user->datecreated != '-0001-11-30 00:00:00' ? $user->datecreated : trans('global.unknown') }}</span>
								</p>
							</div>
							<div class="col-md-6">
								<p>
									<strong>Last Visit</strong><br />
									<span class="text-muted">{{ $user->datelastseen }}</span>
								</p>
							</div>
						</div>
					@endif

					<div class="row">
						<div class="col-md-6">
							<p>
								<strong>Username</strong><br />
								<span class="text-muted">{{ $user->username }}</span>
							</p>
						</div>
						<div class="col-md-6">
							<p>
								<strong>Department</strong><br />
								<span class="text-muted">{!! $user->department ? e(ucwords($user->department)) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span>
							</p>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<p>
								<strong>Title</strong><br />
								<span class="text-muted">{!! $user->title ? e(ucwords($user->title)) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span>
							</p>
						</div>
						<div class="col-md-6">
							<p>
								<strong>Campus</strong><br />
								<span class="text-muted">{!! $user->campus ? e(ucwords($user->campus)) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span>
							</p>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<p>
								<strong>Phone</strong><br />
								<span class="text-muted">{!! $user->phone ? e($user->phone) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span>
							</p>
						</div>
						<div class="col-md-6">
							<p>
								<strong>Building</strong><br />
								<span class="text-muted">{!! $user->building ? e($user->building) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span>
							</p>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<p>
								<strong>Email</strong><br />
								<span class="text-muted">{{ $user->email }}</span>
							</p>
						</div>
						<div class="col-md-6">
							<p>
								<strong>Room</strong><br />
								<span class="text-muted">{!! $user->roomnumber ? e($user->roomnumber) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span>
							</p>
						</div>
					</div>

					<p>
						<strong>Login Shell</strong><br />
						@if ($user->loginShell === false)
							<span class="alert alert-error">Failed to retrieve shell information</span>
						@else
							<span class="text-muted">{!! $user->loginShell ? e($user->loginShell) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span>
						@endif
					</p>
				</div>
			</div>

			@if (auth()->user()->can('manage users'))
				<div class="card panel panel-default session mb-3">
					<div class="card-header panel-heading">
						Sessions
					</div>
					<ul class="list-group list-group-flush">
						@if (count($user->sessions))
							@foreach ($user->sessions as $session)
								<li class="list-group-item">
									<div class="row">
										<div class="col-md-1">
											<i class="fa fa-desktop"></i>
										</div>
										<div class="col-md-11">
											<div class="session-ip card-title">
												<div class="row">
													<div class="col-md-4">
														<strong>{{ $session->ip_address == '::1' ? 'localhost' : $session->ip_address }}</strong>
													</div>
													<div class="col-md-4">
														{{ $session->last_activity->diffForHumans() }}
													</div>
													<div class="col-md-4 text-right">
														@if ($session->id == session()->getId())
															<span class="badge badge-info float-right">Your current session</span>
														@endif
													</div>
												</div>
											</div>
											<div class="session-current card-text text-muted">
												{{ $session->user_agent }}
											</div>
										</div>
									</div>
								</li>
							@endforeach
						@else
							<li class="list-group-item">
								<span class="none">{{ trans('global.none') }}</span>
							</li>
						@endif
					</ul>
				</div>
			@endif

			<?php
			$queues = $user->queues()
				//->where('groupid', '>', 0)
				//->whereIn('membertype', [1, 4])
				->whereIsPending()
				//->whereNotIn('id', $q)
				->withTrashed()
				->whereIsActive()
				->get();

			if (count($queues)):
			?>
			<div class="card panel panel-default session">
				<div class="card-header panel-heading">
					Requests
				</div>
				<ul class="list-group list-group-flush">
					<?php
					// Owner groups
					/*$memberships = $user->groups()
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
						->withTrashed()
						->whereIsActive()
						->get();*/

					foreach ($queues as $qu):
						if ($qu->isMember() && $qu->isTrashed()):
							continue;
						endif;

						$queue = $qu->queue;

						if (!$queue || $queue->isTrashed()):
							continue;
						endif;

						if (!$queue->scheduler || $queue->scheduler->isTrashed()):
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
									@if ($qu->datetimeremoved && $qu->datetimeremoved != '0000-00-00 00:00:00' && $qu->datetimeremoved != '-0001-11-30 00:00:00')
										<span class="badge badge-danger">{{ trans('users::users.removed') }}</span>
									@elseif ($qu->membertype == 4)
										<span class="badge badge-warning">{{ $qu->type->name }}</span>
									@else
										<span class="badge">{{ $qu->type->name }}</span>
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
		<?php endif; ?>

		@if (auth()->user()->can('manage users'))
			<div class="card panel panel-default session mb-3">
				<div class="card-header panel-heading">
					<div class="row">
						<div class="col-md-9">
							Roles
						</div>
						<div class="col-md-3 text-right">
							<a href="#manage_roles_dialog" id="manage_roles" data-membertype="1" class="btn btn-sm">
								<i class="fa fa-pencil" aria-hidden="true"></i> Manage Roles
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
						->where(function($where)
						{
							$where->whereNull('datetimeremoved')
								->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
						})
						->orderBy('display', 'desc')
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
								<td id="resource{{ $resource->id }}_group">-</td>
								<td id="resource{{ $resource->id }}_shell">-</td>
								<td id="resource{{ $resource->id }}_pi">-</td>
								<td id="resource{{ $resource->id }}" data-api="{{ route('api.resources.members') }}">
									<span class="fa fa-exclamation-triangle" aria-hidde="true"></span>
									<span class="sr-only">Loading...</span>
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>

					<div id="manage_roles_dialog" data-id="{{ $user->id }}" title="Manage Roles" class="roles-dialog">
						<form method="post" action="{{ route('site.users.account') }}">
							<div class="form-group">
								<label for="role" class="sr-only">New Role</label>
								<select id="role" class="form-control" data-id="{{ $user->id }}" data-api="{{ route('api.resources.members.create') }}">
									<option value="">(Select Role)</option>
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
									<input id="role_shell" type="text" class="form-control" />
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

								<span id="role_errors" class="alert alert-warning hide"></span>
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