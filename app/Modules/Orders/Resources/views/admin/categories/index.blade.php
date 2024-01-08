@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/orders/css/orders.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/orders/js/orders.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('orders::orders.module name'),
		route('admin.orders.index')
	)
	->append(
		trans('orders::orders.categories')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete orders.categories'))
		{!! Toolbar::deleteList('', route('admin.orders.categories.delete')) !!}
	@endif

	@if (auth()->user()->can('create orders.categories'))
		{!! Toolbar::addNew(route('admin.orders.categories.create')) !!}
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
{{ trans('orders::orders.module name') }}: {{ trans('orders::orders.categories') }}
@stop

@section('content')

@component('orders::admin.submenu')
	categories
@endcomponent

<form action="{{ route('admin.orders.categories') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-3 filter-search">
				<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
				<span class="input-group">
					<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
					<span class="input-group-append"><span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span></span>
				</span>
			</div>
			<div class="col col-md-9 filter-select text-right">
				<label class="sr-only" for="filter_state">{{ trans('global.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('global.option.all states') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<thead>
			<tr>
				@if (auth()->user()->can('delete orders.categories'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('orders::orders.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('orders::orders.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2 numeric">
					{{ trans('orders::orders.products') }}
				</th>
				<th scope="col" class="priority-2 text-right">
					{!! Html::grid('sort', trans('orders::orders.sequence'), 'sequence', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody class="sortable">
			<?php
			$positions = $rows->pluck('parentordercategoryid')->toArray();
			?>
		@foreach ($rows as $i => $row)
			<?php
			$trashed = ($row->datetimeremoved);
			?>
			<tr<?php if ($trashed) { echo ' class="trashed"'; } ?>>
				@if (auth()->user()->can('delete orders.categories'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if ($trashed)
						<span class="fa fa-trash text-muted" aria-hidden="true"></span>
					@endif
					@if (auth()->user()->can('edit orders.categories'))
						<a href="{{ route('admin.orders.categories.edit', ['id' => $row->id]) }}">
							{{ $row->name }}
						</a>
					@else
						{{ $row->name }}
					@endif
				</td>
				<td class="priority-2 numeric">
					{{ $row->products_count }}
				</td>
				<td class="priority-6 text-right">
					<span class="badge badge-secondary">{{ $row->sequence }}</span>
						<!-- <span class="drag-handle" draggable="true">
							<svg class="MiniIcon DragMiniIcon DragHandle-icon" viewBox="0 0 24 24"><path d="M10,4c0,1.1-0.9,2-2,2S6,5.1,6,4s0.9-2,2-2S10,2.9,10,4z M16,2c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,2,16,2z M8,10 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,10,8,10z M16,10c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,10,16,10z M8,18 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,18,8,18z M16,18c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,18,16,18z"></path></svg>
						</span>
						<span class="drag-handle" draggable="true">
							<span class="fa fa-ellipsis-v" aria-hidden="true"></span><span class="sr-only">Move</span>
						</span>-->
					@if (auth()->user()->can('edit orders') && $filters['order'] == 'sequence')
						@if ($filters['order_dir'] == 'asc')
							<span class="ordering-control">{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, (@$positions[$i-1] == $row->parentordercategoryid), route('admin.orders.categories.orderup', ['id' => $row->id])) !!}</span>
							<span class="ordering-control">{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), (@$positions[$i+1] == $row->parentordercategoryid), route('admin.orders.categories.orderdown', ['id' => $row->id])) !!}</span>
						@elseif ($filters['order_dir'] == 'desc')
							<span class="ordering-control">{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, (@$positions[$i-1] == $row->parentordercategoryid), route('admin.orders.categories.orderup', ['id' => $row->id])) !!}</span>
							<span class="ordering-control">{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), (@$positions[$i+1] == $row->parentordercategoryid), route('admin.orders.categories.orderdown', ['id' => $row->id])) !!}</span>
						@endif
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
		</div>
	</div>

	{{ $rows->render() }}
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
</form>

@stop