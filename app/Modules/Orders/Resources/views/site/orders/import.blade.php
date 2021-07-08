@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.css?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css?v=' . filemtime(public_path() . '/modules/orders/css/orders.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/datatables/datatables.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/datatables.min.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.js')) }}"></script>
<script>
	// Force update of totals in case browswer is caching values
	$(document).ready(function() { 
		if ($('.datatable').length) {
			$.fn.dataTable.render.ellipsis = function ( cutoff ) {
				return function ( data, type, row ) {
					console.log(data);
					return type === 'display' && data.length > cutoff ?
						data.substr( 0, cutoff ) +'…' :
						data;
				}
			};

			$('.datatable').DataTable({
				pageLength: 20,
				pagingType: 'numbers',
				info: false,
				ordering: false,
				lengthChange: false,
				scrollX: true,
				//autoWidth: false,
				language: {
					searchPlaceholder: "Filter rows...",
					search: "_INPUT_",
				},
				fixedColumns: {
					leftColumns: 1
				},
				columnDefs: [{
					targets: [-1, -2],
					render: $.fn.dataTable.render.ellipsis(14)
				}],
				initComplete: function () {
					$($.fn.dataTable.tables(true)).css('width', '100%');
				}
			});
		}
	});
</script>
@endpush

@section('title'){{ trans('orders::orders.import') }}@stop

@php
	app('pathway')
		->append(
			trans('orders::orders.orders'),
			route('site.orders.index')
		)
		->append(
			trans('orders::orders.import'),
			route('site.orders.import')
		);
@endphp

@section('content')
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	<div class="row">
		<div class="col-md-6">
			<h2 class="mt-0">{{ trans('orders::orders.import') }}</h2>
		</div>
		<div class="col-md-6 text-right">
			<a class="btn btn-link" href="{{ route('site.orders.index') }}"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back to Orders</a>
		</div>
	</div>

	<form action="{{ route('site.orders.process') }}" method="post">
		@if (count($data))
			<p class="alert alert-info">This is a preview of the data being imported. Please verify that data looks correct before submitting.</p>
			<table class="table order-import datatable nowrap mt-0">
				<caption class="sr-only">{{ trans('orders::orders.import preview') }}</caption>
				<thead>
					<tr>
						@foreach ($headers as $header)
						<th scope="col">
							{{ $header }}
						</th>
						@endforeach
					</tr>
				</thead>
				<tbody>
				@foreach ($data as $item)
					<tr>
						@foreach ($headers as $header)
						<td>
							{{ $item->{strtolower($header)} }}
						</td>
						@endforeach
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<p class="alert alert-info">No data found.</p>
		@endif

		<div class="text-center">
			<input class="order btn btn-primary" type="submit" value="Save" />
		</div>

		<input type="hidden" name="file" value="{{ base64_encode($file) }}" />

		@csrf
	</form>
</div>
@stop