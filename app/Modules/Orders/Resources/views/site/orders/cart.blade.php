@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css?v=' . filemtime(public_path() . '/modules/orders/css/orders.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/orders/js/orders.js?v=' . filemtime(public_path() . '/modules/orders/js/orders.js')) }}"></script>
<script>
	// Force update of totals in case browswer is caching values
	$(document).ready(function() { 
		$('#continue').on('click', function(e){
			e.preventDefault();
			ForMe();
		});
		$('#cancel').on('click', function(e){
			e.preventDefault();
			CancelMou();
		});

		// Add event listener for filters
		var filters = document.getElementsByClassName('filter-submit');
		for (i = 0; i < filters.length; i++)
		{
			filters[i].addEventListener('change', function(e){
				this.form.submit();
			});
		}

		UpdateOrderTotal(this);

		var autocompleteName = function(url) {
			return function(request, response) {
				console.log(url.replace('%s', encodeURIComponent(request.term)));
				return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
					response($.map(data.data, function (el) {
						return {
							label: el.name,
							name: el.name,
							id: el.id,
							username: el.username
							//priorusernames: el.priorusernames
						};
					}));
				});
			};
		};
		$("#search_user").autocomplete({
			source: autocompleteName($("#search_user").data('api')),
			dataName: 'users',
			height: 150,
			delay: 100,
			minLength: 2,
			select: function (event, ui) {
				event.preventDefault();
				var thing = ui['item'].label;
				/*if (typeof(ui['item'].usernames) != 'undefined') {
					thing = thing + " (" + ui['item'].usernames[0]['name'] + ")";
				} else if (typeof(ui['item'].priorusernames) != 'undefined') {
					thing = thing + " (" + ui['item'].priorusernames[0]['name'] + ")";
				}*/
				if (typeof(ui['item'].username) != 'undefined') {
					thing = thing + " (" + ui['item'].username + ")";
				} else if (typeof(ui['item'].priorusername) != 'undefined') {
					thing = thing + " (" + ui['item'].priorusername + ")";
				}
				$("#search_user").val( thing );
				//$("#search_user").data('userid', ui['item'].id);
			},
			create: function () {
				$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
					var thing = item.label;
					/*if (typeof(item.usernames) != 'undefined') {
						thing = thing + " (" + item.usernames[0]['name'] + ")";
					} else if (typeof(item.priorusernames) != 'undefined') {
						thing = thing + " (" + item.priorusernames[0]['name'] + ")";
					}*/
					if (typeof(item.username) != 'undefined') {
						thing = thing + " (" + item.username + ")";
					} else if (typeof(item.priorusername) != 'undefined') {
						thing = thing + " (" + item.priorusername + ")";
					}
					return $( "<li>" )
						.append( $( "<div>" ).text( thing ) )
						.appendTo( ul );
				};
			}
		});
		$("#search_user").on("autocompleteselect", SearchEventHandler);
		/*$("#search_user").on("change", function(e) {
			ValidateForMe();
		});*/
		$('#formeyes,#formno').on('click', function(e) {
			OpenUserSearch();
		});

		$('.quantity-input').on('change', function(e) {
			UpdateOrderTotal(this);
		});
		$('.total-input').on('change', function(e) {
			UpdateOrderTotal(this, true);
		});

		$('.form-block-radio').on('click', function(e) {
			$(this).find('input[type="radio"]').not(':checked').prop("checked", true);

			var c = $(this).find('input[type="checkbox"]');
			if (c.length){
				c.each(function() {
					var el = $(this);
					if (!el.prop("checked")) {
						el.prop("checked", true);
					} else {
						el.prop("checked", false);
					}
				});
				$(this).toggleClass('checked');
			} else {
				$('.form-block').removeClass('checked');
				$(this).addClass('checked');
			}
		});
		$('.form-block-check input[type="checkbox"]').on('change', function(e) {
			if ($(this).is(':checked')) {
				$(this).closest('.form-block').addClass('checked');
			} else {
				$(this).closest('.form-block').removeClass('checked');
			}
		});

		$('.btn-cart-remove').on('click', function(e) {
			e.preventDefault();

			var btn = $(this);

			$.ajax({
				url: btn.data('api'),
				type: 'DELETE',
				dataType: 'json',
				async: false,
				success: function(response){
					$(btn.data('item')).remove();

					$('#ordertotal').text(response.total);

					// Disable the 'continue' button if the cart is empty
					if ($('.cart-item').length <= 0) {
						$('#continue').prop('disabled', true);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					console.log('Failed to update member type.');
				}
			});
		});
	});
</script>
@endpush

@section('title'){{ trans('orders::orders.cart') }}@stop

@php
	app('pathway')
		->append(
			trans('orders::orders.orders'),
			route('site.orders.index')
		)
		->append(
			trans('orders::orders.cart'),
			route('site.orders.cart')
		);
@endphp

@section('content')
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	<div class="row">
		<div class="col-md-6">
			<h2 class="mt-0">{{ trans('orders::orders.cart') }}</h2>
		</div>
		<div class="col-md-6 text-right">
			<a class="btn btn-link" href="{{ route('site.orders.products') }}"><span class="fa fa-arrow-left" aria-hidden="true"></span> Back to Products</a>
		</div>
	</div>

	<form action="{{ route('site.orders.index') }}" method="post">
		<div class="row">
			<div class="col-md-8">
				<?php
				$rows = $cart->content();
				$products = array();
				?>
				@if (count($rows))
					<table class="table order-products mt-0">
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
									{{ trans('orders::orders.subtotal') }}
								</th>
									<th scope="col">
									</th>
							</tr>
						</thead>
						<tbody>
						@foreach ($rows as $i => $item)
							<?php
							$product = App\Modules\Orders\Models\Product::find($item->id);
							$products[] = $product;
							?>
							<tr class="cart-item <?php if (!$product->public) { echo 'orderproductitemprivate'; } ?>" id="{{ $product->id }}_product">
								<td class="orderproductitem">
									<p>
										@if (!$product->public)
											<span class="badge badge-warning order-product-hidden">{{ trans('orders::orders.hidden') }}</span>
										@endif
										<b id="{{ $product->id }}_productname">{{ $product->name }}</b>
									</p>

									@if (auth()->user() && auth()->user()->can('manage orders'))
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
									@endif
								</td>
								<td class="orderproductitem text-right text-nowrap">
									$&nbsp;{{ $product->price }}<br /> per {{ $product->unit }}
									<input type="hidden" id="{{ $product->id }}_price" value="{{ $product->unitprice }}" />
									<input type="hidden" id="{{ $product->id }}_category" value="{{ $product->ordercategoryid }}" />
								</td>
								<td class="orderproductitem text-center">
									<input type="number" name="quantity[{{ $product->id }}][]" id="{{ $product->id }}_quantity" data-id="{{ $product->id }}" size="4" min="1" max="999" class="form-control quantity-input" value="{{ $item->qty }}" />
								</td>
								<td class="orderproductitem text-right text-nowrap">
									@if (auth()->user() && auth()->user()->can('manage orders'))
										<span class="form-inline">
											<span class="input-group">
												<span class="input-group-prepend"><span class="input-group-text">$</span></span>
												<input type="text" name="subtotal[{{ $product->id }}][]" id="{{ $product->id }}_linetotal" size="10" class="form-control total-input text-right" value="{{ $item->total() }}" />
											</span>
										</span>
									@else
										$&nbsp;<span id="{{ $product->id }}_linetotal">{{ $item->total }}</span>
									@endif
								</td>
								<td class="text-nowrap">
									<a href="{{ route('site.orders.products.delete', ['id' => $product->id]) }}" class="btn btn-sm btn-cart-remove text-danger" data-item="#{{ $product->id }}_product" data-api="{{ route('api.orders.cart.delete', ['id' => $item->rowId]) }}" title="{{ trans('orders::orders.remove from cart') }}">
										&times;<span class="sr-only">{{ trans('orders::orders.remove from cart') }}</span>
									</a>
								</td>
							</tr>
						@endforeach
						</tbody>
						<tfoot>
							<tr>
								<td class="orderproductitem text-right" colspan="3">{{ trans('orders::orders.total') }}</td>
								<td class="orderproductitem text-right orderprice">$<span id="ordertotal" class="category-total">{{ $cart->total() }}</span></td>
								<td></td>
							</tr>
						</tfoot>
					</table>
				@else
					<p class="alert alert-info">Your cart is empty.</p>
				@endif
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label for="usernotes">Any comments or notes you'd like to add?</label>
					<textarea name="usernotes" id="usernotes" class="form-control" cols="35" rows="5"></textarea>
				</div>
			</div>
		</div>

		<div id="forme" class="cancellable stash">
			<p>
				Are you placing this order on behalf of a faculty member?
				<a href="#help1" class="help icn tip" title="Help">
					<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only">Help</span>
				</a>
			</p>

			<div id="help1" class="dialog dialog-help" title="Placing order on behalf of someone else">
				<p>You may place an order on behalf of another person. To do so select "Yes" and you will be prompted to search for this person.</p>
				<p>If you are placing this order for yourself click "No" and then "Continue".</p>
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-block form-block-radio">
						<div class="form-check">
							<input class="form-check-input" type="radio" id="formeyes" value="Yes" name="forme" />
							<label class="form-check-label" for="formeyes">{{ trans('global.yes') }}</label>
							<span class="form-text text-muted">If you are a graduate student please answer "Yes".</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-block form-block-radio">
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
			<p>
				Please use the search box below to select the faculty member this order is for:
				<a href="#help2" class="help icn tip" title="Help">
					<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only">Help</span>
				</a>
			</p>

			<div id="help2" class="dialog dialog-help" title="Placing order on behalf of someone else">
				<p>Use this box to search for the person you are placing this order on behalf of. You may search by full name, email address, or Career account username. Once found click the person from the results to fill in the box.</p>
			</div>

			<div class="form-group">
				<span class="input-group">
					<input type="text" name="search" id="search_user" class="form-control" data-api-create="{{ route('api.users.create') }}" data-api="{{ route('api.users.index') }}?search=%s" placeholder="Search users..." value="" />
					<span class="input-group-append">
						<span class="input-group-text"><span class="fa fa-user" aria-hidden="true"></span></span>
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
			foreach ($products as $product):
				if (!$product->mou):
					continue;
				endif;
				?>
				<div id="{{ $product->id }}_mou" class="stash">
					<div class="form-block form-block-check">
						<div class="form-check">
							<input class="form-check-input mou-agree" type="checkbox" data-id="{{ $product->id }}" id="{{ $product->id }}_mouagree" />
							<label class="form-check-label" for="{{ $product->id }}_mouagree">I have read and consent to the <abbr title="Memorandum of Understanding">MOU</abbr> Agreement.</label>
							<p class="form-text text-muted">
								<a href="{{ $product->mou }}" target="_blank">{{ $product->name }} - <abbr title="Memorandum of Understanding">MOU</abbr> Agreement</a>

								<a href="#help3" class="help icn tip" title="Help">
									<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only">Please click checkbox after reading and consenting to MOU Agreement.</span>
								</a>
							</p>
						</div>
					</div>
				</div>
				<?php
			endforeach;
			?>
		</div>

		<div id="restrictagree" class="cancellable" style="display: none;">
			<p>Will you be using any of the selected products for restricted data?</p>

			<?php
			foreach ($products as $product):
				if (!$product->restricteddata):
					continue;
				endif;
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
			endforeach;
			?>
		</div>

		<div class="text-center">
			<input id="continue" class="order btn btn-primary" type="submit" value="Continue" <?php if (!count($rows)) { echo 'disabled'; } ?>
				data-submit-txt="Place Order"
				data-api="{{ route('api.orders.create') }}"
				data-error="There was an error processing your order. Please wait a few minutes and try again or contact rcac-help@purdue.edu for help." />
			<input id="cancel" class="order btn btn-secondary hide" type="submit" value="Edit Order" />
		</div>

		<input type="hidden" id="userid" value="{{ auth()->user()->id }}" />

		@csrf
	</form>
</div>
@php
// Clear out session data
//
// Database info will still be there if we need to restore
// but the API doesn't have access to the session data, so
// when the cart is cleared upon creating a new order, the
// session info doesn't get cleared.
$cart->forget(auth()->user()->username, true);
@endphp
@stop