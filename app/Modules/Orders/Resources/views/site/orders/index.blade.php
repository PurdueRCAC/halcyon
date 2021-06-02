@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css?v=' . filemtime(public_path() . '/modules/orders/css/orders.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/orders/js/orders.js?v=' . filemtime(public_path() . '/modules/orders/js/orders.js')) }}"></script>
<script>
$(document).ready(function() { 
	$('.filter-submit').on('change', function(e){
		$(this).closest('form').submit();
	});

	var users = $(".form-users");
	/*if (users.length) {
		users.each(function (i, user) {
			user = $(user);
			var cl = user.clone()
				.attr('type', 'hidden')
				.val(user.val().replace(/([^:]+):/, ''));
			user
				.attr('name', user.attr('id') + i)
				.attr('id', user.attr('id') + i)
				.val(user.val().replace(/(:\d+)$/, ''))
				.after(cl);
			user.autocomplete({
				minLength: 2,
				source: function (request, response) {
					return $.getJSON(user.attr('data-uri').replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
						response($.map(data.data, function (el) {
							return {
								label: el.name + ' (' + el.username + ')',
								name: el.name,
								id: el.id,
							};
						}));
					});
				},
				select: function (event, ui) {
					event.preventDefault();
					// Set selection
					user.val(ui.item.label); // display the selected text
					cl.val(ui.item.id); // save selected id to input
					user.closest('form').submit();
					return true;
				}
			});
		});
	}*/
	if (users.length) {
		users.each(function(i, el){
			$(el).select2({
				placeholder: $(el).attr('placeholder'),
				ajax: {
					url: $(el).data('api') + '&api_token=' + $('meta[name="api-token"]').attr('content'),
					dataType: 'json',
					maximumSelectionLength: 1,
					data: function (params) {
						var query = {
							search: params.term,
							order: 'name',
							order_dir: 'asc'
						}

						return query;
					},
					processResults: function (data) {
						for (var i = 0; i < data.data.length; i++) {
							data.data[i].text = data.data[i].name + ' (' + data.data[i].username + ')';
						}

						return {
							results: data.data
						};
					}
				}
			});
		});
		users.on('select2:select', function (e) {
			var data = e.params.data;
			window.location = $(this).data('url') + "?u=" + data.id;
		});
		users.on('select2:unselect', function (e) {
			var data = e.params.data;
			window.location = $(this).data('url') + "?u=";
		});
	}
});
</script>
@endpush

@section('title'){{ trans('orders::orders.orders') }}@stop

@php
app('pathway')
	->append(
		trans('orders::orders.orders'),
		route('site.orders.index')
	);
@endphp

@section('content')

@component('orders::site.submenu')
	orders
@endcomponent


<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

<h2 class="sr-only">{{ trans('orders::orders.orders') }}</h2>

<form action="{{ route('site.orders.index') }}" method="get" class="row">
	<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">

		<fieldset class="filters mt-0">
			<?php /*@if (auth()->user()->can('manage orders'))
				<?php
				$user = App\Modules\Users\Models\User::find($filters['userid']);
				?>
				<div class="form-group">
					<label for="filter_userid">{{ trans('orders::orders.submitter') }}</label>
					<!-- <input type="text" name="userid" id="filter_userid" class="form-control form-users filter-submit" data-uri="{{ route('api.users.index') }}?search=%s" placeholder="Find by user or group" value="{{ $user ? $user->name . ':' . $user->id : '' }}" /> -->
					<select name="userid" id="filter_userid" class="form-control form-users filter-submit" multiple="multiple" placeholder="Find by submitter" data-url="{{ route('site.orders.index') }}" data-api="{{ route('api.users.index') }}?search=%s">
						@if ($user)
						<option value="{{ $user->id }}" selected="selected">{{ $user->name }}</option>
						@endif
					</select>
				</div>
			@endif*/ ?>
			<div class="form-group">
				<label for="filter_search">{{ trans('search.label') }}</label>
				<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="Find by account, user, or group" value="{{ $filters['search'] }}" />
			</div>

			<div class="form-group">
				<label for="filter_status">{{ trans('orders::orders.status') }}</label>
				<select name="status" id="filter_status" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['status'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all statuses') }}</option>
					<option value="active"<?php if ($filters['status'] == 'active'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.active') }}</option>
					<option value="pending_payment"<?php if ($filters['status'] == 'pending_payment'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_payment') }}</option>
					<option value="pending_boassignment"<?php if ($filters['status'] == 'pending_boassignment'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_boassignment') }}</option>
					<option value="pending_approval"<?php if ($filters['status'] == 'pending_approval'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_approval') }}</option>
					<option value="pending_collection"<?php if ($filters['status'] == 'pending_collection'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_collection') }}</option>
					<option value="pending_fulfillment"<?php if ($filters['status'] == 'pending_fulfillment'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_fulfillment') }}</option>
					<option value="complete"<?php if ($filters['status'] == 'complete'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.complete') }}</option>
					<option value="canceled"<?php if ($filters['status'] == 'canceled'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.canceled') }}</option>
				</select>
			</div>
			<div class="form-group">
				<label for="filter_category">{{ trans('orders::orders.category') }}</label>
				<select name="category" id="filter_category" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['status'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all categories') }}</option>
					<?php foreach ($categories as $category) { ?>
						<option value="<?php echo $category->id; ?>"<?php if ($filters['category'] == $category->id): echo ' selected="selected"'; endif;?>>{{ $category->name }}</option>
					<?php } ?>
				</select>
			</div>
			<div class="form-group">
				<label for="filter_product">{{ trans('orders::orders.product') }}</label>
				<select name="product" id="filter_product" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['product'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all products') }}</option>
					@foreach ($products as $product)
						<option value="<?php echo $product->id; ?>"<?php if ($filters['product'] == $product->id): echo ' selected="selected"'; endif;?>>{{ $product->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group">
				<label for="filter_start">{{ trans('orders::orders.start date') }}</label>
				<input type="text" name="start" id="filter_start" size="10" class="form-control date-pick filter filter-submit" value="{{ $filters['start'] }}" placeholder="Start date (YYYY-MM-DD)" />
			</div>
			<div class="form-group">
				<label for="filter_end">{{ trans('orders::orders.end date') }}</label>
				<input type="text" name="end" id="filter_end" size="10" class="form-control date-pick filter filter-submit" value="{{ $filters['end'] }}" placeholder="End date (YYYY-MM-DD)" />
			</div>

			<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
			<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

			<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
		</fieldset>
	</div>
	<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	@if (count($rows))
		<table class="table table-hover mt-0">
			<caption class="sr-only">{{ trans('orders::orders.orders placed') }}</caption>
			<thead>
				<tr>
					<th scope="col" class="priority-5">
						<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.id'), 'id', $filters['order_dir'], $filters['order']); ?>
					</th>
					<th scope="col" class="priority-4">
						<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.created'), 'datetimecreated', $filters['order_dir'], $filters['order']); ?>
					</th>
					<th scope="col">
						<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.status'), 'state', $filters['order_dir'], $filters['order']); ?>
					</th>
					<th scope="col" class="priority-4">
						<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.submitter'), 'userid', $filters['order_dir'], $filters['order']); ?>
					</th>
					<th scope="col" class="priority-2 text-right text-nowrap">
						{{ trans('orders::orders.total') }}
					</th>
				</tr>
			</thead>
			<tbody>
			@foreach ($rows as $i => $row)
				<tr>
					<td class="priority-5">
						<a href="{{ route('site.orders.read', ['id' => $row->id]) }}">
							{{ $row->id }}
						</a>
					</td>
					<td class="priority-4">
						@if ($row->getOriginal('datetimecreated') && $row->getOriginal('datetimecreated') != '0000-00-00 00:00:00')
							<time datetime="{{ $row->datetimecreated->toDateTimeString() }}">
								@if ($row->datetimecreated->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
									{{ $row->datetimecreated->diffForHumans() }}
								@else
									{{ $row->datetimecreated->format('Y-m-d') }}
								@endif
							</time>
						@else
							<span class="never">{{ trans('global.unknown') }}</span>
						@endif
					</td>
					<td>
						<span class="badge order-status {{ str_replace(' ', '-', $row->status) }}">
							{{ trans('orders::orders.' . $row->status) }}
						</span>
					</td>
					<td class="priority-4">
						@if ($row->groupid)
							@if (auth()->user()->can('manage groups'))
								<a href="{{ route('admin.groups.edit', ['id' => $row->groupid]) }}">
									<?php echo $row->group ? $row->group->name : ' <span class="unknown">' . trans('global.unknown') . '</span> (group ID: ' . $row->groupid . ')'; ?>
								</a>
							@else
								<?php echo $row->group ? $row->group->name : ' <span class="unknown">' . trans('global.unknown') . '</span> (group ID: ' . $row->groupid . ')'; ?>
							@endif
						@else
							@if (auth()->user()->can('manage users'))
								@if ($row->userid)
									<a href="{{ route('site.orders.index', ['userid' => $row->userid]) }}">
										<?php echo $row->name ? $row->name : ' <span class="unknown">' . trans('global.unknown') . '</span> (user ID: ' . $row->userid . ')'; ?>
									</a>
								@else
									<span class="none">{{ trans('global.none') }}</span>
								@endif
							@else
								@if ($row->userid)
									<?php echo $row->name ? $row->name : ' <span class="unknown">' . trans('global.unknown') . '</span> (user ID: ' . $row->userid . ')'; ?>
								@else
									<span class="none">{{ trans('global.none') }}</span>
								@endif
							@endif
						@endif
					</td>
					<td class="priority-2 text-right text-nowrap">
						{{ config('orders.currency', '$') }} {{ $row->formatNumber($row->ordertotal) }}
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>

		<div class="row w-100">
			<div class="col-sm-9">
				{{ $rows->render() }}
			</div>
			<div class="col-sm-3 text-right">
				Results {{ ($rows->currentPage()-1)*$rows->perPage()+1 }}-{{ $rows->total() > $rows->perPage() ? $rows->currentPage()*$rows->perPage() : $rows->total() }} of {{ $rows->total() }}
			</div>
		</div>
	@else
		<p class="alert alert-info">No orders found.</p>
	@endif

	@csrf
	</div>
</form>
</div>
@stop