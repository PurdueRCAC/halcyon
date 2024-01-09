@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/orders/css/orders.css') }}" />
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.filter-submit').forEach(function(el) {
		el.addeventListener('click', function(e) {
			this.closest('form').submit();
		});
	});

	document.querySelectorAll('.category-delete').forEach(function(el) {
		el.addeventListener('click', function(e) {
			e.preventDefault();

			if (confirm(this.getAttribute('data-confirm'))) {
				fetch(input.getAttribute('data-api'), {
					method: 'DELETE',
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					}
				})
				.then(function (response) {
					if (response.ok) {
						window.location.reload(true);
						return;
					}
					return response.json().then(function (data) {
						var msg = data.message;
						if (typeof msg === 'object') {
							msg = Object.values(msg).join('<br />');
						}
						throw msg;
					});
				})
				.catch(function (error) {
					alert(error);
				});
			}
		});
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

			document.querySelectorAll(".sortable tr").forEach(function(el){
				fetch(el.getAttribute('data-api'), {
					method: 'PUT',
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					},
					body: JSON.stringify({
						'sequence': (i + 1)
					})
				})
				.then(function (response) {
					if (response.ok) {
						return response.json();
					}
					return response.json().then(function (data) {
						var msg = data.message;
						if (typeof msg === 'object') {
							msg = Object.values(msg).join('<br />');
						}
						throw msg;
					});
				})
				.catch(function (err) {
					alert(err);
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
<div class="row">
@component('orders::site.submenu')
	categories
@endcomponent
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

	<h2 class="sr-only">{{ trans('orders::orders.categories') }}</h2>

	<form action="{{ route('site.orders.categories') }}" method="get" name="adminForm" id="adminForm" class="row">
		<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
			<fieldset class="filters mt-0">
				<legend class="sr-only">Filter</legend>

				<div class="form-group">
					<label for="filter_search">{{ trans('search.label') }}</label>
					<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
				</div>

				<div class="form-group">
					<label for="filter_state">{{ trans('global.state') }}</label>
					<select name="state" id="filter_state" class="form-control filter filter-submit">
						<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('global.option.all states') }}</option>
						<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
						<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
					</select>
				</div>

				<input type="hidden" name="order" value="{{ $filters['order'] }}" />
				<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

				<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
			</fieldset>
		</div>
		<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
			@if (auth()->user()->can('create orders.categories'))
			<p class="text-right">
				<a href="{{ route('site.orders.categories.create') }}" class="btn btn-info">
					<span class="fa fa-plus" aria-hidden="true"></span> {{ trans('orders::orders.create category') }}
				</a>
			</p>
			@endif

			<div id="applied-filters" aria-label="{{ trans('orders::orders.applied filters') }}">
				<p class="sr-only">{{ trans('orders::orders.applied filters') }}:</p>
				<ul class="filters-list">
					<?php
					$allfilters = collect($filters);
					$fkeys = ['search', 'state'];

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

						if ($key == 'state'):
							$val = ($val == '*' ? trans('global.option.all states') : $val);
							$val = ($val == 'published' ? trans('global.published') : $val);
							$val = ($val == 'trashed' ? trans('global.trashed') : $val);
						endif;
						?>
						<li>
							<strong>{{ trans('orders::orders.filters.' . $key) }}</strong>: {{ $val }}
							<a href="{{ route('site.orders.products', $f) }}" class="filters-x" title="{{ trans('orders::orders.remove filter') }}">
								<span class="fa fa-times" aria-hidden="true"><span class="sr-only">{{ trans('orders::orders.remove filter') }}</span>
							</a>
						</li>
						<?php
					endforeach;
					?>
				</ul>
			</div>

			@if (count($rows))
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
							@if (auth()->user()->can('delete orders.categories') && !$row->trashed())
								<a class="btn text-danger btn-sm category-delete" href="{{ route('site.orders.categories.delete', ['id' => $row->id]) }}" data-confirm="{{ trans('global.confirm delete') }}" data-api="{{ route('api.orders.categories.delete', ['id' => $row->id]) }}" title="{{ trans('global.button.delete') }}">
									<span class="fa fa-trash" aria-hidden="true"></span>
									<span class="sr-only">{{ trans('global.button.delete') }}</span>
								</a>
							@endif
						</td>
						<td class="priority-5">
							{{ $row->id }}
						</td>
						<td>
							@if ($row->trashed())
								<span class="fa fa-trash text-muted" aria-hidden="true"></span>
							@endif
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
									<span class="fa fa-ellipsis-v" aria-hidde="true"></span>
									<span class="fa fa-ellipsis-v" aria-hidde="true"></span>
									<span class="sr-only">Move</span>
								</span>
							@endif
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

		</div>
	</form>
</div>
</div>
@stop
