
	<div class="contentInner">
		<h2>{{ trans('history::history.history') }}</h2>

		@if (count($groups) > 0)
			<table class="table table-hover">
				<caption>Group History</caption>
				<thead class="thead-dark">
					<tr>
						<th scope="col">Role</th>
						<th scope="col">Group</th>
						<th scope="col">Added</th>
						<th scope="col">Removed</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($groups as $group)
					{
						?>
						<tr>
							<td>
								{{ $group->type->name }}
							</td>
							<td>
								{{ $group->group->name }}
							</td>
							<td>
								{{ $group->datecreated->format('M d, Y') }}
							</td>
							<td>
								@if ($group->dateremoved && $group->dateremoved != '0000-00-00 00:00:00' && $group->dateremoved != '-0001-11-30 00:00:00')
									{{ $group->dateremoved->format('M d, Y') }}
								@else
									-
								@endif
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		@else
			<p class="alert alert-info">No history found.</p>
		@endif

		@if (count($queues) > 0)
			<table class="table table-hover">
				<caption>Queue History</caption>
				<thead class="thead-dark">
					<tr>
						<th scope="col">Resource</th>
						<th scope="col">Queue</th>
						<th scope="col">Added</th>
						<th scope="col">Removed</th>
					</tr>
				</thead>
				<tbody>
					<?php
					/*if (count($user->priorownerofgroups))
					{
						foreach ($user->priorownerofgroups as $own)
						{
							$group = $ws->get($own['group']);
							if ($own['group'] != ROOT_URI . "group/0")
							{
								foreach ($group->queues as $q)
								{
									$qq = $ws->get($q['id']);
									$created_date = date('M d, Y', strtotime($own['created']));
									$removed_date = date('M d, Y', strtotime($own['removed']));

									$qid = preg_replace('/^' . str_replace('/', '\/', ROOT_URI) . 'queue\//', '', $q['id'], 1, $count);
									?>
									<tr>
										<?php if ($qq->group == ROOT_URI . "group/0") { ?>
											<td><?php echo $q['resource']['name']; ?></td>
										<?php } else { ?>
											<td><?php echo $q['subresource']['name']; ?></td>
										<?php } ?>
										<td>
											<a href="/admin/queue/edit/?q=<?php echo $qid; ?>"><?php echo $q['name']; ?></a>
										</td>
										<td><?php echo $created_date; ?></td>
										<td><?php echo $removed_date; ?></td>
									</tr>
									<?php
								}
							}
						}
					}

					if (count($user->priormemberofqueues) > 0)
					{
						foreach ($user->priormemberofqueues as $own)
						{
							$q = $ws->get($own['queue']);
							$created_date = date('M d, Y', strtotime($own['created']));
							$removed_date = date('M d, Y', strtotime($own['removed']));

							$qid = preg_replace('/^' . str_replace('/', '\/', ROOT_URI) . 'queue\//', '', $q->id, 1, $count);
							?>
							<tr>
								<?php if ($q->group == ROOT_URI . "group/0") { ?>
									<td><?php echo $q->resource['name']; ?></td>
								<?php } else { ?>
									<td><?php echo $q->subresource['name']; ?></td>
								<?php } ?>
								<td><a href="/admin/queue/edit/?q=<?php echo $qid; ?>"><?php echo $q->name; ?></a></td>
								<td><?php echo $created_date; ?></td>
								<td><?php echo $removed_date; ?></td>
							</tr>
							<?php
						}
					}*/

					foreach ($queues as $queue)
					{
						?>
						<tr>
							<td>
								{{ $queue->type->name }}
							</td>
							<td>
								{{ $queue->queue->name }}
							</td>
							<td>
								{{ $queue->datetimecreated->format('M d, Y') }}
							</td>
							<td>
								@if ($queue->datetimeremoved && $queue->datetimeremoved != '0000-00-00 00:00:00' && $queue->datetimeremoved != '-0001-11-30 00:00:00')
									{{ $queue->datetimeremoved->format('M d, Y') }}
								@else
									-
								@endif
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		@else
			<p class="alert alert-info">No history found.</p>
		@endif
	</div>