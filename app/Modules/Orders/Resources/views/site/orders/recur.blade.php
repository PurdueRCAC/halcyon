@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/orders/css/orders.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/orders/js/orders.js') }}"></script>
@endpush

@section('title'){{ trans('orders::orders.orders') }: {{ trans('orders::orders.recurring') }}@stop

@php
app('pathway')
	->append(
		trans('orders::orders.orders'),
		route('site.orders.index')
	)
	->append(
		trans('orders::orders.recurring'),
		route('site.orders.recurring')
	);
@endphp

@section('content')
<div class="row">
@component('orders::site.submenu')
	recur
@endcomponent
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	<h2 class="sr-only visually-hidden">{{ trans('orders::orders.recurring') }}</h2>

	<form action="{{ route('site.orders.recurring') }}" method="get" class="row">
		<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
			<fieldset class="filters mt-0">
				<legend class="sr-only visually-hidden">Filter</legend>

				<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
				<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

				<div class="form-group">
					<label for="filter_search">{{ trans('search.label') }}</label>
					<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
				</div>

				<div class="form-group">
					<label for="filter_product">{{ trans('orders::orders.product') }}</label>
					<select name="product" id="filter_product" class="form-control filter filter-submit">
						<option value="*"<?php if ($filters['product'] == 0): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all products') }}</option>
						<?php foreach ($products as $product) { ?>
							<option value="<?php echo $product->id; ?>"<?php if ($filters['product'] == $product->id): echo ' selected="selected"'; endif;?>>{{ $product->name }}</option>
						<?php } ?>
					</select>
				</div>
			</fieldset>
		</div>
		<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
			@if (count($rows))
				<table class="table table-hover mt-0">
					<caption class="sr-only visually-hidden">{{ trans('orders::orders.recurring items') }}</caption>
					<thead>
						<tr>
							<th scope="col" class="priority-5">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.id'), 'id', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col" class="priority-4">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.product'), 'product', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.paid until'), 'paiduntil', $filters['order_dir'], $filters['order']); ?><br />
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.billed until'), 'billeduntil', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col" class="priority-4">
								{{ trans('orders::orders.submitter') }}
							</th>
						</tr>
					</thead>
					<tbody>
					@foreach ($rows as $i => $row)
						<tr>
							<td class="priority-5">
								@if (auth()->user()->can('edit orders') || (auth()->user()->can('edit.own orders') && $row->userid == auth()->user()->id))
									<a href="{{ route('site.orders.recurring.read', ['id' => $row->id]) }}" data-orderid="{{ $row->orderid }}" data-origorderitemid="{{ $row->origorderitemid }}">
										{{ $row->id }}
									</a>
								@else
									{{ $row->id }}
								@endif
							</td>
							<td>
								{{ $row->product->name }}
							</td>
							<td class="priority-4">
								@if (!$row->start())
									{{ trans('Order Pending') }}
								@else
									{{ $row->paiduntil ? $row->paiduntil->format("F j, Y") : trans('global.never') }}

									@if ($row->paiduntil != $row->billeduntil)
										@if ($row->product->timeperiod->name == 'annual')
											<br/>(billed to {{ $row->billeduntil ? $row->billeduntil->format("Y") : trans('global.never') }})
										@elseif ($row->product->timeperiod->name == 'monthly')
											<br/>(billed to {{ $row->billeduntil ? $row->billeduntil->format("F") : trans('global.never') }})
										@else
											<br/>(billed to {{ $row->billeduntil ? $row->billeduntil->format("M j") : trans('global.never') }})
										@endif
									@endif
								@endif
							</td>
							<td class="priority-4">
								@if ($row->order->groupid)
									@if (auth()->user()->can('manage groups'))
										<a href="{{ route('site.groups.show', ['id' => $row->order->groupid]) }}">
											<?php echo $row->order->group ? $row->order->group->name : ' <span class="unknown">' . trans('global.unknown') . '</span>'; ?>
										</a>
									@else
										<?php echo $row->order->group ? $row->order->group->name : ' <span class="unknown">' . trans('global.unknown') . '</span>'; ?>
									@endif
								@else
									@if (auth()->user()->can('manage users'))
										<a href="{{ route('site.users.account', ['u' => $row->order->userid]) }}">
											<?php echo $row->order->user ? $row->order->user->name : ' <span class="unknown">' . trans('global.unknown') . '</span>'; ?>
										</a>
									@else
										<?php echo $row->order->user ? $row->order->user->name : ' <span class="unknown">' . trans('global.unknown') . '</span>'; ?>
									@endif
								@endif
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>

				{{ $rows->render() }}
			@else
				<p class="alert alert-info">No orders found.</p>
			@endif
		</div>
		@csrf
	</form>
</div>
</div>
@stop