<?php
$m = (new \App\Modules\Groups\Models\Member)->getTable();
$ug = (new \App\Modules\Groups\Models\UnixGroupMember)->getTable();
$u = (new \App\Modules\Users\Models\UserUsername)->getTable();
$q = (new \App\Modules\Queues\Models\Queue)->getTable();
$s = (new \App\Modules\Resources\Models\Child)->getTable();
$r = (new \App\Modules\Resources\Models\Asset)->getTable();

$managers = collect([]);
$members = collect([]);
$viewers = collect([]);
$pending = collect([]);
$user_requests = array();
$disabled = collect([]);
$processed = array();

$users = $group->members()
	->orderBy('datecreated', 'desc')
	->get();

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
}

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
				$user_requests[$me->userid][] = $me->userrequestid;

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
		}

		$processed[] = $queue->id . '_' . $me->userid;*/
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

$managers = $managers->sortBy('username');
$members = $members->sortBy('username');
$i = 0;
?>

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/datatables/datatables.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/groups/js/members.js') }}"></script>
@endpush

<div class="row mb-3">
	<div class="col-md-6">
		<button id="export_to_csv" data-id="{{ $group->id }}" class="btn btn-link btn-sm">
			<span class="fa fa-download" ara-hidden="true"></span> Export to CSV
		</button>
	</div>
	<div class="col-md-6 text-right">
		<a href="#add_member_dialog" class="add_member btn btn-info btn-sm" data-membertype="1">
			<span class="fa fa-plus-circle" ara-hidden="true"></span> Add Member
		</a>
	</div>
</div>

@if (count($pending))
<div class="card mb-3">
	<div class="card-header">
		New membership requests
	</div>
	<div class="card-body">
		<form id="FORM_{{ $group->id }}">
			<table class="table table-hover fitToPanel">
				<caption class="sr-only">Membership Requests</caption>
				<thead>
					<tr>
						<th scope="col">Name(s)</th>
						<th scope="col" class="text-center">Accept</th>
						<th scope="col" class="text-center">Deny</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($pending as $i => $req)
						<tr id="entry{{ $i }}" data-id="{{ $req->id }}">
							<td>
								{{ $req->user->name }}
							</td>
							<td class="text-center">
								<?php
								$approves = array();
								$denies = array();
								if (isset($user_requests[$req->userid])):
									foreach ($user_requests[$req->userid] as $reqid):
										$approves[] = route('api.queues.requests.update', ['id' => $reqid]);
										$denies[] = route('api.queues.requests.delete', ['id' => $reqid]);
									endforeach;
								endif;
								?>
								<input type="radio" name="approve{{ $i }}" class="approve-request approve-value0" data-groupid="{{ $group->id }}" data-api="{{ implode(',', $approves) }}" data-membership="{{ route('api.groups.members.update', ['id' => $req->id]) }}" value="{{ $req->userid }},0" />
							</td>
							<td class="text-center">
								<input type="radio" name="approve{{ $i }}" class="approve-request approve-value1" data-groupid="{{ $group->id }}" data-api="{{ implode(',', $denies) }}" data-membership="{{ route('api.groups.members.delete', ['id' => $req->id]) }}" value="{{ $req->userid }},1" />
							</td>
						</tr>
					@endforeach
					<tr id="selectAll">
						<td><strong>Select All</strong></td>
						<td class="text-center"><input type="radio" id="acceptAll" class="radio-toggle" value="0" /></td>
						<td class="text-center"><input type="radio" id="denyAll" class="radio-toggle" value="1" /></td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td></td>
						<td colspan="2" class="text-center">
							<button id="submit-requests" class="btn btn-success" disabled>{{ trans('global.button.save') }}</button>
						</td>
					</tr>
				</tfoot>
			</table>
		</form>
	</div>
</div>
@endif

<div class="card">
	<div class="card-header">
		<h4 class="m-0 p-0">
			Managers
			<a href="#help_managers_span_{{ $group->id }}" data-toggle="modal" class="btn text-info" data-tip="Help">
				<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only">Help</span>
			</a>
		</h4>
		<div class="modal" id="help_managers_span_{{ $group->id }}" tabindex="-1" aria-labelledby="help_managers_span_{{ $group->id }}-title" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content shadow-sm">
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
		<table class="table datatable">
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
						'Table'
					);
					foreach ($queues as $queue):
						$csv_headers[] = $queue->name . ' (' . $queue->resource->name . ')';
						?>
						<th scope="col" class="col-queue text-nowrap text-center">{{ $queue->name }} ({{ $queue->resource->name }})</th>
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
							@if (auth()->user()->can('manage groups'))
								<div class="dropdown dropright">
									<button class="btn btn-options fa fa-ellipsis-h" type="button" id="dropdownMenuButton{{ $member->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<span class="sr-only">Options</span>
									</button>
									<div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $member->id }}">
										<a href="#manager-{{ $member->id }}" class="dropdown-item btn membership-move demote" data-api="{{ route('api.groups.members.update', ['id' => $member->id]) }}" data-target="1" title="Remove manager privileges">
											<span class="fa fa-arrow-down" aria-hidden="true"></span> Remove manager privileges
										</a>
										<a href="#manager-{{ $member->id }}" class="dropdown-item btn membership-remove delete" data-api="{{ $member->groupid ? route('api.groups.members.delete', ['id' => $member->id]) : '' }}" title="Remove from group">
											<span class="fa fa-trash" aria-hidden="true"></span> Remove from group
										</a>
									</div>
								</div>
							@endif
						</td>
						<?php
						$in = array();
						$qu = array();
						$csv = array(
							($member->user ? $member->user->name : trans('global.unknown')),
							($member->user ? $member->username : trans('global.unknown')),
							'Managers'
						);
						foreach ($queues as $queue):
							//$qu[$queue->id] = $queue->users->pluck('userid')->toArray();
							$checked = '';
							$m = null;
							$disable = false;
							// Managers get explicit access to owned queues, but not for free queues.
							if (!$queue->free && $group->cascademanagers):
								$disable = true;
								$checked = ' checked="checked"';
							else:
								//foreach ($queue->users as $m):
									if (isset($queue->qu[$member->userid])):
										$m = $queue->qu[$member->userid];
									//if ($member->userid == $m->userid):
										$checked = ' checked="checked"';
										//break;
									endif;
								//endforeach;
							endif;

							$csv[] = $checked ? 'yes' : 'no';
							?>
							<td class="col-queue text-nowrap text-center">
								<span class="form-chec">
								<input type="checkbox"
									class="membership-toggle queue-toggle form-check-input"
									name="queue[{{ $queue->id }}]"{!! $checked !!}
									data-base="unix-{{ $i }}-{{ $base }}"
									data-userid="{{ $member->userid }}"
									data-objectid="{{ $queue->id }}"
									data-api-create="{{ route('api.queues.users.create') }}"
									data-api="{{ $checked && !$disable ? route('api.queues.users.delete', ['id' => $m]) : route('api.queues.users.create') }}"
									value="1" />
								<label for="queue-{{ $queue->id }}" class="form-check-label"><span class="sr-only">{{ $queue->name }}</span></label>
								</span>
							</td>
							<?php
						endforeach;

						$uu = array();
						foreach ($unixgroups as $unix):
							//$uu[$unix->id] = $unix->members->pluck('userid')->toArray();
							$checked = '';
							$m = null;
							if (isset($unix->uu[$member->userid])):
									$m = $unix->uu[$member->userid];
							//foreach ($unix->members as $m):
								//if (in_array($member->userid, $uu[$unix->id])):
								//if ($member->userid == $m->userid):
									//$in[] = $unix->longname;
									$checked = ' checked="checked"';
									//break;
								endif;
							$csv[] = $checked ? 'yes' : 'no';

							/*if (preg_match("/rcs[0-9]{4}0/", $unix->shortname) && $group->cascademanagers):
								if ($group_boxes > 0 && $checked):
									$checked .= ' disabled="disabled"';
								endif;
							endif;*/
							?>
							<td class="col-unixgroup text-nowrap text-center">
								<span class="form-chec">
								<input type="checkbox"
									class="membership-toggle unixgroup-toggle form-check-input"
									name="unix[{{ $unix->id }}]"{{ $checked }}
									id="unix-{{ $i }}-{{ $unix->id }}"
									data-base="unix-{{ $i }}-{{ $base }}"
									data-userid="{{ $member->userid }}"
									data-objectid="{{ $unix->id }}"
									data-api-create="{{ route('api.unixgroups.members.create') }}"
									data-api="{{ $checked ? route('api.unixgroups.members.delete', ['id' => $m]) : route('api.unixgroups.members.create') }}"
									value="1" />
								<label for="unix-{{ $unix->id }}" class="form-check-label"><span class="sr-only">{{ $unix->name }}</span></label>
								</span>
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
	</div>
</div>

<div class="card mb-3">
	<div class="card-header">
		<h4 class="m-0 p-0">
			Members
			<a href="#help_members_span_{{ $group->id }}" data-toggle="modal" class="btn text-info" data-tip="Help">
				<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only">Help</span>
			</a>
		</h4>
		<div class="modal" id="help_members_span_{{ $group->id }}" tabindex="-1" aria-labelledby="help_members_span_{{ $group->id }}-title" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content shadow-sm">
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
			<table class="table datatable">
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
							<th scope="col" class="col-queue text-nowrap text-center">{{ $queue->name }} ({{ $queue->resource->name }})</th>
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
										<a href="#member{{ $member->id }}" class="dropdown-item btn membership-move change" data-api="{{ route('api.groups.members.update', ['id' => $member->id]) }}" data-target="3" title="Grant usage viewer privileges">
											<span class="fa fa-fw fa-bar-chart" aria-hidden="true"></span> Grant usage viewer privileges
										</a>
										<a href="#member{{ $member->id }}" class="dropdown-item btn membership-move promote" data-api="{{ route('api.groups.members.create') }}" data-target="2" data-userid="{{ $member->userid }}" title="Grant manager privileges">
											<span class="fa fa-fw fa-arrow-up" aria-hidden="true"></span> Grant manager privileges
										</a>
										<a href="#member{{ $member->id }}" class="dropdown-item btn membership-remove delete" data-api="{{ $member->groupid ? route('api.groups.members.delete', ['id' => $member->id]) : '' }}" title="Remove from group">
											<span class="fa fa-fw fa-trash" aria-hidden="true"></span> Remove from group
										</a>
									</div>
								</div>
							</td>
							<?php
							$csv = array(
								($member->user ? $member->user->name : trans('global.unknown')),
								($member->user ? $member->username : trans('global.unknown')),
								'Members'
							);
							foreach ($queues as $queue):
								$checked = '';
								$m = null;

								// We check queues a little differently because it's possible
								// to be a pending member of one queue but full member of another
								//foreach ($queue->users as $m):
									if (isset($queue->qu[$member->userid])):
										$m = $queue->qu[$member->userid];
									//if ($member->userid == $m->userid):
										//if ($m->isPending()):
										if (substr($m, 0, 1) == 'p'):
											$m = substr($m, 1);
											$checked = ' disabled';
										else:
											$checked = ' checked="checked"';
										endif;

									//	break;
									endif;
								//endforeach;
								$csv[] = $checked ? 'yes' : 'no';
								?>
								<td class="text-center col-queue">
									<span class="form-chec">
									<input type="checkbox"
										class="membership-toggle queue-toggle form-check-input"
										id="queue-{{ $queue->id }}"
										name="queue[{{ $queue->id }}]"{{ $checked }}
										data-base="unix-{{ $i }}-{{ $base }}"
										data-userid="{{ $member->userid }}"
										data-objectid="{{ $queue->id }}"
										data-api-create="{{ route('api.queues.users.create') }}"
										data-api="{{ $checked ? route('api.queues.users.delete', ['id' => $m]) : route('api.queues.users.create') }}"
										value="1" />
									<label for="queue-{{ $queue->id }}" class="form-check-label"><span class="sr-only">{{ $queue->name }}</span></label>
									</span>
								</td>
								<?php
							endforeach;

							foreach ($unixgroups as $unix):
								$checked = '';
								$m = null;
								//foreach ($unix->members as $m):
									if (isset($unix->uu[$member->userid])):
										$m = $unix->uu[$member->userid];
									//if ($member->userid == $m->userid):
										//$in[] = $unix->longname;
										$checked = ' checked="checked"';
										//break;
									endif;
								//endforeach;
								$csv[] = $checked ? 'yes' : 'no';

								/*if (preg_match("/rcs[0-9]{4}0/", $unix->shortname)):
									if ($group_boxes > 0 && $checked):
										$checked .= ' disabled="disabled"';
									endif;
								endif;*/
								?>
								<td class="text-center col-unixgroup">
									<span class="form-chec">
									<input type="checkbox"
										class="membership-toggle unixgroup-toggle form-check-input"
										name="unix[{{ $unix->id }}]"{{ $checked }}
										id="unix-{{ $i }}-{{ $unix->id }}"
										data-base="unix-{{ $i }}-{{ $base }}"
										data-userid="{{ $member->userid }}"
										data-objectid="{{ $unix->id }}"
										data-api-create="{{ route('api.unixgroups.members.create') }}"
										data-api="{{ $checked ? route('api.unixgroups.members.delete', ['id' => $m]) : route('api.unixgroups.members.create') }}"
										value="1" />
									<label for="unix-{{ $i }}-{{ $unix->id }}" class="form-check-label"><span class="sr-only">{{ $unix->name }}</span></label>
									</span>
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
		@else
			<p class="alert alert-info">No members found.</p>
		@endif
	</div>
</div>

@if (count($viewers))
<div class="card mb-3">
	<div class="card-header">
		<h4 class="m-0 p-0">
			Usage Reporting Viewers
			<a href="#help_viewers_span_{{ $group->id }}" data-toggle="modal" class="btn text-info" data-tip="Help">
				<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only">Help</span>
			</a>
		</h4>
		<div class="modal" id="help_viewers_span_{{ $group->id }}" tabindex="-1" aria-labelledby="help_viewers_span_{{ $group->id }}-title" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content shadow-sm">
					<div class="modal-header">
						<div class="modal-title" id="help_viewers_span_{{ $group->id }}-title">Usage Reporting Viewers</div>
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
		<table class="table datatable">
			<caption class="sr-only">Members</caption>
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
						<th scope="col" class="col-queue text-nowrap text-center">{{ $queue->name }} ({{ $queue->resource->name }})</th>
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
							<div class="dropdown dropright">
								<button class="btn btn-options fa fa-ellipsis-h" type="button" id="dropdownMenuButton{{ $member->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<span class="sr-only">Options</span>
								</button>
								<div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $member->id }}">
									<a href="#member{{ $member->id }}" class="dropdown-item btn membership-allqueues allqueues" title="Enable all queues for this user">
										<span class="fa fa-fw fa-check-square" aria-hidden="true"></span> Enable all queues
									</a>
									<a href="#member{{ $member->id }}" class="dropdown-item btn membership-move change" data-api="{{ route('api.groups.members.update', ['id' => $member->id]) }}" data-target="1" title="Remove usage viewer privileges">
										<span class="fa fa-fw fa-user" aria-hidden="true"></span> Remove usage viewer privileges
									</a>
									<a href="#member{{ $member->id }}" class="dropdown-item btn membership-remove delete" data-api="{{ $member->groupid ? route('api.groups.members.delete', ['id' => $member->id]) : '' }}" title="Remove from group">
										<span class="fa fa-fw fa-trash" aria-hidden="true"></span> Remove from group
									</a>
								</div>
							</div>
						</td>
						<?php
						$csv = array(
							($member->user ? $member->user->name : trans('global.unknown')),
							($member->user ? $member->username : trans('global.unknown')),
							'Usage Reporting Viewers'
						);
						foreach ($queues as $queue):
							$checked = '';
							$m = null;

							//foreach ($queue->users as $m):
								//if ($member->userid == $m->userid):
								if (isset($queue->qu[$member->userid])):
									$m = $queue->qu[$member->userid];
									$checked = ' checked="checked"';
									//break;
								endif;
							//endforeach;
							$csv[] = $checked ? 'yes' : 'no';
							?>
							<td class="text-center col-queue">
								<span class="form-chec">
								<input type="checkbox"
									class="membership-toggle queue-toggle form-check-input"
									id="queue-{{ $queue->id }}"
									name="queue[{{ $queue->id }}]"{{ $checked }}
									data-base="unix-{{ $i }}-{{ $base }}"
									data-userid="{{ $member->userid }}"
									data-objectid="{{ $queue->id }}"
									data-api-create="{{ route('api.queues.users.create') }}"
									data-api="{{ $checked ? route('api.queues.users.delete', ['id' => $m]) : route('api.queues.users.create') }}"
									value="1" />
								<label for="queue-{{ $queue->id }}" class="form-check-label"><span class="sr-only">{{ $queue->name }}</span></label>
								</span>
							</td>
							<?php
						endforeach;

						foreach ($unixgroups as $unix):
							$checked = '';
							$m = null;
							//foreach ($unix->members as $m):
								//if ($member->userid == $m->userid):
								if (isset($unix->uu[$member->userid])):
									$m = $unix->uu[$member->userid];
									$checked = ' checked="checked"';
									//break;
								endif;
							//endforeach;
							$csv[] = $checked ? 'yes' : 'no';

							/*if (preg_match("/rcs[0-9]{4}0/", $unix->shortname)):
								if ($group_boxes > 0 && $checked):
									$checked .= ' disabled="disabled"';
								endif;
							endif;*/
							?>
							<td class="text-center col-unixgroup">
								<span class="form-chec">
								<input type="checkbox"
									class="membership-toggle unixgroup-toggle form-check-input"
									name="unix[{{ $unix->id }}]"{{ $checked }}
									id="unix-{{ $i }}-{{ $unix->id }}"
									data-base="unix-{{ $i }}-{{ $base }}"
									data-userid="{{ $member->userid }}"
									data-objectid="{{ $unix->id }}"
									data-api-create="{{ route('api.unixgroups.members.create') }}"
									data-api="{{ $checked ? route('api.unixgroups.members.delete', ['id' => $m]) : route('api.unixgroups.members.create') }}"
									value="1" />
								<label for="unix-{{ $i }}-{{ $unix->id }}" class="form-check-label"><span class="sr-only">{{ $unix->name }}</span></label>
								</span>
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
	</div>
</div>
@endif

@if (count($disabled))
<div class="card mb-3">
	<div class="card-header">
		<h4 class="m-0 p-0">
			Disabled Members
			<a href="#help_disabledmembers_span_{{ $group->id }}" data-toggle="modal" class="btn text-info" data-tip="Help">
				<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only">Help</span>
			</a>
		</h4>
		<div class="modal" id="help_disabledmembers_span_{{ $group->id }}" tabindex="-1" aria-labelledby="help_disabledmembers_span_{{ $group->id }}-title" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content shadow-sm">
					<div class="modal-header">
						<div class="modal-title" id="help_disabledmembers_span_{{ $group->id }}-title">Disabled Members</div>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body dialog-body">
						<p>Disabled Members are people that you have granted access to queues but who no longer have an active account. Although queues may be enabled for them, they cannot log into resources and use queues without an active account. If the people listed here have left the University and are no longer participating in research, please remove them from queues.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="card-body">
		@if (count($disabled) > 0)
			<table class="table table-hover hover datatable">
				<caption class="sr-only">Disabled Members</caption>
				<thead>
					<tr>
						<th scope="col">&nbsp;</th>
						<th scope="col">&nbsp;</th>
						@if (count($queues))
						<th scope="col" class="col-queue" colspan="{{ count($queues) }}">Queues</th>
						@endif
						@if (count($unixgroups))
						<th scope="col" class="col-unixgroup" colspan="{{ count($unixgroups) }}">Unix Groups</th>
						@endif
						<th scope="col">&nbsp;</th>
					</tr>
					<tr>
						<th class="text-nowrap" scope="col">User</th>
						<th class="text-nowrap" scope="col">Username</th>
						@foreach ($queues as $queue)
							<th scope="col" class="text-nowrap text-center">{{ $queue->name }} ({{ $queue->resource->name }})</th>
						@endforeach
						@foreach ($unixgroups as $unix)
							<th scope="col" class="text-nowrap text-center">{{ $unix->longname }}</th>
						@endforeach
						<th scope="col" class="text-right">Options</th>
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
							<?php
							$csv = array(
								($member->user ? $member->user->name : trans('global.unknown')),
								($member->user ? $member->username : trans('global.unknown')),
								'Disabled'
							);
							foreach ($queues as $queue):
								$checked = '';
								$m = null;

								foreach ($queue->users()->withTrashed()->get() as $m):
									//if ($m->userid == $member->userid):
									if ($member->userid == $m->userid):
									//if (in_array($member->userid, $qu[$queue->id])):
										//$in[] = $queue->name;
										$checked = ' checked="checked"';
										break;
									endif;
								endforeach;
								$csv[] = $checked ? 'yes' : 'no';
								?>
								<td class="text-center col-queue">
									<span class="form-chec">
									<input type="checkbox"
										class="membership-toggle queue-toggle form-check-input"
										name="queue[{{ $queue->id }}]"{{ $checked }}
										data-base="unix-{{ $i }}-{{ $base }}"
										data-userid="{{ $member->userid }}"
										data-objectid="{{ $queue->id }}"
										data-api="{{ $checked ? route('api.queues.users.delete', ['id' => $m->id]) : route('api.queues.users.create') }}"
										disabled="disabled"
										value="1" />
									<label for="queue-{{ $queue->id }}" class="form-check-label"><span class="sr-only">{{ $queue->name }}</span></label>
									</span>
								</td>
								<?php
							endforeach;

							foreach ($unixgroups as $unix):
								$checked = '';
								$m = null;
								foreach ($unix->members()->withTrashed()->get() as $m):
									//if (in_array($member->userid, $uu[$unix->id])):
									if ($member->userid == $m->userid):
										//$in[] = $unix->longname;
										$checked = ' checked="checked"';
										break;
									endif;
								endforeach;
								$csv[] = $checked ? 'yes' : 'no';
								?>
								<td class="text-center col-unixgroup">
									<span class="form-chec">
									<input type="checkbox"
										class="membership-toggle unixgroup-toggle form-check-input"
										name="unix[{{ $unix->id }}]"{{ $checked }}
										id="unix-{{ $i }}-{{ $unix->id }}"
										data-base="unix-{{ $i }}-{{ $base }}"
										data-userid="{{ $member->userid }}"
										data-objectid="{{ $unix->groupid }}"
										data-api="{{ $checked ? route('api.unixgroups.members.delete', ['id' => $m->id]) : route('api.unixgroups.members.create') }}"
										disabled="disabled"
										value="1" />
									<label for="unix-{{ $i }}-{{ $unix->id }}" class="form-check-label"><span class="sr-only">{{ $unix->name }}</span></label>
									</span>
								</td>
								<?php
							endforeach;
							$csv_data[] = $csv;
							$i++;
							?>
							<td class="text-right text-nowrap">
								<a href="#member{{ $member->id }}" class="membership-remove delete tip" data-api="{{ $member->groupid ? route('api.groups.members.delete', ['id' => $member->id]) : '' }}" title="Remove from group"><span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">Remove from group</span></a>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@else
			<p>No members found.</p>
		@endif
	</div>
</div>
@endif

<div id="add_member_dialog" data-id="{{ $group->id }}" title="Add users to {{ $group->name }}" class="dialog membership-dialog">
	<form id="form_{{ $group->id }}" method="post">
		<fieldset>
		<div class="form-group">
			<label for="addmembers">Enter names, usernames, or email addresses</label>
			<select class="form-control" name="members" id="addmembers" multiple="multiple" data-api="{{ route('api.users.index') }}" data-group="{{ $group->id }}" placeholder="Username, email address, etc.">
			</select>
		</div>

		<div class="form-group">
			<label for="new_membertype">Membership type</label>
			<select class="form-control" id="new_membertype"{{ $group->cascademanagers ? ' data-cascade="1"' : '' }}{{ auth()->user()->can('manage groups') ? '0' : ' data-disable="1"' }}>
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
								<div>
									<div class="form-check">
										<input type="checkbox" class="form-check-input add-queue-member" name="queue[]" id="queue{{ $queue->id }}" value="{{ $queue->id }}" />
										<label class="form-check-label" for="queue{{ $queue->id }}">{{ $queue->name }}</label>
									</div>
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
							<div class="form-group">
								<div class="form-check">
									<input type="checkbox" data-base="unixgroup-{{ $base }}" <?php if ($group->cascademanagers && $name->longname == $group->unixgroup) { echo 'checked disabled'; } ?> class="form-check-input add-unixgroup-member" name="unixgroup[]" id="unixgroup-{{ $name->id }}" value="{{ $name->id }}" />
									<label class="form-check-label" for="unixgroup-{{ $name->id }}">{{ $name->longname }}</label>
								</div>
							</div>
						</div>
					@endforeach
				</div>
			</fieldset>
		@endif

		<div class="dialog-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<input type="button" disabled="disabled" id="add_member_save" class="btn btn-success"
						data-group="{{ $group->id }}"
						data-api="{{ route('api.groups.members.create') }}"
						data-api-unixgroupusers="{{ route('api.unixgroups.members.create') }}"
						data-api-queueusers="{{ route('api.queues.users.create') }}"
						value="{{ trans('global.button.save') }}" />
				</div>
			</div>
		</div>
		</fieldset>
	</form>
</div>

<form id="csv_form_{{ $group->id }}" class="csv_form hide" method="post" action="{{ route('site.groups.export') }}">
	<input type="hidden" name="data" value='<?php echo json_encode($csv_data); ?>' />
	<input type="hidden" name="id" value="{{ $group->id }}" />
	<input type="hidden" name="filename" value="group_{{ $group->id }}_members" />
	<!-- Allow form submission with keyboard without duplicating the dialog button -->
	<input type="submit" tabindex="-1" />
</form>
