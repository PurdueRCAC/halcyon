@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.css?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/datatables/datatables.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/datatables.min.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.js')) }}"></script>
<script>
$(document).ready(function() {
	if ($('.datatable').length) {
		$('.datatable').DataTable({
			//pageLength: 20,
			//pagingType: 'numbers',
			paging: false,
			scrollY: '50vh',
			scrollCollapse: true,
			headers: true,
			info: true,
			ordering: false,
			lengthChange: false,
			dom: "<'row'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6'i>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'p><'col-sm-12 col-md-7'l>>",
			lengthChange: false,
			language: {
				searchPlaceholder: "Filter history...",
				search: "_INPUT_",
			},
			initComplete: function () {
				this.api().columns().every(function (i) {
					if (i == 0) {
						var column = this;
						var select = $('<select class="form-control form-control-sm" data-index="' + i + '"><option value="">' + $(column.header()).html() + '</option></select>')
							.appendTo($(column.header()).empty());
		
						column.data().unique().sort().each(function (d, j) {
							select.append('<option value="'+d+'">'+d+'</option>');
						});
					}
				});

				var table = this;

				$(table.api().table().container()).on('change', 'thead select', function () {
					var val = $.fn.dataTable.util.escapeRegex(
						$(this).val()
					);

					table.api()
						.column($(this).data('index'))
						.search(val ? '^'+val+'$' : '', true, false)
						.draw();
				});
			}
		});
	}
});
</script>
@endpush

<div class="contentInner">
	<h2>{{ trans('history::history.history') }}</h2>

	@if (count($history) > 0)
		<table class="table table-hover datatable">
			<caption>Memberships</caption>
			<thead>
				<tr>
					<th scope="col">Type</th>
					<th scope="col">Name</th>
					<th scope="col">Added</th>
					<th scope="col">Removed</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($history as $item)
					<tr>
						<td>
							{{ $item->type }}
						</td>
						<td>
							@if ($item->route)
								<a href="{{ $item->route }}">
							@endif
							{{ $item->description }}
							@if ($item->route)
								</a>
							@endif
							@if ($item->subtype)
								&nbsp; <span class="badge badge-info">{{ $item->subtype }}</span>
							@endif
						</td>
						<td>
							@if ($item->created)
							<time datetime="{{ $item->created->toDateTimeString() }}">
								{{ $item->created->format('M d, Y') }}
							</time>
							@endif
						</td>
						<td>
							@if ($item->isTrashed)
								<time datetime="{{ $item->removed->toDateTimeString() }}">
									@if ($item->removed->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
										{{ $item->removed->diffForHumans() }}
									@else
										{{ $item->removed->format('M d, Y') }}
									@endif
								</time>
							@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	@else
		<div class="d-flex justify-content-center">
			<div class="card card-help w-50">
				<div class="card-body">
					<h3 class="card-title mt-0">What is this page?</h3>
					<p class="card-text">Here you can find various access history for {{ $user->name }}. This shows when the person was added, given specific roles, or removed from a group, resource queue, or unix group.</p>
				</div>
			</div>
		</div>
	@endif
</div>