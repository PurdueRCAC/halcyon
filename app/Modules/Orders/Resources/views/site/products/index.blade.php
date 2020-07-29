@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css') }}" />
@stop

@section('scripts')
<script src="{{ asset('modules/orders/js/orders.js') }}"></script>
@stop

@section('title')
{!! config('orders.name') !!}: {{ trans('orders::orders.products') }}
@stop

@php
	app('pathway')
		->append(
			trans('orders::orders.orders'),
			route('site.orders.index')
		)
		->append(
			trans('orders::orders.products'),
			route('site.orders.products')
		);
@endphp

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
@component('orders::site.submenu')
	products
@endcomponent
</div>
<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div class="row">
		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
			<h2>{{ trans('orders::orders.products') }}</h2>
		</div>
		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 text-right">
			@if (auth()->user() && auth()->user()->can('manage orders'))
				<a href="{{ route('site.orders.products.create') }}" class="btn btn-primary">
					<i class="fa fa-plus"></i> {{ trans('orders::orders.create product') }}
				</a>
			@endif
		</div>
	</div>

<form action="{{ route('site.orders.products') }}" method="get" name="adminForm" id="adminForm">

	<?php $cat = null; ?>

		<div class="row">
			<div class="col col-md-8">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('orders::orders.search products') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-addon">
							<button class="btn btn-outline" type="submit"><i class="fa fa-search"></i></button>
						</span>
					</span>
				</div>
			</div>
			<div class="col col-md-4 text-right">
				<div class="form-group">
					<label class="sr-only" for="filter_category">{{ trans('orders::orders.category') }}</label>
					<select name="category" id="filter_category" class="form-control filter filter-submit">
						<option value="0"<?php if (!$filters['category']): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all categories') }}</option>
						<?php foreach ($categories as $category) { ?>
							<?php
							if ($filters['category'] == $category->id)
							{
								$cat = $category;
							}
							?>
							<option value="{{ $category->id }}"<?php if ($filters['category'] == $category->id): echo ' selected="selected"'; endif;?>>{{ $category->name }}</option>
						<?php } ?>
					</select>
				</div>
			</div>
		</div>

	<input type="hidden" name="order" value="{{ $filters['order'] }}" />
	<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
</form>

<form action="{{ route('site.orders.products') }}" method="post" name="adminForm" id="adminForm" class="form-inline">
	@if ($cat)
		<h3>{{ $cat->name }}</h3>
		<p>{{ $cat->description }}</p>
	@endif

	<table class="order-products">
		<thead>
			<tr>
				<th scope="col">
					{{ trans('orders::orders.name') }}
				</th>
				<th scope="col">
					{{ trans('orders::orders.quantity') }}
				</th>
				<th scope="col" class="text-right text-nowrap">
					{{ trans('orders::orders.price') }}
					/
					{{ trans('orders::orders.unit') }}
				</th>
				<th scope="col" class="text-right text-nowrap">
					{{ trans('orders::orders.subtotal') }}
				</th>
				@if (auth()->user() && auth()->user()->can('manage orders'))
					<th scope="col">
					</th>
				@endif
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $product)
			<tr<?php if (!$product->public) { echo ' class="orderproductitemprivate"'; } ?>>
				<td class="orderproductitem">
					<p>
						@if (!$product->public)
							<span class="badge badge-warning order-product-hidden">{{ trans('orders::orders.hidden') }}</span>
						@endif
						<b id="{{ $product->id }}_productname">{{ $product->name }}</b>
					</p>
					{{ $product->description }}
				</td>
				<td class="orderproductitem text-center">
					<input type="number" name="quantity[{{ $product->id }}][]" id="quantity_{{ $product->id }}" size="4" min="0" class="form-control quantity-input" value="" />
				</td>
				<td class="orderproductitem text-right text-nowrap">
					{{ $product->unitprice }}<br /> per {{ $product->unit }}
					<input type="hidden" id="{{ $product->id }}_price" value="{{ $product->unitprice }}" />
					<input type="hidden" id="{{ $product->id }}_category" value="{{ $product->ordercategory }}" />
				</td>
				<td class="orderproductitem text-right text-nowrap">
					<!-- <span class="input-group">
						<span class="input-group-addon"><span class="input-group-text">$</span></span>
						<input type="text" name="subtotal[{{ $product->id }}][]" id="{{ $product->id }}_linetotal" size="4" class="form-control total-input text-right" value="0.00" />
					</span> -->
					$ <span id="{{ $product->id }}_linetotal_text">0.00</span>
					<input type="hidden" name="subtotal[{{ $product->id }}][]" id="{{ $product->id }}_linetotal" class="form-control total-input text-right" value="0.00" />
				</td>
				@if (auth()->user() && auth()->user()->can('manage orders'))
				<td class="text-nowrap">
					<a href="{{ route('site.orders.products.edit', ['id' => $product->id]) }}" class="icn tip" title="{{ trans('global.edit') }}">
						<i class="fa fa-pencil"></i> {{ trans('global.edit') }}
					</a>

					<a href="{{ route('site.orders.products.delete', ['id' => $product->id]) }}" class="icn tip" title="{{ trans('global.delete') }}">
						<i class="fa fa-trash"></i> {{ trans('global.delete') }}
					</a>
					<!-- 
					<div class="btn-group btn-group-sm dropdown" role="group" aria-label="Options">
						<button type="button" class="btn dropdown-toggle" title="{{ trans('users::users.status approved') }}" id="btnGroupDrop{{ $product->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="fa fa-ellipsis-h" aria-hidden="true"></i> {{ trans('users::users.options') }}
						</button>
						<ul class="dropdown-menu" aria-labelledby="btnGroupDrop{{ $product->id }}">
							<li class="dropdown-item">
								<a href="{{ route('site.orders.products.edit', ['id' => $product->id]) }}" class="icn tip" title="{{ trans('global.edit') }}">
									<i class="fa fa-pencil" aria-hidden="true"></i> {{ trans('global.edit') }}
								</a>
							</li>
							<li class="divider"></li>
							<li class="dropdown-item">
								<a href="{{ route('site.orders.products.delete', ['id' => $product->id]) }}" class="icn tip" title="{{ trans('global.delete') }}">
									<i class="fa fa-trash" aria-hidden="true"></i> {{ trans('global.delete') }}
								</a>
							</li>
						</ul>
					</div> -->
				</td>
				@endif
			</tr>
		@endforeach
		</tbody>
		<tfoot>
			<tr>
				<td class="orderproductitem text-right" colspan="3">{{ trans('orders::orders.total') }}</td>
				<td class="orderproductitem text-right orderprice">$<span id="{{ $category->id }}_total">0.00</span></td>
				@if (auth()->user() && auth()->user()->can('manage orders'))
				<td></td>
				@endif
			</tr>
		</tfoot>
	</table>

	<div class="row">
		<div class="col-sm-9">
			{{ $rows->render() }}
		</div>
		<div class="col-sm-3 text-right">
			Results {{ ($rows->currentPage()-1)*$rows->perPage()+1 }}-{{ $rows->total() > $rows->perPage() ? $rows->currentPage()*$rows->perPage() : $rows->total() }} of {{ $rows->total() }}
		</div>
	</div>

	@csrf
</form>
</div>
@stop