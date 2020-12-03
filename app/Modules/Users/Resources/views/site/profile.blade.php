@extends('layouts.master')

@php
$active = $sections->firstWhere('active', '=', true);
@endphp

@section('title'){{ ($active ? str_replace(['<span class="badge">', '</span>'], ['(', ')'], $active['name']) : trans('users::users.my accounts')) }}@stop

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
	<?php
	if ($active)
	{
		echo isset($active['content']) ? $active['content'] : '';
	}
	else
	{
	?>
	<div class="contentInner">
		<div class="row">
			<div class="col-md-9">
				<h2>{{ trans('users::users.my accounts') }}</h2>
			</div>
			<div class="col-md-3 text-right">
				@if (auth()->user()->id == $user->id)
					<a class="btn btn-default btn-secondary" href="{{ route('site.users.account.request') }}">{{ trans('users::users.request access') }}</a>
				@endif
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				Profile
			</div>
			<div class="panel-body">
				<!-- <div class="row">
					<div class="col-md-2">
						<strong>Username:</strong>
					</div>
					<div class="col-md-4">
						<span class="text-muted">{{ $user->username }}</span>
					</div>
					<div class="col-md-1">
						<strong>Title:</strong>
					</div>
					<div class="col-md-5">
						<span class="text-muted">{!! $user->title ? e(ucwords($user->title)) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span>
					</div>
				</div>
				<div class="row">
					<div class="col-md-2">
						<strong>Department:</strong>
					</div>
					<div class="col-md-4">
						<span class="text-muted">{!! $user->department ? e(ucwords($user->department)) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span>
					</div>
					<div class="col-md-1">
						<strong>Campus:</strong>
					</div>
					<div class="col-md-5">
						<span class="text-muted">{!! $user->campus ? e(ucwords($user->campus)) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span>
					</div>
				</div> -->

			@if (auth()->user()->can('manage users'))
				@if ($user->isTrashed())
					<p class="alert alert-warning">This account was removed on {{ $user->deleted_at }}.</p>
				@endif

				<div class="row">
					<div class="col-md-6">
						<p>
							<span class="text-muted">Created</span><br />
							<strong>{{ $user->datecreated && $user->datecreated != '-0001-11-30 00:00:00' ? $user->datecreated : trans('global.unknown') }}</strong>
						</p>
					</div>
					<div class="col-md-6">
						<p>
							<span class="text-muted">Last Visit</span><br />
							<strong>{{ $user->datelastseen }}</strong>
						</p>
					</div>
				</div>
			@endif

				<div class="row">
					<div class="col-md-6">
						<p>
							<span class="text-muted">Username</span><br />
							<strong>{{ $user->username }}</strong>
						</p>
					</div>
					<div class="col-md-6">
						<p>
							<span class="text-muted">Department</span><br />
							<strong>{!! $user->department ? e(ucwords($user->department)) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</strong>
						</p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<p>
							<span class="text-muted">Title</span><br />
							<strong>{!! $user->title ? e(ucwords($user->title)) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</strong>
						</p>
					</div>
					<div class="col-md-6">
						<p>
							<span class="text-muted">Campus</span><br />
							<strong>{!! $user->campus ? e(ucwords($user->campus)) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</strong>
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
							<span class="text-muted">{{ $user->email }}
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
					@if ($user->loginshell === false)
						<span class="alert alert-error">Failed to retrieve shell information</span>
					@else
						<span class="text-muted">{!! $user->loginshell ? e($user->loginshell) : '<span class="none">' . trans('global.unknown') . '</span>' !!}</span>
					@endif
				</p>
			</div>
		</div>

		@if (auth()->user()->can('manage users'))
			<div class="card panel panel-default session">
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
											{{ $session->last_activity }}
										</div>
										<div class="col-md-4 text-right">
											@if ($session->id == session()->getId())
												<span class="badge badge-info float-right">Your current session</span>
											@endif
										</div>
									</div>
								</div>
								<div class="session-current card-text">
									{{ $session->user_agent }}
								</div>
							</div></div>
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

		<div class="card panel panel-default session">
			<div class="card-header panel-heading">
				Resources
			</div>
			<ul class="list-group list-group-flush">
				<?php
				// Owner groups
				$memberships = $user->groups()
					->where('groupid', '>', 0)
					->where('membertype', '=', 2)
					->get();

				$q = array();
				foreach ($memberships as $membership)
				{
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
							<?php
							foreach ($group->queues as $queue):
								?>
								<div class="row">
									<div class="col-md-6">
										<strong class="text-muted">Queue</strong>: {{ $queue->name }}
									</div>
									<div class="col-md-6">
										<strong class="text-muted">Resource</strong>: {{ $queue->subresource->name }}
									</div>
								</div>
								<?php
							endforeach;
							?>
							@if (!empty($unixgroups))
								<strong class="text-muted">Unix Groups</strong>: {{ implode(', ', $unixgroups) }}
							@endif
						</div>
					</li>
					<?php
				}

				$queues = $user->queues()
					//->where('groupid', '>', 0)
					->whereIn('membertype', [1, 4])
					->whereNotIn('id', $q)
					->withTrashed()
					->get();

				foreach ($queues as $qu)
				{
					// We only want trashed requests (4)
					if ($qu->membertype == 1
					 && $qu->datetimeremoved
					 && $qu->datetimeremoved != '0000-00-00 00:00:00'
					 && $qu->datetimeremoved != '-0001-11-30 00:00:00')//$qu->trashed())
					{
						continue;
					}

					$queue = $qu->queue;

					if (!$queue || ($queue->datetimeremoved
					 && $queue->datetimeremoved != '0000-00-00 00:00:00'
					 && $queue->datetimeremoved != '-0001-11-30 00:00:00'))
					{
						continue;
					}

					if (!$queue->scheduler
					 || ($queue->scheduler->datetimeremoved
					 && $queue->scheduler->datetimeremoved != '0000-00-00 00:00:00'
					 && $queue->scheduler->datetimeremoved != '-0001-11-30 00:00:00'))
					{
						continue;
					}

					$group = $queue->group;

					if (!$group->id)
					{
						continue;
					}

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
				}
				?>
			</ul>
		</div>

		<div class="card panel panel-default session">
			<div class="card-header panel-heading">
				Roles
			</div>
			<div class="card-body panel-body">
				<table>
					<caption class="sr-only">Roles</caption>
					<tbody>
					<?php
					// Gather roles
					$resources = App\Modules\Resources\Entities\Asset::query()
						->where('rolename', '!=', '')
						//->where('retired', '=', 0)
						->where('listname', '!=', '')
						->orderBy('display', 'desc')
						->get();
					foreach ($resources as $resource)
					{
						?>
						<tr>
							<th scope="row">{{ $resource->name }}</th>
							<td class="text-right"><span class="fa fa-exclamation-triangle"></span><span class="sr-only">Loading...</span></td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php
	}
	?>
</div>

@stop