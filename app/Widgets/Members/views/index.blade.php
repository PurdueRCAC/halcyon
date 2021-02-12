<?php
/**
 * Members widget
 */
?>

@push('scripts')
	<script src="{{ asset('/modules/core/vendor/chartjs/Chart.min.js') }}"></script>
@endpush

<div class="card widget {{ $widget->widget }}" id="{{ $widget->widget . $widget->id }}">

	<canvas id="users-pastmonth" width="365" height="182">
		<table class="table stats-overview">
			<caption>New Registrations</caption>
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
		"datasets":[{
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
		}]},
	"options":{
		title: {
			display: false
		},
		legend: {
			display: false
		}
	}
});
</script>
