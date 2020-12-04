@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/orders/js/orders.js') }}"></script>
<script>
$(document).ready(function() { 
	$('.filter-submit').on('change', function(e){
		//Filter($(this).data('type'), $(this).data('field'));
		$(this).closest('form').submit();
	});
});
</script>
@endpush

<div class="contentInner">
<h2>{{ trans('orders::orders.orders') }}</h2>

<form action="{{ route('site.orders.index') }}" method="get" class="form-inline">

	<fieldset id="filter-bar">
		<legend class="sr-only">Filter</legend>

		<div class="row">
			<div class="col-md-12">
				<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
				<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />

				<label class="sr-only" for="filter_category">{{ trans('orders::orders.category') }}</label>
				<select name="category" id="filter_category" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['status'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all categories') }}</option>
					<?php foreach ($categories as $category) { ?>
						<option value="<?php echo $category->id; ?>"<?php if ($filters['category'] == $category->id): echo ' selected="selected"'; endif;?>>{{ $category->name }}</option>
					<?php } ?>
				</select>

				<label class="sr-only" for="filter_status">{{ trans('orders::orders.status') }}</label>
				<select name="status" id="filter_status" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['status'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all statuses') }}</option>
					<option value="active"<?php if ($filters['status'] == 'active'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.active') }}</option>
					<option value="pending_payment"<?php if ($filters['status'] == 'pending_payment'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_payment') }}</option>
					<option value="pending_boassignment"<?php if ($filters['status'] == 'pending_boassignment'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_boassignment') }}</option>
					<option value="pending_approval"<?php if ($filters['status'] == 'pending_approval'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_approval') }}</option>
					<option value="pending_collection"<?php if ($filters['status'] == 'pending_collection'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_collection') }}</option>
					<option value="pending_fulfillment"<?php if ($filters['status'] == 'pending_fulfillment'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_fulfillment') }}</option>
					<option value="complete"<?php if ($filters['status'] == 'complete'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.complete') }}</option>
					<option value="canceled"<?php if ($filters['status'] == 'canceled'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.canceled') }}</option>
				</select>

				<label class="sr-only" for="filter_start">{{ trans('orders::orders.start date') }}</label>
				<input type="text" name="start" id="filter_start" size="10" class="form-control date-pick filter filter-submit" value="{{ $filters['start'] }}" placeholder="Start date" />

				<label class="sr-only" for="filter_end">{{ trans('orders::orders.end date') }}</label>
				<input type="text" name="end" id="filter_end" size="10" class="form-control date-pick filter filter-submit" value="{{ $filters['end'] }}" placeholder="End date" />
			</div>
		</div>

		<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
		<table class="table table-hover adminlist">
			<caption class="sr-only">{{ trans('orders::orders.orders placed') }}</caption>
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
							<a href="{{ route('site.orders.read', ['id' => $row->id]) }}">
								{{ $row->id }}
							</a>
						@else
							{{ $row->id }}
						@endif
					</td>
					<td class="priority-4">
						@if ($row->getOriginal('datetimecreated') && $row->getOriginal('datetimecreated') != '0000-00-00 00:00:00')
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
								<a href="{{ route('site.groups.show', ['id' => $row->groupid]) }}">
									{!! $row->group ? $row->group->name : ' <span class="unknown">' . trans('global.unknown') . '</span>' !!}
								</a>
							@else
								{!! $row->group ? $row->group->name : ' <span class="unknown">' . trans('global.unknown') . '</span>' !!}
							@endif
						@else
							@if (auth()->user()->can('manage users'))
								<a href="{{ route('site.users.account', ['u' => $row->userid]) }}">
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
	@else
		<p class="alert alert-info">No orders found.</p>
	@endif

	@csrf
</form>
</div>
