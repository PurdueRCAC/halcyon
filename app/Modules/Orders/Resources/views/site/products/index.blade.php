@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css?v=' . filemtime(public_path() . '/modules/orders/css/orders.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/orders/js/orders.js?v=' . filemtime(public_path() . '/modules/orders/js/orders.js')) }}"></script>
<script>
// Force update of totals in case browswer is caching values
$(document).ready(function() { 
	// Add event listener for filters
	var filters = document.getElementsByClassName('filter-submit');
	for (i = 0; i < filters.length; i++)
	{
		filters[i].addEventListener('change', function(e){
			this.form.submit();
		});
	}

	// Enable/disable button when quantity changes
	$('.quantity-input').on('change', function(e) {
		var inp = $(this);
		if (inp.val() > 0) {
			$(inp.closest('tr')).find('.btn-secondary').prop('disabled', false);
		} else {
			$(inp.closest('tr')).find('.btn-secondary').prop('disabled', true);
		}
	});

	// Confirm deletion
	$('.btn-delete').on('click', function(e) {
		e.preventDefault();
		if (confirm($(this).data('conrim'))) {
			return true;
		}
		return false;
	});

	// Update something in the cart
	$('.btn-cart-update').on('click', function(e) {
		e.preventDefault();

		var btn = $(this),
			qty = $(btn.closest('tr')).find('.quantity-input')[0].value;

		if (!qty) {
			return;
		}

		btn.addClass('processing');

		$.ajax({
			url: btn.data('api'),
			type: 'PUT',
			data: {
				quantity: qty
			},
			dataType: 'json',
			async: false,
			success: function(response){
				updateCart(response);
				btn.removeClass('processing');
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(xhr.responseJSON.message);
				btn.removeClass('processing');
			}
		});
	});

	// Add to the cart
	$('.btn-cart-add').on('click', function(e) {
		e.preventDefault();

		var btn = $(this),
			qty = $(btn.closest('tr')).find('.quantity-input')[0].value;

		if (!qty) {
			return;
		}

		btn.addClass('processing');

		$.ajax({
			url: btn.data('api'),
			type: 'POST',
			data: {
				productid: btn.data('product'),
				quantity: qty
			},
			dataType: 'json',
			async: false,
			success: function(response){
				updateCart(response);

				for (var i=0; i < response.data.length; i++)
				{
					if (response.data[i].id == btn.data('product')) {
						btn.attr('data-api', response.data[i].api);
						btn.removeClass('btn-cart-add')
							.addClass('btn-cart-update')
							.text(btn.data('text-update'));
					}
				}

				btn.removeClass('processing');

				$('#' + btn.data('product') + "_product").addClass('selected');
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(xhr.responseJSON.message);
				btn.removeClass('processing');
			}
		});
	});

	// Update the cart display
	function updateCart(response) {
		var cart = $('#cart');
			cart.find('.cart-item').remove();

		var t = $(cart.find('.template')[0]);

		for (var i=0; i < response.data.length; i++)
		{
			var tmpl = t.clone();
			tmpl.removeClass('hide')
				.removeClass('template')
				.addClass('cart-item');

			var content = tmpl.html()
				.replace(/\{name\}/g, response.data[i].name)
				.replace(/\{price\}/g, response.data[i].price)
				.replace(/\{total\}/g, response.data[i].subtotal)
				.replace(/\{qty\}/g, response.data[i].qty);

			tmpl.html(content);

			cart.prepend(tmpl);
		}

		$('#order-total').text(response.total);
	}
});
</script>
@endpush

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

@component('orders::site.submenu')
	products
@endcomponent
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	<div class="row">
		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
			<h2 class="sr-only">{{ trans('orders::orders.products') }}</h2>
		</div>
		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 text-right">
			@if (auth()->user() && auth()->user()->can('manage orders'))
				<a href="{{ route('site.orders.products.create') }}" class="btn btn-secondary">
					<i class="fa fa-plus"></i> {{ trans('orders::orders.create product') }}
				</a>
			@endif
		</div>
	</div>
	<div class="row">
		
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<form action="{{ route('site.orders.products') }}" method="get" name="products" id="products">
		<div>
			<fieldset class="filters mt-0">
				<legend class="sr-only">Filter Results</legend>
				<?php $cat = null; ?>

				<div class="form-group">
					<label for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('orders::orders.search products') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append">
							<span class="input-group-text"><i class="fa fa-search"></i></span>
						</span>
					</span>
				</div>

				<div class="form-group">
					<label for="filter_category">{{ trans('orders::orders.category') }}</label>
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

				@if (auth()->user()->can('manage orders'))
				<div class="form-group">
					<label for="filter_restricteddata">{{ trans('orders::orders.restricted data') }}</label>
					<select name="restricteddata" id="filter_restricteddata" class="form-control filter filter-submit">
						<option value="*"<?php if ($filters['restricteddata'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all restricted data') }}</option>
						<option value="0"<?php if (!$filters['restricteddata']): echo ' selected="selected"'; endif;?>>{{ trans('global.no') }}</option>
						<option value="1"<?php if ($filters['restricteddata'] == 1): echo ' selected="selected"'; endif;?>>{{ trans('global.yes') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="filter_public">{{ trans('orders::orders.visibility') }}</label>
					<select name="public" id="filter_public" class="form-control filter filter-submit">
						<option value="*"<?php if ($filters['public'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all visibilities') }}</option>
						<option value="1"<?php if ($filters['public'] == 1): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.public') }}</option>
						<option value="0"<?php if (!$filters['public']): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.hidden') }}</option>
					</select>
				</div>
				@endif

				<button class="sr-only" type="submit">{{ trans('global.filter') }}</button>
			</fieldset>
		</div>
	</form>

	<div class="card">
		<ul class="list-group list-group-flush" id="cart" style="font-size: 90%">
			<?php
			$items = $cart->content();

			if (count($items))
			{
				foreach ($items as $item)
				{
					$product = App\Modules\Orders\Models\Product::find($item->id);
					?>
					<li class="list-group-item cart-item">
					<div class="cart-item row">
						<div class="col-md-12">{{ $product->name }}</div>
						<div class="col-md-7">
							<span class="text-muted text-sm">{{ $item->qty }} &times; $&nbsp;{{ $product->price }}</span>
						</div>
						<div class="col-md-5 text-right text-nowrap">
							$&nbsp;{{ number_format($item->total, 2) }}
						</div>
					</div>
					</li>
					<?php
				}
			}
			else
			{
				?>
				<li class="list-group-item cart-item cart-empty">
					<i class="fa fa-shopping-cart" aria-hidden="true"></i>
					Your cart is empty.
				</li>
				<?php
			}
			?>
			<li class="list-group-item template hide">
				<div class="row">
					<div class="col-md-12">{name}</div>
					<div class="col-md-7">
						<span class="text-muted text-sm">{qty} &times; $&nbsp;{price}</span>
					</div>
					<div class="col-md-5 text-right text-nowrap">
						$&nbsp;{total}
					</div>
				</div>
			</li>
			<li class="list-group-item">
				<div class="row">
					<div class="col-md-7">
						<strong class="text-sm">Total</strong>
					</div>
					<div class="col-md-5 text-right text-nowrap">
						$&nbsp;<span id="order-total" class="order-total">{{ $cart->total() }}</span>
					</div>
				</div>
			</li>
			<li class="list-group-item">
				<a href="{{ route('site.orders.cart') }}" class="btn btn-primary d-block">View Cart</a>
			</li>
		</ul>
	</div>
</div>
<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<form action="{{ route('site.orders.products') }}" method="post" name="adminForm" id="adminForm" class="form-iline">
		@if ($cat)
			<h3>{{ $cat->name }}</h3>
			<p class="mb-5">{{ $cat->description }}</p>
		@endif
		<table class="table order-products">
			<caption class="sr-only">{{ trans('orders::orders.products') }}</caption>
			<thead>
				<tr>
					<th scope="col">
						{{ trans('orders::orders.name') }}
					</th>
					<th scope="col" class="text-right text-nowrap">
						{{ trans('orders::orders.price') }}
						/
						{{ trans('orders::orders.unit') }}
					</th>
					<th scope="col">
						{{ trans('orders::orders.quantity') }}
					</th>
					<th scope="col">
					</th>
					@if (auth()->user() && auth()->user()->can('manage orders'))
						<th scope="col">
						</th>
						<th scope="col">
						</th>
					@endif
				</tr>
			</thead>
			<tbody>
			@foreach ($rows as $i => $product)
				<?php
				$found = null;
				foreach ($items as $item):
					if ($item->id == $product->id):
						$found = $item;
						break;
					endif;
				endforeach;

				$cls = array();
				if (!$product->public):
					$cls[] = 'orderproductitemprivate';
				endif;
				if ($found):
					$cls[] = 'selected';
				endif;
				?>
				<tr<?php if (!empty($cls)) { echo ' class="' . implode(' ', $cls) . '"'; } ?> id="{{ $product->id }}_product">
					<td class="orderproductitem">
						<p>
							@if (!$product->public)
								<span class="badge badge-warning order-product-hidden">{{ trans('orders::orders.hidden') }}</span>
							@endif
							<b id="{{ $product->id }}_productname">{{ $product->name }}</b><br />
							<span class="text-muted hide">{{ $product->category->name }}</span>
						</p>
						{{ $product->description }}

						@if ($product->mou || $product->restricteddata)
						<div>
						@if ($product->mou)
							<span class="badge badge-info">MOU</span>
						@endif
						@if ($product->restricteddata)
							<span class="badge badge-danger">Restricted data check</span>
						@endif
							</div>
						@endif
					</td>
					<td class="orderproductitem text-right text-nowrap">
						$&nbsp;{{ $product->price }}<br /> per {{ $product->unit }}
						<input type="hidden" id="{{ $product->id }}_price" value="{{ $product->unitprice }}" />
						<input type="hidden" id="{{ $product->id }}_category" value="{{ $product->ordercategoryid }}" />
					</td>
					<td class="orderproductitem text-center">
						<input type="number" name="quantity[{{ $product->id }}][]" id="{{ $product->id }}_quantity" data-id="{{ $product->id }}" size="4" min="0" class="form-control quantity-input" value="{{ $found ? $found->qty : 0 }}" />
					</td>
					<td class="orderproductitem text-right text-nowrap">
						@if (!$found)
							<span id="{{ $product->id }}_linetotal" class="hide">{{ $found ? $found->price() : 0.00 }}</span>
						
							<button class="btn btn-cart-add btn-secondary" disabled data-product="{{ $product->id }}" data-api="{{ route('api.orders.cart.create') }}" data-text-update="Update cart">
								Add to cart
								<span class="spinner-border spinner-border-sm" role="status"><span class="hide">Working...</span></span>
							</button>
						@else
							<button class="btn btn-cart-update btn-secondary" disabled data-product="{{ $product->id }}" data-api="{{ route('api.orders.cart.update', ['id' => $item->rowId]) }}">
								Update cart
								<span class="spinner-border spinner-border-sm" role="status"><span class="hide">Working...</span></span>
							</button>
						@endif
					</td>
					@if (auth()->user() && (auth()->user()->can('edit orders') || auth()->user()->can('delete orders')))
						@if (auth()->user()->can('edit orders'))
					<td class="text-nowrap">
						<a href="{{ route('site.orders.products.edit', ['id' => $product->id]) }}" class="btn btn-sm btn-edit tip" title="{{ trans('global.button.edit') }}">
							<i class="fa fa-pencil" aria-hidden="true"></i><span class="sr-only">{{ trans('global.button.edit') }}</span>
						</a>
					</td>
						@endif
						@if (auth()->user()->can('delete orders'))
					<td class="text-nowrap">
						<a href="{{ route('site.orders.products.delete', ['id' => $product->id]) }}" class="btn btn-sm btn-delete text-danger tip" title="{{ trans('global.button.delete') }}" data-confirm="{{ trans('global.confirm delete') }}">
							<i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">{{ trans('global.button.delete') }}</span>
						</a>
					</td>
						@endif
					@endif
				</tr>
			@endforeach
			</tbody>
			<tfoot>
				<tr class="hide">
					<td class="orderproductitem text-right" colspan="3">{{ trans('orders::orders.total') }}</td>
					<td class="orderproductitem text-right orderprice">$ <span id="ordertotal" class="category-total">0.00</span></td>
					@if (auth()->user() && auth()->user()->can('manage orders'))
					<td></td>
					<td></td>
					@endif
				</tr>
			</tfoot>
		</table>

		<input type="hidden" id="userid" value="<?php echo auth()->user() ? auth()->user()->id : 0; ?>" />

		@csrf
	</form>
</div>
	</div>
</div>
@stop