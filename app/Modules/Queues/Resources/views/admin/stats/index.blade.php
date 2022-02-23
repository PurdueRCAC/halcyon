@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ Module::asset('core:vendor/chartjs/Chart.css') . '?v=' . filemtime(public_path() . '/modules/core/vendor/chartjs/Chart.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css?v=' . filemtime(public_path() . '/modules/orders/css/orders.css')) }}" />
@endpush

@push('scripts')
<script src="{{ Module::asset('core:vendor/chartjs/Chart.min.js') . '?v=' . filemtime(public_path() . '/modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<script src="{{ asset('modules/orders/js/orders.js?v=' . filemtime(public_path() . '/modules/orders/js/orders.js')) }}"></script>
<script>
$(document).ready(function () {
	$('.items-toggle').on('click', function(e){
		e.preventDefault();
		$($(this).attr('href')).toggle('collapse');
	});

	var charts = new Array;
	$('.sparkline-chart').each(function (i, el) {
		const ctx = el.getContext('2d');
		const chart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: JSON.parse($(el).attr('data-labels')),
				datasets: [
					{
						fill: true,
						data: JSON.parse($(el).attr('data-values'))
					}
				]
			},
			options: {
				//responsive: false,
				bezierCurve: false,
				animation: {
					duration: 0
				},
				legend: {
					display: false
				},
				elements: {
					line: {
						borderColor: 'rgb(54, 162, 235)', //'#0091EB',
						backgroundColor: 'rgb(54, 162, 235)',
						borderWidth: 1,
						tension: 0
					},
					point: {
						borderColor: 'rgb(54, 162, 235)'//'#0091EB'
					}
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
		charts.push(chart);
	});

	$('.pie-chart').each(function (i, el) {
		const ctx = el.getContext('2d');
		const pchart = new Chart(ctx, {
			type: 'doughnut',
			data: {
				labels: JSON.parse($(el).attr('data-labels')),
				datasets: [
					{
						data: JSON.parse($(el).attr('data-values')),
						backgroundColor: [
							'rgb(255, 99, 132)', // red
							'rgb(54, 162, 235)', // blue
							'rgb(255, 205, 86)', // yellow
							'rgb(201, 203, 207)', // grey
							'rgb(75, 192, 192)', // blue green
							'rgb(255, 159, 64)', // orange
							'rgb(153, 102, 255)' // purple
						]
					}
				]
			},
			options: {
				animation: {
					duration: 0
				}/*,
				legend: {
					display: false
				}*/
			}
		});
		charts.push(pchart);
	});
});
</script>
@endpush

@php
app('pathway')
	->append(
		trans('queues::queues.module name'),
		route('admin.queues.index')
	)
	->append(
		trans('queues::queues.stats')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('admin queues'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('queues')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('queues.name') !!}
@stop

@section('content')
@component('queues::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.queues.stats') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-12 filter-select text-right">
				<label class="sr-only" for="filter_resource">{{ trans('queues::queues.resource') }}</label>
				<select name="resource" id="filter_resource" class="form-control filter filter-submit">
					<option value="0">{{ trans('queues::queues.all resources') }}</option>
					@foreach ($resources as $res)
						<?php $selected = ($res->id == $filters['resource'] ? ' selected="selected"' : ''); ?>
						<option value="{{ $res->id }}"<?php echo $selected; ?>>{{ str_repeat('- ', $res->level) . $res->name }}</option>
					@endforeach
				</select>

				<label class="sr-only" for="filter_class">{{ trans('queues::queues.class') }}</label>
				<select name="class" id="filter_class" class="form-control filter filter-submit">
					<option value="*">{{ trans('queues::queues.all queue classes') }}</option>
					<option value="system"<?php if ($filters['class'] == 'system'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.system queues') }}</option>
					<option value="owner"<?php if ($filters['class'] == 'owner'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.owner queues') }}</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" id="filter_order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" id="filter_order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (!$filters['resource'])
	<div class="card">
		<table class="table">
			<caption>Total Allocations</caption>
			<thead>
				<th scope="col">Resource</th>
				<th scope="col" class="text-right">Queues</th>
				<th scope="col" class="text-right">Sold</th>
				<th scope="col" class="text-right">Loaned</th>
			</thead>
			<tbody>
				@foreach ($resources as $resource)
					<tr>
						<td><a href="{{ route('admin.queues.stats', ['resource' => $resource->id]) }}">{{ str_repeat('- ', $resource->level) . $resource->name }}</a></td>
						<td class="text-right">
							<?php
							$total = 0;
							$purchased = array('nodes' => 0, 'cores' => 0, 'sus' => 0);
							$loaned = array('nodes' => 0, 'cores' => 0, 'sus' => 0);

							$s = (new App\Modules\Queues\Models\Size)->getTable();
							$l = (new App\Modules\Queues\Models\Loan)->getTable();
							$q = (new App\Modules\Queues\Models\Queue)->getTable();
							$now = Carbon\Carbon::now();

							foreach ($resource->subresources()->orderBy('name', 'asc')->get() as $subresource):
								$query = $subresource->queues();
								if ($filters['class'] == 'system')
								{
									$query->where('groupid', '<=', 0);
								}
								elseif ($filters['class'] == 'owner')
								{
									$query->where('groupid', '>', 0);
								}
								$total += $query->count();

								$pquery = App\Modules\Queues\Models\Size::query()
									->select(Illuminate\Support\Facades\DB::raw('SUM(' . $s . '.nodecount) AS nodes, SUM(' . $s . '.corecount) AS cores, SUM(' . $s . '.serviceunits) AS sus'))
									->join($q, $q . '.id', $s . '.queueid')
									->where($q . '.subresourceid', '=', $subresource->id)
									->where($s . '.corecount', '>=', 0)
									->where($s . '.serviceunits', '>=', 0)
									->where(function($where) use ($now, $s)
									{
										$where->whereNull($s . '.datetimestop')
											->orWhere($s . '.datetimestop', '>', $now->toDateTimeString());
									})
									->where($s . '.datetimestart', '<=', $now->toDateTimeString());

								if ($filters['class'] == 'system')
								{
									$pquery->where($q . '.groupid', '<=', 0);
								}
								elseif ($filters['class'] == 'owner')
								{
									$pquery->where($q . '.groupid', '>', 0);
								}

								$purchases = $pquery->get()->first();

								$soldnodes = 0;
								if ($subresource->nodecores != 0)
								{
									$soldnodes = round($purchases->cores / $subresource->nodecores, 1);
								}

								$purchased['nodes'] += ($soldnodes ? $soldnodes : $purchases->nodes);
								$purchased['cores'] += $purchases->cores;
								$purchased['sus'] += $purchases->sus;

								$lquery = App\Modules\Queues\Models\Loan::query()
									->select(Illuminate\Support\Facades\DB::raw('SUM(' . $l . '.nodecount) AS nodes, SUM(' . $l . '.corecount) AS cores, SUM(' . $l . '.serviceunits) AS sus'))
									->join($q, $q . '.id', $l . '.queueid')
									->where($q . '.subresourceid', '=', $subresource->id)
									->where($l . '.corecount', '>=', 0)
									->where($l . '.serviceunits', '>=', 0)
									->where(function($where) use ($now, $l)
									{
										$where->whereNull($l . '.datetimestop')
											->orWhere($l . '.datetimestop', '>', $now->toDateTimeString());
									})
									->where($l . '.datetimestart', '<=', $now->toDateTimeString());

								if ($filters['class'] == 'system')
								{
									$lquery->where($q . '.groupid', '<=', 0);
								}
								elseif ($filters['class'] == 'owner')
								{
									$lquery->where($q . '.groupid', '>', 0);
								}

								$loans = $lquery->get()->first();

								$loaned['nodes'] += $loans->nodes;
								$loaned['cores'] += $loans->cores;
								$loaned['sus'] += $loans->sus;
							endforeach;

							echo $total;
							?>
						</td>
						<td class="text-right">
							@if ($purchased['nodes'] || $purchased['cores'])
								{{ number_format($purchased['nodes']) }} <span class="text-muted">nodes</span>, {{ number_format($purchased['cores']) }} <span class="text-muted">cores</span>
							@elseif ($purchased['sus'])
								{{ number_format($purchased['sus']) }} <span class="text-muted"><abbr title="Service Units">SUs</abbr></span>
							@else
								-
							@endif
						</td>
						<td class="text-right">
							@if ($loaned['nodes'] || $loaned['cores'])
								{{ number_format($loaned['nodes']) }} <span class="text-muted">nodes</span>, {{ number_format($loaned['cores']) }} <span class="text-muted">cores</span>
							@elseif ($loaned['sus'])
								{{ number_format($loaned['sus']) }} <span class="text-muted"><abbr title="Service Units">SUs</abbr></span>
							@else
								-
							@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	@else
	<div class="container-fluid">
	<div class="row">
		<div class="col-md-6">
	<div class="card">
		<table class="table">
			<caption>Total Allocations</caption>
			<thead>
				<th scope="col">Sub-Resource</th>
				<th scope="col" class="text-right">Queues</th>
				<th scope="col" class="text-right">Sold</th>
				<th scope="col" class="text-right">Loaned</th>
			</thead>
			<tbody>
				@foreach ($resource->subresources()->orderBy('name', 'asc')->get() as $subresource)
					<?php
					$total = 0;
					$purchased = array('nodes' => 0, 'cores' => 0, 'sus' => 0);
					$loaned = array('nodes' => 0, 'cores' => 0, 'sus' => 0);

					$s = (new App\Modules\Queues\Models\Size)->getTable();
					$l = (new App\Modules\Queues\Models\Loan)->getTable();
					$q = (new App\Modules\Queues\Models\Queue)->getTable();
					$now = Carbon\Carbon::now();

					$query = $subresource->queues();
					if ($filters['class'] == 'system')
					{
						$query->where('groupid', '<=', 0);
					}
					elseif ($filters['class'] == 'owner')
					{
						$query->where('groupid', '>', 0);
					}
					$total = $query->count();

					$pquery = App\Modules\Queues\Models\Size::query()
						->select(Illuminate\Support\Facades\DB::raw('SUM(' . $s . '.nodecount) AS nodes, SUM(' . $s . '.corecount) AS cores, SUM(' . $s . '.serviceunits) AS sus'))
						->join($q, $q . '.id', $s . '.queueid')
						->where($q . '.subresourceid', '=', $subresource->id)
						->where($s . '.corecount', '>=', 0)
						->where($s . '.serviceunits', '>=', 0)
						->where(function($where) use ($now, $s)
						{
							$where->whereNull($s . '.datetimestop')
								->orWhere($s . '.datetimestop', '>', $now->toDateTimeString());
						})
						->where($s . '.datetimestart', '<=', $now->toDateTimeString());

					if ($filters['class'] == 'system')
					{
						$pquery->where($q . '.groupid', '<=', 0);
					}
					elseif ($filters['class'] == 'owner')
					{
						$pquery->where($q . '.groupid', '>', 0);
					}

					$purchases = $pquery->get()->first();

					$soldnodes = 0;
					if ($subresource->nodecores != 0)
					{
						$soldnodes = round($purchases->cores / $subresource->nodecores, 1);
					}

					$purchased['nodes'] += ($soldnodes ? $soldnodes : $purchases->nodes);
					$purchased['cores'] += $purchases->cores;
					$purchased['sus'] += $purchases->sus;

					$lquery = App\Modules\Queues\Models\Loan::query()
						->select(Illuminate\Support\Facades\DB::raw('SUM(' . $l . '.nodecount) AS nodes, SUM(' . $l . '.corecount) AS cores, SUM(' . $l . '.serviceunits) AS sus'))
						->join($q, $q . '.id', $l . '.queueid')
						->where($q . '.subresourceid', '=', $subresource->id)
						->where($l . '.corecount', '>=', 0)
						->where($l . '.serviceunits', '>=', 0)
						->where(function($where) use ($now, $l)
						{
							$where->whereNull($l . '.datetimestop')
								->orWhere($l . '.datetimestop', '>', $now->toDateTimeString());
						})
						->where($l . '.datetimestart', '<=', $now->toDateTimeString());

					if ($filters['class'] == 'system')
					{
						$lquery->where($q . '.groupid', '<=', 0);
					}
					elseif ($filters['class'] == 'owner')
					{
						$lquery->where($q . '.groupid', '>', 0);
					}

					$loans = $lquery->get()->first();

					$loaned['nodes'] += $loans->nodes;
					$loaned['cores'] += $loans->cores;
					$loaned['sus'] += $loans->sus;
					?>
					<tr>
						<td>{{ $subresource->name }}</td>
						<td class="text-right">{{ $total }}</td>
						<td class="text-right">
							@if ($purchased['nodes'] || $purchased['cores'])
								{{ number_format($purchased['nodes']) }} <span class="text-muted">nodes</span>, {{ number_format($purchased['cores']) }} <span class="text-muted">cores</span>
							@elseif ($purchased['sus'])
								{{ number_format($purchased['sus']) }} <span class="text-muted"><abbr title="Service Units">SUs</abbr></span>
							@else
								-
							@endif
						</td>
						<td class="text-right">
							@if ($loaned['nodes'] || $loaned['cores'])
								{{ number_format($loaned['nodes']) }} <span class="text-muted">nodes</span>, {{ number_format($loaned['cores']) }} <span class="text-muted">cores</span>
							@elseif ($loaned['sus'])
								{{ number_format($loaned['sus']) }} <span class="text-muted"><abbr title="Service Units">SUs</abbr></span>
							@else
								-
							@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
		</div>
		<div class="col-md-6">
			<div class="card">
				<table class="table">
					<caption>Top Allocated Queues</caption>
					<thead>
						<th scope="col">Queue</th>
						<th scope="col">Sub-Resource</th>
						<th scope="col" class="text-right">Allocation</th>
					</thead>
					<tbody>
					<?php
					$queues = array();
					foreach ($resource->subresources()->orderBy('name', 'asc')->get() as $subresource):
						$query = $subresource->queues();
						if ($filters['class'] == 'system')
						{
							$query->where('groupid', '<=', 0);
						}
						elseif ($filters['class'] == 'owner')
						{
							$query->where('groupid', '>', 0);
						}
						foreach ($query->get() as $queue):
							$queues[$queue->id] = $queue->totalcores + $queue->serviceunits;
							unset($queue);
						endforeach;
					endforeach;
					
					arsort($queues);
					$queues = array_slice($queues, 0, 10, true);

					foreach ($queues as $id => $val):
						$qu = App\Modules\Queues\Models\Queue::find($id);
						?>
						<tr>
							<td><a href="{{ route('admin.queues.edit', ['id' => $qu->id]) }}">{{ $qu->name }}</a></td>
							<td>{{ $qu->subresource->name }}</td>
							<td class="text-right">
								@if ($qu->totalcores)
									{{ number_format($qu->totalnodes) }} <span class="text-muted">nodes</span>, {{ number_format($qu->totalcores) }} <span class="text-muted">cores</span>
								@elseif ($qu->serviceunits)
									{{ number_format($qu->serviceunits) }} <span class="text-muted"><abbr title="Service Units">SUs</abbr></span>
								@else
									-
								@endif
							</td>
						</tr>
						<?php
					endforeach;
					?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
				</div>
	@endif

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop
