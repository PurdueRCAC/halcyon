@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ timestamped_asset('module/core/vendor/chartjs/Chart.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/queues/js/admin.js') }}"></script>
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
{{ trans('queues::queues.module name') }}: {{ trans('queues::queues.stats') }}
@stop

@section('content')
@component('queues::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.queues.stats') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-12 filter-select text-right">
				<label class="sr-only visually-hidden" for="filter_type">{{ trans('queues::queues.type') }}</label>
				<select name="type" id="filter_type" class="form-control filter filter-submit">
					<option value="0">{{ trans('queues::queues.all types') }}</option>
					@foreach ($types as $type)
						<option value="{{ $type->id }}"<?php if ($filters['type'] == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
					@endforeach
				</select>

				<label class="sr-only visually-hidden" for="filter_resource">{{ trans('queues::queues.resource') }}</label>
				<select name="resource" id="filter_resource" class="form-control filter filter-submit">
					<option value="0">{{ trans('queues::queues.all resources') }}</option>
					@foreach ($resources as $res)
						<?php $selected = ($res->id == $filters['resource'] ? ' selected="selected"' : ''); ?>
						<option value="{{ $res->id }}"<?php echo $selected; ?>>{{ str_repeat('- ', $res->level) . $res->name }}</option>
					@endforeach
				</select>

				<label class="sr-only visually-hidden" for="filter_class">{{ trans('queues::queues.class') }}</label>
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
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="card">
					<div class="card-body">
						<h3 class="card-title">New queues (weekly) for the past year</h3>
						<div>
							<?php
							//$start = Carbon\Carbon::now()->modify('-1 year');
							$day = date('w');
							$week_start = Carbon\Carbon::now()->modify('-' . $day . ' days');
							$start = $week_start->modify('-1 year');

							for ($d = 0; $d < 54; $d++)
							{
								$weekstart = $start->format('Y-m-d');

								$start->modify('+1 week');
								$weekend   = $start->format('Y-m-d') . ' 00:00:00';

								$qry = App\Modules\Queues\Models\Queue::query()
									->where('datetimecreated', '>', $weekstart . ' 00:00:00')
									->where('datetimecreated', '<', $weekend);
								if ($filters['type'])
								{
									$qry->where('queuetype', '=', $filters['type']);
								}
								$stats[$weekstart] = $qry->count();
							}
							?>
							<canvas id="sparkline" class="sparkline-chart" width="500" height="110" data-labels="{{ json_encode(array_keys($stats)) }}" data-values="{{ json_encode(array_values($stats)) }}">
								<table>
									<caption>New queues (weekly) for the past year</caption>
									<thead>
										<tr>
											<th scope="col">Week</th>
											<th scope="col">Created</th>
										</tr>
									</thead>
									<tbody>
										@foreach ($stats as $day => $val)
										<tr>
											<td>{{ $day }}</td>
											<td>{{ $val }}</td>
										</tr>
										@endforeach
									</tbody>
								</table>
							</canvas>
						</div>
					</div>
				</div>

				<div class="card">
					<div class="card-body">
						<h3 class="card-title">Total allocations</h3>
					</div>
					<table class="table">
						<caption class="sr-only visually-hidden">Total allocations</caption>
						<thead>
							<th scope="col">Resource</th>
							<!-- <th scope="col" class="text-right">Queues</th> -->
							<th scope="col" class="text-right">Sold</th>
							<th scope="col" class="text-right">Loaned</th>
						</thead>
						<tbody>
							<?php
							$ress = array();
							?>
							@foreach ($resources as $resource)
								<tr>
									<td><a href="{{ route('admin.queues.stats', ['resource' => $resource->id]) }}">{{ str_repeat('- ', $resource->level) . $resource->name }}</a></td>
									<!-- <td class="text-right"> -->
										<?php
										$unit = 'nodes';

										if ($facet = $resource->getFacet('allocation_unit')):
											$unit = $facet->value;
										endif;

										$total = 0;
										$purchased = array('nodes' => 0, 'cores' => 0, 'sus' => 0);
										$loaned = array('nodes' => 0, 'cores' => 0, 'sus' => 0);

										$s = (new App\Modules\Queues\Models\Size)->getTable();
										$l = (new App\Modules\Queues\Models\Loan)->getTable();
										$q = (new App\Modules\Queues\Models\Queue)->getTable();
										$now = Carbon\Carbon::now();

										foreach ($resource->subresources()->orderBy('name', 'asc')->get() as $subresource):
											$query = $subresource->queues();
											if ($filters['type'])
											{
												$query->where($q . '.queuetype', '=', $filters['type']);
											}
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

											if ($filters['type'])
											{
												$pquery->where($q . '.queuetype', '=', $filters['type']);
											}

											if ($filters['class'] == 'system')
											{
												$pquery->where($q . '.groupid', '<=', 0);
											}
											elseif ($filters['class'] == 'owner')
											{
												$pquery->where($q . '.groupid', '>', 0);
											}

											$purchases = $pquery->first();

											$soldnodes = 0;
											if ($subresource->nodecores != 0)
											{
												$soldnodes = round($purchases->cores / $subresource->nodecores, 1);
											}
											$gpus = ($purchases->sus && $purchases->sus > 0 ? $purchases->sus : round($soldnodes * $subresource->nodegpus));

											$purchased['nodes'] += ($soldnodes ? $soldnodes : $purchases->nodes);
											$purchased['cores'] += $purchases->cores;
											$purchased['sus'] += $gpus;

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

											if ($filters['type'])
											{
												$lquery->where($q . '.queuetype', '=', $filters['type']);
											}

											if ($filters['class'] == 'system')
											{
												$lquery->where($q . '.groupid', '<=', 0);
											}
											elseif ($filters['class'] == 'owner')
											{
												$lquery->where($q . '.groupid', '>', 0);
											}

											$loans = $lquery->first();

											$loannodes = 0;
											if ($subresource->nodecores != 0)
											{
												$loannodes = round($loans->cores / $subresource->nodecores, 1);
											}
											$gpus = ($loans->sus && $loans->sus > 0 ? $loans->sus : round($loannodes * $subresource->nodegpus));

											$loaned['nodes'] += ($loannodes ? $loannodes : $loans->nodes);
											$loaned['cores'] += $loans->cores;
											$loaned['sus'] += $gpus;
										endforeach;

										$ress[$resource->name] = $total;

										//echo $total;
										?>
									<!-- </td> -->
									<td class="text-right">
										@if ($unit == 'sus')
											@if ($purchased['sus'])
												{{ number_format($purchased['sus']) }} <span class="text-muted"><abbr title="Service Units">SUs</abbr></span>
											@else
												-
											@endif
										@elseif ($unit == 'gpus')
											@if ($purchased['cores'] || $purchased['sus'])
												{{ number_format($purchased['cores']) }} <span class="text-muted">cores</span>,
												{{ number_format($purchased['sus']) }} <span class="text-muted">GPUs</span>
											@else
												-
											@endif
										@else
											@if ($purchased['nodes'] || $purchased['cores'])
												{{ number_format($purchased['nodes'], 1) }} <span class="text-muted">nodes</span>,
												{{ number_format($purchased['cores']) }} <span class="text-muted">cores</span>
											@else
												-
											@endif
										@endif
									</td>
									<td class="text-right">
										@if ($unit == 'sus')
											@if ($loaned['sus'])
												{{ number_format($loaned['sus']) }} <span class="text-muted"><abbr title="Service Units">SUs</abbr></span>
											@else
												-
											@endif
										@elseif ($unit == 'gpus')
											@if ($loaned['cores'] || $loaned['sus'])
												{{ number_format($loaned['cores']) }} <span class="text-muted">cores</span>,
												{{ number_format($loaned['sus']) }} <span class="text-muted">GPUs</span>
											@else
												-
											@endif
										@else
											@if ($loaned['nodes'] || $loaned['cores'])
												{{ number_format($loaned['nodes'], 1) }} <span class="text-muted">nodes</span>,
												{{ number_format($loaned['cores']) }} <span class="text-muted">cores</span>
											@else
												-
											@endif
										@endif
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card">
					<div class="card-body">
						<h3 class="card-title">Queues by type</h3>
						<?php
						$cats = array();
						foreach ($types as $type):
							$cats[$type->name] = $type->queues()->count();
						endforeach;

						$cats = array_filter($cats);
						/*foreach ($cats as $key => $val):
							$cats[$key] = ($val / 100);
						endforeach;*/
						?>
						<div>
							<canvas id="queue-types" class="pie-chart" width="275" height="275" data-labels="{{ json_encode(array_keys($cats)) }}" data-values="{{ json_encode(array_values($cats)) }}">
								<table class="table">
									<caption class="sr-only visually-hidden">Queues by type</caption>
									<thead>
										<tr>
											<th scope="col">Queue</th>
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

				<div class="card">
					<div class="card-body">
						<h3 class="card-title">Queues by resource</h3>
						<?php
						$ress = array_filter($ress);
						?>
						<div>
							<canvas id="queue-types" class="pie-chart" width="275" height="275" data-labels="{{ json_encode(array_keys($ress)) }}" data-values="{{ json_encode(array_values($ress)) }}">
								<table class="table">
									<caption class="sr-only visually-hidden">Queues by resource</caption>
									<thead>
										<tr>
											<th scope="col">Queue</th>
											<th scope="col" class="text-right">Total</th>
										</tr>
									</thead>
									<tbody>
										@foreach ($ress as $name => $val)
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
		</div>
	@else
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-6">
				<div class="card">
					<div class="card-body">
						<h3 class="card-title">New queues (weekly) for the past year</h3>
						<div>
							<?php
							$subresources = $resource->subresources()->orderBy('name', 'asc')->get();
							$ids = $subresources->pluck('id')->toArray();

							//$start = Carbon\Carbon::now()->modify('-1 year');
							$day = date('w');
							$week_start = Carbon\Carbon::now()->modify('-' . $day . ' days');
							$start = $week_start->modify('-1 year');

							for ($d = 0; $d < 54; $d++)
							{
								$weekstart = $start->format('Y-m-d');

								$start->modify('+1 week');
								$weekend   = $start->format('Y-m-d') . ' 00:00:00';

								$qry = App\Modules\Queues\Models\Queue::query()
									->whereIn('subresourceid', $ids)
									->where('datetimecreated', '>', $weekstart . ' 00:00:00')
									->where('datetimecreated', '<', $weekend);
								if ($filters['type'])
								{
									$qry->where('queuetype', '=', $filters['type']);
								}
								$stats[$weekstart] = $qry->count();
							}
							?>
							<canvas id="sparkline" class="sparkline-chart" width="500" height="110" data-labels="{{ json_encode(array_keys($stats)) }}" data-values="{{ json_encode(array_values($stats)) }}">
								<table>
									<caption>New queues (weekly) for the past year</caption>
									<thead>
										<tr>
											<th scope="col">Week</th>
											<th scope="col">Created</th>
										</tr>
									</thead>
									<tbody>
								@foreach ($stats as $day => $val)
										<tr>
											<td>{{ $day }}</td>
											<td>{{ $val }}</td>
										</tr>
								@endforeach
									</tbody>
								</table>
							</canvas>
						</div>
					</div>
				</div>

				<div class="card">
					<div class="card-body">
						<h3 class="card-title">Total allocations by sub-resource</h3>
					</div>
					<table class="table">
						<caption class="sr-only visually-hidden">Total allocations by sub-resource</caption>
						<thead>
							<th scope="col">Sub-Resource</th>
							<th scope="col" class="text-right">Queues</th>
							<th scope="col" class="text-right">Sold</th>
							<th scope="col" class="text-right">Loaned</th>
							<th scope="col" class="text-right">Remaining</th>
						</thead>
						<tbody>
							@foreach ($subresources as $subresource)
								<?php
								$unit = 'nodes';

								if ($facet = $resource->getFacet('allocation_unit')):
									$unit = $facet->value;
								endif;

								$total = 0;
								$purchased = array('nodes' => 0, 'cores' => 0, 'sus' => 0);
								$loaned    = array('nodes' => 0, 'cores' => 0, 'sus' => 0);
								$remaining = array('nodes' => 0, 'cores' => 0, 'sus' => 0);

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
									->where(function($where) use ($now, $q)
									{
										$where->whereNull($q . '.datetimeremoved')
											->orWhere($q . '.datetimeremoved', '>', $now->toDateTimeString());
									})
									->where($s . '.corecount', '>=', 0)
									->where($s . '.serviceunits', '>=', 0)
									->where(function($where) use ($now, $s)
									{
										$where->whereNull($s . '.datetimestop')
											->orWhere($s . '.datetimestop', '>', $now->toDateTimeString());
									})
									->where($s . '.datetimestart', '<=', $now->toDateTimeString());

								if ($filters['type'])
								{
									$pquery->where($q . '.queuetype', '=', $filters['type']);
								}

								if ($filters['class'] == 'system')
								{
									$pquery->where($q . '.groupid', '<=', 0);
								}
								elseif ($filters['class'] == 'owner')
								{
									$pquery->where($q . '.groupid', '>', 0);
								}

								$purchases = $pquery->first();

								$soldnodes = 0;
								if ($subresource->nodecores != 0)
								{
									$soldnodes = round($purchases->cores / $subresource->nodecores, 1);
								}

								$purchased['nodes'] += ($soldnodes ? $soldnodes : $purchases->nodes);
								$purchased['cores'] += $purchases->cores;
								$purchased['sus']   += ($subresource->nodegpus ? $subresource->nodegpus * $purchased['nodes'] : $purchases->sus);

								$lquery = App\Modules\Queues\Models\Loan::query()
									->select(Illuminate\Support\Facades\DB::raw('SUM(' . $l . '.nodecount) AS nodes, SUM(' . $l . '.corecount) AS cores, SUM(' . $l . '.serviceunits) AS sus'))
									->join($q, $q . '.id', $l . '.queueid')
									->where($q . '.subresourceid', '=', $subresource->id)
									->where(function($where) use ($now, $q)
									{
										$where->whereNull($q . '.datetimeremoved')
											->orWhere($q . '.datetimeremoved', '>', $now->toDateTimeString());
									})
									->where($l . '.corecount', '>=', 0)
									->where($l . '.serviceunits', '>=', 0)
									->where(function($where) use ($now, $l)
									{
										$where->whereNull($l . '.datetimestop')
											->orWhere($l . '.datetimestop', '>', $now->toDateTimeString());
									})
									->where($l . '.datetimestart', '<=', $now->toDateTimeString());

								if ($filters['type'])
								{
									$lquery->where($q . '.queuetype', '=', $filters['type']);
								}

								if ($filters['class'] == 'system')
								{
									$lquery->where($q . '.groupid', '<=', 0);
								}
								elseif ($filters['class'] == 'owner')
								{
									$lquery->where($q . '.groupid', '>', 0);
								}

								$loans = $lquery->first();

								$loanednodes = 0;
								if ($subresource->nodecores != 0 && $loans->cores)
								{
									//echo $subresource->nodecores . ' - ' . $loans->cores . '<Br />';
									$loanednodes = round($loans->cores / $subresource->nodecores, 1);
								}

								$loaned['nodes'] += ($loanednodes ? $loanednodes : $loans->nodes);
								$loaned['cores'] += $loans->cores;
								$loaned['sus']   += ($subresource->nodegpus ? $subresource->nodegpus * $loaned['nodes'] : $loans->sus);

								//----

								$pquery = App\Modules\Queues\Models\Size::query()
									->select(Illuminate\Support\Facades\DB::raw('SUM(' . $s . '.nodecount) AS nodes, SUM(' . $s . '.corecount) AS cores, SUM(' . $s . '.serviceunits) AS sus'))
									->join($q, $q . '.id', $s . '.queueid')
									->where($q . '.subresourceid', '=', $subresource->id)
									->where(function($where) use ($now, $q)
									{
										$where->whereNull($q . '.datetimeremoved')
											->orWhere($q . '.datetimeremoved', '>', $now->toDateTimeString());
									})
									->where($s . '.corecount', '>=', 0)
									->where($s . '.serviceunits', '>=', 0)
									->where(function($where) use ($now, $s)
									{
										$where->whereNull($s . '.datetimestop')
											->orWhere($s . '.datetimestop', '>', $now->toDateTimeString());
									})
									->where($s . '.datetimestart', '<=', $now->toDateTimeString())
									->where($q . '.groupid', '<=', 0);

								$purchaser = $pquery->first();

								$remaining['nodes'] += $purchaser->nodes;
								$remaining['cores'] += $purchaser->cores;
								$remaining['sus']   += $purchaser->sus;

								$lquery = App\Modules\Queues\Models\Loan::query()
									->select(Illuminate\Support\Facades\DB::raw('SUM(' . $l . '.nodecount) AS nodes, SUM(' . $l . '.corecount) AS cores, SUM(' . $l . '.serviceunits) AS sus'))
									->join($q, $q . '.id', $l . '.queueid')
									->where($q . '.subresourceid', '=', $subresource->id)
									->where(function($where) use ($now, $q)
									{
										$where->whereNull($q . '.datetimeremoved')
											->orWhere($q . '.datetimeremoved', '>', $now->toDateTimeString());
									})
									->where($l . '.corecount', '>=', 0)
									->where($l . '.serviceunits', '>=', 0)
									->where(function($where) use ($now, $l)
									{
										$where->whereNull($l . '.datetimestop')
											->orWhere($l . '.datetimestop', '>', $now->toDateTimeString());
									})
									->where($l . '.datetimestart', '<=', $now->toDateTimeString())
									->where($q . '.groupid', '<=', 0);

								$loansr = $lquery->first();

								$remaining['nodes'] += $loansr->nodes;
								$remaining['cores'] += $loansr->cores;
								$remaining['sus']   += $loansr->sus;

								if ($subresource->nodecores != 0 && $remaining['cores'])
								{
									$remaining['nodes'] = round($remaining['cores'] / $subresource->nodecores, 1);
								}
								$remaining['sus'] = ($subresource->nodegpus ? $subresource->nodegpus * $remaining['nodes'] : $remaining['sus']);

								$remaining['nodes'] = $remaining['nodes'] - ($purchased['nodes'] + $loaned['nodes']);
								$remaining['cores'] = $remaining['cores'] - ($purchased['cores'] + $loaned['cores']);
								$remaining['sus']   = $remaining['sus'] - ($purchased['sus'] + $loaned['sus']);
								?>
								<tr>
									<td>{{ $subresource->name }}</td>
									<td class="text-right">{{ $total }}</td>
									<td class="text-right">
										@if ($unit == 'sus')
											@if ($purchased['sus'])
												{{ number_format($purchased['sus']) }} <span class="text-muted"><abbr title="Service Units">SUs</abbr></span>
											@else
												-
											@endif
										@elseif ($unit == 'gpus')
											@if ($purchased['cores'])
												{{ number_format($purchased['cores']) }} <span class="text-muted">cores</span><br />
												{{ number_format($purchased['sus']) }} <span class="text-muted">GPUs</span>
											@else
												-
											@endif
										@else
											@if ($purchased['nodes'] || $purchased['cores'])
												{{ number_format($purchased['nodes'], 1) }} <span class="text-muted">nodes</span><br />
												{{ number_format($purchased['cores']) }} <span class="text-muted">cores</span>
											@else
												-
											@endif
										@endif
									</td>
									<td class="text-right">
										@if ($unit == 'sus')
											@if ($loaned['sus'])
												{{ number_format($loaned['sus']) }} <span class="text-muted"><abbr title="Service Units">SUs</abbr></span>
											@else
												-
											@endif
										@elseif ($unit == 'gpus')
											@if ($loaned['cores'])
												{{ number_format($loaned['cores']) }} <span class="text-muted">cores</span><br />
												{{ number_format($loaned['sus']) }} <span class="text-muted">GPUs</span>
											@else
												-
											@endif
										@else
											@if ($loaned['nodes'] || $loaned['cores'])
												{{ number_format($loaned['nodes'], 1) }} <span class="text-muted">nodes</span><br />
												{{ number_format($loaned['cores']) }} <span class="text-muted">cores</span>
											@else
												-
											@endif
										@endif
									</td>
									<td class="text-right">
										@if ($unit == 'sus')
											@if ($remaining['sus'])
												{{ number_format($remaining['sus']) }} <span class="text-muted"><abbr title="Service Units">SUs</abbr></span>
											@else
												-
											@endif
										@elseif ($unit == 'gpus')
											@if ($remaining['cores'])
												@if ($remaining['cores'] < 0)
													<span class="text-danger">
												@endif
												{{ number_format($remaining['cores']) }}
												@if ($remaining['cores'] < 0)
													</span>
												@endif
												<span class="text-muted">cores</span><br />

												@if ($remaining['sus'] < 0)
													<span class="text-danger">
												@endif
												{{ number_format($remaining['sus']) }}
												@if ($remaining['sus'] < 0)
													</span>
												@endif
												<span class="text-muted">GPUs</span>
											@else
												-
											@endif
										@else
											@if ($remaining['nodes'] || $remaining['cores'])
												@if ($remaining['nodes'] < 0)
													<span class="text-danger">
												@endif
												{{ number_format($remaining['nodes'], 1) }}
												@if ($remaining['nodes'] < 0)
													</span>
												@endif
												<span class="text-muted">nodes</span><br />
												@if ($remaining['cores'] < 0)
													<span class="text-danger">
												@endif
												{{ number_format($remaining['cores']) }}
												@if ($remaining['cores'] < 0)
													</span>
												@endif
												<span class="text-muted">cores</span>
											@else
												-
											@endif
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
						<div class="card-body">
							<h3 class="card-title">Top Allocated Queues</h3>
						</div>
						<table class="table">
							<caption class="sr-only visually-hidden">Top Allocated Queues</caption>
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
								if ($filters['type'])
								{
									$query->where('queuetype', '=', $filters['type']);
								}
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
									<td><a href="{{ route('admin.queues.show', ['id' => $qu->id]) }}">{{ $qu->name }}</a></td>
									<td>{{ $qu->subresource->name }}</td>
									<td class="text-right">
										@if ($unit == 'sus')
											{{ number_format($qu->serviceunits) }} <span class="text-muted"><abbr title="Service Units">SUs</abbr></span>
										@elseif ($unit == 'gpus')
											<?php
											$nodes = ($qu->subresource->nodecores ? round($qu->totalcores / $qu->subresource->nodecores, 1) : 0);
											$gpus = ($qu->serviceunits && $qu->serviceunits > 0 ? $qu->serviceunits : round($nodes * $qu->subresource->nodegpus));
											?>
											{{ number_format($qu->totalcores) }} <span class="text-muted">cores</span>,
											{{ number_format($gpus) }} <span class="text-muted">GPUs</span>
										@else
											{{ number_format($qu->totalnodes, 1) }} <span class="text-muted">nodes</span>,
											{{ number_format($qu->totalcores) }} <span class="text-muted">cores</span>
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
</form>

@stop
