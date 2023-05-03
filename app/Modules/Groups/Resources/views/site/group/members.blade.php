<?php
$m = (new \App\Modules\Groups\Models\Member)->getTable();
$ug = (new \App\Modules\Groups\Models\UnixGroupMember)->getTable();
$u = (new \App\Modules\Users\Models\UserUsername)->getTable();
$q = (new \App\Modules\Queues\Models\Queue)->getTable();
$s = (new \App\Modules\Resources\Models\Child)->getTable();
$r = (new \App\Modules\Resources\Models\Asset)->getTable();

$limit = 200;

$managers = $group->members()
	->whereIsManager()
	->select($m . '.*')
	->join($u, $u . '.userid', $m . '.userid')
	->whereNull($u . '.dateremoved')
	->orderBy($u . '.username', 'asc')
	->paginate($limit, ['*'], 'mgpage', request()->input('mgpage', 1));

// Grab all manager IDs as we want to filter potential
// duplicate member records from the members list
$managerids = $group->members()
	->select($m . '.userid')
	->whereIsManager()
	->join($u, $u . '.userid', $m . '.userid')
	->whereNull($u . '.dateremoved')
	->get()
	->pluck('userid')
	->toArray();

$members = $group->members()
	->whereIsMember()
	->select($m . '.*')
	->join($u, $u . '.userid', $m . '.userid')
	->whereNull($u . '.dateremoved')
	->whereNotIn($m . '.userid', $managerids)
	->orderBy($u . '.username', 'asc')
	->paginate($limit, ['*'], 'mbpage', request()->input('mbpage', 1));

$viewers = $group->members()
	->whereIsViewer()
	->select($m . '.*')
	->join($u, $u . '.userid', $m . '.userid')
	->whereNull($u . '.dateremoved')
	->orderBy($u . '.username', 'asc')
	->paginate($limit, ['*'], 'vwpage', request()->input('vwpage', 1));

$pending = $group->members()
	->whereIsPending()
	->select($m . '.*')
	->join($u, $u . '.userid', $m . '.userid')
	->whereNull($u . '.dateremoved')
	->orderBy($u . '.username', 'asc')
	->paginate($limit, ['*'], 'pdpage', request()->input('pdpage', 1));

/*$disabled = $group->members()
	->withTrashed()
	->join($u, $u . '.userid', $m . '.userid')
	->where(function($where) use ($m, $u)
	{
		$where->whereNotNull($m . '.dateremoved')
			->orWhereNotNull($u . '.dateremoved');
	})
	->orderBy($u . '.username', 'asc')
	->paginate($limit, ['*'], 'dspage', request()->input('dspage', 1));*/

$processed = array();
/*$managers = collect([]);
$members = collect([]);
$viewers = collect([]);
$pending = collect([]);
$user_requests = array();
$disabled = collect([]);
$processed = array();

$total = $group->members()->count();

if ($total > 500)
{
	$users = $group->members()
		->orderBy('datecreated', 'desc')
		->paginate(500, ['*'], 'page', request()->input('page', 1));
}
else
{
	$users = $group->members()
		->orderBy('datecreated', 'desc')
		->get();
}

foreach ($users as $me)
{
	if (in_array($me->userid, $processed))
	{
		continue;
	}

	$me->membershiptype = 'groupuser';

	if (!$me->user || $me->user->trashed())
	{
		if (!$disabled->contains('userid', $me->userid))
		{
			$disabled->push($me);
		}
	}
	else
	{
		$me->username = $me->user->username;
		if ($me->isManager())
		{
			if (!$managers->contains('userid', $me->userid))
			{
				$managers->push($me);
			}
		}
		elseif ($me->isMember())
		{
			if (!$managers->contains('userid', $me->userid)
			 && !$members->contains('userid', $me->userid))
			{
				$members->push($me);
			}
		}
		elseif ($me->isViewer())
		{
			if (!$managers->contains('userid', $me->userid)
			 && !$members->contains('userid', $me->userid)
			 && !$viewers->contains('userid', $me->userid))
			{
				$viewers->push($me);
			}
		}
		elseif ($me->isPending())
		{
			if (!$managers->contains('userid', $me->userid)
			 && !$members->contains('userid', $me->userid)
			 && !$viewers->contains('userid', $me->userid)
			 && !$pending->contains('userid', $me->userid))
			{
				$pending->push($me);
			}
		}
	}

	$processed[] = $me->userid;
}*/

$resources = array();

$queues = $group->queues()
	->withTrashed()
	->select($q . '.*')
	->join($s, $s . '.subresourceid', $q . '.subresourceid')
	->join($r, $r . '.id', $s . '.resourceid')
	->whereNull($q . '.datetimeremoved')
	->whereNull($r . '.datetimeremoved')
	->get();

foreach ($queues as $queue)
{
	if (!isset($resources[$queue->resource->name]))
	{
		$resources[$queue->resource->name] = array();
	}
	$resources[$queue->resource->name][] = $queue;

	$users = $queue->users()
		->orderBy('datetimecreated', 'desc')
		->get();
	$qu = array();

	foreach ($users as $me)
	{
		$qu[$me->userid] = ($me->isPending() ? 'p' : '') . $me->id;

		if (in_array($queue->id . '_' . $me->userid, $processed))
		{
			continue;
		}

		$me->membershiptype = 'queueuser';

		/*if (!$me->user || $me->user->trashed())
		{
			if (!($found = $disabled->firstWhere('userid', $me->userid)))
			{
				$disabled->push($me);
			}
		}
		else
		{
			$me->username = $me->user->username;*/

			if ($me->isPending())
			{
				if (!isset($user_requests[$me->userid]))
				{
					$user_requests[$me->userid] = array();
				}
				$user_requests[$me->userid][] = $me;//->userrequestid;

				if (!$pending->contains('userid', $me->userid))
				{
					$pending->push($me);
				}
			}
			/*elseif ($me->isManager())
			{
				if (!$managers->contains('userid', $me->userid))
				{
					$managers->push($me);
				}
			}
			elseif ($me->isMember())
			{
				if (!$managers->contains('userid', $me->userid)
				 && !$members->contains('userid', $me->userid))
				{
					$members->push($me);
				}
			}
			elseif ($me->isViewer())
			{
				if (!$managers->contains('userid', $me->userid)
				 && !$members->contains('userid', $me->userid)
				 && !$viewers->contains('userid', $me->userid))
				{
					$viewers->push($me);
				}
			}
		}*/

		$processed[] = $queue->id . '_' . $me->userid;
	}

	$queue->qu = $qu;
}

$unixgroups = $group->unixgroups()
	->orderBy('longname', 'asc')
	->get();

$base = null;
$group_boxes = 0;
foreach ($unixgroups as $unixgroup)
{
	// Shortname is only defined when queue is actually a unix group
	// And we only want to add boxes for non-base groups (ones not ending in 0).
	if (!preg_match("/rcs[0-9]{4}0/", $unixgroup->shortname))
	{
		$group_boxes++;
	}

	if ($unixgroup->longname == $group->unixgroup)
	{
		$base = $unixgroup->id;
	}

	$uu = array();

	foreach ($unixgroup->members as $me)
	{
		$uu[$me->userid] = $me->id;

		/*if (in_array($me->userid, $processed))
		{
			continue;
		}

		$me->membershiptype = 'unixgroupuser';

		if (!$me->user || $me->user->trashed())
		{
			if (!$disabled->contains('userid', $me->userid))
			{
				$disabled->push($me);
			}
		}
		else
		{
			$uu[$me->userid] = $me->id;

			$me->username = $me->user->username;

			if (!$members->contains('userid', $me->userid))
			{
				$members->push($me);
			}
		}

		$processed[] = $me->userid;*/
	}

	$unixgroup->uu = $uu;
}

$i = 0;
?>
<div class="row mb-3">
	<div class="col-md-6">
		<button id="export_to_csv" data-id="{{ $group->id }}" class="btn btn-info btn-sm">
			<span class="fa fa-table" ara-hidden="true"></span> Export
		</button>
	</div>
	<div class="col-md-6 text-right">
		<a href="#add_member_dialog" data-toggle="modal" class="add_member btn btn-secondary btn-sm" data-membertype="1">
			<span class="fa fa-plus-circle" aria-hidden="true"></span> Add Member
		</a>
		<a href="#import_member_dialog" data-toggle="modal" class="import_member btn btn-secondary btn-sm" data-membertype="1">
			<span class="fa fa-upload" aria-hidden="true"></span> Import
		</a>
	</div>
</div>

@if (count($pending))
<div class="card">
	<div class="card-header bg-warning">
		New membership requests
	</div>
	<div class="card-body">
		<form id="FORM_{{ $group->id }}" method="post">
			<table class="table table-hover fitToPanel">
				<caption class="sr-only">Membership Requests</caption>
				<thead>
					<tr>
						<th scope="col">Name(s)</th>
						<th scope="col">Queue(s)</th>
						<th scope="col" class="text-center">Accept</th>
						<th scope="col" class="text-center">Deny</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($pending as $j => $req)
						<tr id="entry{{ $j }}" data-id="{{ $req->id }}">
							<td>
								{{ $req->user->name }}
								@if ($req->request && $req->request->comment)
									<div class="text-muted">{{ $req->request->comment }}</div>
								@endif
							</td>
							<td>
								<?php
								$approves = array();
								$denies = array();
								$reqqueues = array();

								if (isset($user_requests[$req->userid])):
									foreach ($user_requests[$req->userid] as $rq):
										$approves[] = route('api.queues.requests.update', ['id' => $rq->userrequestid]);
										$denies[]   = route('api.queues.requests.delete', ['id' => $rq->userrequestid]);

										$reqqueues[] = '<span class="text-nowrap">' . $rq->queue->name . ' (' . $rq->queue->resource->name . ')</span>';
									endforeach;
								endif;

								echo (!empty($reqqueues) ? implode('<br />', $reqqueues) : '<span class="text-muted">' . trans('global.none') . '</span>');
								?>
							</td>
							<td class="text-center">
								<input type="radio" name="approve{{ $j }}" class="approve-request approve-value0" data-groupid="{{ $group->id }}" data-api="{{ implode(',', $approves) }}" data-membership="{{ route('api.groups.members.update', ['id' => $req->id]) }}" value="{{ $req->userid }},0" />
							</td>
							<td class="text-center">
								<input type="radio" name="approve{{ $j }}" class="approve-request approve-value1" data-groupid="{{ $group->id }}" data-api="{{ implode(',', $denies) }}" data-membership="{{ route('api.groups.members.delete', ['id' => $req->id]) }}" value="{{ $req->userid }},1" />
							</td>
						</tr>
					@endforeach
					<tr id="selectAll">
						<td><strong>Select All</strong></td>
						<td></td>
						<td class="text-center"><input type="radio" id="acceptAll" class="toggle-requests" value="0" /></td>
						<td class="text-center"><input type="radio" id="denyAll" class="toggle-requests" value="1" /></td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td></td>
						<td></td>
						<td colspan="2" class="text-center">
							<button id="submit-requests" data-groupid="{{ $group->id }}" class="btn btn-success" disabled>
								{{ trans('global.button.save') }}
								<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('global.saving') }}</span></span>
							</button>
						</td>
					</tr>
				</tfoot>
			</table>

			@csrf
		</form>
	</div>
</div>
@endif

<div class="card">
	<div class="card-header">
		Managers
		<a href="#help_managers_span_{{ $group->id }}" data-toggle="modal" class="text-info tip" title="Help">
			<span class="fa fa-question-circle" aria-hidden="true"></span>
			<span class="sr-only">Help</span>
		</a>
		<div class="modal dialog" id="help_managers_span_{{ $group->id }}" tabindex="-1" aria-labelledby="help_managers_span_{{ $group->id }}-title" aria-hidden="true" title="Managers">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content dialog-content shadow-sm">
					<div class="modal-header">
						<div class="modal-title" id="help_managers_span_{{ $group->id }}-title">Managers</div>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body dialog-body">
						<p>Managers are the owners or <abbr title="Principle Investigators">PIs</abbr> of this group and any others they may choose to delegate to manage access to this group. Only Managers can access this interface and are able to grant queue access for other people in the group. Managers can also grant and remove Group Management privileges to and from others, although you cannot remove your own Group Management privileges.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="card-body">
		@if (!$group->cascademanagers && auth()->user()->can('manage groups'))
			<p class="alert alert-info">Managers are <strong>not</strong> automatically added to queues and unix groups.</p>
		@endif

		<table class="table datatable" data-length="{{ $managers->total() }}">
			<caption class="sr-only">Managers</caption>
			<thead>
				<tr>
					<th scope="col" colspan="3">User Info</th>
					@if (count($queues))
					<th scope="col" class="col-queue" colspan="{{ count($queues) }}">Queues</th>
					@endif
					@if (count($unixgroups))
					<th scope="col" class="col-unixgroup text-nowrap" colspan="{{ count($unixgroups) }}">Unix Groups</th>
					@endif
				</tr>
				<tr>
					<th scope="col" class="text-nowrap">Name</th>
					<th scope="col">Username</th>
					<th scope="col" class="text-center">Options</th>
					<?php
					$csv_headers = array(
						'Name',
						'Username',
						'Membership'
					);
					foreach ($queues as $queue):
						$csv_headers[] = $queue->name . ' (' . $queue->resource->name . ')';
						?>
						<th scope="col" class="col-queue text-nowrap text-center">
							{{ $queue->name }} ({{ $queue->resource->name }})
							@if (!$queue->active)
								<!-- <span class="fa fa-exclamation-triangle text-warning tip" title="{{ trans('queues::queues.inactive queue and membership is frozen') }}">
									<span class="sr-only">{{ trans('queues::queues.inactive queue and membership is frozen') }}</span>
								</span> -->
							@endif
						</th>
						<?php
					endforeach;

					foreach ($unixgroups as $unix):
						$csv_headers[] = $unix->longname;
						?>
						<th scope="col" class="col-unixgroup text-nowrap text-center">{{ $unix->longname }}</th>
						<?php
					endforeach;

					$csv_data = array();
					$csv_data[] = $csv_headers;
					?>
				</tr>
			</thead>
			<tbody>
				@foreach ($managers as $member)
					<tr id="manager-{{ $member->userid }}">
						<td class="text-nowrap">
							@if (auth()->user()->can('manage users'))
								<a href="{{ route('site.users.account', ['u' => $member->userid]) }}">
									{{ $member->user ? $member->user->name : trans('global.unknown') }}
								</a>
							@else
								{{ $member->user ? $member->user->name : trans('global.unknown') }}
							@endif
						</td>
						<td>
							{{ $member->user ? $member->user->username : trans('global.unknown') }}
						</td>
						<td class="text-center">
							@if ($member->user)
								@if (!$member->user->enabled)
									<span class="fa fa-exclamation-triangle text-warning" aria-hidden="true"></span>
								@elseif ($member->userid != $user->id || auth()->user()->can('manage groups'))
									<div class="dropdown dropright">
										<button class="btn btn-options fa fa-ellipsis-h" type="button" id="dropdownMenuButton{{ $member->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<span class="sr-only">Options</span>
										</button>
										<div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $member->id }}">
											<a href="#manager-{{ $member->id }}" class="dropdown-item btn membership-move demote" data-api="{{ route('api.groups.members.update', ['id' => $member->id]) }}" data-userid="{{ $member->userid }}" data-target="1" data-method="{{ $member->groupid ? 'put' : 'post' }}" title="Remove manager privileges">
												<span class="fa fa-arrow-down" aria-hidden="true"></span> Remove manager privileges
											</a>
											<a href="#manager-{{ $member->id }}" class="dropdown-item btn membership-remove delete" data-api="{{ $member->groupid ? route('api.groups.members.delete', ['id' => $member->id]) : '' }}" title="Remove from group">
												<span class="fa fa-trash" aria-hidden="true"></span> Remove from group
											</a>
										</div>
									</div>
								@endif
							@endif
						</td>
						<?php
						$in = array();
						$qu = array();
						$csv = array(
							($member->user ? $member->user->name : trans('global.unknown')),
							($member->user ? $member->user->username : trans('global.unknown')),
							'Manager'
						);
						foreach ($queues as $queue):
							$checked = '';
							$disable = '';
							$m = null;

							// Managers get explicit access to owned queues, but not for free queues.
							if (!$queue->free && $group->cascademanagers && !auth()->user()->can('manage groups')):
								$disable = true;
								$checked = ' checked="checked"';
								$disable = ' disabled="disabled"';
							else:
								if (isset($queue->qu[$member->userid])):
									$m = $queue->qu[$member->userid];

									$checked = ' checked="checked"';
								endif;
							endif;
							$csv[] = $checked ? 'yes' : 'no';

							if (!$member->user->enabled): // || (!$checked && !$queue->active)):
								$disable = ' disabled="disabled"';
							endif;
							?>
							<td class="col-queue text-nowrap text-center">
								<input type="checkbox"
									class="membership-toggle queue-toggle"
									name="queue[{{ $i }}][{{ $queue->id }}]"{!! $checked !!}{!! $disable !!}
									data-base="unix-{{ $i }}-{{ $base }}"
									data-userid="{{ $member->userid }}"
									data-objectid="{{ $queue->id }}"
									data-api-create="{{ route('api.queues.users.create') }}"
									data-api="{{ $checked && !$disable ? route('api.queues.users.delete', ['id' => $m]) : route('api.queues.users.create') }}"
									value="1" />
							</td>
							<?php
						endforeach;

						$uu = array();

						foreach ($unixgroups as $unix):
							$checked = '';
							$disable = '';
							$m = null;

							if (isset($unix->uu[$member->userid])):
								$m = $unix->uu[$member->userid];

								$checked = ' checked="checked"';
							endif;

							$csv[] = $checked ? 'yes' : 'no';

							// Disable unchecking the base unix group
							if (preg_match("/rcs[0-9]{4}0/", $unix->shortname) && $group->cascademanagers && !auth()->user()->can('manage groups')):
								if ($group_boxes > 0 && $checked):
									$disable = ' disabled="disabled"';
								endif;
							endif;

							if (!$member->user->enabled):
								$disable = ' disabled="disabled"';
							endif;
							?>
							<td class="col-unixgroup text-nowrap text-center">
								<input type="checkbox"
									class="membership-toggle unixgroup-toggle"
									name="unix[{{ $unix->id }}]"{!! $checked !!}{!! $disable !!}
									id="unix-{{ $i }}-{{ $unix->id }}"
									data-base="unix-{{ $i }}-{{ $base }}"
									data-userid="{{ $member->userid }}"
									data-objectid="{{ $unix->id }}"
									data-api-create="{{ route('api.unixgroups.members.create') }}"
									data-api="{{ $checked ? route('api.unixgroups.members.delete', ['id' => $m]) : route('api.unixgroups.members.create') }}"
									value="1" />
							</td>
							<?php
						endforeach;
						$csv_data[] = $csv;
						$i++;
						?>
					</tr>
				@endforeach
			</tbody>
		</table>

		{{ $managers->render() }}

		<div class="alert alert-danger hide" id="managers_error"></div>
	</div>
</div>

<div class="card">
	<div class="card-header">
		Members
		<a href="#help_members_span_{{ $group->id }}" data-toggle="modal" class="text-info tip" title="Help">
			<span class="fa fa-question-circle" aria-hidden="true"></span>
			<span class="sr-only">Help</span>
		</a>
		<div class="modal dialog" id="help_members_span_{{ $group->id }}" tabindex="-1" aria-labelledby="help_members_span_{{ $group->id }}-title" aria-hidden="true" title="Members">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content dialog-content shadow-sm">
					<div class="modal-header">
						<div class="modal-title" id="help_members_span_{{ $group->id }}-title">Members</div>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body dialog-body">
						<p>Members are people that have access to some or all of this group's queues but have no other special privileges such as Group Usage Reporting privileges or Group Managment privileges. Enabling a queue for someone will also create an account for them on the appropriate resource if they do not already have one. New accounts on a cluster will be processed overnight and be ready use the next morning. The person will receive an email notification once their account is ready.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="card-body">
		@if (count($members) > 0)
			<table class="table datatable" data-length="{{ $members->total() }}">
				<caption class="sr-only">Members</caption>
				<thead>
					<tr>
						<th scope="col" colspan="3">User Info</th>
						@if (count($queues))
						<th scope="col" class="col-queue" colspan="{{ count($queues) }}">Queues</th>
						@endif
						@if (count($unixgroups))
						<th scope="col" class="col-unixgroup text-nowrap" colspan="{{ count($unixgroups) }}">Unix Groups</th>
						@endif
					</tr>
					<tr>
						<th scope="col" class="text-nowrap">Name</th>
						<th scope="col">Username</th>
						<th scope="col" class="text-center">Options</th>
						@foreach ($queues as $queue)
							<th scope="col" class="col-queue text-nowrap text-center">
								{{ $queue->name }} ({{ $queue->resource->name }})
								@if (!$queue->active)
									<!-- <span class="fa fa-exclamation-triangle text-warning tip" title="{{ trans('queues::queues.inactive queue and membership is frozen') }}">
										<span class="sr-only">{{ trans('queues::queues.inactive queue and membership is frozen') }}</span>
									</span> -->
								@endif
							</th>
						@endforeach
						@foreach ($unixgroups as $unix)
							<th scope="col" class="col-unixgroup text-nowrap text-center">{{ $unix->longname }}</th>
						@endforeach
					</tr>
				</thead>
				<tbody>
					@foreach ($members as $member)
						<tr id="member{{ $member->id }}">
							<td class="text-nowrap">
								@if (auth()->user()->can('manage users'))
									<a href="{{ route('site.users.account', ['u' => $member->userid]) }}">
										{{ $member->user ? $member->user->name : trans('global.unknown') }}
									</a>
								@else
									{{ $member->user ? $member->user->name : trans('global.unknown') }}
								@endif
								<!-- <br />
								<span class="text-muted">{{ $member->user ? $member->user->username : trans('global.unknown') }}</span> -->
							</td>
							<td class="text-nowrap">
								{{ $member->user ? $member->user->username : trans('global.unknown') }}
							</td>
							<td class="text-center">
								@if ($member->user)
									@if (!$member->user->enabled)
										<span class="fa fa-exclamation-triangle text-warning" aria-hidden="true"></span>
									@elseif ($member->userid != $user->id || auth()->user()->can('manage groups'))
										<div class="dropdown dropleft">
											<button class="btn btn-options fa fa-ellipsis-h" type="button" id="dropdownMenuButton{{ $member->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
												<span class="sr-only">Options</span>
											</button>
											<div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $member->id }}">
												@if (count($queues))
												<a href="#member{{ $member->id }}" class="dropdown-item btn membership-allqueues allqueues" title="Enable all queues for this user">
													<span class="fa fa-fw fa-check-square" aria-hidden="true"></span> Enable all queues
												</a>
												@endif
												<a href="#member{{ $member->id }}" class="dropdown-item btn membership-move change" data-api="{{ $member->groupid ? route('api.groups.members.update', ['id' => $member->id]) : route('api.groups.members.create') }}" data-target="3" data-method="{{ $member->groupid ? 'put' : 'post' }}" data-userid="{{ $member->userid }}" title="Grant usage viewer privileges">
													<span class="fa fa-fw fa-bar-chart" aria-hidden="true"></span> Grant usage viewer privileges
												</a>
												<a href="#member{{ $member->id }}" class="dropdown-item btn membership-move promote" data-api="{{ $member->groupid ? route('api.groups.members.update', ['id' => $member->id]) : route('api.groups.members.create') }}" data-target="2" data-method="{{ $member->groupid ? 'put' : 'post' }}" data-userid="{{ $member->userid }}" title="Grant manager privileges">
													<span class="fa fa-fw fa-arrow-up" aria-hidden="true"></span> Grant manager privileges
												</a>
												<a href="#member{{ $member->id }}" class="dropdown-item btn membership-remove delete" data-api="{{ $member->groupid ? route('api.groups.members.delete', ['id' => $member->id]) : '' }}" title="Remove from group">
													<span class="fa fa-fw fa-trash" aria-hidden="true"></span> Remove from group
												</a>
											</div>
										</div>
									@endif
								@endif
							</td>
							<?php
							$csv = array(
								($member->user ? $member->user->name : trans('global.unknown')),
								($member->user ? $member->user->username : trans('global.unknown')),
								'Member'
							);
							foreach ($queues as $queue):
								$checked = '';
								$disable = '';
								$m = null;

								// We check queues a little differently because it's possible
								// to be a pending member of one queue but full member of another
								if (isset($queue->qu[$member->userid])):
									$m = $queue->qu[$member->userid];

									if (substr($m, 0, 1) == 'p'):
										$m = substr($m, 1);
										$checked = ' checked="checked"';
										$disable = ' disabled';
									else:
										$checked = ' checked="checked"';
									endif;
								endif;

								$csv[] = $checked ? 'yes' : 'no';

								if (!$member->user || !$member->user->enabled): // || (!$checked && !$queue->active)):
									$disable = ' disabled';
								endif;
								?>
								<td class="text-center col-queue">
									<input type="checkbox"
										class="membership-toggle queue-toggle"
										name="queue[{{ $i }}][{{ $queue->id }}]"{!! $checked !!}{!! $disable !!}
										data-base="unix-{{ $i }}-{{ $base }}"
										data-userid="{{ $member->userid }}"
										data-objectid="{{ $queue->id }}"
										data-api-create="{{ route('api.queues.users.create') }}"
										data-api="{{ $checked ? route('api.queues.users.delete', ['id' => $m]) : route('api.queues.users.create') }}"
										value="1" />
								</td>
								<?php
							endforeach;

							foreach ($unixgroups as $unix):
								$checked = '';
								$disable = '';
								$m = null;

								if (isset($unix->uu[$member->userid])):
									$m = $unix->uu[$member->userid];

									$checked = ' checked="checked"';
								endif;

								$csv[] = $checked ? 'yes' : 'no';

								if (preg_match("/rcs[0-9]{4}0/", $unix->shortname) && !auth()->user()->can('manage groups')):
									if ($group_boxes > 0 && $checked):
										$checked .= ' disabled="disabled"';
									endif;
								endif;

								if (!$member->user->enabled):
									$disable = ' disabled';
								endif;
								?>
								<td class="text-center col-unixgroup">
									<input type="checkbox"
										class="membership-toggle unixgroup-toggle"
										name="unix[{{ $i }}][{{ $unix->id }}]"{!! $checked !!}{!! $disable !!}
										id="unix-{{ $i }}-{{ $unix->id }}"
										data-base="unix-{{ $i }}-{{ $base }}"
										data-userid="{{ $member->userid }}"
										data-objectid="{{ $unix->id }}"
										data-api-create="{{ route('api.unixgroups.members.create') }}"
										data-api="{{ $checked ? route('api.unixgroups.members.delete', ['id' => $m]) : route('api.unixgroups.members.create') }}"
										value="1" />
								</td>
								<?php
							endforeach;
							$csv_data[] = $csv;
							$i++;
							?>
						</tr>
					@endforeach
				</tbody>
			</table>

			{{ $members->render() }}
		@else
			<p class="alert alert-info">No members found.</p>
		@endif

		<div class="alert alert-danger hide" id="members_error"></div>
	</div>
</div>

@if (count($viewers))
<div class="card">
	<div class="card-header">
		Usage Reporting Viewers
		<a href="#help_viewers_span_{{ $group->id }}" data-toggle="modal" class="text-help tip" title="Help">
			<span class="fa fa-question-circle" aria-hidden="true"></span>
			<span class="sr-only">Help</span>
		</a>
		<div class="modal dialog" id="help_viewers_span_{{ $group->id }}" tabindex="-1" aria-labelledby="help_viewers_span_{{ $group->id }}-title" aria-hidden="true" title="Usage Reporting Viewers">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content dialog-content shadow-sm">
					<div class="modal-header">
						<div class="modal-title" id="help_viewers_span_{{ $group->id }}-title">Usage Reporting Viewers</div>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body dialog-body">
						<p>Group Usage Reporting Viewers are people who have been given permission to view all usage data for the entire group. You may also grant queue submission privileges individually for these people if desired. Group Usage Reporting Viewers may not access this interface or grant or remove privileges to or from others.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="card-body">
		<table class="table datatable" data-length="{{ $viewers->total() }}">
			<caption class="sr-only">Viewers</caption>
			<thead>
				<tr>
					<th scope="col" colspan="3">User Info</th>
					@if (count($queues))
					<th scope="col" class="text-left col-queue" colspan="{{ count($queues) }}">Queues</th>
					@endif
					@if (count($unixgroups))
					<th scope="col" class="text-left col-unixgroup" colspan="{{ count($unixgroups) }}">Unix Groups</th>
					@endif
				</tr>
				<tr>
					<th scope="col" class="text-nowrap">Name</th>
					<th scope="col">Username</th>
					<th scope="col" class="text-center">Options</th>
					@foreach ($queues as $queue)
						<th scope="col" class="col-queue text-nowrap text-center">
							{{ $queue->name }} ({{ $queue->resource->name }})
							@if (!$queue->active)
								<!-- <span class="fa fa-exclamation-triangle text-warning tip" title="{{ trans('queues::queues.inactive queue and membership is frozen') }}">
									<span class="sr-only">{{ trans('queues::queues.inactive queue and membership is frozen') }}</span>
								</span> -->
							@endif
						</th>
					@endforeach
					@foreach ($unixgroups as $unix)
						<th scope="col" class="col-unixgroup text-nowrap text-center">{{ $unix->longname }}</th>
					@endforeach
				</tr>
			</thead>
			<tbody>
				@foreach ($viewers as $member)
					<tr id="member{{ $member->id }}">
						<td class="text-nowrap">
							@if (auth()->user()->can('manage users'))
								<a href="{{ route('site.users.account', ['u' => $member->userid]) }}">
									{{ $member->user ? $member->user->name : trans('global.unknown') }}
								</a>
							@else
								{{ $member->user ? $member->user->name : trans('global.unknown') }}
							@endif
						</td>
						<td class="text-nowrap">
							{{ $member->user ? $member->user->username : trans('global.unknown') }}
						</td>
						<td class="text-right text-nowrap">
							@if ($member->user)
								@if (!$member->user->enabled)
									<span class="fa fa-exclamation-triangle text-warning" aria-hidden="true"></span>
								@elseif ($member->userid != $user->id || auth()->user()->can('manage groups'))
									<div class="dropdown dropright">
										<button class="btn btn-options fa fa-ellipsis-h" type="button" id="dropdownMenuButton{{ $member->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<span class="sr-only">Options</span>
										</button>
										<div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $member->id }}">
											<a href="#member{{ $member->id }}" class="dropdown-item btn membership-allqueues allqueues" title="Enable all queues for this user">
												<span class="fa fa-fw fa-check-square" aria-hidden="true"></span> Enable all queues
											</a>
											<a href="#member{{ $member->id }}" class="dropdown-item btn membership-move change" data-api="{{ route('api.groups.members.update', ['id' => $member->id]) }}" data-target="1" data-userid="{{ $member->userid }}" data-method="{{ $member->groupid ? 'put' : 'post' }}" title="Remove usage viewer privileges">
												<span class="fa fa-fw fa-user" aria-hidden="true"></span> Remove usage viewer privileges
											</a>
											<a href="#member{{ $member->id }}" class="dropdown-item btn membership-remove delete" data-api="{{ $member->groupid ? route('api.groups.members.delete', ['id' => $member->id]) : '' }}" title="Remove from group">
												<span class="fa fa-fw fa-trash" aria-hidden="true"></span> Remove from group
											</a>
										</div>
									</div>
								@endif
							@endif
						</td>
						<?php
						$csv = array(
							($member->user ? $member->user->name : trans('global.unknown')),
							($member->user ? $member->user->username : trans('global.unknown')),
							'Viewer'
						);
						foreach ($queues as $queue):
							$checked = '';
							$disable = '';
							$m = null;

							if (isset($queue->qu[$member->userid])):
								$m = $queue->qu[$member->userid];
								$checked = ' checked="checked"';
							endif;

							$csv[] = $checked ? 'yes' : 'no';

							if (!$member->user->enabled): // || (!$checked && !$queue->active)):
								$disable = ' disabled';
							endif;
							?>
							<td class="text-center col-queue">
								<input type="checkbox"
									class="membership-toggle queue-toggle"
									name="queue[{{ $i }}][{{ $queue->id }}]"{!! $checked !!}{!! $disable !!}
									data-base="unix-{{ $i }}-{{ $base }}"
									data-userid="{{ $member->userid }}"
									data-objectid="{{ $queue->id }}"
									data-api-create="{{ route('api.queues.users.create') }}"
									data-api="{{ $checked ? route('api.queues.users.delete', ['id' => $m]) : route('api.queues.users.create') }}"
									value="1" />
							</td>
							<?php
						endforeach;

						foreach ($unixgroups as $unix):
							$checked = '';
							$disable = '';
							$m = null;

							if (isset($unix->uu[$member->userid])):
								$m = $unix->uu[$member->userid];
								$checked = ' checked="checked"';
							endif;

							$csv[] = $checked ? 'yes' : 'no';

							if (preg_match("/rcs[0-9]{4}0/", $unix->shortname) && !auth()->user()->can('manage groups')):
								if ($group_boxes > 0 && $checked):
									$checked .= ' disabled="disabled"';
								endif;
							endif;

							if (!$member->user->enabled):
								$disable = ' disabled';
							endif;
							?>
							<td class="text-center col-unixgroup">
								<input type="checkbox"
									class="membership-toggle unixgroup-toggle"
									name="unix[{{ $i }}][{{ $unix->id }}]"{!! $checked !!}{!! $disable !!}
									id="unix-{{ $i }}-{{ $unix->id }}"
									data-base="unix-{{ $i }}-{{ $base }}"
									data-userid="{{ $member->userid }}"
									data-objectid="{{ $unix->id }}"
									data-api-create="{{ route('api.unixgroups.members.create') }}"
									data-api="{{ $checked ? route('api.unixgroups.members.delete', ['id' => $m]) : route('api.unixgroups.members.create') }}"
									value="1" />
							</td>
							<?php
						endforeach;
						$csv_data[] = $csv;
						$i++;
						?>
					</tr>
				@endforeach
			</tbody>
		</table>

		{{ $viewers->render() }}

		<div class="alert alert-danger hide" id="viewers_error"></div>
	</div>
</div>
@endif

<?php /*
@if (count($disabled))
<div class="card">
	<div class="card-header">
		Disabled Members
		<a href="#help_disabledmembers_span_{{ $group->id }}" data-toggle="modal" class="text-help tip" title="Help">
			<span class="fa fa-question-circle" aria-hidden="true"></span>
			<span class="sr-only">Help</span>
		</a>
		<div class="modal dialog" id="help_disabledmembers_span_{{ $group->id }}" tabindex="-1" aria-labelledby="help_disabledmembers_span_{{ $group->id }}-title" aria-hidden="true" title="Disabled Members">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content dialog-content shadow-sm">
					<div class="modal-header">
						<div class="modal-title" id="help_disabledmembers_span_{{ $group->id }}-title">Disabled Members</div>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body dialog-body">
						<p>Disabled Members are people that you have granted access to your queues but who no longer have an active account with {{ config('app.name') }}. Although queues may be enabled for them, they cannot log into {{ config('app.name') }} resources and use your queues without an active account. If the people listed here have left the institution and are no longer participating in research, please remove them from your queues. If people listed here have left but still require access to your queues then you will need to file a Request for Privileges (R4P). If you believe people are listed here in error, please contact support.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="card-body">
		@if (count($disabled) > 0)
			<table class="table table-hover hover datatable" data-length="{{ $disabled->total() }}">
				<caption class="sr-only">Disabled Members</caption>
				<thead>
					<tr>
						<th scope="col" colspan="3">User Info</th>
						@if (count($queues))
						<th scope="col" class="col-queue" colspan="{{ count($queues) }}">Queues</th>
						@endif
						@if (count($unixgroups))
						<th scope="col" class="col-unixgroup" colspan="{{ count($unixgroups) }}">Unix Groups</th>
						@endif
					</tr>
					<tr>
						<th scope="col" class="text-nowrap">Name</th>
						<th scope="col">Username</th>
						<th scope="col" class="text-center">Options</th>
						@foreach ($queues as $queue)
							<th scope="col" class="col-queue text-nowrap text-center">{{ $queue->name }} ({{ $queue->resource->name }})</th>
						@endforeach
						@foreach ($unixgroups as $unix)
							<th scope="col" class="col-unixgroup text-nowrap text-center">{{ $unix->longname }}</th>
						@endforeach
					</tr>
				</thead>
				<tbody>
					@foreach ($disabled as $member)
						<tr id="member{{ $member->id }}">
							<td class="text-nowrap">
								@if (auth()->user()->can('manage users'))
									<a href="{{ route('site.users.account', ['u' => $member->userid]) }}">
										{{ $member->user ? $member->user->name : trans('global.unknown') }}
									</a>
								@else
									{{ $member->user ? $member->user->name : trans('global.unknown') }}
								@endif
							</td>
							<td class="text-nowrap">
								{{ $member->user ? $member->user->username : trans('global.unknown') }}
							</td>
							<td class="text-center">
								<a href="#member{{ $member->id }}" class="membership-remove delete tip" data-api="{{ $member->groupid ? route('api.groups.members.delete', ['id' => $member->id]) : '' }}" title="Remove from group">
									<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">Remove from group</span>
								</a>
							</td>
							<?php
							$csv = array(
								($member->user ? $member->user->name : trans('global.unknown')),
								($member->user ? $member->user->username : trans('global.unknown')),
								'Disabled'
							);
							foreach ($queues as $queue):
								$checked = '';
								$m = null;

								foreach ($queue->users()->withTrashed()->get() as $m):
									if ($member->userid == $m->userid):
										$checked = ' checked="checked"';
										break;
									endif;
								endforeach;
								$csv[] = $checked ? 'yes' : 'no';
								?>
								<td class="text-center col-queue">
									<input type="checkbox"
										class="membership-toggle queue-toggle"
										name="queue[{{ $i }}][{{ $queue->id }}]"{!! $checked !!}
										data-base="unix-{{ $i }}-{{ $base }}"
										data-userid="{{ $member->userid }}"
										data-objectid="{{ $queue->id }}"
										data-api-create="{{ route('api.queues.users.create') }}"
										data-api="{{ $checked ? route('api.queues.users.delete', ['id' => $m->id]) : route('api.queues.users.create') }}"
										disabled="disabled"
										value="1" />
								</td>
								<?php
							endforeach;

							foreach ($unixgroups as $unix):
								$checked = '';
								$m = null;

								if (isset($unix->uu[$member->userid])):
									$m = $unix->uu[$member->userid];
									$checked = ' checked="checked"';
								endif;

								$csv[] = $checked ? 'yes' : 'no';
								?>
								<td class="text-center col-unixgroup">
									<input type="checkbox"
										class="membership-toggle unixgroup-toggle"
										name="unix[{{ $i }}][{{ $unix->id }}]"{!! $checked !!}
										id="unix-{{ $i }}-{{ $unix->id }}"
										data-base="unix-{{ $i }}-{{ $base }}"
										data-userid="{{ $member->userid }}"
										data-objectid="{{ $unix->groupid }}"
										data-api-create="{{ route('api.unixgroups.members.create') }}"
										data-api="{{ $checked ? route('api.unixgroups.members.delete', ['id' => $m]) : route('api.unixgroups.members.create') }}"
										disabled="disabled"
										value="1" />
								</td>
								<?php
							endforeach;
							$csv_data[] = $csv;
							$i++;
							?>
						</tr>
					@endforeach
				</tbody>
			</table>

			{{ $disabled->render() }}
		@else
			<p class="alert alert-info">No members found.</p>
		@endif

		<div class="alert alert-danger hide" id="disabledmembers_error"></div>
	</div>
</div>
@endif
*/
?>
<div class="modal dialog" id="add_member_dialog" tabindex="-1" aria-labelledby="add_member_dialog-title" aria-hidden="true" title="Add users to {{ $group->name }}">
	<div class="modal-dialog modal-dialog-centered">
		<form id="form_{{ $group->id }}" method="post" class="modal-content dialog-content shadow-sm">
			<div class="modal-header">
				<div class="modal-title" id="add_member_dialog-title">Add users to {{ $group->name }}</div>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body dialog-body">

				<div class="form-group">
					<label for="addmembers">Enter names, usernames, or email addresses</label>
					<input type="text" class="form-control" name="members" id="addmembers" data-api="{{ route('api.users.index') }}" data-group="{{ $group->id }}" placeholder="Username, email address, etc." />
				</div>

				<div class="form-group">
					<label for="new_membertype">Membership type</label>
					<select class="form-control" id="new_membertype"{!! $group->cascademanagers ? ' data-cascade="1"' : '' !!}{!! auth()->user()->can('manage groups') ? '0' : ' data-disable="1"' !!}>
						<option value="1">Member</option>
						<option value="2">Manager</option>
						<option value="3">Usage Viewer</option>
					</select>
				</div>

				@if (count($resources))
					<fieldset>
						<legend>Queue Selection</legend>

						<table id="queue-selection" class="table table-hover mb-0 groupSelect">
							<caption class="sr-only">Queues by Resource</caption>
							<tbody>
								@foreach ($resources as $name => $queues)
									<tr>
										<th scope="row" class="rowHead">{{ $name }}</th>
										<td class="rowData">
										@foreach ($queues as $queue)
											<div class="form-check">
												<input type="checkbox" class="form-check-input add-queue-member" name="queue[]" data-base="unixgroup-{{ $base }}" id="queue{{ $queue->id }}" value="{{ $queue->id }}" />
												<label class="form-check-label" for="queue{{ $queue->id }}">{{ $queue->name }}</label>
											</div>
										@endforeach
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</fieldset>
				@endif

				@if (count($unixgroups))
					<fieldset>
						<legend>Unix Group Selection</legend>

						<div id="unix-group-selection" class="row groupSelect">
							@foreach ($unixgroups as $name)
								<div class="col-sm-4 unixData">
									<div class="form-check">
										<input type="checkbox" data-base="unixgroup-{{ $base }}" <?php if ($group->cascademanagers && $name->longname == $group->unixgroup) { echo (!auth()->user()->can('manage groups') ? 'checked disabled' : 'checked'); } ?> class="form-check-input add-unixgroup-member" name="unixgroup[]" id="unixgroup-{{ $name->id }}" value="{{ $name->id }}" />
										<label class="form-check-label" for="unixgroup-{{ $name->id }}">{{ $name->longname }}</label>
									</div>
								</div>
							@endforeach
						</div>
					</fieldset>
				@endif

				<div class="alert alert-danger hide" id="add_member_error"></div>
			</div>
			<div class="modal-footer dialog-footer text-right">
				<button disabled="disabled" id="add_member_save" class="btn btn-success"
					data-group="{{ $group->id }}"
					data-api="{{ route('api.groups.members.create') }}"
					data-api-unixgroupusers="{{ route('api.unixgroups.members.create') }}"
					data-api-queueusers="{{ route('api.queues.users.create') }}"
					>
					<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('global.saving') }}</span></span>
					{{ trans('global.button.save') }}
				</button>
			</div>

			@csrf
		</form>
	</div>
</div>

<div class="modal dialog" id="import_member_dialog" tabindex="-1" aria-labelledby="import_member_dialog-title" aria-hidden="true" title="Import spreadsheet to {{ $group->name }}">
	<div class="modal-dialog modal-dialog-centered">
		<form action="{{ route('site.groups.import') }}" method="post" enctype="multipart/form-data" class="modal-content dialog-content shadow-sm">
			<div class="modal-header">
				<div class="modal-title" id="import_member_dialog-title">Import spreadsheet to {{ $group->name }}</div>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body dialog-body">
				<p>CSV, XLSX (Excel), and ODS files are accepted. The first row must be headers with at least a <code>Username</code> column. Optional columns: <code>Name</code>, <code>Membership</code>, and columns for each queue or unix group.</p>

				<ul>
					<li>Membership types: <code>member</code> (default), <code>manager</code>, <code>viewer</code>.</li>
					<li>To add membership to a queue or unix group, set column to <code>yes</code>, <code>1</code>, or <code>true</code>.</li>
					<li>To remove membership from a queue or unix group, set column to <code>no</code>, <code>0</code>, <code>false</code>, or leave blank.</li>
				</ul>

				<div class="form-group dropzone has-advanced-upload" data-acceptedfiles=".csv,.xlsx,.ods">
					<div id="uploader" class="fallback" data-instructions="Click or Drop files" data-list="#uploader-list">
						<label for="upload">Choose a file<span class="dropzone__dragndrop"> or drag it here</span></label>
						<input type="file" name="file" id="upload" class="form-control-file" />
					</div>
					<div class="file-list" id="uploader-list"></div>
					<input type="hidden" name="tmp_dir" id="ticket-tmp_dir" value="{{ ('-' . time()) }}" />
					<input type="hidden" name="id" value="{{ $group->id }}" />
				</div>

			</div>
			<div class="modal-footer dialog-footer text-right">
				<input type="submit" class="order btn btn-primary" data-group="{{ $group->id }}" value="Import" />
			</div>

			@csrf
		</form>
	</div>
</div>

<form id="csv_form_{{ $group->id }}" class="csv_form" method="post" action="{{ route('site.groups.export') }}">
	<input type="hidden" name="data" value="<?php echo urlencode(json_encode($csv_data)); ?>" />
	<input type="hidden" name="id" value="{{ $group->id }}" />
	<input type="hidden" name="filename" value="group_{{ $group->id }}_members" />
	<!-- Allow form submission with keyboard without duplicating the dialog button -->
	<input type="submit" tabindex="-1" />
	@csrf
</form>
