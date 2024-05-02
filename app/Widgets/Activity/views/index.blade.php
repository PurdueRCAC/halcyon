<?php
/**
 * View for Activity widget
 */
?>
@pushOnce('styles')
<link rel="stylesheet" type="text/css" href="{{ timestamped_asset('modules/core/vendor/chartjs/Chart.css') }}" />
@endpushOnce

@pushOnce('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.sparkline-chart').forEach(function (el) {
		const ctx = el.getContext('2d');
		const chart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: JSON.parse(el.getAttribute('data-labels')),
				datasets: [
					{
						label: 'Success',
						fill: false,
						//backgroundColor: 'rgba(48, 166, 73, 0.1)',
						borderColor: 'rgb(48, 166, 73)',
						data: JSON.parse(el.getAttribute('data-success-values')),
						lineTension: 0.1
					},
					{
						label: 'Errors',
						fill: false,
						borderColor: '#dc3545',
						data: JSON.parse(el.getAttribute('data-error-values')),
						lineTension: 0.1
					},
					{
						label: '404 Not Found',
						fill: false,
						//backgroundColor: 'rgba(212, 149, 1, 0.1)',
						borderColor: 'rgb(212, 149, 1)',
						data: JSON.parse(el.getAttribute('data-missing-values')),
						lineTension: 0.1
					}
				]
			},
			options: {
				responsive: true,
				animation: {
					duration: 0
				},
				legend: {
					display: false
				},
				elements: {
					line: {
						/*borderColor: 'rgb(48, 166, 73)',*/
						borderWidth: 1
					}/*,
					point: {
						borderColor: 'rgb(48, 166, 73)'
					}*/
				},
				scales: {
					/*yAxes: [
						{
							display: false
						}
					],*/
					xAxes: [
						{
							display: false
						}
					]
				}
			}
		});
	});
});
</script>
@endpushOnce

<div class="card mb-4 widget {{ $widget->module }}">
	<div class="card-body">
		@if ($widget->showtitle)
			<div class="row">
				<div class="col-md-8">
					<h4 class="card-title py-0">{{ $widget->title }}</h4>
				</div>
				<div class="col-md-4 text-right text-end">
					<a href="{{ route('admin.history.activity') }}">{{ trans('widget.activity::activity.view all') }}</a>
				</div>
			</div>
		@endif
		<canvas id="sparkline{{ $widget->id }}" class="sparkline-chart" width="350" height="100"
			data-labels="{{ json_encode(array_keys($success)) }}"
			data-success-values="{{ json_encode(array_values($success)) }}"
			data-error-values="{{ json_encode(array_values($errors)) }}"
			data-missing-values="{{ json_encode(array_values($notfound)) }}">
			<table class="table">
				<caption>Success</caption>
				<thead>
					<tr>
						<th scope="col">Day</th>
						<th scope="col">Visits</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($success as $day => $val)
						<tr>
							<td>{{ $day }}</td>
							<td>{{ $val }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>

			<table class="table">
				<caption>404 Not Found</caption>
				<thead>
					<tr>
						<th scope="col">Day</th>
						<th scope="col">Visits</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($notfound as $day => $val)
						<tr>
							<td>{{ $day }}</td>
							<td>{{ $val }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>

			<table class="table">
				<caption>Errors</caption>
				<thead>
					<tr>
						<th scope="col">Day</th>
						<th scope="col">Visits</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($errors as $day => $val)
						<tr>
							<td>{{ $day }}</td>
							<td>{{ $val }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</canvas>

		<?php /*
		<table class="table stats-overview">
			<caption>{{ $widget->title }}</caption>
			<thead>
				<th scope="col">{{ trans('widget.activity::activity.timestamp') }}</th>
				<th scope="col">{{ trans('widget.activity::activity.transportmethod') }}</th>
				<th scope="col">{{ trans('widget.activity::activity.uri') }}</th>
				<th scope="col" class="text-right text-end">{{ trans('widget.activity::activity.status') }}</th>
			</thead>
			<tbody>
				@php
				$now = Carbon\Carbon::now()->modify('-1 week');
				@endphp
				@foreach ($activity as $row)
				<tr>
					<td>
						@if ($row->datetime->timestamp > $now->timestamp)
							{{ $row->datetime->diffForHumans() }}
						@else
							{{ $row->datetime->format('M d, g:ia') }}
						@endif
					</td>
					<td>
						@if ($row->app == 'email')
							<span class="badge badge-info">{{ $row->app }}</span>
						@elseif ($row->app == 'cli')
							<span class="badge badge-secondary">{{ $row->app }}</span>
						@else
							@if ($row->transportmethod == 'DELETE')
								<span class="badge badge-danger">{{ $row->transportmethod }}</span>
							@elseif ($row->transportmethod == 'POST')
								<span class="badge badge-success">{{ $row->transportmethod }}</span>
							@elseif ($row->transportmethod == 'PUT')
								<span class="badge badge-info">{{ $row->transportmethod }}</span>
							@elseif ($row->transportmethod == 'GET')
								<span class="badge badge-info">{{ $row->transportmethod }}</span>
							@else
								<span class="badge badge-secondary">{{ $row->transportmethod }}</span>
							@endif
						@endif
					</td>
					<td>
						@if ($row->app == 'ui' || $row->app == 'api')
							{{ '/' . trim($row->uri, '/') }}
						@else
							{{ $row->uri }}
						@endif
					</td>
					<td class="text-right text-end">
						@if ($row->status >= 500)
							<span class="badge badge-danger">
						@elseif ($row->status >= 300)
							<span class="badge badge-warning">
						@else
							<span class="badge badge-success">
						@endif
						{{ $row->status }}
						</span>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		*/ ?>

		<p class="text-muted">{{ trans('widget.activity::activity.data for past days', ['num' => $widget->params->get('range', 14)]) }}</p>
	</div>
</div>
