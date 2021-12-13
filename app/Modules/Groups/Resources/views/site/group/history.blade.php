
<p>Any actions taken by managers of this group are listed below. There may be a short delay in actions showing up in the log.</p>

<?php
$l = App\Modules\History\Models\Log::query()
	->where('groupid', '=', $group->id)
	->whereIn('classname', ['groupowner', 'groupviewer', 'queuemember', 'groupqueuemember', 'unixgroupmember', 'unixgroup', 'userrequest', 'UsersController', 'UserRequestsController', 'UnixGroupsController', 'UnixGroupMembersController', 'MembersController', 'OrdersController'])
	->where('classmethod', '!=', 'read')
	->orderBy('datetime', 'desc')
	->limit(20)
	->paginate();

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
				if ($log->targetuserid <= 0 && $log->payload)
				{
					if (isset($log->jsonPayload->userid) && $log->jsonPayload->userid)
					{
						if (is_numeric($log->jsonPayload->userid))
						{
							$log->targetuserid = $log->jsonPayload->userid;
						}
						else
						{
							$target = App\Modules\Users\Models\User::findByUsername($log->jsonPayload->userid);
							$log->targetuserid = $target ? $target->id : $log->payload->userid;
						}
						$log->save();
					}

					if ($log->transportmethod == 'DELETE')
					{
						$segments = strstr($log->uri, '?') ? strstr($log->uri, '?', true) : $log->uri;
						$segments = explode('/', $segments);
						$id = array_pop($segments);

						if ($log->classname == 'UsersController')
						{
							$queueuser = App\Modules\Queues\Models\User::query()->withTrashed()->where('id', '=', $id)->first();
							if ($queueuser)
							{
								$log->targetuserid = $queueuser->userid;
								$log->targetobjectid = $queueuser->queueid;
								$log->save();
							}
						}
					}
				}

				if ($log->targetobjectid <= 0 && $log->payload)
				{
					if (isset($log->jsonPayload->unixgroupid) && $log->jsonPayload->unixgroupid)
					{
						$log->targetobjectid = $log->jsonPayload->unixgroupid;
						$log->save();
					}
				}

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
						// Some fiddling here. Delete events are only to a URL /api/unixgroups/members/####
						// So we need to parse out the record's ID to look up its unix group and user.
						if ($log->targetobjectid <= 0 && $log->classmethod == 'delete')
						{
							$parts = explode('/', $log->uri);
							$mid = end($parts);
							$mid = intval($mid);

							if ($mid)
							{
								$m = App\Modules\Groups\Models\UnixGroupMember::query()->withTrashed()->where('id', '=', $mid)->first();
								$log->targetobjectid = $m ? $m->unixgroupid : $log->targetobjectid;
								$log->targetuserid = $m ? $m->userid : $log->targetuserid;
							}
						}

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
						$queuenames = array();
						$queuename = '#' . $log->targetobjectid;

						if (isset($payload->resources))
						{
							foreach ($payload->resources as $resourceid)
							{
								foreach ($group->queues as $queue)
								{
									if ($queue->resource && $queue->resource->id == $resourceid)
									{
										$queuenames[] = $queue->name . ' (' . ($queue->subresource ? $queue->subresource->name : trans('global.unknown')) . ')';
									}
								}
							}
						}
						else
						{
							$queue = App\Modules\Queues\Models\Queue::find($log->targetobjectid);
							if ($queue)
							{
								$queuenames[] = $queue->name . ' (' . ($queue->subresource ? $queue->subresource->name : trans('global.unknown')) . ')';
							}
						}

						$queuename = implode(', ', $queuenames);

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
					<td><time datimetime="{{ $log->datetime->format('Y-m-d\TH:i:s\Z') }}">{{ $log->datetime->format('M j, Y') }}</time></td>
					<td><time datimetime="{{ $log->datetime->format('Y-m-d\TH:i:s\Z') }}">{{ $log->datetime->format('g:ia') }}</time></td>
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
						@if (substr($log->status, 0, 1) != '2')
							<span class="tip text-warning" title="An error occurred while performing this action. Action may not have completed.">
								<span class="fa fa-exclamation-circle" aria-hidden="true"></span>
								<span class="sr-only">An error occurred while performing this action. Action may not have completed.</span>
							</span>
						@endif
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
