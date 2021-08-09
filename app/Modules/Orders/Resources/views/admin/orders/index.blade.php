@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/orders/js/orders.js?v=' . filemtime(public_path() . '/modules/orders/js/orders.js')) }}"></script>
<script>
$(document).ready(function() {
	var dialog = $("#export-orders").dialog({
		autoOpen: false,
		height: 'auto',
		width: 250,
		modal: true
	});

	$('#toolbar-export>.btn-export').off('click').on('click', function(e){
		e.preventDefault();

		dialog.dialog("open");
	});
});
</script>
@endpush

@php
app('pathway')
	->append(
		trans('orders::orders.module name'),
		route('admin.orders.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete orders'))
		{!! Toolbar::deleteList('', route('admin.orders.delete')) !!}
	@endif

	{!!
		Toolbar::link('export', trans('orders::orders.export'), route('admin.orders.index'));
		Toolbar::spacer();
	!!}

	@if (auth()->user()->can('create orders'))
		{!! Toolbar::addNew(route('admin.orders.create')) !!}
	@endif

	@if (auth()->user()->can('admin orders'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('orders');
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('orders.name') !!}
@stop

@section('content')

@component('orders::admin.submenu')
	orders
@endcomponent

<form action="{{ route('admin.orders.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-3 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="icon-search" aria-hidden="true"></span><span class="sr-only">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
			</div>
			<div class="col col-md-9 filter-select text-right">
				<label class="sr-only" for="filter_category">{{ trans('orders::orders.category') }}</label>
				<select name="category" id="filter_category" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['status'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all categories') }}</option>
					<?php foreach ($categories as $category) { ?>
						<option value="<?php echo $category->id; ?>"<?php if ($filters['category'] == $category->id): echo ' selected="selected"'; endif;?>>{{ $category->name }}</option>
					<?php } ?>
				</select>

				<label class="sr-only" for="filter_status">{{ trans('orders::orders.status') }}</label>
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

				<label class="sr-only" for="filter_start">{{ trans('orders::orders.start date') }}</label>
				<input type="text" name="start" id="filter_start" class="form-control date filter filter-submit" value="{{ $filters['start'] }}" placeholder="Start date" />

				<label class="sr-only" for="filter_end">{{ trans('orders::orders.end date') }}</label>
				<input type="text" name="end" id="filter_end" class="form-control date filter filter-submit" value="{{ $filters['end'] }}" placeholder="End date" />
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<thead>
			<tr>
				@if (auth()->user()->can('delete orders'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('orders::orders.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('orders::orders.created'), 'datetimecreated', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('orders::orders.notes'), 'usernotes', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">{{ trans('orders::orders.status') }}</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('orders::orders.submitter'), 'userid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2 numeric">
					{!! Html::grid('sort', trans('orders::orders.total'), 'ordertotal', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete orders'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-5">
					@if (auth()->user()->can('edit orders'))
						<a href="{{ route('admin.orders.edit', ['id' => $row->id]) }}">
							{{ $row->id }}
						</a>
					@else
						{{ $row->id }}
					@endif
				</td>
				<td class="priority-4">
					@if (auth()->user()->can('edit orders'))
						<a href="{{ route('admin.orders.edit', ['id' => $row->id]) }}">
					@endif
					@if ($row->datetimecreated && $row->datetimecreated != '0000-00-00 00:00:00' && $row->datetimecreated != '-0001-11-30 00:00:00')
						<time datetime="{{ $row->datetimecreated->format('Y-m-d\TH:i:s\Z') }}">
							@if ($row->datetimecreated->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
								{{ $row->datetimecreated->diffForHumans() }}
							@else
								{{ $row->datetimecreated->format('Y-m-d') }}
							@endif
						</time>
					@else
						<span class="unknown">{{ trans('global.unknown') }}</span>
					@endif
					@if (auth()->user()->can('edit orders'))
						</a>
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit orders'))
						<a href="{{ route('admin.orders.edit', ['id' => $row->id]) }}">
							{{ $row->usernotes ? Illuminate\Support\Str::limit($row->usernotes, 50) : '' }}
						</a>
					@else
						{{ $row->usernotes ? Illuminate\Support\Str::limit($row->usernotes, 50) : '' }}
					@endif
					<!-- <br />
					accounts: {{ $row->accounts }}<br />
					assigned: {{ $row->accountsassigned }}<br />
					approved: {{ $row->accountsapproved }}<br />
					denied: {{ $row->accountsdenied }}<br />
					paid: {{ $row->accountspaid }}<br />
					items: {{ $row->items_count }}<br />
					fulfilled {{ $row->itemsfulfilled }}<br />
					-->
				</td>
				<td>
					<span class="badge badge-sm order-status {{ str_replace(' ', '-', $row->status) }}" data-tip="Accounts: {{ $row->accounts }}<br />Assigned: {{ $row->accountsassigned }}<br />Approved: {{ $row->accountsapproved }}<br />Denied: {{ $row->accountsdenied }}<br />Paid: {{ $row->accountspaid }}<br />---<br />Items: {{ $row->items_count }}<br />Fulfilled: {{ $row->itemsfulfilled }}">
						{{ trans('orders::orders.' . $row->status) }}
					</span>
				</td>
				<td class="priority-4">
					@if ($row->groupid)
						@if (auth()->user()->can('manage groups'))
							<a href="{{ route('admin.groups.edit', ['id' => $row->groupid]) }}">
								<?php echo $row->group ? $row->group->name : 'Group ID #' . $row->groupid; ?>
							</a>
						@else
							<?php echo $row->group ? $row->group->name : 'Group ID #' . $row->groupid; ?>
						@endif
					@else
						@if (auth()->user()->can('manage users'))
							<a href="{{ route('admin.users.edit', ['id' => $row->userid]) }}">
								<?php echo $row->name ? $row->name : 'User ID #' . $row->userid; ?>
							</a>
						@else
							<?php echo $row->name ? $row->name : 'User ID #' . $row->userid; ?>
						@endif
					@endif
				</td>
				<td class="priority-2 numeric">
					{{ config('orders.currency', '$') }} {{ $row->formatNumber($row->ordertotal) }}
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	<div id="export-orders" class="hide" title="{{ trans('orders::orders.export') }}">
		<h2 class="modal-title sr-only">{{ trans('knowledge::knowledge.choose type') }}</h2>

		<p>
			<a href="{{ route('admin.orders.index', ['export' => 'only_main']) }}" class="btn btn-outline-primary d-block">
				{{ trans('orders::orders.export summary') }}
			</a>
		</p>
		<p>
			<a href="{{ route('admin.orders.index', ['export' => 'items']) }}" class="btn btn-outline-secondary d-block">
				{{ trans('orders::orders.export items') }}
			</a>
		</p>
		<p>
			<a href="{{ route('admin.orders.index', ['export' => 'accounts']) }}" class="btn btn-outline-secondary d-block">
				{{ trans('orders::orders.export accounts') }}
			</a>
		</p>
	</div>

	@csrf
</form>

@stop