@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css?v=' . filemtime(public_path() . '/modules/orders/css/orders.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/orders/js/orders.js?v=' . filemtime(public_path() . '/modules/orders/js/orders.js')) }}"></script>
<script>
$(document).ready(function() { 
	$('.filter-submit').on('change', function(e){
		$(this).closest('form').submit();
	});
});
</script>
@endpush

@php
app('pathway')
	->append(
		trans('orders::orders.orders'),
		route('site.orders.index')
	)
	->append(
		trans('orders::orders.recurring'),
		route('site.orders.recurring')
	)
	->append(
		'#' . $item->id,
		route('site.orders.recurring.read', ['id' => $item->id])
	);
@endphp

@section('content')
@component('orders::site.submenu')
	recur
@endcomponent
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	<h2 class="sr-only">{{ trans('orders::orders.recurring') }}</h2>

	<form action="{{ route('site.orders.recurring') }}" method="get" class="row">
		<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
			<fieldset class="filters mt-0">
				<legend class="sr-only">Filter</legend>

				<div class="form-group">
					<label for="filter_search">{{ trans('search.label') }}</label>
					<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="" />
				</div>
			</fieldset>
		</div>
		<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
			@if (count($items))
				<table class="table table-hover mt-0">
					<caption class="sr-only">{{ trans('orders::orders.recurring items') }}</caption>
					<thead>
						<tr>
							<th scope="col" class="priority-5">
								{{ trans('orders::orders.id') }}
							</th>
							<th scope="col" class="priority-4">
								{{ trans('orders::orders.product') }}
							</th>
							<th scope="col">
								{{ trans('orders::orders.billed until') }}
							</th>
							<th scope="col" class="priority-4">
								{{ trans('orders::orders.submitter') }}
							</th>
						</tr>
					</thead>
					<tbody>
					@foreach ($items as $i => $row)
						<tr>
							<td class="priority-5">
								
								{{ $row['id'] }}

							</td>
							<td>
								
							</td>
							<td class="priority-4">
								
							</td>
							<td class="priority-4">
								
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			@else
				<p class="alert alert-info">No orders found.</p>
			@endif
		</div>
		@csrf
	</form>
</div>
@stop