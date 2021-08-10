@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css?v=' . filemtime(public_path() . '/modules/orders/css/orders.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/orders/js/orders.js?v=' . filemtime(public_path() . '/modules/orders/js/orders.js')) }}"></script>
<script>
$(document).ready(function() { 
	$('.filter-submit').on('change', function(e){
		$(this).closest('form').submit();
	});
});
</script>
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
	);
@endphp

@section('content')
@component('orders::site.submenu')
	recur
@endcomponent
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	<h2 class="sr-only">{{ trans('orders::orders.recurring') }}</h2>

	<form action="{{ route('site.orders.recurring') }}" method="get" class="row">
		<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
			<fieldset class="filters mt-0">
				<legend class="sr-only">Filter</legend>

				<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
				<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

				<div class="form-group">
					<label for="filter_search">{{ trans('search.label') }}</label>
					<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('Find by ID, user, or group') }}" value="{{ $filters['search'] }}" />
				</div>

				<div class="form-group">
					<label for="filter_product">{{ trans('orders::orders.product') }}</label>
					<select name="product" id="filter_product" class="form-control filter filter-submit">
						<option value="*"<?php if ($filters['product'] == 0): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all products') }}</option>
						<?php
						$prev = 0;
						foreach ($products as $product) { ?>
							@if ($product->recurringtimeperiodid != $prev)
								@if ($prev)
									</optgroup>
								@endif
								@php
								$prev = $product->recurringtimeperiodid;
								@endphp
								<optgroup label="{{ $product->timeperiod->name }}">
							@endif
							<option value="<?php echo $product->id; ?>"<?php if ($filters['product'] == $product->id): echo ' selected="selected"'; endif;?>>{{ $product->name }}</option>
						<?php } ?>
					</select>
				</div>
			</fieldset>
		</div>
		<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">

			<div id="applied-filters" aria-label="Applied filters">
				<p class="sr-only">Applied Filters:</p>
				<ul class="filters-list">
					<?php
					$allfilters = collect($filters);
					$fkeys = ['search', 'product'];

					foreach ($fkeys as $key):
						if (!isset($filters[$key]) || $filters[$key] == '*'):
							continue;
						endif;

						$f = $allfilters
							->reject(function($v, $k) use ($key)
							{
								return (in_array($k, ['userid', 'limit', 'page', 'order', 'order_dir']));
							})
							->map(function($v, $k) use ($key)
							{
								if ($k == $key)
								{
									$v = '*';
									$v = ($k == 'search' ? '' : $v);
								}
								return $v;
							})
							->toArray();

						$val = $filters[$key];
						$val = ($val == '*' ? 'all' : $val);
						if ($key == 'product'):
							foreach ($products as $product):
								if ($val == $product->id):
									$val = $product->name;
									break;
								endif;
							endforeach;
						endif;
						?>
						<li>
							<strong>{{ trans('orders::orders.filters.' . $key) }}</strong>: {{ $val }}
							<a href="{{ route('site.orders.recurring', $f) }}" class="icon-remove filters-x" title="{{ trans('orders::orders.remove filter') }}">
								<span class="fa fa-times" aria-hidden="true"><span class="sr-only">{{ trans('orders::orders.remove filter') }}</span>
							</a>
						</li>
						<?php
					endforeach;
					?>
				</ul>
			</div>

			@if (count($rows))
				<table class="table table-hover mt-0">
					<caption class="sr-only">{{ trans('orders::orders.recurring items') }}</caption>
					<thead>
						<tr>
							<th scope="col" class="priority-5">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.id'), 'id', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col" class="priority-5">
								{{ trans('orders::orders.recurrence') }}
							</th>
							<th scope="col" class="priority-4">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.product'), 'product', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.paid until'), 'paiduntil', $filters['order_dir'], $filters['order']); ?><br />
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.billed until'), 'billeduntil', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col" class="priority-4">
								{{ trans('orders::orders.for') }}
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
								{{ $row->product->timeperiod->name }}
							</td>
							<td>
								{{ $row->product->name }}
							</td>
							<td class="priority-4">
								@if (!$row->start() || $row->start() == '0000-00-00 00:00:00')
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
				<div class="placeholder card text-center">
					<div class="placeholder-body card-body">
						<span class="fa fa-ban" aria-hidden="true"></span>
						<p>{{ trans('global.no results') }}</p>
					</div>
				</div>
			@endif
		</div>
		@csrf
	</form>
</div>
@stop