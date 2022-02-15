<?php
/**
 * Members widget
 */
?>

@push('scripts')
	<script src="{{ asset('/modules/core/vendor/chartjs/Chart.min.js') }}"></script>
@endpush

<div class="card widget {{ $widget->widget }}" id="{{ $widget->widget . $widget->id }}">
	<div class="card-body">
		@if ($widget->showtitle)
			<div class="row">
				<div class="col-md-8">
					<h4 class="card-title py-0">{{ $widget->title }}</h4>
				</div>
				<div class="col-md-4 text-right">
					<a href="{{ route('admin.users.index') }}">{{ trans('widget.members::members.view all') }}</a>
				</div>
			</div>
		@endif

		<canvas id="users-pastmonth" width="350" height="100">
			<table class="table">
				<caption>{{ trans('widget.members::members.new registrations') }}</caption>
				<thead>
					<tr>
						<th scope="col">Day</th>
						<th scope="col">Total</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($data as $dt => $total)
					<tr>
						<td>
							{{ $total->x }}
						</td>
						<td>
							{{ $total->y }}
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</canvas>
	</div>
</div>
<script>
var ctx = document.getElementById('users-pastmonth');

new Chart(ctx,{
	"type":"line",
	"data":{
		"labels":<?php
			$x = array();
			foreach ($data as $item)
			{
				$x[] = $item->x;
			}
			echo json_encode($x);
		?>,
		"datasets": [{
			"label":"New Users",
			"data":<?php
				$y = array();
				foreach ($data as $item)
				{
					$y[] = $item->y;
				}
				echo json_encode($y);
			?>,
			"fill":false,
			"borderColor":"rgb(75, 192, 192)",
			"lineTension":0.1
		}]
	},
	options: {
		responsive: true,
		animation: {
			duration: 0
		},
		title: {
			display: false
		},
		legend: {
			display: false
		},
		scales: {
			xAxes: [
				{
					display: false
				}
			]
		}
	}
});
</script>
