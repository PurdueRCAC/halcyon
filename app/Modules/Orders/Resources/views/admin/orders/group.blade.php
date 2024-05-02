@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/orders/css/orders.css') }}" />
@endpush

<div class="row">
	<div class="col-md-12">
		<fieldset class="adminform">
			<legend>{{ trans('orders::orders.orders') }}</legend>

			<table class="table table-hover">
				<caption class="sr-only visually-hidden">{{ trans('orders::orders.orders') }}</caption>
				<thead>
					<tr>
						<th scope="col">{{ trans('orders::orders.id') }}</th>
						<th scope="col">{{ trans('orders::orders.status') }}</th>
						<th scope="col">{{ trans('orders::orders.created') }}</th>
						<th scope="col">{{ trans('orders::orders.items') }}</th>
						<th scope="col" class="text-right text-end">{{ trans('orders::orders.total') }}</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$orders = \App\Modules\Orders\Models\Order::query()
						->with('accounts')
						//->with('items')
						->where('groupid', '=', $group->id)
						->orderBy('datetimecreated', 'desc')
						->paginate();
					?>
				@if (count($orders))
					@foreach ($orders as $order)
						<tr>
							<td>
								@if (auth()->user()->can('manage orders'))
									<a href="{{ route('admin.orders.edit', ['id' => $order->id]) }}">{{ $order->id }}</a>
								@else
									{{ $order->id }}
								@endif
							</td>
							<td>
								<?php
								$accountsassigned = $order->accounts->filter(function($value, $key)
								{
									return $value->approveruserid > 0;
								})->count();
								$accountsapproved = $order->accounts->filter(function($value, $key)
								{
									return $value->isApproved();
								})->count();
								$accountsdenied = $order->accounts->filter(function($value, $key)
								{
									return $value->isDenied();
								})->count();
								$accountspaid = $order->accounts->filter(function($value, $key)
								{
									return $value->isPaid();
								})->count();

								$order->items = $order->items()->with('product')->get();
								$itemsfulfilled = $order->items->filter(function($value, $key)
								{
									return $value->isFulfilled();
								})->count();
								?>
								<span class="badge badge-sm order-status {{ str_replace(' ', '-', $order->status) }}" data-tip="Accounts: {{ $order->accounts->count() }}<br />Assigned: {{ $accountsassigned }}<br />Approved: {{ $accountsapproved }}<br />Denied: {{ $accountsdenied }}<br />Paid: {{ $accountspaid }}<br />---<br />Items: {{ count($order->items) }}<br />Fulfilled: {{ $itemsfulfilled }}">
									{{ trans('orders::orders.' . $order->status) }}
								</span>
							</td>
							<td>{{ $order->datetimecreated->format('Y-m-d') }}</td>
							<td>
								<?php
								$products = array();
								foreach ($order->items as $item)
								{
									$products[] = $item->product->name;
								}
								echo implode('<br />', $products);
								?>
							</td>
							<td class="text-right text-end">
								{{ config('orders.currency', '$') }} {{ $order->formattedTotal }}
							</td>
						</tr>
					@endforeach
				@else
					<tr>
						<td colspan="5" class="text-center">{{ trans('global.none') }}</td>
					</tr>
				@endif
				</tbody>
			</table>

			<?php $orders->render(); ?>
		</fieldset>
	</div>
</div>
