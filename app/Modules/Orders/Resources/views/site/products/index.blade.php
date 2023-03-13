@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css?v=' . filemtime(public_path() . '/modules/orders/css/orders.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/orders/js/orders.js?v=' . filemtime(public_path() . '/modules/orders/js/orders.js')) }}"></script>
<script src="{{ asset('modules/orders/js/cart.js?v=' . filemtime(public_path() . '/modules/orders/js/cart.js')) }}"></script>
@endpush

@section('title')
{!! trans('orders::orders.orders') !!}: {{ trans('orders::orders.products') }}
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
<div class="row">
@component('orders::site.submenu')
	products
@endcomponent

<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	<h2 class="sr-only">{{ trans('orders::orders.products') }}</h2>

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
								<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('orders::orders.search products') }}" value="{{ $filters['search'] }}" />
								<span class="input-group-append">
									<span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span>
								</span>
							</span>
						</div>

						<div class="form-group">
							<label for="filter_category">{{ trans('orders::orders.category') }}</label>
							<select name="category" id="filter_category" class="form-control filter filter-submit">
								<option value="*"<?php if ($filters['category'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all categories') }}</option>
								<?php foreach ($categories as $category) { ?>
									<?php
									if ($filters['category'] == $category->id):
										$cat = $category;
									endif;
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

					if (count($items)):
						foreach ($items as $item):
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
						endforeach;
					else:
						?>
						<li class="list-group-item cart-item cart-empty">
							<span class="fa fa-shopping-cart" aria-hidden="true"></span>
							Your cart is empty.
						</li>
						<?php
					endif;
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
						@if (auth()->user() && auth()->user()->enabled)
							<a href="{{ route('site.orders.cart') }}" class="btn btn-primary d-block">View Cart</a>
						@else
							<a href="{{ route('site.orders.cart') }}" class="btn btn-primary d-block disabled">View Cart</a>
						@endif
					</li>
				</ul>
			</div>
		</div>
		<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
			<form action="{{ route('site.orders.products') }}" method="post" name="adminForm" id="adminForm" class="form-iline">
				@if (auth()->user() && auth()->user()->can('manage orders'))
					<p class="text-right">
						<a href="{{ route('site.orders.products.create') }}" class="btn btn-info">
							<span class="fa fa-plus" aria-hidden="true"></span> {{ trans('orders::orders.create product') }}
						</a>
					</p>
				@endif

				<div id="applied-filters" aria-label="{{ trans('orders::orders.applied filters') }}">
					<p class="sr-only">{{ trans('orders::orders.applied filters') }}:</p>
					<ul class="filters-list">
						<?php
						$allfilters = collect($filters);
						$fkeys = ['search', 'category'];
						if (auth()->user()->can('manage orders')):
							$fkeys = ['search', 'category', 'restricteddata', 'public'];
						endif;

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
							if ($key == 'restricteddata'):
								$val = ($val == '*' ? trans('all restricted data') : $val);
								$val = ($val == 1 ? trans('global.yes') : $val);
								$val = (!$val ? trans('global.no') : $val);
							endif;
							if ($key == 'category'):
								foreach ($categories as $category):
									if ($val == $category->id):
										$val = $category->name;
										break;
									endif;
								endforeach;
							endif;
							if ($key == 'public'):
								$val = ($val == '*' ? trans('orders::orders.all visibilities') : $val);
								$val = ($val == 1 ? trans('orders::orders.public') : $val);
								$val = (!$val ? trans('orders::orders.hidden') : $val);
							endif;
							?>
							<li>
								<strong>{{ trans('orders::orders.filters.' . $key) }}</strong>: {{ $val }}
								<a href="{{ route('site.orders.products', $f) }}" class="icon-remove filters-x" title="{{ trans('orders::orders.remove filter') }}">
									<span class="fa fa-times" aria-hidden="true"><span class="sr-only">{{ trans('orders::orders.remove filter') }}</span>
								</a>
							</li>
							<?php
						endforeach;
						?>
					</ul>
				</div>

				@if ($cat)
					<h3>{{ $cat->name }}</h3>
					<p class="mb-5">{{ $cat->description }}</p>
				@endif

				@if (count($rows))
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
								{{ trans('orders::orders.purchase') }}
							</th>
							@if (auth()->user() && auth()->user()->can('manage orders'))
								<th scope="col" class="text-right"<?php if (auth()->user()->can('edit orders') && auth()->user()->can('delete orders')) { echo ' colspan="2"'; } ?>>
									Options
								</td>
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
									<strong id="{{ $product->id }}_productname">{{ $product->name }}</strong>
									@if (auth()->user() && auth()->user()->can('manage orders'))
									<br />
									<span class="text-muted">
										<span class="fa fa-folder-o" aria-hidden="true"></span>
										{{ $product->category->name }}
									</span>
									@endif
								</p>
								<p class="mt-0">
									{{ $product->description }}
								</p>
								@if ($product->mou || $product->restricteddata)
									<div>
										@if ($product->mou)
											<span class="badge badge-info tip" title="{{ trans('orders::orders.mou') }}: Usage of this product requires an agreement to conform to certain guidelines.">MOU</span>
										@endif
										@if ($product->restricteddata)
											<span class="badge badge-danger tip" title="{{ trans('orders::orders.restricted data explanation') }}">Restricted data check</span>
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
								<label class="sr-only" for="{{ $product->id }}_quantity">Quantity</label>
								@if (auth()->user() && auth()->user()->enabled)
									<input type="number" name="quantity[{{ $product->id }}][]" id="{{ $product->id }}_quantity" data-id="{{ $product->id }}" size="4" min="0" class="form-control quantity-input" value="{{ $found ? $found->qty : 1 }}" />
								@else
									<input type="number" name="quantity[{{ $product->id }}][]" id="{{ $product->id }}_quantity" data-id="{{ $product->id }}" size="4" min="0" class="form-control quantity-input" value="{{ $found ? $found->qty : 1 }}" disabled />
								@endif
							</td>
							<td class="orderproductitem text-right text-nowrap">
								@if (auth()->user() && auth()->user()->enabled)
								@if (!$found)
									<span id="{{ $product->id }}_linetotal" class="hide">{{ $found ? $found->price() : 0.00 }}</span>
									<button class="btn btn-cart-add btn-secondary" data-product="{{ $product->id }}" data-api="{{ route('api.orders.cart.create') }}" data-text-update="Update cart">
										Add to cart
										<span class="spinner-border spinner-border-sm" role="status"><span class="hide">Working...</span></span>
									</button>
								@else
									<button class="btn btn-cart-update btn-secondary" disabled data-product="{{ $product->id }}" data-api="{{ route('api.orders.cart.update', ['id' => $item->rowId]) }}">
										Update cart
										<span class="spinner-border spinner-border-sm" role="status"><span class="hide">Working...</span></span>
									</button>
								@endif
								@else
									<span id="{{ $product->id }}_linetotal" class="hide">{{ $found ? $found->price() : 0.00 }}</span>
									<button class="btn btn-cart-add btn-secondary" disabled data-product="{{ $product->id }}" data-api="{{ route('api.orders.cart.create') }}" data-text-update="Update cart">
										Add to cart
										<span class="spinner-border spinner-border-sm" role="status"><span class="hide">Working...</span></span>
									</button>
								@endif
							</td>
							@if (auth()->user() && auth()->user()->can('manage orders'))
								@if (auth()->user()->can('edit orders'))
							<td class="text-nowrap">
								<a href="{{ route('site.orders.products.edit', ['id' => $product->id]) }}" class="btn btn-sm btn-edit tip" title="{{ trans('global.button.edit') }} '{{ $product->name }}' product">
									<span class="fa fa-pencil" aria-hidden="true"></span><span class="sr-only">{{ trans('global.button.edit') }} '{{ $product->name }}' product</span>
								</a>
							</td>
								@endif
								@if (auth()->user()->can('delete orders'))
							<td class="text-nowrap">
								<a href="{{ route('site.orders.products.delete', ['id' => $product->id]) }}" class="btn btn-sm btn-delete text-danger tip" title="{{ trans('global.button.delete') }} '{{ $product->name }}' product" data-confirm="{{ trans('global.confirm delete') }}">
									<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.button.delete') }} '{{ $product->name }}' product</span>
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
				@else
					<div class="placeholder card text-center">
						<div class="placeholder-body card-body">
							<span class="fa fa-ban" aria-hidden="true"></span>
							<p>{{ trans('global.no results') }}</p>
						</div>
					</div>
				@endif

				<input type="hidden" id="userid" value="<?php echo auth()->user() ? auth()->user()->id : 0; ?>" />

				@csrf
			</form>
		</div>
	</div>
</div>
</div>
@stop
