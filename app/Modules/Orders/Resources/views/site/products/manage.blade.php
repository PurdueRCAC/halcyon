@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/orders/css/orders.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/orders/js/orders.js') }}"></script>
@endpush

@section('title')
{!! config('orders.name') !!}: {{ trans('orders::orders.products') }}
@stop

@section('content')

@component('orders::site.submenu')
	products
@endcomponent
<h2>Manage Products</h2>
<form action="{{ route('admin.orders.products') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
				<input type="text" name="filter_search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
			</div>
			<div class="col col-md-8 filter-select text-right">
				<label class="sr-only visually-hidden" for="filter_access">{{ trans('orders::orders.access') }}</label>
				<select name="filter_access" id="filter_access" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['access'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all accesses') }}</option>
					<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
						<option value="<?php echo $access->id; ?>"<?php if ($filters['access'] == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
					<?php endforeach; ?>
				</select>

				<label class="sr-only visually-hidden" for="filter_state">{{ trans('global.state') }}</label>
				<select name="filter_state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('global.option.all states') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only visually-hidden" for="filter_restricteddata">{{ trans('orders::orders.restricted data') }}</label>
				<select name="filter_restricteddata" id="filter_restricteddata" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['restricteddata'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('global.option.all restricted data') }}</option>
					<option value="0"<?php if ($filters['restricteddata'] == 0): echo ' selected="selected"'; endif;?>>{{ trans('global.no') }}</option>
					<option value="1"<?php if ($filters['restricteddata'] == 1): echo ' selected="selected"'; endif;?>>{{ trans('global.yes') }}</option>
				</select>

				<label class="sr-only visually-hidden" for="filter_category">{{ trans('orders::orders.category') }}</label>
				<select name="filter_category" id="filter_category" class="form-control filter filter-submit">
					<option value="0"<?php if (!$filters['category']): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all categories') }}</option>
					<?php foreach ($categories as $category) { ?>
						<option value="<?php echo $category->id; ?>"<?php if ($filters['category'] == $category->id): echo ' selected="selected"'; endif;?>>{{ $category->name }}</option>
					<?php } ?>
				</select>
			</div>
		</div>

		<button class="btn btn-secondary" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<table class="table table-hover adminlist">
		<thead>
			<tr>
				<!--<th>
					{!! Html::grid('checkall') !!}
				</th> -->
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', 'orders::orders.id', 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', 'orders::orders.name', 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{!! Html::grid('sort', 'orders::orders.category', 'ordercategoryid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="text-center">
					{{ trans('orders::orders.restricted data') }}
				</th>
				<th scope="col" class="text-center">
					{!! Html::grid('sort', 'global.access', 'public', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2 text-right">
					{!! Html::grid('sort', 'orders::orders.price', 'unitprice', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="text-center">/</th>
				<th scope="col">
					{!! Html::grid('sort', 'orders::orders.unit', 'unit', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2 text-right" colspan="2">
					{!! Html::grid('sort', 'orders::orders.sequence', 'sequence', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody class="sortable">
		@foreach ($rows as $i => $row)
			<tr>
				<!--<td>
					@if (auth()->user()->can('edit orders.products'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td> -->
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit orders.products'))
						<a href="{{ route('site.orders.products.edit', ['id' => $row->id]) }}">
							{{ $row->name }}
						</a>
					@else
						{{ $row->name }}
					@endif
				</td>
				<td class="priority-2">
					{!! $row->category_name ? $row->category_name : '<span class="unknown">' . trans('global.unknown') . '</span>' !!}
				</td>
				<td class="priority-2 text-center">
					@if ($row->restricteddata)
						<!-- <span class="badge badge-success">{{ trans('global.yes') }}</span> -->
						<span class="icn">
							<span class="fa fa-check" aria-hidden="true"></span> {{ trans('global.yes') }}
						</span>
					@else
						<!-- <span class="badge badge-danger">{{ trans('global.no') }}</span> -->
						<span class="icn unknown">
							<span class="fa fa-minus" aria-hidden="true"></span> {{ trans('global.yes') }}
						</span>
					@endif
				</td>
				<td class="text-center">
					@if ($row->viewlevel)
						<span class="badge badge-success access {{ str_replace(' ', '', strtolower($row->viewlevel->title)) }}">{{ $row->viewlevel->title }}</span>
					@else
						<span class="badge badge-default access private">{{ trans('orders::orders.private') }}</span>
					@endif
				</td>
				<td class="priority-2 text-right">
					{{ config('orders.currency', '$') }} {{ number_format($row->unitprice, 2) }}
				</td>
				<td class="text-center">
					/
				</td>
				<td>
					{{ $row->unit }}
				</td>
				<td class="priority-2 text-right">
					{{ $row->sequence }}
				</td>
				<td class="text-right">
					@if ($filters['order'] == 'sequence')
						<span class="drag-handle" draggable="true">
							<svg class="MiniIcon DragMiniIcon DragHandle-icon" viewBox="0 0 24 24"><path d="M10,4c0,1.1-0.9,2-2,2S6,5.1,6,4s0.9-2,2-2S10,2.9,10,4z M16,2c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,2,16,2z M8,10 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,10,8,10z M16,10c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,10,16,10z M8,18 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,18,8,18z M16,18c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,18,16,18z"></path></svg>
						</span>
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	<div class="row">
		<div class="col col-md-8">
			{{ $rows->render() }}
		</div>
		<div class="col col-md-4 text-right">
			<div class="pagination-limit">
				<label class="sr-onlys" for="filter_limit">Per page</label>
				<select name="filter_limit" id="filter_limit">
					<option value="20"@if ($filters['limit'] == 20) selected="selected" @endif>20</option>
					<option value="25"@if ($filters['limit'] == 25) selected="selected" @endif>25</option>
					<option value="50"@if ($filters['limit'] == 50) selected="selected" @endif>50</option>
					<option value="100"@if ($filters['limit'] == 100) selected="selected" @endif>100</option>
				</select>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
	<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />
</form>

@stop