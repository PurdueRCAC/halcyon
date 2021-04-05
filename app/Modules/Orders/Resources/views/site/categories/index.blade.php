@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css?v=' . filemtime(public_path() . '/modules/orders/css/orders.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/orders/js/orders.js?v=' . filemtime(public_path() . '/modules/orders/js/orders.js')) }}"></script>
<script>
jQuery(document).ready(function($){
	$('.filter-submit').on('change', function(e){
		$(this).closest('form').submit();
	});

	$('.category-delete').on('click', function(e){
		e.preventDefault();
		if (confirm($(this).attr('data-confirm'))) {
			$.ajax({
				url: $(this).attr('data-api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function (response) {
					window.location.reload();
				},
				error: function (xhr, ajaxOptions, thrownError) {
					console.log(xhr.responseJSON.message);
					//btn.find('.spinner-border').addClass('d-none');
					//alert(xhr.responseJSON.message);
				}
			});
		}
	});

	var sortableHelper = function (e, ui) {
		ui.children().each(function () {
			$(this).width($(this).width());
		});
		return ui;
	};
	//var corresponding;
	$('.sortable').sortable({
		handle: '.draghandle',
		cursor: 'move',
		helper: sortableHelper,
		/*containment: 'parent',
		start: function (e, ui) {
			//corresponding = [];
			var height = ui.helper.outerHeight();
			$(this).find('> tr[data-parent=' + $(ui.item).data('id') + ']').each(function (idx, row) {

				height += $(row).outerHeight();

			});
			ui.placeholder.height(height);
		},
		update: function (e, ui) {
			//var tableHasUnsortableRows = $(this).find('> tbody > tr:not(.sortable)').length;

			$(this).find('> tr').each(function (idx, row) {
				var uniqID = $(row).attr('data-id'),
					correspondingFixedRow = $('tr[data-parent=' + uniqID + ']');
				correspondingFixedRow.detach().insertAfter($(this));
			});
		},*/
		stop: function (e, ui) {
			//corresponding.detach().insertAfter($(ui.item));

			$(".sortable tr").each(function(i, el){
				var url = $(el).data('api');
				
				$.ajax({
					url: url,
					type: 'put',
					data: {
						'sequence': (i + 1)
					},
					dataType: 'json',
					async: false,
					success: function (response) {
						
					},
					error: function (xhr, ajaxOptions, thrownError) {
						console.log(xhr.responseJSON.message);
						//btn.find('.spinner-border').addClass('d-none');
						//alert(xhr.responseJSON.message);
					}
				});
			});
		}
	}).disableSelection();
});
</script>
@endpush

@section('title')
{!! trans('orders::orders.orders') !!}: {{ trans('orders::orders.categories') }}
@stop

@php
app('pathway')
	->append(
		trans('orders::orders.orders'),
		route('site.orders.index')
	)
	->append(
		trans('orders::orders.categories')
	);
@endphp

@section('content')
@component('orders::site.submenu')
	categories
@endcomponent
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

	<div class="row">
		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
			<h2 class="sr-only">{{ trans('orders::orders.categories') }}</h2>
		</div>
		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 text-right">
			@if (auth()->user()->can('create orders.categories'))
			<p>
				<a href="{{ route('site.orders.categories.create') }}" class="btn btn-info">
					<i class="fa fa-plus"></i> {{ trans('orders::orders.create category') }}
				</a>
			</p>
			@endif
		</div>
	</div>

	<form action="{{ route('site.orders.categories') }}" method="post" name="adminForm" id="adminForm" class="row">
		<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
			<fieldset class="filters mt-0">
				<legend class="sr-only">Filter</legend>

				<div class="form-group">
					<label for="filter_search">{{ trans('search.label') }}</label>
					<input type="text" name="filter_search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
				</div>
				<div class="form-group">
					<label for="filter_state">{{ trans('global.state') }}</label>
					<select name="filter_state" id="filter_state" class="form-control filter filter-submit">
						<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('global.option.all states') }}</option>
						<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
						<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
					</select>
				</div>

				<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
				<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

				<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
			</fieldset>
		</div>
		<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
			<table class="table table-hover mt-0">
				<caption class="sr-only">{{ trans('orders::orders.categories') }}</caption>
				<thead>
					<tr>
						@if (auth()->user()->can('delete orders.categories'))
							<th scope="col">
								Options
							</th>
						@endif
						<th scope="col" class="priority-5">
							{!! Html::grid('sort', trans('orders::orders.id'), 'id', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col">
							{!! Html::grid('sort', trans('orders::orders.name'), 'name', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col" class="priority-2 text-right">
							{{ trans('orders::orders.products') }}
						</th>
						<th scope="col" class="priority-2 text-right">
							{!! Html::grid('sort', trans('orders::orders.sequence'), 'sequence', $filters['order_dir'], $filters['order']) !!}
						</th>
					</tr>
				</thead>
				<tbody class="sortable">
				@foreach ($rows as $i => $row)
					<tr data-id="{{ $row->id }}" data-api="{{ route('api.orders.categories.update', ['id' => $row->id]) }}">
						<td>
							@if (auth()->user()->can('delete orders.categories'))
								<a class="btn text-danger btn-sm category-delete" href="{{ route('site.orders.categories.delete', ['id' => $row->id]) }}" data-confirm="{{ trans('global.confirm delete') }}" data-api="{{ route('api.orders.categories.delete', ['id' => $row->id]) }}">
									<i class="fa fa-trash" aria-hidden="true"></i>
									<span class="sr-only">{{ trans('global.button.delete') }}</span>
								</a>
							@endif
						</td>
						<td class="priority-5">
							{{ $row->id }}
						</td>
						<td>
							@if (auth()->user()->can('edit orders.categories'))
								<a href="{{ route('site.orders.categories.edit', ['id' => $row->id]) }}">
									{{ $row->name }}
								</a>
							@else
								{{ $row->name }}
							@endif
						</td>
						<td class="priority-2 text-right">
							{{ $row->products_count }}
						</td>
						<td class="priority-2 text-right">
							<!-- {{ $row->sequence }} -->
							@if ($filters['order'] == 'sequence')
								<span class="draghandle" draggable="true">
									<i class="fa fa-ellipsis-v" aria-hidde="true"></i>
									<i class="fa fa-ellipsis-v" aria-hidde="true"></i>
									<span class="sr-only">Move</span>
								</span>
							@endif
						</td>
					</tr>
				@endforeach
				</tbody>
			</table>

			{{ $rows->render() }}

			@csrf
		</div>
	</form>
</div>
@stop
