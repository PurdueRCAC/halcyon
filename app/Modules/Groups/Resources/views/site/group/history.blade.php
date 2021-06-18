
<p>Any actions taken by managers of this group are listed below. There may be a short delay in actions showing up in the log.</p>

<?php
// Get manager adds
$l = App\Modules\History\Models\Log::query()
	->where('groupid', '=', $group->id)
	->whereIn('classname', ['groupowner', 'groupviewer', 'queuemember', 'groupqueuemember', 'unixgroupmember', 'unixgroup', 'userrequest', 'UsersController', 'UserRequestsController', 'UnixGroupsController', 'UnixGroupMembersController', 'MembersController', 'OrdersController'])
	->where('classmethod', '!=', 'read')
	//->where('datetime', '>', Carbon\Carbon::now()->modify('-1 month')->toDateTimeString())
	->orderBy('datetime', 'desc')
	->limit(20)
	->paginate();
/*
$history = $group->history()->orderBy('created_at', 'desc')->get();
$ids = array();
foreach ($group->members as $member)
{
	$ids[] = 'groupusers' . $member->id;
	$history = $history->merge($member->history()->orderBy('created_at', 'desc')->get());
}

foreach ($group->unixgroups as $unixgroup)
{
	$ids[] = 'unixgroups' . $unixgroup->id;
	$history = $history->merge($unixgroup->history()->orderBy('created_at', 'desc')->get());

	foreach ($unixgroup->members as $u)
	{
		$ids[] = 'unixgroupusers' . $u->id;
		$history = $history->merge($u->history()->orderBy('created_at', 'desc')->get());
	}
}

foreach ($group->queues as $queue)
{
	$ids[] = 'queues' . $queue->id;
	$history = $history->merge($queue->history()->orderBy('created_at', 'desc')->get());

	foreach ($queue->users as $u)
	{
		$ids[] = 'queueusers' . $u->id;
		$history = $history->merge($u->history()->orderBy('created_at', 'desc')->get());
	}
}
$sorted = $history->sortByDesc('id');
echo '<pre>';
print_r($ids);
echo '</pre>';
*/
if (count($l))
{
	?>
	<table class="table table-hover history">
		<caption class="sr-only">Group history</caption>
		<thead>
			<tr>
				<th scope="col">Date</th>
				<th scope="col">Time</th>
				<th scope="col">Manager</th>
				<th scope="col">User</th>
				<th scope="col">Action Taken</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($l as $log)
			{
				switch ($log->classname)
				{
					case 'groupowner':
						if ($log->classmethod == 'create')
						{
							$log->action = 'Promoted to manager';
						}

						if ($log->classmethod == 'delete')
						{
							$log->action = 'Demoted as manager';
						}
					break;

					case 'groupviewer':
						if ($log->classmethod == 'create')
						{
							$log->action = 'Promoted to group usage viewer';
						}

						if ($log->classmethod == 'delete')
						{
							$log->action = 'Demoted as group usage viewer';
						}
					break;

					case 'MembersController':
						if ($log->classmethod == 'update')
						{
							$log->action = 'Membership status changed';

							$payload = $log->jsonPayload;
							if (isset($payload->membertype))
							{
								if ($payload->membertype == 1)
								{
									$log->action = 'Status set to member';
								}
								if ($payload->membertype == 2)
								{
									$log->action = 'Promoted to manager';
								}
								if ($payload->membertype == 2)
								{
									$log->action = 'Promoted to group usage viewer';
								}
							}
						}

						if ($log->classmethod == 'create')
						{
							$log->action = 'Added to group';
						}

						if ($log->classmethod == 'delete')
						{
							$log->action = 'Removed from group';
						}
					break;

					case 'UsersController':
					case 'queuemember':
					case 'groupqueuemember':
						$payload = $log->jsonPayload;
						if (isset($payload->queueid))
						{
							$queue = App\Modules\Queues\Models\Queue::find($payload->queueid);
						}
						else
						{
							$queue = App\Modules\Queues\Models\Queue::find($log->targetobjectid);
						}
						if ($log->classmethod == 'create')
						{
							$log->action = 'Added to queue ' . ($queue ? $queue->name : trans('global.unknown')) . ' (' . ($queue ? $queue->subresource->name : trans('global.unknown')) . ')';
						}

						if ($log->classmethod == 'delete')
						{
							$log->action = 'Removed from queue ' . ($queue ? $queue->name : trans('global.unknown')) . ' (' . ($queue ? $queue->subresource->name : trans('global.unknown')) . ')';
						}
					break;

					case 'UnixGroupMembersController':
					case 'unixgroupmember':
						$g = App\Modules\Groups\Models\UnixGroup::find($log->targetobjectid);
						$groupname = '#' . $log->targetobjectid;
						if ($g)
						{
							$groupname = $g->longname;
						}

						if ($log->classmethod == 'create')
						{
							$log->action = 'Added to Unix group ' . $groupname;
						}

						if ($log->classmethod == 'delete')
						{
							$log->action = 'Removed from Unix group ' . $groupname;
						}
					break;

					case 'UnixGroupsController':
					case 'unixgroup':
						$payload = $log->jsonPayload;

						if (isset($payload->longname))
						{
							$groupname = $payload->longname;
						}
						else
						{
							$g = App\Modules\Groups\Models\UnixGroup::find($log->targetobjectid);
							$groupname = '#' . $log->targetobjectid;
							if ($g)
							{
								$groupname = $g->longname;
							}
						}

						if ($log->classmethod == 'create')
						{
							$log->action = 'Created Unix group ' . $groupname;
						}

						if ($log->classmethod == 'delete')
						{
							$log->action = 'Deleted Unix group ' . $groupname;
						}
					break;

					case 'UserRequestsController':
					case 'userrequest':
						$payload = $log->jsonPayload;
						if (isset($payload->queueid))
						{
							$queue = App\Modules\Queues\Models\Queue::find($payload->queueid);
						}
						else
						{
							$queue = App\Modules\Queues\Models\Queue::find($log->targetobjectid);
						}
						$queuename = '#' . $log->targetobjectid;
						if ($queue)
						{
							$queuename = $queue->name . ' (' . ($queue->subresource ? $queue->subresource->name : trans('global.unknown')) . ')';
						}

						if ($log->classmethod == 'create')
						{
							$log->action = 'Submitted request to queue ' . $queuename;
						}

						if ($log->classmethod == 'update')
						{
							$log->action = 'Approved request to queue ' . $queuename;
						}

						if ($log->classmethod == 'delete')
						{
							$log->action = 'Canceled request to queue ' . $queuename;
						}
					break;

					case 'OrdersController':
					case 'order':
						if ($log->classmethod == 'create')
						{
							$log->action = 'Order #' . $log->objectid . ' created';
						}

						if ($log->classmethod == 'update')
						{
							$log->action = 'Order #' . $log->objectid . ' updated';
						}

						if ($log->classmethod == 'delete')
						{
							$log->action = 'Order #' . $log->objectid . ' cancelled';
						}
					break;
				}
				?>
				<tr>
					<td><?php echo $log->datetime->format('M j, Y'); ?></td>
					<td><?php echo $log->datetime->format('g:ia'); ?></td>
					<td>
						@if ($log->userid > 0)
							{{ $log->user ? $log->user->name : trans('global.unknown') }}
						@else
							-
						@endif
					</td>
					<td>
						@if ($log->targetuserid > 0)
							{{ $log->targetuser ? $log->targetuser->name : trans('global.unknown') }}
						@else
							-
						@endif
					</td>
					<td>
						<?php if (substr($log->status, 0, 1) != '2') { ?>
							<span class="tip text-warning" title="An error occurred while performing this action. Action may not have completed.">
								<i class="fa fa-exclamation-circle" aria-hidden="true"></i><span class="sr-only">An error occurred while performing this action. Action may not have completed.</span>
							</span>
						<?php } ?>
						{{ $log->action }}
					</td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>

	<?php
	echo $l->render();
}
else
{
	?>
	<p class="alert alert-warning">No activity found.</p>
	<?php
}
?>
