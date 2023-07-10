@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ timestamped_asset('modules/core/vendor/chartjs/Chart.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/news/css/news.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/news/vendor/github-heatmap-contribution-graph/css/github_contribution_graph.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/news/vendor/github-heatmap-contribution-graph/js/github_contribution.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.heatmap').forEach(function(el) {
		var data = document.getElementById(el.getAttribute('data-src')).value;
		data = JSON.parse(data);

		for (var i = 0; i < data.length; i++)
		{
			data[i].timestamp = data[i].timestamp * 1000;
		}

		$(el).github_graph({
			data: data,
			texts: ['reservation','reservations']
		});
	});

	var charts = new Array;
	document.querySelectorAll('.sparkline-chart').forEach(function(el) {
		const ctx = el.getContext('2d');
		const chart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: JSON.parse(el.getAttribute('data-labels')),
				datasets: [
					{
						fill: true,
						data: JSON.parse(el.getAttribute('data-values'))
					}
				]
			},
			options: {
				bezierCurve: false,
				animation: {
					duration: 0
				},
				legend: {
					display: false
				},
				elements: {
					line: {
						borderColor: 'rgb(54, 162, 235)',
						backgroundColor: 'rgb(54, 162, 235)',
						borderWidth: 1,
						tension: 0
					},
					point: {
						borderColor: 'rgb(54, 162, 235)'
					}
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
		charts.push(chart);
	});

	document.querySelectorAll('.pie-chart').forEach(function(el) {
		const ctx = el.getContext('2d');
		const pchart = new Chart(ctx, {
			type: 'doughnut',
			data: {
				labels: JSON.parse(el.getAttribute('data-labels')),
				datasets: [
					{
						data: JSON.parse(el.getAttribute('data-values')),
						backgroundColor: [
							'rgb(255, 99, 132)', // red
							'rgb(54, 162, 235)', // blue
							'rgb(255, 205, 86)', // yellow
							'rgb(201, 203, 207)', // grey
							'rgb(75, 192, 192)', // blue green
							'rgb(255, 159, 64)', // orange
							'rgb(153, 102, 255)' // purple
						],
						borderColor: el.getAttribute('data-border')
					}
				]
			},
			options: {
				animation: {
					duration: 0
				}
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
		trans('news::news.module name'),
		route('admin.news.index')
	)
	->append(
		trans('news::news.stats'),
		route('admin.news.stats')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('admin news'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('news');
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('news::news.module name') }}: {{ trans('news::news.stats') }}
@stop

@section('content')

@component('news::admin.submenu')
	stats
@endcomponent

<form action="{{ route('admin.news.stats') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-12 text-right">
				<label class="sr-only" for="filter_start">{{ trans('news::news.start date') }}</label>
				<input type="text" name="start" id="filter_start" class="form-control date filter filter-submit" value="{{ $filters['start'] }}" placeholder="Start date" />
				to
				<label class="sr-only" for="filter_end">{{ trans('news::news.end date') }}</label>
				<input type="text" name="end" id="filter_end" class="form-control date filter filter-submit" value="{{ $filters['end'] }}" placeholder="End date" />

				<button type="submit" class="btn btn-secondary">Filter</button>
			</div>
		</div>
	</fieldset>

	<div class="container-fluid">
		<div class="row">
			<div class="col-md-4">
				<div class="card mb-3">
					<div class="card-body">
						<h4>Articles By Category</h4>
						<?php
						$cats = array();
						foreach ($types as $type):
							$val = $type->articles()
								->where('datetimenews', '>=', $filters['start'] . ' 00:00:00')
								->where('datetimenews', '<', $filters['end'] . ' 00:00:00')
								->count();

							$cats[$type->name] = $val;
						endforeach;

						$cats = array_filter($cats);
						?>
						<div>
							<canvas id="news-categories" class="pie-chart" width="275" height="275" data-labels="{{ json_encode(array_keys($cats)) }}" data-values="{{ json_encode(array_values($cats)) }}" data-border="<?php echo (auth()->user()->facet('theme.admin.mode') == 'dark' ? '"rgba(0, 0, 0, 0.6)"' : '"#fff"'); ?>">
								<table class="table">
									<caption class="sr-only">Orders By Category</caption>
									<thead>
										<tr>
											<th scope="col">Category</th>
											<th scope="col" class="text-right">Total</th>
										</tr>
									</thead>
									<tbody>
										@foreach ($cats as $name => $val)
										<tr>
											<td>
												<span class="legend-key"></span> {{ $name }}
											</td>
											<td class="text-right">
												{{ number_format($val) }}
											</td>
										</tr>
										@endforeach
									</tbody>
								</table>
							</canvas>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-8">
				<?php
				$tagusers = $types->filter(function($item)
				{
					return $item->tagusers == 1;
				});
				foreach ($tagusers as $type):
					$stats = $type->stats($filters['start'], $filters['end']);
					?>
					<h3>{{ $type->name }}</h3>
					<div class="card mb-3">
						<div class="card-body">
							<h4>Daily Reservations</h4>
							<?php /*<canvas id="sparkline" class="sparkline-chart" width="275" height="30" data-labels="{{ json_encode(array_keys($stats['daily'])) }}" data-values="{{ json_encode(array_values($stats['daily'])) }}">
								@foreach ($stats['daily'] as $item)
									{{ $item->timestamp }}: {{ $item->count }}<br />
								@endforeach
							</canvas>*/ ?>

							<div id="activity{{ $type->id }}" class="heatmap" data-src="activity_data{{ $type->id }}">
								<input type="hidden" id="activity_data{{ $type->id }}" value="{{ json_encode($stats['daily']) }}" />
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-4">
							<div class="card">
								<div class="card-body">
									<div class="stat-block">
										<div class="text-success">
											<span class="fa fa-check display-4 float-left" aria-hidden="true"></span>
											<span class="value">{{ number_format($stats['reservations']) }}</span><br />
											<span class="key">{{ trans('news::news.reservations') }}</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card">
								<div class="card-body">
									<div class="stat-block">
										<div class="text-info">
											<span class="fa fa-refresh display-4 float-left" aria-hidden="true"></span>
											<span class="value">{{ number_format($stats['repeat_users']) }}</span><br />
											<span class="key">{{ trans('news::news.repeat users') }}</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card">
								<div class="card-body">
									<div class="stat-block">
										<div class="text-danger">
											<span class="fa fa-trash display-4 float-left" aria-hidden="true"></span>
											<span class="value">{{ number_format($stats['canceled']) }}</span><br />
											<span class="key">{{ trans('news::news.canceled reservations') }}</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="card mb-3">
								<div class="card-body">
									<h4>Most Reservations</h4>
									<table class="table table-hover">
										<caption class="sr-only">Most Reservations</caption>
										<thead>
											<tr>
												<th scope="col">Name</th>
												<th scope="col" class="text-right">Reservations</th>
											</tr>
										</thead>
										<tbody>
											@foreach ($stats['users'] as $userid => $val)
											<?php
											$user = App\Modules\Users\Models\User::find($userid);
											?>
											<tr>
												<td>
													<a href="{{ route('admin.users.show', ['id' => $userid]) }}">{{ $user ? $user->name . ' (' . $user->username . ')' : $userid }}</a>
												</td>
												<td class="text-right">
													{{ number_format($val) }}
												</td>
											</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card mb-3">
								<div class="card-body">
									<h4>Top Topics</h4>
									<?php
									$assocs = App\Modules\News\Models\Association::query()->whereNotNull('comment')->get();
									foreach ($assocs as $assoc)
									{
										$keywords = $assoc->extractKeywords($assoc->comment, 0);
										$keywords = array_keys($keywords);

										if (!empty($keywords))
										{
											$tags = array();

											foreach ($keywords as $match)
											{
												if ($assoc->isTag($match))
												{
													$tags[] = $match;
												}
											}

											$assoc->setTags($tags);
										}
									}
									?>
									<table class="table table-hover">
										<caption class="sr-only">Top Topics</caption>
										<thead>
											<tr>
												<th scope="col">Topic</th>
												<th scope="col" class="text-right">Occurrences</th>
											</tr>
										</thead>
										<tbody>
											@foreach ($stats['tags'] as $i => $tag)
												<tr>
													<td><span class="badge badge-secondary">{{ $tag->tag->name }}</span></td>
													<td class="text-right">{{ $tag->total }}</td>
												</tr>
											@endforeach
											@if ($i < 9)
												@php
												$i++;
												@endphp
												@for ($i; $i < 10; $i++)
													<tr>
														<th scope="row"><span class="text-muted">-</span></th>
														<td class="text-right"></td>
													</tr>
												@endfor
											@endif
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
					<?php
				endforeach;
				?>
			</div>

		</div>
	</div>
</form>

@stop