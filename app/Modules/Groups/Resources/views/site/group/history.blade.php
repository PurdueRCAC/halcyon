<form action="{{ $client == 'admin' ? route('admin.groups.show', ['id' => $group->id, 'section' => 'history']) : route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $group->id, 'subsection' => 'history']) }}" method="get" name="adminForm" id="adminForm-history" class="editform form-validate">
	<p>Any actions taken by managers of this group are listed below. There may be a short delay in actions showing up in the log.</p>

	<?php
	$l = App\Modules\History\Models\Log::query()
		->where('groupid', '=', $group->id)
		->whereIn('classname', ['groupowner', 'groupviewer', 'queuemember', 'groupqueuemember', 'unixgroupmember', 'unixgroup', 'userrequest', 'UsersController', 'UserRequestsController', 'UnixGroupsController', 'UnixGroupMembersController', 'MembersController', 'OrdersController'])
		->where('classmethod', '!=', 'read')
		->orderBy('datetime', 'desc')
		->paginate(request()->input('limit', 20), ['*'], 'page', request()->input('page', 1));

	if (app('isAdmin'))
	{
		$l->appends(['active' => 'history']);
	}

	if (count($l))
	{
		?>
		<table class="table table-hover history">
			<caption class="sr-only visually-hidden">Group history</caption>
			<thead>
				<tr>
					<th scope="col">Date</th>
					<th scope="col">Time</th>
					<th scope="col">Actor</th>
					<th scope="col">User</th>
					<th scope="col">Action Taken</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($l as $log)
				{
					$log = $log->process($log);
					?>
					<tr id="log{{ $log->id }}">
						<td><time datimetime="{{ $log->datetime->toDateTimeLocalString() }}">{{ $log->datetime->format('M j, Y') }}</time></td>
						<td><time datimetime="{{ $log->datetime->toDateTimeLocalString() }}">{{ $log->datetime->format('g:ia') }}</time></td>
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
									<span class="sr-only visually-hidden">An error occurred while performing this action. Action may not have completed.</span>
								</span>
							@endif
							{!! $log->summary !!}
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
	@csrf
</form>