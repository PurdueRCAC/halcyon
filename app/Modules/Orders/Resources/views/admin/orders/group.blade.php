@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css') }}" />
@endpush

<div class="row">
	<div class="col-md-12">
		<fieldset class="adminform">
			<legend>{{ trans('orders::orders.orders') }}</legend>

			<table class="table table-hover">
				<caption class="sr-only">{{ trans('orders::orders.orders') }}</caption>
				<thead>
					<tr>
						<th scope="col">{{ trans('orders::orders.id') }}</th>
						<th scope="col">{{ trans('orders::orders.status') }}</th>
						<th scope="col">{{ trans('orders::orders.created') }}</th>
						<th scope="col">{{ trans('orders::orders.items') }}</th>
						<th scope="col" class="text-right">{{ trans('orders::orders.total') }}</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$orders = \App\Modules\Orders\Models\Order::query()
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
								<span class="badge badge-sm order-status {{ str_replace(' ', '-', $order->status) }}" data-tip="Accounts: {{ $order->accounts }}<br />Assigned: {{ $order->accountsassigned }}<br />Approved: {{ $order->accountsapproved }}<br />Denied: {{ $order->accountsdenied }}<br />Paid: {{ $order->accountspaid }}<br />---<br />Items: {{ $order->items }}<br />Fulfilled: {{ $order->itemsfulfilled }}">
									{{ trans('orders::orders.' . $order->status) }}
								</span>
							</td>
							<td>{{ $order->datetimecreated }}</td>
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
							<td class="text-right">
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
