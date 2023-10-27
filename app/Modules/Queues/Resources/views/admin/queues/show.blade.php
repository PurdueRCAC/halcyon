@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/queues/js/admin.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('queues::queues.module name'),
		route('admin.queues.index')
	)
	->append(
		trans('queues::queues.queues'),
		route('admin.queues.index')
	)
	->append(
		('#' . $row->id . ' - ' . $row->name . ' (' . $row->subresource->name . ')')
	);
@endphp

@section('toolbar')
	{!!
		Toolbar::spacer();
		Toolbar::link('back', trans('queues::queues.back'), route('admin.queues.index'), false) ;
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('queues::queues.module name') }}: {{ '#' . $row->id . ' - ' . $row->name . ' (' . $row->subresource->name . ')' }}
@stop

@section('content')

@if ($row->trashed())
	<div class="alert alert-warning">{{ trans('queues::queues.entry marked as trashed') }}</div>
@endif

<?php
$cores = '-';
$mem   = '-';
$unit = 'nodes';

if ($row->subresource):
	$cores = $row->subresource->nodecores;
	$mem = $row->subresource->nodemem;

	if ($facet = $row->resource->getFacet('allocation_unit')):
		$unit = $facet->value;
	endif;
endif;
?>

	<nav class="container-fluid">
		<ul id="queue-tabs" class="nav nav-tabs" role="tablist">
			<li class="nav-item" role="presentation"><a class="nav-link active" href="#queue-details" data-toggle="tab" role="tab" id="queue-details-tab" aria-controls="queue-details" aria-selected="true">{{ trans('queues::queues.queue') }}</a></li>
			<li class="nav-item" role="presentation"><a class="nav-link" href="#queue-history" data-toggle="tab" role="tab" id="queue-history-tab" aria-controls="queue-history" aria-selected="false">{{ trans('queues::queues.history') }}</a></li>
		</ul>
	</nav>
	<div class="tab-content" id="queue-tabs-contant">
		<div class="tab-pane show active" id="queue-details" role="tabpanel" aria-labelledby="queue-details-tab">

<div class="row">
	<div class="col-md-6">

		<div class="card">
			<div class="card-header">
				@if (auth()->user()->can('edit queues'))
					<a class="btn btn-sm btn-link float-right" data-toggl="modal" href="{{ route('admin.queues.edit', ['id' => $row->id]) }}" data-tip="{{ trans('global.edit') }}">
						<span class="fa fa-pencil" aria-hidden="true"></span>
						<span class="sr-only">{{ trans('global.edit') }}</span>
					</a>
				@endif
				<h3 class="card-title">{{ trans('global.details') }}</h3>
			</div>
			<div class="card-body">

				<table>
					<caption class="sr-only">{{ trans('global.details') }}</caption>
					<tbody>
						<tr>
							<th scope="row">{{ trans('queues::queues.scheduling') }}</th>
							<td>
								@if ($row->started)
									<span class="badge badge-success">{{ trans('queues::queues.started') }}</span>
								@else
									<span class="badge badge-danger">{{ trans('queues::queues.stopped') }}</span>
								@endif
							</td>
						</tr>
						<tr>
							<th scope="row">{{ trans('queues::queues.name') }}</th>
							<td>{{ $row->name }}</td>
						</tr>
						<tr>
							<th scope="row">{{ trans('queues::queues.subresource') }}</th>
							<td>{{ $row->subresource->name }}</td>
						</tr>
						<tr>
							<th scope="row">{{ trans('queues::queues.class') }}</th>
							<td>
								@if ($row->groupid <= 0)
									<span class="icon-cpu" aria-hidden="true"></span> {{ trans('queues::queues.system') }}
								@else
									<span class="icon-user" aria-hidden="true"></span> {{ trans('queues::queues.owner') }}
								@endif
							</td>
						</tr>
						<tr>
							<th scope="row">{{ trans('queues::queues.group') }}</th>
							<td>
								@if ($row->group)
									<a href="{{ route('admin.groups.show', ['id' => $row->groupid]) }}">{{ $row->group->name }}</a>
								@endif
							</td>
						</tr>
						<tr>
							<th scope="row">Quality of Service</th>
							<td>
								@if (count($row->queueqoses) > 0)
									@foreach ($row->queueqoses as $queueqos)
										<a href="{{ route('admin.queues.qos.edit', ['id' => $queueqos->qosid]) }}">{{ $queueqos->qos->name }}</a><br />
									@endforeach
								@endif
							</td>
						</tr>
					</tbody>
				</table>

			</div>
		</div>

		<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
		<input type="hidden" name="subresourceid" id="field-subresourceid" value="{{ $row->subresourceid }}" />
	</div>
	<div class="col-md-6">
		<div class="row">
			<div class="col-md-5">
				<div class="card">
					<div class="card-body">
						<div class="stat-block d-flex">
							<div class="text-info">
								<span class="fa fa-user display-4 float-left" aria-hidden="true"></span>
							</div>
							<div class="flex-grow-1">
								<span class="text-muted">Users</span><br />
								<strong class="floa-right display-5" style="font-size: 1.8rem; line-height: 1.5em;">{{ number_format($row->users()->count()) }}</strong>
								<?php
								/*$recent = $row->users()->orderBy('datetimecreated', 'desc')->first();
								?>
								@if ($recent)
									<div>Most recent added {{ $recent->datetimecreated->diffForHumans() }}</div>
								@endif
								*/ ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-7">
				<div class="card">
					<div class="card-body">
						<div class="stat-block d-flex">
							@if (!$row->active)
							<div class="text-warning">
								<span class="fa fa-exclamation-triangle display-4" aria-hidden="true"></span>
							</div>
							<div class="flex-grow-1">
								<span class="text-muted">Allocation</span><br />
								@if ($upcoming = $row->getUpcomingLoanOrPurchase())
									@if ($upcoming->serviceunits > 0)
										{{ number_format($upcoming->serviceunits) }} <span class="text-muted">SUs</span>
									@else
										{{ number_format($upcoming->cores) }} <span class="text-muted">{{ strtolower(trans('queues::queues.cores')) }}</span>
									@endif
									<br /><span class="text-success">starts {{ $upcoming->datetimestart->diffForHumans() }}</span>
								@endif
								{{ trans('queues::queues.queue has no active resources') }}
							</div>
							@else
							<div class="text-success">
								<span class="fa fa-battery display-4 float-left" aria-hidden="true"></span>
							</div>
							<div class="flex-grow-1">
								<span class="text-muted">Allocation</span><br />
								<strong class="display-5" style="font-size: 1.8rem; line-height: 1.5em;">
									@if ($unit == 'sus')
										{{ number_format($row->serviceunits) }} <span class="text-muted">{{ strtolower(trans('queues::queues.' . $unit)) }}</span>
									@elseif ($unit == 'gpus')
										<?php
										$nodes = ($row->subresource->nodecores ? round($row->totalcores / $row->subresource->nodecores, 1) : 0);
										$gpus = round($nodes * $row->subresource->nodegpus); //($row->serviceunits && $row->serviceunits > 0 ? $row->serviceunits : round($nodes * $row->subresource->nodegpus));
										?>
										{{ number_format($row->totalcores) }} <span class="text-muted">{{ strtolower(trans('queues::queues.cores')) }}</span>,
										{{ number_format($gpus) }} <span class="text-muted">{{ strtolower(trans('queues::queues.' . $unit)) }}</span>
									@else
										{{ number_format($row->totalcores) }} <span class="text-muted">{{ strtolower(trans('queues::queues.cores')) }}</span>,
										{{ number_format($row->totalnodes) }} <span class="text-muted">{{ strtolower(trans('queues::queues.nodes')) }}</span>
									@endif
								</strong>
							</div>
							@endif
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-12">
				<details class="card">
					<summary class="card-header">{{ trans('queues::queues.walltime') }}</summary>

					<div class="card-body">
						<table>
							<caption class="sr-only">{{ trans('queues::queues.walltime') }}</caption>
							<tbody>
								<tr>
									<th scope="row">{{ trans('queues::queues.default walltime') }}</th>
									<td>{{ $row->defaultHumanWalltime }}</td>
								</tr>
								<tr>
									<th scope="row">{{ trans('queues::queues.max walltime') }}</th>
									<td>{{ $row->humanWalltime }}</td>
								</tr>
								<tr>
									<th scope="row">{{ trans('queues::queues.priority') }}</th>
									<td>{{ $row->priority }}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</details>

				<details class="card">
					<summary class="card-header">{{ trans('queues::queues.jobs') }}</summary>

					<div class="card-body">
						<table>
							<caption class="sr-only">{{ trans('queues::queues.jobs') }}</caption>
							<tbody>
								<tr>
									<th scope="col">{{ trans('queues::queues.max jobs queued') }}</th>
									<td class="text-right">{{ number_format($row->maxjobsqueued) }}</td>
								</tr>
								<tr>
									<th scope="col">{{ trans('queues::queues.max jobs queued per user') }}</th>
									<td class="text-right">{{ number_format($row->maxjobsqueueduser) }}</td>
								</tr>
								<tr>
									<th scope="col">{{ trans('queues::queues.max jobs run') }}</th>
									<td class="text-right">{{ number_format($row->maxjobsrun) }}</td>
								</tr>
								<tr>
									<th scope="col">{{ trans('queues::queues.max jobs run per user') }}</th>
									<td class="text-right">{{ number_format($row->maxjobsrunuser) }}</td>
								</tr>
								<tr>
									<th scope="col">{{ trans('queues::queues.max job cores') }}</th>
									<td class="text-right">{{ number_format($row->maxjobcores) }}</td>
								</tr>
								<tr>
									<th scope="col">{{ trans('queues::queues.max ijob factor') }}</th>
									<td class="text-right">{{ number_format($row->maxijobfactor) }}</td>
								</tr>
								<tr>
									<th scope="col">{{ trans('queues::queues.max ijob user factor') }}</th>
									<td class="text-right">{{ number_format($row->maxijobuserfactor) }}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</details>

				<details class="card">
					<summary class="card-header">{{ trans('queues::queues.nodes') }}</summary>

					<div class="card-body">
						<table>
							<caption class="sr-only">{{ trans('queues::queues.nodes') }}</caption>
							<tbody>
								<tr>
									<th scope="col">{{ trans('queues::queues.node cores default') }}</th>
									<td class="text-right">{{ number_format($row->nodecoresdefault) }}</td>
								</tr>
								<tr>
									<th scope="col">{{ trans('queues::queues.node cores min') }}</th>
									<td class="text-right">{{ number_format($row->nodecoresmin) }}</td>
								</tr>
								<tr>
									<th scope="col">{{ trans('queues::queues.node cores max') }}</th>
									<td class="text-right">{{ number_format($row->nodecoresmax) }}</td>
								</tr>
								<tr>
									<th scope="col">{{ trans('queues::queues.node mem min') }}</th>
									<td class="text-right">{{ $row->nodememmin }}</td>
								</tr>
								<tr>
									<th scope="col">{{ trans('queues::queues.node mem max') }}</th>
									<td class="text-right">{{ $row->nodememmax }}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</details>
			</div>
		</div>
	</div>

	<div class="col-md-12">
		<div class="card">
			<div class="card-header">
				<div class="float-right">
					<a href="#dialog-sell" id="node-sell" data-toggle="modal" data-target="#dialog-sell" class="btn btn-secondary dialog-btn icon-dollar-sign">{{ trans('queues::queues.sell') }}</a>
					<a href="#dialog-loan" id="node-loan" data-toggle="modal" data-target="#dialog-loan" class="btn btn-secondary dialog-btn icon-shuffle">{{ trans('queues::queues.loan') }}</a>
				</div>
				<h3 class="card-title">Allocations</h3>
			</div>
			<div class="card-bod">
				<?php
				$purchases = $row->sizes()->get();
				$loans = $row->loans()->get();

				$nodecores = $row->subresource->nodecores;
				$nodegpus  = $row->subresource->nodegpus;
				$total = 0;

				$items = $purchases;//$purchases->merge($sold);
				$items = $items->merge($loans)->sortBy('datetimestart');
				?>
				<table class="table table-hover adminlist">
					<caption class="sr-only">{{ trans('queues::queues.purchases and loans') }}</caption>
					<thead>
						<tr>
							<th scope="col">{{ trans('queues::queues.id') }}</th>
							<th scope="col">{{ trans('queues::queues.start') }}</th>
							<th scope="col">{{ trans('queues::queues.end') }}</th>
							<th scope="col">{{ trans('queues::queues.action') }}</th>
							<th scope="col">{{ trans('queues::queues.source') }}</th>
							<th scope="col">{{ trans('queues::queues.resource') }}</th>
							<th scope="col">{{ trans('queues::queues.queue') }}</th>
							<th scope="col" class="text-right">{{ trans('queues::queues.amount') }}</th>
							<th scope="col" class="text-right">{{ trans('queues::queues.total') }}</th>
							<th scope="col" class="text-right" colspan="2">{{ trans('queues::queues.options') }}</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($items as $item):
							if ($item->hasEnded()):
								$item->total = $total;
								continue;
							endif;

							/*if (($item->sellerqueueid == $row->id && $item->corecount > 0)
							|| ($item->corecount < 0 && $item->type == 0)
							|| ($item->corecount < 0 && $item->type == 1))
							{
								$total -= $nodecores ? round($item->corecount / $nodecores, 1) : 0;
							}
							else if (($item->corecount > 0 && $item->type == 0)
								|| ($item->corecount > 0 && $item->type == 1))
							{
								$total += $nodecores ? round($item->corecount / $nodecores, 1) : 0;
							}*/
							if ($unit == 'sus'):
								$total += $item->serviceunits;
							elseif ($unit == 'gpus'):
								$nodes = ($nodecores ? round($item->corecount / $nodecores, 1) : 0);
								$total += ($item->serviceunits > 0 ? $item->serviceunits : ceil($nodes * $nodegpus));
							else:
								$total += $item->corecount; //$nodecores ? round($item->corecount / $nodecores, 1) : 0;
							endif;

							$item->total = $total;
						endforeach;

						$items = $items->sortByDesc('datetimestart'); //->slice(0, 20);

						foreach ($items as $item):
							$cl = '';
							if ($item->hasEnd() && $item->hasEnded())
							{
								$cl = 'trashed';
							}
							if ($item->hasStart() && !$item->hasStarted())
							{
								$cl = 'locked';
							}
						?>
						<tr<?php if ($cl) { echo ' class="' . $cl . '"'; } ?>>
							<td>
								{{ $item->id }}
							</td>
							<td>
								@if ($item->hasStart())
									@if (!$item->hasStarted())
										<span class="fa fa-exclamation-triangle text-warning" aria-hidden="true"></span>
										in <time datetime="{{ $item->datetimestart->toDateTimeLocalString() }}">{{ $item->willStart() }}</time>
									@else
										<time datetime="{{ $item->datetimestart->toDateTimeLocalString() }}">{{ $item->datetimestart->format('Y-m-d') }}</time>
									@endif
								@else
									<span class="never">{{ trans('global.immediately') }}</span>
								@endif
							</td>
							<td>
								@if ($item->hasEnd())
									@if (!$item->hasEnded())
										<span class="fa fa-exclamation-triangle text-warning" aria-hidden="true"></span>
										in <time datetime="{{ $item->datetimestop->toDateTimeLocalString() }}">{{ $item->willEnd() }}</time>
									@else
										<time datetime="{{ $item->datetimestop->toDateTimeLocalString() }}">{{ $item->datetimestop->format('Y-m-d') }}</time>
									@endif
								@else
									<span class="never">{{ trans('global.never') }}</span>
								@endif
							</td>
							<td>
								<?php
								$what = '';
								$cls = '';
								if ($item->type == 1):
									$what = 'Loan';
									if ($item->corecount < 0):
										$what .= " to";
										$cls = 'decrease';
									elseif ($item->corecount >= 0):
										$what .= " from";
										$cls = 'increase';
									endif;
								else:
									if ($item->sellerqueueid == $row->id || $item->corecount < 0):
										$what = 'Sale to';
										$cls = 'decrease';
									elseif ($item->corecount >= 0):
										$what = 'Purchase from';
										$cls = 'increase';
									endif;
								endif;

								//$title  = $item->nodecount . " nodes / ";
								//$title .= $item->corecount . " cores; ".$what.": ";
								if ($unit == 'sus'):
									$amt = $item->serviceunits;
								elseif ($unit == 'gpus'):
									$nodes = ($nodecores ? round($item->corecount / $nodecores, 1) : 0);
									$gpus = ($item->serviceunits && $item->serviceunits > 0 ? $item->serviceunits : ceil($nodes * $nodegpus));
									$amt = $item->corecount;
								else:
									/*$amt = $item->nodecount;
									if ($item->corecount):
										$amt = $nodecores ? round($item->corecount / $nodecores, 1) : 0;
									endif;*/
									$amt = $item->corecount;
								endif;

								echo '<a href="#dialog-edit' . $item->id . '" class="dialog-btn">' . $what . '</a>';
								?>
								@if ($comment = $item->comment)
									<br /><span class="text-muted">{{ $comment }}</span>
								@endif
							</td>
							<td>
								@if ($item->sellerqueueid == $row->id)
									{{ $item->queue->group ? $item->queue->group->name : '(Organization Owned)' }}
								@elseif ($item->source)
									{{ $item->source->group ? $item->source->group->name : '(Organization Owned)' }}
								@else
									{{ trans('queues::queues.new hardware') }}
								@endif
							</td>
							<td>
								@if ($item->source)
									{{ $item->source->subresource->name }}
								@endif
							</td>
							<td>
								@if ($item->source)
									{{ $item->source->name }}
								@endif
							</td>
							<td class="text-right text-nowrap">
								@if ($item->hasEnd() && $item->hasEnded())
									<del>
								@endif
								@if ($unit == 'sus')
									<span class="{{ $cls }}">{{ ($cls == 'increase' ? '+' : '-') }} {{ number_format(abs($amt), 1) }}</span>
								@elseif ($unit == 'gpus')
									<span class="{{ $cls }}">{{ ($cls == 'increase' ? '+' : '-') }} {{ number_format(abs($amt)) }}</span> <span class="text-muted">{{ strtolower(trans('queues::queues.cores')) }}</span>,
									<span class="{{ $cls }}">{{ ($cls == 'increase' ? '+' : '-') }} {{ number_format(abs($gpus)) }}</span> <span class="text-muted">{{ strtolower(trans('queues::queues.' . $unit)) }}</span>
								@else
									<span class="{{ $cls }}">{{ ($cls == 'increase' ? '+' : '-') }} {{ number_format(abs($amt)) }}</span> <span class="text-muted">{{ strtolower(trans('queues::queues.cores')) }}</span>,
								@endif
								@if ($item->hasEnd() && $item->hasEnded())
									</del>
								@endif
							</td>
							<td class="text-right">
								{{ number_format($item->total, 1) }}
							</td>
							<td class="text-right">
								<a href="#dialog-edit{{ $item->id }}" class="btn btn-sm edit"
									data-success="{{ trans('global.messages.item updated') }}"
									data-api="{{ route('api.queues.' . ($item->type == 1 ? 'loans' : 'sizes'). '.update', ['id' => $item->id]) }}"
									data-id="{{ $item->id }}"
									data-toggle="modal"
									data-target="#dialog-edit{{ $item->id }}">
									<span class="icon-edit" aria-hidden="true"></span><span class="sr-only">{{ trans('global.button.edit') }}</span>
								</a>
							</td>
							<td>
								@if (auth()->user()->can('edit queues'))
								<button class="btn btn-sm text-danger delete"
									data-confirm="{{ trans('global.confirm delete') }}"
									data-success="{{ trans('global.messages.item deleted', ['count' => 1]) }}"
									data-api="{{ route('api.queues.' . ($item->type == 1 ? 'loans' : 'sizes'). '.delete', ['id' => $item->id]) }}"
									data-id="{{ $item->id }}">
									<span class="icon-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.button.delete') }}</span>
								</button>
								@endif

								<div class="modal dialog" id="dialog-edit{{ $item->id }}" tabindex="-1" aria-labelledby="dialog-edit{{ $item->id }}-title" aria-hidden="true" title="{{ trans('queues::queues.edit ' . ($item->type == 1 ? 'loan' : 'size')) }}">
									<div class="modal-dialog modal-dialog-centered">
										<form class="modal-content dialog-content shadow-sm" method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.queues.' . ($item->type == 1 ? 'loans' : 'sizes') . '.update', ['id' => $item->id]) }}">
											<div class="modal-header">
												<div class="modal-title" id="dialog-edit{{ $item->id }}-title">{{ trans('queues::queues.edit ' . ($item->type == 1 ? 'loan' : 'size')) }}</div>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
											</div>
											<div class="modal-body dialog-body">
												@if ($unit == 'sus')
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<label for="loan-serviceunits{{ $item->id }}">{{ trans('queues::queues.service units') }} <span class="required">{{ trans('global.required') }}</span></label>
															<input type="number" name="serviceunits" class="form-control serviceunits" size="4" id="loan-serviceunits{{ $item->id }}" value="{{ $item->serviceunits }}" step="0.25" />
														</div>
													</div>
												</div>
												<input type="hidden" name="nodecount" class="form-control nodes" size="4" id="loan-nodes{{ $item->id }}" data-cores="{{ $row->subresource->nodecores }}" data-cores-field="loan-cores{{ $item->id }}" value="{{ $nodecores ? round($item->corecount / $nodecores, 1) : $item->nodecount }}" step="0.5" />
												<input type="hidden" name="corecount" class="form-control cores" size="4" id="loan-cores{{ $item->id }}" data-cores="{{ $row->subresource->nodecores }}" data-nodes-field="loan-nodes{{ $item->id }}" value="{{ $item->corecount }}" />
												@elseif ($unit == 'gpus')
												<div class="row">
													<div class="col-md-3">
														<div class="form-group">
															<label for="loan-nodes{{ $item->id }}">{{ trans('queues::queues.nodes') }}</label>
															<input type="number" name="nodecount" class="form-control nodes" size="4" id="loan-nodes{{ $item->id }}" data-cores="{{ $row->subresource->nodecores }}" data-gpus="{{ $row->subresource->nodegpus }}" data-cores-field="loan-cores{{ $item->id }}" data-gpus-field="loan-serviceunits{{ $item->id }}" value="{{ $nodecores ? round($item->corecount / $nodecores, 1) : $item->nodecount }}" step="0.5" />
														</div>
													</div>
													<div class="col-md-5">
														<div class="form-group">
															<label for="loan-cores{{ $item->id }}">{{ trans('queues::queues.cores') }} <span class="required">{{ trans('global.required') }}</span></label>
															<input type="number" name="corecount" class="form-control cores" size="4" id="loan-cores{{ $item->id }}" data-cores="{{ $row->subresource->nodecores }}" data-nodes-field="loan-nodes{{ $item->id }}" value="{{ $item->corecount }}" />
															<span class="text-muted">({{ trans('queues::queues.cores per nodes', ['cores' => $row->subresource->nodecores]) }})</span>
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label for="loan-serviceunits{{ $item->id }}">{{ trans('queues::queues.gpus') }} <span class="required">{{ trans('global.required') }}</span></label>
															<input type="number" name="serviceunits" class="form-control gpus" size="4" id="loan-serviceunits{{ $item->id }}" data-gpus="{{ $row->subresource->nodegpus }}" data-nodes-field="loan-nodes{{ $item->id }}" value="{{ round($item->serviceunits) }}" />
															<span class="text-muted">({{ trans('queues::queues.cores per nodes', ['cores' => $row->subresource->nodegpus]) }})</span>
														</div>
													</div>
												</div>
												@else
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label for="loan-nodes{{ $item->id }}">{{ trans('queues::queues.' . $unit) }}</label>
															<input type="number" name="nodecount" class="form-control nodes" size="4" id="loan-nodes{{ $item->id }}" data-cores="{{ $row->subresource->nodecores }}" data-cores-field="loan-cores{{ $item->id }}" value="{{ $nodecores ? round($item->corecount / $nodecores, 1) : $item->nodecount }}" step="0.5" />
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label for="loan-cores{{ $item->id }}">{{ trans('queues::queues.cores') }} <span class="required">{{ trans('global.required') }}</span></label>
															<input type="number" name="corecount" class="form-control cores" size="4" id="loan-cores{{ $item->id }}" data-cores="{{ $row->subresource->nodecores }}" data-nodes-field="loan-nodes{{ $item->id }}" value="{{ $item->corecount }}" />
															<span class="text-muted">({{ trans('queues::queues.cores per ' . $unit, ['cores' => $row->subresource->nodecores]) }})</span>
														</div>
													</div>
												</div>
												<input type="hidden" name="serviceunits" class="form-control serviceunits" size="4" id="loan-serviceunits{{ $item->id }}" value="{{ $item->serviceunits }}" step="0.25" />
												@endif

												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label for="loan-datetimestart{{ $item->id }}">{{ trans('queues::queues.start') }} <span class="required">{{ trans('global.required') }}</span></label>
															<input type="text" name="datetimestart" class="form-control datetime" id="loan-datetimestart{{ $item->id }}" value="{{ $item->datetimestart->toDateTimeString() }}" />
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label for="loan-datetimestop{{ $item->id }}">{{ trans('queues::queues.end') }}</label>
															@if ($item->type == 1)
																<input type="text" name="datetimestop" class="form-control datetime" id="loan-datetimestop{{ $item->id }}" value="{{ $item->hasEnd() ? $item->datetimestop->toDateTimeString() : '' }}" />
															@else
																<input type="text" name="datetimestop" class="form-control datetime" id="sell-datetimestop{{ $item->id }}" value="{{ $item->hasEnd() ? $item->datetimestop->toDateTimeString() : '' }}" placeholder="{{ trans('queues::queues.end of life') }}" />
															@endif
														</div>
													</div>
												</div>

												<div class="form-group">
													<label for="loan-comment{{ $item->id }}">{{ trans('queues::queues.comment') }}</label>
													<textarea id="loan-comment{{ $item->id }}" name="comment" class="form-control" rows="3" cols="40" maxlength="2000">{{ $item->comment }}</textarea>
												</div>

												@php
												$creation = $item->history()
													->where('action', '=', 'created')
													->first();
												@endphp
												@if ($creation)
													<div class="row">
														<div class="col-md-6 mb-0">
															<div class="form-group mb-0">
																<label for="loan-created{{ $item->id }}">{{ trans('queues::queues.created') }}</label>
																<input type="text" name="created" id="loan-created{{ $item->id }}" class="form-control-plaintext" value="{{ $creation->created_at->format('M j, Y g:ia') }}" readonly />
															</div>
														</div>
														<div class="col-md-6 mb-0">
															<div class="form-group mb-0">
																<label for="loan-creator{{ $item->id }}">{{ trans('storage::storage.creator') }}</label>
																<input type="text" name="creator" id="loan-creator{{ $item->id }}" class="form-control-plaintext" value="{{ $creation->user ? $creation->user->name . ' (' . $creation->user->username . ')' : 'ID #' . $creation->user_id }}" readonly />
															</div>
														</div>
													</div>
												@endif
											</div>
											<div class="modal-footer dialog-footer text-right">
												<button type="submit" class="btn btn-success queue-dialog-submit" data-action="update" data-success="{{ trans('queues::queues.item updated') }}">
													<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('queues::queues.saving') }}</span></span>
													{{ trans('global.button.update') }}
												</button>
											</div>

											<input type="hidden" name="id" value="{{ $item->id }}" />
											@csrf
										</form>
									</div>
								</div>
							</td>
						</tr>
						<?php
					endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="modal dialog" id="dialog-sell" tabindex="-1" aria-labelledby="dialog-sell-title" aria-hidden="true" title="{{ trans('queues::queues.sell') }}">
				<div class="modal-dialog modal-dialog-centered">
					<form class="modal-content dialog-content shadow-sm" method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.queues.sizes.create') }}">
						<div class="modal-header">
							<div class="modal-title" id="dialog-sell-title">{{ trans('queues::queues.sell') }}</div>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body dialog-body">
							@if ($unit == 'sus')
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label for="sell-serviceunits">{{ trans('queues::queues.service units') }} <span class="required">{{ trans('global.required') }}</span></label>
										<input type="number" class="form-control serviceunits" size="4" id="sell-serviceunits" name="serviceunits" value="0.00" step="0.25" />
									</div>
								</div>
							</div>
							<input type="hidden" class="form-control nodes" size="4" id="sell-nodes" name="nodecount" data-cores="{{ $row->subresource->nodecores }}" data-cores-field="sell-cores" value="0" step="0.5" />
							<input type="hidden" class="form-control cores" size="4" id="sell-cores" name="corecount" data-cores="{{ $row->subresource->nodecores }}" data-nodes-field="sell-nodes" value="0" />
							@elseif ($unit == 'gpus')
								<div class="row">
									<div class="col-md-4">
										<div class="form-group">
											<label for="sell-nodes">{{ trans('queues::queues.nodes') }}</label>
											<input type="number" name="nodecount" class="form-control nodes" size="4" id="sell-nodes" data-cores="{{ $row->subresource->nodecores }}" data-gpus="{{ $row->subresource->nodegpus }}" data-cores-field="sell-cores" data-gpus-field="sell-serviceunits" value="0" step="0.5" />
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label for="sell-cores">{{ trans('queues::queues.cores') }} <span class="required">{{ trans('global.required') }}</span></label>
											<input type="number" name="corecount" class="form-control cores" size="4" id="sell-cores" data-cores="{{ $row->subresource->nodecores }}" data-nodes-field="sell-nodes" value="0" />
											<span class="text-muted">({{ trans('queues::queues.cores per nodes', ['cores' => $row->subresource->nodecores]) }})</span>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label for="sell-serviceunits">{{ trans('queues::queues.gpus') }} <span class="required">{{ trans('global.required') }}</span></label>
											<input type="number" name="serviceunits" class="form-control gpus" size="4" id="sell-serviceunits" data-gpus="{{ $row->subresource->nodegpus }}" data-nodes-field="sell-nodes" value="0" />
											<span class="text-muted">({{ trans('queues::queues.cores per nodes', ['cores' => $row->subresource->nodegpus]) }})</span>
										</div>
									</div>
								</div>
							@else
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="sell-nodes">{{ trans('queues::queues.' . $unit) }}</label>
										<input type="number" class="form-control nodes" size="4" id="sell-nodes" name="nodecount" data-cores="{{ $row->subresource->nodecores }}" data-cores-field="sell-cores" value="0" step="0.5" />
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="sell-cores">{{ trans('queues::queues.cores') }} <span class="required">{{ trans('global.required') }}</span></label>
										<input type="number" class="form-control cores" size="4" id="sell-cores" name="corecount" data-cores="{{ $row->subresource->nodecores }}" data-nodes-field="sell-nodes" value="0" />
										<span class="text-muted">({{ trans('queues::queues.cores per ' . $unit, ['cores' => $row->subresource->nodecores]) }})</span>
									</div>
								</div>
							</div>
							<input type="hidden" class="form-control serviceunits" size="4" id="sell-serviceunits" name="serviceunits" value="0.00" step="0.25" />
							@endif

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="sell-datetimestart">{{ trans('queues::queues.start') }}</label>
										<span class="input-group input-datetime">
											<input type="text" class="form-control datetime" id="sell-datetimestart" name="datetimestart" value="{{ Carbon\Carbon::now()->toDateTimeString() }}" />
											<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
										</span>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="sell-datetimestop">{{ trans('queues::queues.end') }}</label>
										<span class="input-group input-datetime">
											<input type="text" class="form-control datetime" id="sell-datetimestop" name="datetimestop" placeholder="{{ trans('queues::queues.end of life') }}" value="" />
											<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
										</span>
									</div>
								</div>
							</div>

							<div class="form-group">
								<label for="seller-group">{{ trans('queues::queues.seller') }}</label>
								<select name="sellergroupid" id="seller-group"
									class="form-control form-group-queues"
									data-update="seller-queue"
									data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&amp;search=%s"
									data-queue-api="{{ route('api.queues.index') }}"
									data-subresource="{{ $row->subresourceid }}">
									<option value="0" data-hide="#seller-queue">{{ trans('queues::queues.new hardware') }}</option>
									<?php
									$groups = array();
									$first = null;
									foreach ($row->subresource->queues as $queue):
										if (isset($groups[$queue->groupid])):
											continue;
										endif;

										if ($queue->groupid < 0 && !$first):
											$first = App\Modules\Groups\Models\Group::find(1);
											$first->id = -1;
										endif;

										if (!$queue->group):
											continue;
										endif;

										$groups[$queue->groupid] = $queue->group;
									endforeach;

									$groups = collect($groups)->sortBy('name');
									if ($first):
										$groups->prepend($first);
									endif;
									?>
									@foreach ($groups as $group)
										<option value="{{ $group->id }}"<?php if ($group->id == '-1') { echo ' selected="selected"'; } ?>>{{ $group->name }}</option>
									@endforeach
								</select>
							</div>

							<div class="form-group">
								<label for="seller-queue">{{ trans('queues::queues.queue') }}</label>
								<select id="seller-queue" name="sellerqueueid" class="form-control">
									<option value="0">{{ trans('queues::queues.select queue') }}</option>
									@foreach ($groups as $group)
										@if ($group->id == -1)
											@php
											$queues = $group->queues()->where('subresourceid', '=', $row->subresourceid)->get();
											@endphp
											@foreach ($queues as $queue)
												<option value="{{ $queue->id }}"<?php if (count($queues) == 1) { echo ' selected="selected"'; } ?>>{{ $queue->name }} ({{ $row->subresource->name }})</option>
											@endforeach
										@endif
									@endforeach
								</select>
								<span class="invalid-feedback">{{ trans('queues::queues.error.invalid queue') }}</span>
							</div>

							<div class="form-group">
								<label for="sell-group">{{ trans('queues::queues.sell to') }} <span class="required">{{ trans('global.required') }}</span></label>
								<select name="groupid" id="sell-group"
									class="form-control form-group-queues"
									data-update="sell-queue"
									data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&amp;search=%s"
									data-queue-api="{{ route('api.queues.index') }}"
									data-subresource="{{ $row->subresourceid }}">
									<option value="0">{{ trans('queues::queues.select group') }}</option>
									@foreach ($groups as $group)
										<option value="{{ $group->id }}"<?php if ($group->id == $row->groupid) { echo ' selected="selected"'; } ?>>{{ $group->name }}</option>
									@endforeach
								</select>
							</div>

							<div class="form-group">
								<label for="sell-queue">{{ trans('queues::queues.queue') }}  <span class="required">{{ trans('global.required') }}</span></label>
								<select id="sell-queue" name="queueid" class="form-control" required>
									<option value="0">{{ trans('queues::queues.select queue') }}</option>
									@foreach ($groups as $group)
										@if ($group->id == $row->groupid)
											@foreach ($group->queues()->where('subresourceid', '=', $row->subresourceid)->get() as $queue)
												<option value="{{ $queue->id }}"<?php if ($queue->id == $row->id) { echo ' selected="selected"'; } ?>>{{ $queue->name }} ({{ $row->subresource->name }})</option>
											@endforeach
										@endif
									@endforeach
								</select>
								<span class="invalid-feedback">{{ trans('queues::queues.error.invalid queue') }}</span>
							</div>

							<div class="form-group">
								<label for="sell-comment">{{ trans('queues::queues.comment') }}</label>
								<textarea id="sell-comment" name="comment" class="form-control" cols="35" rows="2" maxlength="2000"></textarea>
							</div>
						</div>
						<div class="modal-footer dialog-footer text-right">
							<button type="submit" class="btn btn-success queue-dialog-submit" data-success="{{ trans('queues::queues.item created') }}">
								<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('queues::queues.saving') }}</span></span>
								{{ trans('global.button.create') }}
							</button>
						</div>

						@csrf
					</form>
				</div>
			</div><!-- / .modal -->

			<div class="modal dialog" id="dialog-loan" tabindex="-1" aria-labelledby="dialog-loan-title" aria-hidden="true" title="{{ trans('queues::queues.loan') }}">
				<div class="modal-dialog modal-dialog-centered">
					<form class="modal-content dialog-content shadow-sm" method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.queues.loans.create') }}">
						<div class="modal-header">
							<div class="modal-title" id="dialog-loan-title">{{ trans('queues::queues.loan') }}</div>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body dialog-body">
							@if ($unit == 'sus')
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label for="sell-serviceunits">{{ trans('queues::queues.service units') }} <span class="required">{{ trans('global.required') }}</span></label>
										<input type="number" name="serviceunits" class="form-control serviceunits" size="4" id="loan-serviceunits" value="0.00" step="0.25" />
									</div>
								</div>
							</div>
							<input type="hidden" name="nodecount" class="form-control nodes" size="4" id="loan-nodes" data-cores="{{ $row->subresource->nodecores }}" data-cores-field="loan-cores" value="0" step="0.5" />
							<input type="hidden" name="corecount" class="form-control cores" size="4" id="loan-cores" data-cores="{{ $row->subresource->nodecores }}" data-nodes-field="loan-nodes" value="0" />
							@elseif ($unit == 'gpus')
								<div class="row">
									<div class="col-md-4">
										<div class="form-group">
											<label for="loan-nodes">{{ trans('queues::queues.nodes') }}</label>
											<input type="number" name="nodecount" class="form-control nodes" size="4" id="loan-nodes" data-cores="{{ $row->subresource->nodecores }}" data-gpus="{{ $row->subresource->nodegpus }}" data-cores-field="loan-cores" data-gpus-field="loan-serviceunits" value="0" step="0.5" />
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label for="loan-cores">{{ trans('queues::queues.cores') }} <span class="required">{{ trans('global.required') }}</span></label>
											<input type="number" name="corecount" class="form-control cores" size="4" id="loan-cores" data-cores="{{ $row->subresource->nodecores }}" data-nodes-field="loan-nodes" value="0" />
											<span class="text-muted">({{ trans('queues::queues.cores per nodes', ['cores' => $row->subresource->nodecores]) }})</span>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label for="loan-serviceunits">{{ trans('queues::queues.gpus') }} <span class="required">{{ trans('global.required') }}</span></label>
											<input type="number" name="serviceunits" class="form-control gpus" size="4" id="loan-serviceunits" data-gpus="{{ $row->subresource->nodegpus }}" data-nodes-field="loan-nodes" value="0" />
											<span class="text-muted">({{ trans('queues::queues.cores per nodes', ['cores' => $row->subresource->nodegpus]) }})</span>
										</div>
									</div>
								</div>
							@else
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="loan-nodes">{{ trans('queues::queues.' . $unit) }}</label>
										<input type="number" name="nodecount" class="form-control nodes" size="4" id="loan-nodes" data-cores="{{ $row->subresource->nodecores }}" data-cores-field="loan-cores" value="0" step="0.5" />
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="loan-cores">{{ trans('queues::queues.cores') }} <span class="required">{{ trans('global.required') }}</span></label>
										<input type="number" name="corecount" class="form-control cores" size="4" id="loan-cores" data-cores="{{ $row->subresource->nodecores }}" data-nodes-field="loan-nodes" value="0" />
										<span class="text-muted">({{ trans('queues::queues.cores per ' . $unit, ['cores' => $row->subresource->nodecores]) }})</span>
									</div>
								</div>
							</div>
							<input type="hidden" name="serviceunits" class="form-control serviceunits" size="4" id="loan-serviceunits" value="0.00" step="0.25" />
							@endif

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="loan-datetimestart">{{ trans('queues::queues.start') }}</label>
										<span class="input-group input-datetime">
											<input type="text" name="datetimestart" class="form-control datetime" id="loan-datetimestart" value="{{ Carbon\Carbon::now()->toDateTimeString() }}" />
											<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
										</span>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="loan-datetimestop">{{ trans('queues::queues.end') }}</label>
										<span class="input-group input-datetime">
											<input type="text" name="datetimestop" class="form-control datetime" id="loan-datetimestop" value="" placeholder="{{ trans('global.never') }}" />
											<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
										</span>
									</div>
								</div>
							</div>

							<div class="form-group">
								<label for="loan-group">{{ trans('queues::queues.lender') }} <span class="required">{{ trans('global.required') }}</span></label>
								<select name="lendergroupid" id="lender-group"
									class="form-control form-group-queues"
									data-update="lender-queue"
									data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&amp;search=%s"
									data-queue-api="{{ route('api.queues.index') }}"
									data-subresource="{{ $row->subresourceid }}">
									<option value="0">{{ trans('queues::queues.select group') }}</option>
									@foreach ($groups as $group)
										<option value="{{ $group->id }}"<?php if ($group->id == -1) { echo ' selected="selected"'; } ?>>{{ $group->name }}</option>
									@endforeach
								</select>
							</div>

							<div class="form-group">
								<label for="lender-queue">{{ trans('queues::queues.queue') }} <span class="required">{{ trans('global.required') }}</span></label>
								<select id="lender-queue" name="lenderqueueid" class="form-control">
									<option value="0">{{ trans('queues::queues.select queue') }}</option>
									@foreach ($groups as $group)
										@if ($group->id == -1)
											@php
											$queues = $group->queues()->where('subresourceid', '=', $row->subresourceid)->get();
											@endphp
											@foreach ($queues as $queue)
												<option value="{{ $queue->id }}"<?php if (count($queues) == 1) { echo ' selected="selected"'; } ?>>{{ $queue->name }} ({{ $row->subresource->name }})</option>
											@endforeach
										@endif
									@endforeach
								</select>
							</div>

							<div class="form-group">
								<label for="loan-group">{{ trans('queues::queues.loan to') }} <span class="required">{{ trans('global.required') }}</span></label>
								<select name="groupid" id="loan-group"
									class="form-control form-group-queues"
									data-update="loan-queue"
									data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&amp;search=%s"
									data-queue-api="{{ route('api.queues.index') }}"
									data-subresource="{{ $row->subresourceid }}">
									<option value="0">{{ trans('queues::queues.select group') }}</option>
									@foreach ($groups as $group)
										<option value="{{ $group->id }}"<?php if ($group->id == $row->groupid) { echo ' selected="selected"'; } ?>>{{ $group->name }}</option>
									@endforeach
								</select>
							</div>

							<div class="form-group">
								<label for="loan-queue">{{ trans('queues::queues.queue') }} <span class="required">{{ trans('global.required') }}</span></label>
								<select id="loan-queue" name="queueid" class="form-control">
									<option value="0">{{ trans('queues::queues.select queue') }}</option>
									@foreach ($groups as $group)
										@if ($group->id == $row->groupid)
											@foreach ($group->queues()->where('subresourceid', '=', $row->subresourceid)->get() as $queue)
												<option value="{{ $queue->id }}">{{ $queue->name }} ({{ $row->subresource->name }})</option>
											@endforeach
										@endif
									@endforeach
								</select>
							</div>

							<div class="form-group">
								<label for="loan-comment">{{ trans('queues::queues.comment') }}</label>
								<textarea id="loan-comment" name="comment" class="form-control" rows="2" cols="40" maxlength="2000"></textarea>
							</div>
						</div>
						<div class="modal-footer dialog-footer text-right">
							<button type="submit" class="btn btn-success queue-dialog-submit" data-success="{{ trans('queues::queues.item created') }}">
								<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('queues::queues.saving') }}</span></span>
								{{ trans('global.button.create') }}
							</button>
						</div>

						@csrf
					</form>
				</div>
			</div><!-- / .modal -->
		</div><!-- / .card -->

	</div><!-- / .col-md-12 -->
</div><!-- / .row -->

	</div><!-- / #queue-details -->
	<div class="tab-pane" id="queue-history" role="tabpanel" aria-labelledby="queue-history-tab">
		<?php
		$history = $row->history()
			->orderBy('created_at', 'desc')
			->get();

		if (!count($history)):
			$created = new App\Modules\History\Models\History;
			$created->created_at = $row->datetimecreated;
			$created->action = 'created';
			$created->historable_type = $row::class;
			$created->historable_table = $row->getTable();
			$created->new = json_encode($row->toArray());

			$history->push($created);
		endif;

		$items = $row->sizes;
		$items = $items->merge($row->loans)->sortBy('datetimestart');

		foreach ($items as $item):
			foreach ($item->history()->orderBy('created_at', 'desc')->get() as $ev):
				$history->push($ev);
			endforeach;
		endforeach;

		$users = $row->users()
			->withTrashed()
			->get();

		foreach ($users as $user):
			foreach ($user->history()->orderBy('created_at', 'desc')->get() as $ev):
				$ev->name = $user->user ? $user->user->name . ' (' . $user->user->username . ')' : 'user ID #' . $user->userid;
				$ev->target_id = $user->userid;

				$history->push($ev);
			endforeach;
		endforeach;

		$sorted = $history->sortByDesc('created_at');
		?>
		<ul class="entry-log timeline">
			<?php
			if (count($sorted)):
				foreach ($sorted as $action):
					$actor = trans('global.unknown');
					$target = trans('global.unknown');

					if ($action->user):
						$actor = '<a href="' . route('admin.users.show', ['id' => $action->user_id]) . '">' . e($action->user->name) . ' (' . e($action->user->username) . ')</a>';
					endif;

					$created = $action->created_at ? $action->created_at : trans('global.unknown');

					$f = array();
					if (is_object($action->new)):
						$f = get_object_vars($action->new);
					elseif (is_array($action->new)):
						$f = $action->new;
					endif;

					$fields = array_keys($f);
					foreach ($fields as $i => $k):
						if (in_array($k, ['created_at', 'updated_at', 'deleted_at'])):
							unset($fields[$i]);
						endif;
					endforeach;

					$msg = trans('history::history.action ' . $action->action, ['user' => $actor, 'entity' => $action->historable_table]);

					if ($action->historable_table == 'queueusers'):
						if ($action->name):
							$target = '<a href="' . route('admin.users.show', ['id' => $action->target_id]) . '">' . e($action->name) . '</a>';
						endif;

						if ($action->action == 'created'):
							$msg = $actor . ' <span class="text-success">added</span> ' . $target . ' to queue';
						endif;
						if ($action->action == 'deleted'):
							$msg = $actor . ' <span class="text-danger">removed</span> ' . $target . ' from queue';
						endif;
						// Skip separate updates that are just setting notice state right after creation
						if ($action->action == 'updated'):
							if (isset($action->new->notice)):
								continue;
							endif;
							if (isset($action->old->datetimeremoved)):
								$msg = $actor . ' <span class="text-success">added</span> ' . $target . ' to queue';
							endif;
						endif;
					endif;

					if ($action->historable_table == 'queues'):
						if ($action->action == 'created'):
							$msg = $actor . ' <span class="text-success">created</span> queue';
						endif;
						if ($action->action == 'deleted'):
							$msg = $actor . ' <span class="text-danger">removed</span> queue';
						endif;
						if ($action->action == 'updated'):
							$msg = $actor . ' <span class="text-info">updated</span> queue';
						endif;
					endif;

					if ($action->historable_table == 'queueloans'):
						if ($action->action == 'created'):
							$msg = $actor . ' <span class="text-success">created</span> loan #' . $action->historable_id;
						endif;
						if ($action->action == 'deleted'):
							$msg = $actor . ' <span class="text-danger">removed</span> loan #' . $action->historable_id;
						endif;
						if ($action->action == 'updated'):
							$msg = $actor . ' <span class="text-info">updated</span> loan #' . $action->historable_id;
						endif;
					endif;

					if ($action->historable_table == 'queuesizes'):
						if ($action->action == 'created'):
							$msg = $actor . ' <span class="text-success">created</span> purchase #' . $action->historable_id;
						endif;
						if ($action->action == 'deleted'):
							$msg = $actor . ' <span class="text-danger">removed</span> purchase #' . $action->historable_id;
						endif;
						if ($action->action == 'updated'):
							$msg = $actor . ' <span class="text-info">updated</span> purchase #' . $action->historable_id;
						endif;
					endif;

					$old = Carbon\Carbon::now()->subDays(2);
					?>
					<li class="{{ $action->action }}" data-id="{{ $action->id }}">
						<span class="entry-action">{!! $msg !!}</span><br />
						<span class="entry-date">
							<time datetime="{{ $action->created_at->toDateTimeLocalString() }}">
							@if ($action->created_at < $old)
								{{ $action->created_at->format('d M Y @ g:ia T') }}
							@else
								{{ $action->created_at->diffForHumans() }}
							@endif
							</time>
						</span>
						@if ($action->action == 'updated' && $action->historable_table != 'queueusers')
							<span class="entry-diff">{{ trans('history::history.changed fields') }}: <code><?php echo implode('</code>, <code>', $fields); ?></code></span>
						@endif
					</li>
					<?php
				endforeach;
			else:
				?>
				<li>
					<span class="entry-diff">{{ trans('history::history.none found') }}</span>
				</li>
				<?php
			endif;
			?>
		</ul>
	</div>
</div><!-- / .tab-content -->
@stop
