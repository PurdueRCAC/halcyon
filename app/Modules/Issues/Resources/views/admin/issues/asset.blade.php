<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only visually-hidden">{{ trans('issues::issues.issues') }}</caption>
		<thead>
			<tr>
				<th scope="col" class="priority-5">
					{{ trans('issues::issues.id') }}
				</th>
				<th scope="col">
					{{ trans('issues::issues.report') }}
				</th>
				<th scope="col" class="priority-4">
					{{ trans('issues::issues.resources') }}
				</th>
				<th scope="col" class="priority-4">
					{{ trans('issues::issues.created') }}
				</th>
				<th scope="col" class="priority-2 text-right">
					{{ trans('issues::issues.comments') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit issues'))
						<a href="{{ route('admin.issues.edit', ['id' => $row->id]) }}">
							{{ Illuminate\Support\Str::limit($row->report, 70) }}
						</a>
					@else
						{{ Illuminate\Support\Str::limit($row->report, 70) }}
					@endif
				</td>
				<td class="priority-4">
					@if ($r = $row->resourcesString)
						{{ $r }}
					@else
						<span class="text-muted">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->datetimecreated)
							<time datetime="{{ $row->datetimecreated->toDateTimeLocalString() }}">
								@if ($row->datetimecreated->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
									{{ $row->datetimecreated->diffForHumans() }}
								@else
									{{ $row->datetimecreated->format('Y-m-d') }}
								@endif
							</time>
						@else
							<span class="text-muted">{{ trans('global.unknown') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-4 text-right">
					@if ($row->comments_count)
						{{ $row->comments_count }}
					@else
						<span class="text-muted">{{ $row->comments_count }}</span>
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
</div>

{{ $rows->render() }}
