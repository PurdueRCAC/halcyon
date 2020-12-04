
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
								{{ $group->type ? $group->type->name : trans('global.unknown') }}
							</td>
							<td>
								{{ $group->group ? $group->group->name : trans('global.unknown') }}
							</td>
							<td>
								{{ $group->datecreated->format('M d, Y') }}
							</td>
							<td>
								@if ($group->isTrashed())
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

		@if (count($groups) > 0)
			<table class="table table-hover">
				<caption>Unix Group History</caption>
				<thead class="thead-dark">
					<tr>
						<th scope="col">Group</th>
						<th scope="col">Unix Group</th>
						<th scope="col">Added</th>
						<th scope="col">Removed</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($unixgroups as $group)
					{
						$ug = $group->unixgroup()->withTrashed()->first();
						?>
						<tr id="unixgroup{{ $group->id }}">
							<td>
								{{ $ug->group ? $ug->group->name : trans('global.unknown') }}
							</td>
							<td>
								{{ $ug ? $ug->longname : trans('global.unknown') }}
							</td>
							<td>
								{{ $group->datetimecreated->format('M d, Y') }}
							</td>
							<td>
								@if ($group->isTrashed())
									{{ $group->datetimeremoved->format('M d, Y') }}
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
					foreach ($queues as $queue)
					{
						$q = $queue->queue()->withTrashed()->first();
						?>
						<tr id="queueuser{{ $queue->id }}">
							<td>
								{{ $q->resource ? $q->resource->name : trans('global.unknown') }}
							</td>
							<td>
								{{ $q ? $q->name : trans('global.unknown') }}
							</td>
							<td>
								{{ $queue->datetimecreated->format('M d, Y') }}
							</td>
							<td>
								@if ($queue->isTrashed())
									{{ $queue->datetimeremoved->format('M d, Y') }}
								@else
									@if ($q->isTrashed())
										{{ $q->datetimeremoved->format('M d, Y') }}
									@else
										@if ($q->resource->isTrashed())
											{{ $q->resource->datetimeremoved->format('M d, Y') }}
										@else
											-
										@endif
									@endif
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