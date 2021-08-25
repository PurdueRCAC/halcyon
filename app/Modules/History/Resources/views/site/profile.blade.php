
<div class="contentInner">
	<h2>{{ trans('history::history.history') }}</h2>

	@if (count($groups) || count($unixgroups) || count($queues) || count($courses))
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
								<a href="{{ route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $group->groupid, 'subsection' => 'members', 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}">
									{{ $group->group ? $group->group->name : trans('global.unknown') }}
								</a>
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
								<a href="{{ route('site.users.account.section.show', ['section' => 'groups', 'id' => $ug->groupid, 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}">
									{{ $ug->group ? $ug->group->name : trans('global.unknown') }}
								</a>
							</td>
							<td>
								<a href="{{ route('site.users.account.section.show', ['section' => 'groups', 'id' => $ug->groupid, 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}">
									{{ $ug ? $ug->longname : trans('global.unknown') }}
								</a>
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
								@if ($q)
									{{ $q->resource ? $q->resource->name : trans('global.unknown') }}
								@else
									{{ trans('global.unknown') }}
								@endif
							</td>
							<td>
								<a href="{{ route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $q->groupid, 'subsection' => 'queues', 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}">
								{{ $q ? $q->name : trans('global.unknown') }}
								</a>
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
								@elseif ($q)
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

		@if (count($courses) > 0)
			<table class="table table-hover">
				<caption>Class Account History</caption>
				<thead>
					<tr>
						<th scope="col">Resource</th>
						<th scope="col">Class</th>
						<th scope="col">Semester</th>
						<th scope="col">Added</th>
						<th scope="col">Removed</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($courses as $member):
						$class = $member->account()
							->withTrashed()
							->first();
						?>
						<tr id="classuser{{ $member->id }}">
							<td>
								{{ $class->resource ? $class->resource->name : trans('global.unknown') }}
							</td>
							<td>
								<a href="{{ route('site.users.account.section', ['section' => 'class', 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}">
								@if ($class->semester == 'Workshop')
									{{ $class->classname }}
								@else
									{{ $class->department . ' ' . $class->coursenumber . ' (' . $class->crn . ')' }}
								@endif
								</a>
							</td>
							<td>
								{{ $class->semester }}
							</td>
							<td>
								<time datetime="{{ $member->datetimecreated->toDateTimeString() }}">
									@if ($member->datetimecreated->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
										{{ $member->datetimecreated->diffForHumans() }}
									@else
										{{ $member->datetimecreated->format('M d, Y') }}
									@endif
								</time>
							</td>
							<td>
								@if ($member->isTrashed())
									<time datetime="{{ $member->datetimeremoved->toDateTimeString() }}">
										@if ($member->datetimeremoved->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
											{{ $member->datetimeremoved->diffForHumans() }}
										@else
											{{ $member->datetimeremoved->format('M d, Y') }}
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
	@else
		<div class="card card-help">
			<div class="card-body">
				<h3 class="card-title">What is this page?</h3>
				<p>Here you can find various access history for {{ $user->name }}. This shows when the person was added, given specific roles, or removed from a group, resource queue, or unix group.</p>
			</div>
		</div>
	@endif
</div>