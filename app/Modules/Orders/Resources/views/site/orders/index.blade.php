@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/select2/css/select2.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/orders/css/orders.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/select2/js/select2.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/orders/js/orders.js') }}"></script>
<script src="{{ timestamped_asset('modules/orders/js/import.js') }}"></script>
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
<div class="row">
@component('orders::site.submenu')
	orders
@endcomponent


<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

<h2 class="sr-only visually-hidden">{{ trans('orders::orders.orders') }}</h2>

<form action="{{ route('site.orders.index') }}" method="get" class="row">
	<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">

		<fieldset class="filters mt-0">
			<div class="form-group">
				<label for="filter_search">{{ trans('search.label') }}</label>
				<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="Find by account, user, or group" value="{{ $filters['search'] }}" />
			</div>
			<div class="form-group">
				<label for="filter_status">{{ trans('orders::orders.status') }}</label>
				<select name="status" id="filter_status" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['status'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all statuses') }}</option>
					<option value="active"<?php if ($filters['status'] == 'active'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.active') }}</option>
					<option value="pending_payment"<?php if ($filters['status'] == 'pending_payment'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_payment') }}</option>
					<option value="pending_boassignment"<?php if ($filters['status'] == 'pending_boassignment'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_boassignment') }}</option>
					<option value="pending_approval"<?php if ($filters['status'] == 'pending_approval'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_approval') }}</option>
					<option value="pending_fulfillment"<?php if ($filters['status'] == 'pending_fulfillment'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_fulfillment') }}</option>
					<option value="pending_collection"<?php if ($filters['status'] == 'pending_collection'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_collection') }}</option>
					<option value="complete"<?php if ($filters['status'] == 'complete'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.complete') }}</option>
					<option value="canceled"<?php if ($filters['status'] == 'canceled'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.canceled') }}</option>
				</select>
			</div>
			<div class="form-group">
				<label for="filter_category">{{ trans('orders::orders.category') }}</label>
				<select name="category" id="filter_category" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['category'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all categories') }}</option>
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
		@if (auth()->user()->can('manage orders'))
			<div class="text-right">
				<div class="dropdown btn-group">
					<button class="btn btn-primary dropdown-toggle" type="button" id="exportbutton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="fa fa-table" aria-hidden="true"></span> {{ trans('orders::orders.export') }}
					</button>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="exportbutton">
						<?php
						$filters['export'] = 'only_main';
						?>
						<a href="{{ route('site.orders.index', $filters) }}" class="dropdown-item">
							{{ trans('orders::orders.export summary') }}
						</a>
						<?php
						$filters['export'] = 'items';
						?>
						<a href="{{ route('site.orders.index', $filters) }}" class="dropdown-item">
							{{ trans('orders::orders.export items') }}
						</a>
						<?php
						$filters['export'] = 'accounts';
						?>
						<a href="{{ route('site.orders.index', $filters) }}" class="dropdown-item">
							{{ trans('orders::orders.export accounts') }}
						</a>
					</div>
				</div>
				<a href="#import-orders" data-toggle="modal" class="btn btn-secondary btn-import">
					<span class="fa fa-upload" aria-hidden="true"></span> {{ trans('orders::orders.import') }}
				</a>
			</div>
		@endif

		<div id="applied-filters" aria-label="{{ trans('orders::orders.applied filters') }}">
			<p class="sr-only visually-hidden">{{ trans('orders::orders.applied filters') }}:</p>
			<ul class="filters-list">
				<?php
				$allfilters = collect($filters);

				$keys = ['search', 'status', 'category', 'product', 'start', 'end'];
				if (auth()->user()->can('manage users'))
				{
					$keys[] = 'userid';
				}

				foreach ($keys as $key):
					if (!isset($filters[$key]) || !$filters[$key] || $filters[$key] == '*'):
						continue;
					endif;

					$f = $allfilters
						->reject(function($v, $k) use ($key)
						{
							$ks = ['export', 'limit', 'page', 'order', 'order_dir', 'type'];
							if (!auth()->user()->can('manage users'))
							{
								$ks[] = 'userid';
							}
							return (in_array($k, $ks));
						})
						->map(function($v, $k) use ($key)
						{
							if ($k == $key)
							{
								$v = '*';
								$v = (in_array($k, ['start', 'end', 'search', 'userid']) ? '' : $v);
							}
							return $v;
						})
						->toArray();

					$val = $filters[$key];
					$val = ($val == '*' ? 'all' : $val);
					if ($key == 'status'):
						$val = trans('orders::orders.' . $val);
					endif;
					if ($key == 'category'):
						foreach ($categories as $category):
							if ($val == $category->id):
								$val = $category->name;
								break;
							endif;
						endforeach;
					endif;
					if ($key == 'product'):
						foreach ($products as $product):
							if ($val == $product->id):
								$val = $product->name;
								break;
							endif;
						endforeach;
					endif;
					if ($key == 'userid'):
						$u = App\Modules\Users\Models\User::find($val);
						$val = $u ? $u->name : $val;
					endif;
					?>
					<li>
						<strong>{{ trans('orders::orders.filters.' . $key) }}</strong>: {{ $val }}
						<a href="{{ route('site.orders.index', $f) }}" class="filters-x" title="{{ trans('orders::orders.remove filter') }}">
							<span class="fa fa-times" aria-hidden="true"><span class="sr-only visually-hidden">{{ trans('orders::orders.remove filter') }}</span>
						</a>
					</li>
					<?php
				endforeach;
				?>
			</ul>
		</div>

	@if (count($rows))
		<table class="table table-hover mt-0">
			<caption class="sr-only visually-hidden">{{ trans('orders::orders.orders placed') }}</caption>
			<thead>
				<tr>
					<th scope="col">
						<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.id'), 'id', $filters['order_dir'], $filters['order']); ?>
					</th>
					<th scope="col">
						<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.created'), 'datetimecreated', $filters['order_dir'], $filters['order']); ?>
					</th>
					<th scope="col">
						<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.status'), 'state', $filters['order_dir'], $filters['order']); ?>
					</th>
					<th scope="col">
						<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('orders::orders.submitter'), 'userid', $filters['order_dir'], $filters['order']); ?>
					</th>
					<th scope="col" class="text-right text-nowrap">
						{{ trans('orders::orders.total') }}
					</th>
					<th scope="col">
						<span class="sr-only visually-hidden">Items</span>
					</th>
				</tr>
			</thead>
			<tbody id="orders">
			@foreach ($rows as $i => $row)
				<tr>
					<td class="priority-5">
						<a href="{{ route('site.orders.read', ['id' => $row->id]) }}">
							{{ $row->id }}
						</a>
					</td>
					<td class="priority-4">
						@if ($row->datetimecreated)
							<time datetime="{{ $row->datetimecreated->toDateTimeLocalString() }}">
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
								<a href="{{ route('admin.groups.show', ['id' => $row->groupid]) }}">
									<?php echo $row->group ? $row->group->name : ' <span class="unknown">' . trans('global.unknown') . '</span> (group ID: ' . $row->groupid . ')'; ?>
								</a>
							@else
								<?php echo $row->group ? $row->group->name : ' <span class="unknown">' . trans('global.unknown') . '</span> (group ID: ' . $row->groupid . ')'; ?>
							@endif
						@else
							@if (auth()->user()->can('manage users'))
								@if ($row->userid)
									<a href="{{ route('site.orders.index', ['userid' => $row->userid]) }}">
										<?php echo $row->user ? $row->user->name : ' <span class="unknown">' . trans('global.unknown') . '</span> (user ID: ' . $row->userid . ')'; ?>
									</a>
								@else
									<span class="none">{{ trans('global.none') }}</span>
								@endif
							@else
								@if ($row->userid)
									<?php echo $row->user ? $row->user->name : ' <span class="unknown">' . trans('global.unknown') . '</span> (user ID: ' . $row->userid . ')'; ?>
								@else
									<span class="none">{{ trans('global.none') }}</span>
								@endif
							@endif
						@endif
					</td>
					<td class="text-right text-nowrap">
						{{ config('orders.currency', '$') }} {{ $row->formatNumber($row->ordertotal) }}
					</td>
					<td>
						<a class="items-toggle tip" data-toggle="collapse" data-parent="#orders" href="#row{{ $row->id }}" title="Items in this order">
							<span class="fa fa-shopping-cart" aria-hidden="true"></span><span class="sr-only visually-hidden">Items</span>
						</a>
					</td>
				</tr>
				<tr class="details-row collapse" id="row{{ $row->id }}">
					<td colspan="6">
						<table class="table">
							<caption class="sr-only visually-hidden">{{ trans('orders::orders.items') }}</caption>
							<thead>
								<tr>
									<th scope="col">{{ trans('orders::orders.item') }}</th>
									<th scope="col" class="text-right">{{ trans('orders::orders.quantity') }}</th>
									<th scope="col" class="text-right">{{ trans('orders::orders.price') }}</th>
									<th scope="col" class="text-right">{{ trans('orders::orders.total') }}</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($row->items as $item)
									<tr>
										<td>
											<strong>{{ $item->product->name }}</strong>
											<p class="form-text text-muted">
												@if ($item->origorderitemid)
													@if ($item->start() && $item->end())
														@if ($item->id == $item->origorderitemid)
															{{ trans('orders::orders.new service', ['start' => $item->start()->format('M j, Y'), 'end' => $item->end()->format('M j, Y')]) }}
														@else
															{{ trans('orders::orders.service renewal', ['start' => $item->start()->format('M j, Y'), 'end' => $item->end()->format('M j, Y')]) }}
														@endif
													@else
														{{ 'Service for ' . $item->timeperiodcount . ' ' }}
														@if ($item->timeperiodcount > 1)
															{{ $item->product->timeperiod->plural }}
															{{ trans('orders::orders.service for', ['count' => $item->timeperiodcount, 'timeperiod' => $item->product->timeperiod->plural]) }}
														@else
															{{ trans('orders::orders.service for', ['count' => $item->timeperiodcount, 'timeperiod' => $item->product->timeperiod->singular]) }}
														@endif
													@endif
												@endif
											</p>
										</td>
										<td class="text-right">
											<span class="item-edit-hide quantity_span">{{ $item->quantity }}</span>
											@if ($item->product->timeperiod && $item->origorderitemid)
												for<br/>
												<span class="item-edit-hide periods_span">{{ $item->timeperiodcount }}</span>
												@if ($item->timeperiodcount > 1)
													{{ $item->product->timeperiod->plural }}
												@else
													{{ $item->product->timeperiod->singular }}
												@endif
											@endif
										</td>
										<td class="text-right">
											{{ config('orders.currency', '$') }} <span name="price">{{ $item->formattedPrice }}</span><br/>
											<span class="text-nowrap">per {{ $item->product->unit }}</span>
										</td>
										<td class="text-right text-nowrap">
											<span class="item-edit-hide">{{ config('orders.currency', '$') }} <span name="itemtotal">{{ $item->formattedTotal }}</span></span>
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
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

	@csrf
	</div>
</form>

<div class="modal" id="import-orders" tabindex="-1" aria-labelledby="import-orders-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content dialog-content shadow-sm">
			<div class="modal-header">
				<div class="modal-title" id="import-orders-title">{{ trans('orders::orders.import') }}</div>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body dialog-body">
				<form action="{{ route('site.orders.import') }}" method="post" enctype="multipart/form-data">
					<p>CSV, XLSX (Excel), and ODS files are accepted. The first row must be headers with at least the following columns: order <code>ID</code>, <code>purchaseio</code> or <code>purchasewbse</code>, and <code>paymentdocid</code>.</p>

					<div class="form-group dropzone">
						<div id="uploader" class="fallback" data-instructions="Click or Drop files" data-list="#uploader-list">
							<label for="upload">Choose a file<span class="dropzone__dragndrop"> or drag it here</span></label>
							<input type="file" name="file" id="upload" class="form-control-file" multiple="multiple" />
						</div>
						<div class="file-list" id="uploader-list"></div>
						<input type="hidden" name="tmp_dir" id="ticket-tmp_dir" value="{{ ('-' . time()) }}" />
					</div>

					<div class="text-center">
						<input class="order btn btn-primary" type="submit" value="Import" />
					</div>

					@csrf
				</form>
			</div>
		</div>
	</div>
</div>
</div>
</div>
@stop