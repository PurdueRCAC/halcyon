@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/orders/css/orders.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/orders/js/orders.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('orders::orders.orders'),
		route('site.orders.index')
	)
	->append(
		trans('orders::orders.recurring'),
		route('site.orders.recurring')
	)
	->append(
		'#' . $item->id,
		route('site.orders.recurring.read', ['id' => $item->id])
	);
@endphp

@section('content')
<div class="row">
@component('orders::site.submenu')
	recur
@endcomponent
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	<h2 class="sr-only visually-hidden">{{ trans('orders::orders.recurring') }}</h2>

	<form action="{{ route('site.orders.recurring.read', ['id' => $item->id]) }}" method="get" class="row">
		<div class="contentInner col-lg-12 col-md-12 col-sm-12 col-xs-12">
			
			<div class="card">
				<div class="card-header">
					<div class="row">
						<div class="col-md-8">
							<h3 class="card-title">Recurring Item #{{ $item->id }}</h3>
						</div>
						<div class="col-md-4 text-right text-end">
							@if ($item->paiduntil && $item->paiduntil == $item->billeduntil)
								<button class="btn btn-sm btn-secondary recur-renew tip" title="Generate an order to extend service for this recurring item" data-api="{{ route('api.orders.create') }}" data-item="{{ $item->id }}">Renew Now</button>
							@endif
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="form-group">
						<p><strong>{{ trans('orders::orders.product') }}:</strong></p>
						<p class="form-text">
							<a href="{{ route('site.orders.recurring', ['product' => $item->orderproductid]) }}">{{ $item->product->name }}</a>
						</p>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<p><strong>{{ trans('orders::orders.user') }}:</strong></p>
								<ul>
									@foreach ($item->orderusers as $user)
										<li><a href="{{ route('site.users.account', ['u' => $user]) }}">{{ App\Modules\Users\Models\User::findOrFail($user)->name }}</a></li>
									@endforeach
								</ul>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<p><strong>{{ trans('orders::orders.group') }}:</strong></p>
								<ul>
									@foreach ($item->ordergroups as $group)
										<li>{{ App\Modules\Groups\Models\Group::findOrFail($group)->name }}</li>
									@endforeach
								</ul>
							</div>
						</div>
					</div>

					@if ($item->start())
						@php
						$until = $item->until();
						@endphp
					<div class="row">
						<div class="col">
							<p><strong>{{ trans('orders::orders.started') }}:</strong></p>
							<p>
								{{ $item->start()->format('F j, Y') }}
							</p>
						</div>
						<div class="col">
							<p><strong>{{ trans('orders::orders.paid through') }}:</strong></p>
							<p>
								{{ $item->paiduntil ? $item->paiduntil->format("F j, Y") : trans('global.never') }}
							</p>
						</div>
						@if ($item->paiduntil != $item->billeduntil)
							<div class="col">
								<p><strong>{{ trans('orders::orders.billed through') }}:</strong></p>
								<p>
									{{ $item->billeduntil ? $item->billeduntil->format("F j, Y") : trans('global.never') }}
								</p>
							</div>
						@endif
					</div>
					@endif
				</div>
			</div>

			@if (count($items))
				<table class="table table-hover">
					<caption>{{ trans('orders::orders.order history') }}</caption>
					<thead>
						<tr>
							<th scope="col" class="priority-5">
								{{ trans('orders::orders.order') }}
							</th>
							<th scope="col" class="priority-4">
								{{ trans('orders::orders.status') }}
							</th>
							<th scope="col">
								{{ trans('orders::orders.quantity') }}
							</th>
							<th scope="col" class="priority-4" colspan="2">
								{{ trans('orders::orders.service') }}
							</th>
							<th scope="col" class="priority-4 text-right text-end">
								{{ trans('orders::orders.price') }}
							</th>
						</tr>
					</thead>
					<tbody>
					@php
						$total = 0;
					@endphp
					@foreach ($items as $i => $row)
						<tr>
							<td class="priority-5">
								<a href="{{ route('site.orders.read', ['id' => $row->orderid]) }}">
									{{ $row->orderid }}
								</a>
							</td>
							<td>
								@if ($row->isFulfilled())
									Paid
								@elseif ($row->order->isCanceled())
									Canceled
								@else
									Billed
								@endif
							</td>
							<td>
								{{ $row->quantity }}
							</td>
							<td class="priority-4">
								@if ($row->order->isCanceled())
									-
								@else
									{{ $row->start ? $row->start->format('Y-m-d') : '-' }}
								@endif
							</td>
							<td class="priority-4">
								@if ($row->order->isCanceled())
									-
								@else
									{{ $row->end ? $row->end->format('Y-m-d') : '-' }}
								@endif
							</td>
							<td class="text-right text-end">
								@if ($row->order->isCanceled())
									-
								@else
									$&nbsp;{{ $item->formatCurrency($row->price) }}
								@endif
								@php
									$total += $row->price;
								@endphp
							</td>
						</tr>
					@endforeach
					</tbody>
					<tfoot>
						<tr>
							<th scope="row" colspan="5" class="text-right text-end">
								<strong>Total</strong>
							</th>
							<td class="text-right text-end">
								$&nbsp;{{ $item->formatCurrency($total) }}
							</td>
						</tr>
					</tfoot>
				</table>
			@else
				<p class="alert alert-info">No orders found.</p>
			@endif
		</div>
		@csrf
	</form>
</div>
</div>
@stop