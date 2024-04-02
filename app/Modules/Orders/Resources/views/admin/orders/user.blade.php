@push('scripts')
<script src="{{ timestamped_asset('modules/orders/js/orders.js') }}"></script>
@endpush


@if (count($rows))
<div class="card">
	<div class="card-header">
		<h3 class="card-title">{{ trans('orders::orders.orders') }}</h3>
	</div>

	<div class="card-body">

		<table class="table table-hover adminlist">
			<caption class="sr-only visually-hidden">{{ trans('orders::orders.orders placed') }}</caption>
			<thead>
				<tr>
					<th scope="col" class="priority-5">
						<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.id'), 'id', $filters['order_dir'], $filters['order']); ?>
					</th>
					<th scope="col" class="priority-4">
						<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.created'), 'datetimecreated', $filters['order_dir'], $filters['order']); ?>
					</th>
					<th scope="col">
						<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.status'), 'state', $filters['order_dir'], $filters['order']); ?>
					</th>
					<th scope="col" class="priority-4">
						<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.submitter'), 'userid', $filters['order_dir'], $filters['order']); ?>
					</th>
					<th scope="col" class="priority-2 text-right">
						{{ trans('orders::orders.total') }}
					</th>
				</tr>
			</thead>
			<tbody>
			@foreach ($rows as $i => $row)
				<tr>
					<td class="priority-5">
						@if (auth()->user()->can('edit orders'))
							<a href="{{ route('admin.orders.edit', ['id' => $row->id]) }}">
								{{ $row->id }}
							</a>
						@else
							{{ $row->id }}
						@endif
					</td>
					<td class="priority-4">
						@if ($row->datetimecreated)
							<time datetime="{{ $row->datetimecreated->toDateTimeString() }}">
								@if ($row->datetimecreated->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
									{{ $row->datetimecreated->diffForHumans() }}
								@else
									{{ $row->datetimecreated->format('Y-m-d') }}
								@endif
							</time>
						@else
							<span class="never">{{ trans('global.unknown') }}</span>
						@endif
					</td>
					<td>
						<span class="order-status {{ str_replace(' ', '-', $row->status) }}">
							{{ trans('orders::orders.' . $row->status) }}
						</span>
					</td>
					<td class="priority-4">
						@if ($row->groupid)
							@if (auth()->user()->can('manage groups'))
								<a href="{{ route('admin.groups.edit', ['id' => $row->groupid]) }}">
									{!! $row->group ? $row->group->name : ' <span class="unknown">' . trans('global.unknown') . '</span>' !!}
								</a>
							@else
								{!! $row->group ? $row->group->name : ' <span class="unknown">' . trans('global.unknown') . '</span>' !!}
							@endif
						@else
							@if (auth()->user()->can('manage users'))
								<a href="{{ route('admin.users.show', ['id' => $row->userid]) }}">
									{!! $row->name ? $row->name : ' <span class="unknown">' . trans('global.unknown') . '</span>' !!}
								</a>
							@else
								{!! $row->name ? $row->name : ' <span class="unknown">' . trans('global.unknown') . '</span>' !!}
							@endif
						@endif
					</td>
					<td class="priority-2 text-right">
						{{ config('orders.currency', '$') }} {{ $row->formatNumber($row->ordertotal) }}
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>

		{{ $rows->render() }}

	</div>
</div>

@else
	<div class="d-flex justify-content-center">
		<div class="card card-help w-50">
			<div class="card-body">
				<h3 class="card-title mt-0">What is this page?</h3>
				<p class="card-text">Here you can find orders submitted by or related to this user.</p>
			</div>
		</div>
	</div>
@endif
