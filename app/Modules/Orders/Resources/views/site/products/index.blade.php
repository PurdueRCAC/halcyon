@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/orders/js/orders.js') }}"></script>
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

	//UpdateOrderTotal(this);

	/*$('.quantity-input').on('change', function(e) {
		UpdateOrderTotal(this);
	});
	$('.total-input').on('change', function(e) {
		UpdateOrderTotal(this, true);
	});*/

	$('.btn-delete').on('click', function(e) {
		e.preventDefault();
		if (confirm($(this).data('conrim'))) {
			return true;
		}
		return false;
	});

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
				console.log('Failed to update cart.');
				btn.removeClass('processing');
			}
		});
	});

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
			},
			error: function(xhr, ajaxOptions, thrownError) {
				console.log('Failed to add to cart.');
				btn.removeClass('processing');
			}
		});
	});

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
							<span class="text-muted text-sm">{{ $item->qty }} &times; $ {{ $product->price }}</span>
						</div>
						<div class="col-md-5 text-right text-nowrap">
							$&nbsp;{{ number_format($item->total, 2) }}
						</div>
					</div>
					</li>
					<?php
				}
			}
			?>
			<li class="list-group-item template hide">
				<div class="row">
					<div class="col-md-12">{name}</div>
					<div class="col-md-7">
						<span class="text-muted text-sm">{qty} &times; $ {price}</span>
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
	<!-- 
	<table>
		<tbody>
			<?php
			foreach ($items as $item)
			{
				$product = App\Modules\Orders\Models\Product::find($item->id);
				?>
				<tr>
					<th scope="row">{{ $product->name }}<br /><span class="text-muted text-sm">{{ $item->qty }} &times; {{ $product->price }} {{ $product->unit }}</span></th>
					<td class="text-right text-nowrap">$ {{ number_format($item->total, 2) }}</td>
				</tr>
				<?php
			}
			?>
		</tbody>
		<tfoot>
			<tr>
				<td>Total</td>
				<td class="text-right text-nowrap"><strong>$ {{ $cart->total() }}</strong></td>
			</tr>
		</tfoot>
	</table>
	<p class="text-center"><a href="{{ route('site.orders.cart') }}" class="btn btn-primary d-block">View Cart</a></p> -->
</div>
<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">

	<?php /*<form action="{{ route('site.orders.products') }}" method="get" name="products" id="products">
		<div>
			<fieldset class="filters">
				<legend class="sr-only">Filter Results</legend>
				<?php $cat = null; ?>

				<div class="row">
					<div class="col">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('orders::orders.search products') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append">
							<span class="input-group-text"><i class="fa fa-search"></i></span>
						</span>
					</span>
				</div>
					</div>
					<div class="col">
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
					<div class="col">
				<div class="form-group">
					<label class="sr-only" for="filter_public">{{ trans('orders::orders.visibility') }}</label>
					<select name="public" id="filter_public" class="form-control filter filter-submit">
						<option value="*"<?php if ($filters['public'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all visibilities') }}</option>
						<option value="1"<?php if ($filters['public'] == 1): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.public') }}</option>
						<option value="0"<?php if (!$filters['public']): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.hidden') }}</option>
					</select>
				</div>
					</div>
				</div>
				<button class="sr-only" type="submit">{{ trans('global.filter') }}</button>
			</fieldset>
		</div>
	</form>*/ ?>
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
				<th scope="col" class="text-right text-nowrap">
					<!-- {{ trans('orders::orders.subtotal') }} -->
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
					{{ $product->price }}<br /> per {{ $product->unit }}
					<input type="hidden" id="{{ $product->id }}_price" value="{{ $product->unitprice }}" />
					<input type="hidden" id="{{ $product->id }}_category" value="{{ $product->ordercategoryid }}" />
				</td>
				<td class="orderproductitem text-center">

					<input type="number" name="quantity[{{ $product->id }}][]" id="{{ $product->id }}_quantity" data-id="{{ $product->id }}" size="4" min="0" class="form-control quantity-input" value="{{ $found ? $found->qty : 0 }}" />

				</td>
				<td class="orderproductitem text-right text-nowrap">
					@if (!$found)
						<?php /*@if (auth()->user() && auth()->user()->can('manage orders'))
							<span class="form-inline">
								<span class="input-group">
									<span class="input-group-prepend"><span class="input-group-text">$</span></span><input type="text" name="subtotal[{{ $product->id }}][]" id="{{ $product->id }}_linetotal" size="6" class="form-control total-input text-right" value="{{ $found ? $found->price() : 0.00 }}" />
								</span>
							</span>
						@else
							$&nbsp;<span id="{{ $product->id }}_linetotal">{{ $found ? $found->price() : 0.00 }}</span>
						@endif*/ ?>
						<span id="{{ $product->id }}_linetotal" class="hide">{{ $found ? $found->price() : 0.00 }}</span>
					
						<button class="btn btn-cart-add btn-secondary" data-product="{{ $product->id }}" data-api="{{ route('api.orders.cart.create') }}" data-text-update="Update cart">
							Add to cart
							<span class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Working...</span></span>
						</button>
					@else
						<button class="btn btn-cart-update btn-secondary" data-product="{{ $product->id }}" data-api="{{ route('api.orders.cart.update', ['id' => $item->rowId]) }}">
							Update cart
							<span class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Working...</span></span>
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
					<!-- 
					<div class="btn-group dropdown" role="group" aria-label="Options">
						<button type="button" class="btn dropdown-toggle" title="{{ trans('users::users.status approved') }}" id="btnGroupDrop{{ $product->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="fa fa-ellipsis-h" aria-hidden="true"></i><span class="sr-only">{{ trans('orders::orders.options') }}</span>
						</button>
						<ul class="dropdown-menu" aria-labelledby="btnGroupDrop{{ $product->id }}">
							@if (auth()->user()->can('edit orders'))
							<li class="dropdown-item">
								<a href="{{ route('site.orders.products.edit', ['id' => $product->id]) }}" title="{{ trans('global.edit') }}">
									<i class="fa fa-pencil" aria-hidden="true"></i> {{ trans('global.edit') }}
								</a>
							</li>
							@endif
							@if (auth()->user()->can('delete orders'))
							<li class="divider"></li>
							<li class="dropdown-item">
								<a href="{{ route('site.orders.products.delete', ['id' => $product->id]) }}" title="{{ trans('global.delete') }}">
									<i class="fa fa-trash" aria-hidden="true"></i> {{ trans('global.delete') }}
								</a>
							</li>
							@endif
						</ul>
					</div> -->
				</td>
					@endif
				@endif
			</tr>
		@endforeach
		</tbody>
		<tfoot>
			<!--<tr>
				<td class="orderproductitem text-right" colspan="3">{{ trans('orders::orders.total') }}</td>
				<td class="orderproductitem text-right orderprice">$<span id="{{ $category->id }}_total" class="category-total">0.00</span></td>
				@if (auth()->user() && auth()->user()->can('manage orders'))
				<td></td>
				@endif
			</tr>-->
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

	<?php /*
	@foreach ($rows as $i => $product)
		<div class="card panel product<?php if (!$product->public) { echo ' private'; } ?>" id="product{{ $product->id }}">
			<div class="card-body panel-body">
				<div class="row">
					<div class="col-md-6">
						<p class="card-title">
							@if (!$product->public)
								<span class="badge badge-warning order-product-hidden">{{ trans('orders::orders.hidden') }}</span>
							@endif
							<b id="{{ $product->id }}_productname">{{ $product->name }}</b>
						</p>
						{{ $product->description }}
					</div>
					<div class="col-md-2 text-center">
						<input type="number" name="quantity[{{ $product->id }}][]" id="quantity_{{ $product->id }}" size="4" min="0" class="form-control quantity-input" value="0" />
					</div>
					<div class="col-md-2 text-right text-nowrap">
						{{ $product->price }}<br /> per {{ $product->unit }}
						<input type="hidden" id="{{ $product->id }}_price" value="{{ $product->unitprice }}" />
						<input type="hidden" id="{{ $product->id }}_category" value="{{ $product->ordercategory }}" />
					</div>
					<div class="col-md-2 text-right text-nowrap">
					<!-- <span class="input-group">
						<span class="input-group-addon"><span class="input-group-text">$</span></span>
						<input type="text" name="subtotal[{{ $product->id }}][]" id="{{ $product->id }}_linetotal" size="4" class="form-control total-input text-right" value="0.00" />
					</span> -->
						$ <span id="{{ $product->id }}_linetotal_text">0.00</span>
						<input type="hidden" name="subtotal[{{ $product->id }}][]" id="{{ $product->id }}_linetotal" class="form-control total-input text-right" value="0.00" />
					</div>
				</div>
			</div>
				@if (auth()->user() && (auth()->user()->can('edit orders') || auth()->user()->can('delete orders')))
				<div class="card-footer">
					<p>
					@if (auth()->user()->can('edit orders'))
				
					<a href="{{ route('site.orders.products.edit', ['id' => $product->id]) }}" class="icn tip" title="{{ trans('global.edit') }}">
						<i class="fa fa-pencil"></i> {{ trans('global.edit') }}
					</a>

					@endif
					@if (auth()->user()->can('delete orders'))

					<a href="{{ route('site.orders.products.delete', ['id' => $product->id]) }}" class="icn tip" title="{{ trans('global.delete') }}">
						<i class="fa fa-trash"></i> {{ trans('global.delete') }}
					</a>
					<!-- 
					<div class="btn-group dropdown" role="group" aria-label="Options">
						<button type="button" class="btn dropdown-toggle" title="{{ trans('users::users.status approved') }}" id="btnGroupDrop{{ $product->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="fa fa-ellipsis-h" aria-hidden="true"></i><span class="sr-only">{{ trans('orders::orders.options') }}</span>
						</button>
						<ul class="dropdown-menu" aria-labelledby="btnGroupDrop{{ $product->id }}">
							@if (auth()->user()->can('edit orders'))
							<li class="dropdown-item">
								<a href="{{ route('site.orders.products.edit', ['id' => $product->id]) }}" title="{{ trans('global.edit') }}">
									<i class="fa fa-pencil" aria-hidden="true"></i> {{ trans('global.edit') }}
								</a>
							</li>
							@endif
							@if (auth()->user()->can('delete orders'))
							<li class="divider"></li>
							<li class="dropdown-item">
								<a href="{{ route('site.orders.products.delete', ['id' => $product->id]) }}" title="{{ trans('global.delete') }}">
									<i class="fa fa-trash" aria-hidden="true"></i> {{ trans('global.delete') }}
								</a>
							</li>
							@endif
						</ul>
					</div> -->
					</p>
					@endif
					</div>
				@endif
			</div>
		@endforeach

	
	<div class="row">
		<div class="col-sm-9">
			{{ $rows->render() }}
		</div>
		<div class="col-sm-3 text-right">
			Results {{ ($rows->currentPage()-1)*$rows->perPage()+1 }}-{{ $rows->total() > $rows->perPage() ? $rows->currentPage()*$rows->perPage() : $rows->total() }} of {{ $rows->total() }}
		</div>
	</div>
	*/ 
	
	/*?>

	<div id="forme" class="cancellable stash">
		<p>
			Are you placing this order on behalf of a faculty member?
			<a href="#help1" class="help icn tip" title="Help">
				<i class="fa fa-question-circle" aria-hidden="true"></i><span class="sr-only">Help</span>
			</a>
		</p>

		<div id="help1" class="dialog dialog-help" title="Placing order on behalf of someone else">
			<p>You may place an order on behalf of another person. To do so select "Yes" and you will be prompted to search for this person.</p>
			<p>If you are placing this order for yourself click "No" and then "Continue".</p>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="form-block">
					<div class="form-check">
						<input class="form-check-input" type="radio" id="formeyes" value="Yes" name="forme" />
						<label class="form-check-label" for="formeyes">{{ trans('global.yes') }}</label>
						<span class="form-text text-muted">If you are a graduate student please answer "Yes".</span>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-block">
					<div class="form-check">
						<input class="form-check-input" type="radio" id="formeno" value="No" name="forme" />
						<label class="form-check-label" for="formeno">{{ trans('global.no') }}</label>
						<span class="form-text text-muted">If you are placing this order for yourself select "No".</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="usersearch" class="cancellable stash">
		<p>Please use the search box below to select the faculty member this order is for:
			<a href="#help2" class="help icn tip" title="Help">
				<i class="fa fa-question-circle" aria-hidden="true"></i><span class="sr-only">Help</span>
			</a>
		</p>

		<div id="help2" class="dialog dialog-help" title="Placing order on behalf of someone else">
			<p>Use this box to search for the person you are placing this order on behalf of. You may search by full name, email address, or Career account username. Once found click the person from the results to fill in the box.</p>
		</div>

		<div class="form-group">
			<span class="input-group">
				<input type="text" name="search" id="search_user" class="form-control" data-api-create="{{ route('api.users.create') }}" data-api="{{ route('api.users.index') }}?search=%s" placeholder="Search users..." value="" />
				<span class="input-group-addon">
					<i class="fa fa-user"></i>
				</span>
			</span>
		</div>
	</div>

	<div id="mouagree" class="cancellable stash">
		<div><p>Please read and consent to the MOU Agreement for the following items.</p></div>

		<div id="help3" class="dialog dialog-help" title="MOU Agreement">
			<p>Please use the link provided to download the MOU Agreement. Please read the Agreement carefully and click the checkbox once you have read and consent to the Agreement.</p>
		</div>
		<?php
		foreach ($categories as $subcat)
		{
			if ($filters['category'] && $filters['category'] != $subcat->id)
			{
				continue;
			}

			$products = $subcat->products()
				->whereIn('public', (auth()->user() ? auth()->user()->getAuthorisedViewLevels() : [1]))
				->orderBy('sequence', 'asc')
				->get();

			foreach ($products as $product)
			{
				if (!$product->mou)
				{
					continue;
				}
				?>
				<div id="<?php echo $product->id; ?>_mou" class="stash">
					<div class="form-block">
						<div class="form-check">
							<input class="form-check-input mou-agree" type="checkbox" data-id="<?php echo $product->id; ?>" id="<?php echo $product->id; ?>_mouagree" />
							<label class="form-check-label" for="<?php echo $product->id; ?>_mouagree">I have read and consent to the MOU Agreement.</label>
							<p class="form-text text-muted">
								<a href="<?php echo $product->mou; ?>" target="_blank"><?php echo $product->name; ?> - MOU Agreement</a>

								<a href="#help3" class="help icn tip" title="Help">
									<i class="fa fa-question-circle" aria-hidden="true"></i><span class="sr-only">Please click checkbox after reading and consenting to MOU Agreement.</span>
								</a>
							</p>
						</div>
					</div>
				</div>
				<?php
			}
		}
		?>
	</div>

	<div id="restrictagree" class="cancellable" style="display: none;">
		<p>Will you be using any of the selected products for restricted data?</p>

		<?php
		foreach ($categories as $subcat)
		{
			if ($filters['category'] && $filters['category'] != $subcat->id)
			{
				continue;
			}

			$products = $subcat->products()
				->whereIn('public', (auth()->user() ? auth()->user()->getAuthorisedViewLevels() : [1]))
				->orderBy('sequence', 'asc')
				->get();

			foreach ($products as $product)
			{
				if (!$product->restricteddata)
				{
					continue;
				}
				?>
				<fieldset id="{{ $product->id }}_restrict" style="display: none;">
					<legend>{{ $product->name }} restricted data use. Please check all that apply:</legend>

					<div class="form-check">
						<input class="form-check-input restrict-agree" type="checkbox" data-id="{{ $product->id }}" data-dialog="#help_restrictagree1" id="{{ $product->id }}_restrictagree1" />
						<label class="form-check-label" for="{{ $product->id }}_restrictagree1">Government restricted or export-controlled data (eg., ITAR, EAR, 7012)</label>

						<div id="help_restrictagree1" class="dialog dialog-help" title="Government restricted data">
							<p>Storing or use of government restricted or export controlled data is prohibited.</p>
						</div>
					</div>

					<div class="form-check">
						<input class="form-check-input restrict-agree" type="checkbox" data-id="{{ $product->id }}" id="{{ $product->id }}_restrictagree2" />
						<label class="form-check-label" for="{{ $product->id }}_restrictagree2">IRB restricted data</label>
					</div>

					<div class="form-check">
						<input class="form-check-input restrict-agree" type="checkbox" data-id="{{ $product->id }}" id="{{ $product->id }}_restrictagree3" />
						<label class="form-check-label" for="{{ $product->id }}_restrictagree3">HIPAA restricted data</label>
					</div>

					<div class="form-check">
						<input class="form-check-input restrict-agree" type="checkbox" data-id="{{ $product->id }}" id="{{ $product->id }}_restrictagree4" />
						<label class="form-check-label" for="{{ $product->id }}_restrictagree4">FERPA restricted data</label>
					</div>
				</fieldset>
				<?php
			}
		}
		?>
	</div>

	<div class="text-center">
		<input id="continue" class="order btn btn-primary" type="submit" value="Continue"
			data-api="{{ route('api.orders.create') }}"
			data-error="There was an error processing your order. Please wait a few minutes and try again or contact rcac-help@purdue.edu for help." />
		<input id="cancel" class="order btn btn-secondary" type="submit" value="Edit Order" style="display: none" />
	</div>
	*/ ?>

	<input type="hidden" id="userid" value="<?php echo auth()->user() ? auth()->user()->id : 0; ?>" />

	@csrf
</form>
</div>
	</div>
	</div>
@stop