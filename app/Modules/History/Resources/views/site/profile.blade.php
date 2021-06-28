
<div class="contentInner">
	<h2>{{ trans('history::history.history') }}</h2>

	@if (count($groups) || count($unixgroups) || count($queues))
		@if (count($groups) > 0)
			<table class="table table-hover">
				<caption>Group History</caption>
				<thead>
					<tr>
						<th scope="col">Role</th>
						<th scope="col">Group</th>
						<th scope="col">Added</th>
						<th scope="col">Removed</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($groups as $group)
						<tr>
							<td>
								{{ $group->type ? $group->type->name : trans('global.unknown') }}
							</td>
							<td>
								{{ $group->group ? $group->group->name : trans('global.unknown') }}
							</td>
							<td>
								<time datetime="{{ $group->datecreated->toDateTimeString() }}">
									{{ $group->datecreated->format('M d, Y') }}
								</time>
							</td>
							<td>
								@if ($group->isTrashed())
									<time datetime="{{ $group->dateremoved->toDateTimeString() }}">
										@if ($group->dateremoved->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
											{{ $group->dateremoved->diffForHumans() }}
										@else
											{{ $group->dateremoved->format('M d, Y') }}
										@endif
									</time>
								@else
									-
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@endif

		@if (count($unixgroups) > 0)
			<table class="table table-hover">
				<caption>Unix Group History</caption>
				<thead>
					<tr>
						<th scope="col">Group</th>
						<th scope="col">Unix Group</th>
						<th scope="col">Added</th>
						<th scope="col">Removed</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($unixgroups as $group):
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
								<time datetime="{{ $group->datetimecreated->toDateTimeString() }}">
									{{ $group->datetimecreated->format('M d, Y') }}
								</time>
							</td>
							<td>
								@if ($group->isTrashed())
									<time datetime="{{ $group->datetimeremoved->toDateTimeString() }}">
										@if ($group->datetimeremoved->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
											{{ $group->datetimeremoved->diffForHumans() }}
										@else
											{{ $group->datetimeremoved->format('M d, Y') }}
										@endif
									</time>
								@else
									-
								@endif
							</td>
						</tr>
						<?php
					endforeach;
					?>
				</tbody>
			</table>
		@endif

		@if (count($queues) > 0)
			<table class="table table-hover">
				<caption>Queue History</caption>
				<thead>
					<tr>
						<th scope="col">Resource</th>
						<th scope="col">Queue</th>
						<th scope="col">Added</th>
						<th scope="col">Removed</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($queues as $queue):
						$q = $queue->queue()
							->withTrashed()
							->first();
						?>
						<tr id="queueuser{{ $queue->id }}">
							<td>
								{{ $q->resource ? $q->resource->name : trans('global.unknown') }}
							</td>
							<td>
								{{ $q ? $q->name : trans('global.unknown') }}
							</td>
							<td>
								<time datetime="{{ $queue->datetimecreated->toDateTimeString() }}">
									@if ($queue->datetimecreated->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
										{{ $queue->datetimecreated->diffForHumans() }}
									@else
										{{ $queue->datetimecreated->format('M d, Y') }}
									@endif
								</time>
							</td>
							<td>
								@if ($queue->isTrashed())
									<time datetime="{{ $queue->datetimeremoved->toDateTimeString() }}">
										@if ($queue->datetimeremoved->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
											{{ $queue->datetimeremoved->diffForHumans() }}
										@else
											{{ $queue->datetimeremoved->format('M d, Y') }}
										@endif
									</time>
								@else
									@if ($q->isTrashed())
										<time datetime="{{ $q->datetimeremoved->toDateTimeString() }}">
											@if ($q->datetimeremoved->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
												{{ $q->datetimeremoved->diffForHumans() }}
											@else
												{{ $q->datetimeremoved->format('M d, Y') }}
											@endif
										</time>
									@else
										@if ($q->resource && $q->resource->isTrashed())
											<time datetime="{{ $q->resource->datetimeremoved->toDateTimeString() }}">
												@if ($q->resource->datetimeremoved->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
													{{ $q->resource->datetimeremoved->diffForHumans() }}
												@else
													{{ $q->resource->datetimeremoved->format('M d, Y') }}
												@endif
											</time>
										@else
											-
										@endif
									@endif
								@endif
							</td>
						</tr>
						<?php
					endforeach;
					?>
				</tbody>
			</table>
		@endif
	@else
		<p class="alert alert-info">No history found.</p>
	@endif
</div>