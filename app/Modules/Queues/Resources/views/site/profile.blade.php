
<div class="card mb-3">
	<div class="card-header">
		<h3 class="card-title my-0">{{ trans('queues::queues.queues') }}</h3>
	</div>
	<div class="card-body">
		@if (count($queues))
			<table class="table table-hover">
				<caption class="sr-only">{{ trans('queues::queues.queues') }}</caption>
				<thead>
					<tr>
						<th scope="col">{{ trans('queues::queues.queue') }}</th>
						<th scope="col">{{ trans('queues::queues.resource') }}</th>
						<th scope="col">{{ trans('queues::queues.group') }}</th>
						<th scope="col">{{ trans('queues::queues.status') }}</th>
					</tr>
				</thead>
				<tbody>
				@foreach ($queues as $queue)
					<tr>
						<td>
							@if (auth()->user()->can('manage queues'))
								<a href="{{ route('admin.queues.edit', ['id' => $queue->id]) }}">
							@endif
							{{ $queue->name }}
							@if (auth()->user()->can('manage queues'))
								</a>
							@endif
						</td>
						<td>
							{{ $queue->resource ? $queue->resource->name : '' }}
						</td>
						<td>
							@php
							$group = $queue->group;
							@endphp
							<a href="{{ route('site.users.account.section.show', ['section' => 'groups', 'id' => $group->id, 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}">{{ $group->name }}</a>
						</td>
						<td>
						@if ($queue->status == 'pending')
							<span class="badge badge-warning">{{ trans('queues::queues.pending') }}</span>
						@else
							<span class="badge badge-success">{{ trans('queues::queues.member') }}</span>
						@endif
						</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p class="text-center text-muted">{{ trans('global.none') }}</p>
		@endif
	</div>
</div>
