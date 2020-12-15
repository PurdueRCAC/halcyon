@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/queues/js/admin.js?v=' . filemtime(public_path() . '/modules/queues/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

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
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit queues'))
		{!! Toolbar::save(route('admin.queues.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.queues.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('queues.name') !!}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')

	@if ($row->id)
	<div class="tabs">
		<ul>
			<li><a href="#queue-details">{{ trans('queues::queues.queue') }}</a></li>
			<li><a href="#queue-nodes">{{ trans('queues::queues.purchases and loans') }}</a></li>
		</ul>
		<div id="queue-details">
	@endif

<form action="{{ route('admin.queues.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="row">
		<div class="col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-queuetype">{{ trans('queues::queues.type') }}:</label>
							<select name="fields[queuetype]" id="field-queuetype" class="form-control">
								<option value="0">{{ trans('global.none') }}</option>
								@foreach ($types as $type)
									<option value="{{ $type->id }}"<?php if ($row->queuetype == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-queueclass">{{ trans('queues::queues.class') }}</label>
							<select name="queueclass" id="field-queueclass" class="form-control">
								<option value="owner">{{ trans('queues::queues.owner') }}</option>
								<option value="standby">{{ trans('queues::queues.standby') }}</option>
								<option value="workq">{{ trans('queues::queues.work') }}</option>
								<option value="debug">{{ trans('queues::queues.debug') }}</option>
							</select>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-name">{{ trans('queues::queues.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" required pattern="[a-zA-Z0-9_]{1,64}" maxlength="64" value="{{ $row->name }}" data-invalid-msg="The field 'Queue Name' is required." />
					<span class="invalid-feedback">{{ trans('queues::queues.error.invalid name') }}</span>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-schedulerid">{{ trans('queues::queues.scheduler') }}:  <span class="required">{{ trans('global.required') }}</span></label>
							<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('global.loading') }}</span></span>
							<select name="fields[schedulerid]" id="field-schedulerid" class="form-control required" required>
								<option value="0">{{ trans('global.none') }}</option>
								@foreach ($schedulers as $scheduler)
									<?php $selected = ($scheduler->id == $row->schedulerid ? ' selected="selected"' : ''); ?>
									<option value="{{ $scheduler->id }}"<?php echo $selected; ?>
										data-defaultmaxwalltime="{{ $scheduler->defaultmaxwalltime }}"
										data-schedulerpolicyid="{{ $scheduler->schedulerpolicyid }}"
										data-resourceid="{{ $scheduler->resource->id }}"
										data-api="{{ route('api.resources.read', ['id' => $scheduler->resource->id]) }}">{{ $scheduler->hostname }}</option>
								@endforeach
							</select>
							<span class="invalid-feedback">{{ trans('queues::queues.error.invalid scheduler') }}</span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-schedulerpolicyid">{{ trans('queues::queues.scheduler policy') }}:</label>
							<select name="fields[schedulerpolicyid]" id="field-schedulerpolicyid" class="form-control">
								<option value="0">{{ trans('global.none') }}</option>
								@foreach ($schedulerpolicies as $schedulerpolicy)
									<?php $selected = ($schedulerpolicy->id == $row->schedulerpolicyid ? ' selected="selected"' : ''); ?>
									<option value="{{ $schedulerpolicy->id }}"<?php echo $selected; ?>>{{ $schedulerpolicy->name }}</option>
								@endforeach
							</select>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<?php
							$cores = '-';
							$mem   = '-';
							?>
							<label for="field-subresourceid">{{ trans('queues::queues.subresource') }}:  <span class="required">{{ trans('global.required') }}</span></label>
							<select name="fields[subresourceid]" id="field-subresourceid" class="form-control required" required>
								<option value="0">{{ trans('global.none') }}</option>
								<?php foreach ($resources as $resource): ?>
									<?php
									$children = $resource->children()->get();
									if (count($children)) { ?>
										<optgroup data-resourceid="{{ $resource->id }}" label="{{ $resource->name }}">
											<?php foreach ($children as $child):
												$selected = '';
												if ($row->subresourceid == $child->subresourceid)
												{
													$cores = $child->subresource ? $child->subresource->nodecores : 0;
													$mem = $child->subresource ? $child->subresource->nodemem : 0;
													$selected = ' selected="selected"';
												}
												?>
												<option value="{{ $child->subresourceid }}"<?php echo $selected; ?>
													data-nodecores="{{ $child->subresource ? $child->subresource->nodecores : 0 }}"
													data-nodemem="{{ $child->subresource ? $child->subresource->nodemem : 0 }}"
													data-cluster="{{ $child->subresource ? $child->subresource->cluster : '' }}">{{ $child->subresource ? $child->subresource->name : '(unknown)' }}</option>
											<?php endforeach; ?>
										</optgroup>
									<?php } ?>
								<?php endforeach; ?>
							</select>
							<span class="invalid-feedback">{{ trans('queues::queues.error.invalid subresource') }}</span>
							<span class="form-text text-muted">
								{!! trans('queues::queues.number cores', ['num' => '<span id="SPAN_nodecores">' . $cores . '</span>']) !!},
								{!! trans('queues::queues.number memory', ['num' => '<span id="SPAN_nodemem">' . $mem . '</span>']) !!}
							</span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-cluster">{{ trans('queues::queues.cluster') }}:</label>
							<input type="text" name="fields[cluster]" id="field-cluster" class="form-control" maxlength="32" value="{{ $row->cluster }}" />
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label for="field-defaultwalltime">{{ trans('queues::queues.default walltime') }}:</label>
							<span class="input-group">
								<input type="number" name="fields[defaultwalltime]" id="field-defaultwalltime" class="form-control" min="0" step="0.25" value="{{ ($row->defaultwalltime/60/60) }}" />
								<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.hours', 2) }}</span></span>
							</span>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<?php
							$val = 0;
							$wid = '';
							$walltime = $row->walltimes()->orderBy('id', 'desc')->first();
							if ($walltime)
							{
								$val = ($walltime->walltime/60/60);
								$wid = $walltime->id;
							}
							?>
							<label for="field-maxwalltime">{{ trans('queues::queues.max walltime') }}:</label>
							<span class="input-group">
								<input type="number" name="maxwalltime" id="field-maxwalltime" class="form-control" min="0" step="0.25" value="{{ $val }}" />
								<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.hours', 2) }}</span></span>
							</span>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-priority">{{ trans('queues::queues.priority') }}:</label>
					<input type="number" name="fields[priority]" id="field-priority" class="form-control" min="0" value="{{ $row->priority }}" />
				</div>

				<div class="form-group">
					<label for="field-groupid">{{ trans('queues::queues.group') }}:</label>
					<span class="input-group">
						<input type="text" name="fields[groupid]" id="field-groupid" class="form-control form-groups" data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" data-multiple="false" placeholder="Search for group..." value="{{ ($row->group ? $row->group->name . ':' . $row->groupid : '') }}" />
						<span class="input-group-append"><span class="input-group-text icon-users"></span></span>
					</span>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group form-block">
							<div class="form-check">
								<input type="checkbox" name="fields[reservation]" id="field-reservation" class="form-check-input" value="1"<?php if ($row->reservation) { echo ' checked="checked"'; } ?> />
								<label for="field-reservation" class="form-check-label">{{ trans('queues::queues.reservation') }}</label>
								<span class="form-text text-muted">{{ trans('queues::queues.reservation desc') }}</span>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group form-block">
							<div class="form-check">
								<input type="checkbox" name="fields[free]" id="field-free" class="form-check-input" value="1"<?php if ($row->free) { echo ' checked="checked"'; } ?> />
								<label for="field-free" class="form-check-label">{{ trans('queues::queues.free') }}</label>
								<span class="form-text text-muted">{{ trans('queues::queues.free desc') }}</span>
							</div>
						</div>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('queues::queues.jobs') }}</legend>

				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label for="field-maxjobsqueued">{{ trans('queues::queues.max jobs queued') }}:</label>
							<input type="number" name="fields[maxjobsqueued]" id="field-maxjobsqueued" class="form-control" min="0" value="{{ $row->maxjobsqueued }}" />
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="field-maxjobsqueueduser">{{ trans('queues::queues.max jobs queued per user') }}:</label>
							<input type="number" name="fields[maxjobsqueueduser]" id="field-maxjobsqueueduser" class="form-control" min="0" value="{{ $row->maxjobsqueueduser }}" />
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label for="field-maxjobsrun">{{ trans('queues::queues.max jobs run') }}:</label>
							<input type="number" name="fields[maxjobsrun]" id="field-maxjobsrun" class="form-control" min="0" value="{{ $row->maxjobsrun }}" />
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="field-maxjobsrunuser">{{ trans('queues::queues.max jobs run per user') }}:</label>
							<input type="number" name="fields[maxjobsrunuser]" id="field-maxjobsrunuser" class="form-control" min="0" value="{{ $row->maxjobsrunuser }}" />
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-maxjobcores">{{ trans('queues::queues.max job cores') }}:</label>
					<input type="number" name="fields[maxjobcores]" id="field-maxjobcores" class="form-control" min="0" value="{{ $row->maxjobcores }}" />
				</div>

				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label for="field-maxijobfactor">{{ trans('queues::queues.max ijob factor') }}:</label>
							<input type="number" name="fields[maxijobfactor]" id="field-maxijobfactor" class="form-control" min="0" value="{{ $row->maxijobfactor }}" />
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="field-maxijobuserfactor">{{ trans('queues::queues.max ijob user factor') }}:</label>
							<input type="number" name="fields[maxijobuserfactor]" id="field-maxijobuserfactor" class="form-control" min="0" value="{{ $row->maxijobuserfactor }}" />
						</div>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('queues::queues.nodes') }}</legend>

				<div class="form-group">
					<label for="field-nodecoresdefault">{{ trans('queues::queues.node cores default') }}:</label>
					<input type="number" name="fields[nodecoresdefault]" id="field-nodecoresdefault" class="form-control" maxlength="250" value="{{ $row->nodecoresdefault }}" />
				</div>

				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label for="field-nodecoresmin">{{ trans('queues::queues.node cores min') }}:</label>
							<input type="number" name="fields[nodecoresmin]" id="field-nodecoresmin" class="form-control" maxlength="250" value="{{ $row->nodecoresmin }}" />
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="field-nodecoresmax">{{ trans('queues::queues.node cores max') }}:</label>
							<input type="number" name="fields[nodecoresmax]" id="field-nodecoresmax" class="form-control" maxlength="250" value="{{ $row->nodecoresmax }}" />
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label for="field-nodememmin">{{ trans('queues::queues.node mem min') }}:</label>
							<input type="text" name="fields[nodememmin]" id="field-nodememmin" class="form-control" maxlength="250" value="{{ $row->nodememmin }}" />
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="field-nodememmax">{{ trans('queues::queues.node mem max') }}:</label>
							<input type="text" name="fields[nodememmax]" id="field-nodememmax" class="form-control" maxlength="250" value="{{ $row->nodememmax }}" />
						</div>
					</div>
				</div>
			</fieldset>

		</div>
		<div class="col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('queues::queues.access') }}</legend>

				<div class="form-group">
					<label for="field-aclgroups">{{ trans('queues::queues.acl groups') }}:</label>
					<input type="text" name="fields[aclgroups]" id="field-aclgroups" class="form-control" value="{{ $row->aclgroups }}" />
					<span class="form-text text-muted">{{ trans('queues::queues.acl groups desc') }}</span>
				</div>

				<div class="form-group form-block">
					<div class="form-check">
						<input type="checkbox" name="fields[aclusersenabled]" id="field-aclusersenabled" class="form-check-input" value="1"<?php if ($row->aclusersenabled) { echo ' checked="checked"'; } ?> />
						<label for="field-aclusersenabled" class="form-check-label">{{ trans('queues::queues.acl users enabled') }}</label>
						<span class="form-text text-muted">acl users enabled</span>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-enabled">{{ trans('queues::queues.submission state') }}:</label><br />
					<select class="form-control" name="fields[enabled]" id="field-enabled">
						<option value="0"<?php if ($row->enabled == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.disabled') }}</option>
						<option value="1"<?php if ($row->enabled == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.enabled') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="field-started">{{ trans('queues::queues.scheduling') }}:</label><br />
					<select class="form-control" name="fields[started]" id="field-started">
						<option value="0"<?php if ($row->started == 0) { echo ' selected="selected"'; } ?>>{{ trans('queues::queues.stopped') }}</option>
						<option value="1"<?php if ($row->started == 1) { echo ' selected="selected"'; } ?>>{{ trans('queues::queues.started') }}</option>
					</select>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
	@csrf
</form>
	@if ($row->id)
	</div><!-- / #queue-details -->
	<div id="queue-nodes">
		<p class="text-right">
			<a href="#dialog-sell" id="node-sell" class="btn btn-secondary dialog-btn icon-dollar-sign">{{ trans('queues::queues.sell nodes') }}</a>
			<a href="#dialog-loan" id="node-loan" class="btn btn-secondary dialog-btn icon-shuffle">{{ trans('queues::queues.loan nodes') }}</a>
		</p>

		<div class="card">
			<table class="table table-hover adminlist">
				<caption class="sr-only">{{ trans('queues::queues.purchases and loans') }}</caption>
				<thead>
					<tr>
						<th scope="col">{{ trans('queues::queues.start') }}</th>
						<th scope="col">{{ trans('queues::queues.end') }}</th>
						<th scope="col">{{ trans('queues::queues.action') }}</th>
						<th scope="col">{{ trans('queues::queues.source') }}</th>
						<th scope="col">{{ trans('queues::queues.resource') }}</th>
						<th scope="col">{{ trans('queues::queues.queue') }}</th>
						<th scope="col" class="text-right">{{ trans('queues::queues.nodes') }}</th>
						<th scope="col" class="text-right">{{ trans('queues::queues.total') }}</th>
						<th scope="col"></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$sizes = $row->sizes;
					$sold  = $row->sold;
					$loans = $row->loans;
					$nodecores = $row->subresource->nodecores;
					$total = 0;

					$items = $sizes->merge($sold);
					$items = $items->merge($loans)->sortBy('datetimestart');

					foreach ($items as $item)
					{
						if ($item->hasEnded())
						{
							$item->total = $total;
							continue;
						}
						if (($item->sellerqueueid == $row->id && $item->corecount > 0)
						|| ($item->corecount < 0 && $item->type == 0)
						|| ($item->corecount < 0 && $item->type == 1))
						{
							$total -= $nodecores ? round($item->corecount / $nodecores, 1) : 0;
						}
						else if (($item->corecount > 0 && $item->type == 0)
							|| ($item->corecount > 0 && $item->type == 1))
						{
							$total += $nodecores ? round($item->corecount / $nodecores, 1) : 0;
						}
						$item->total = $total;
					}

					$items = $items->sortByDesc('datetimestart')->slice(0, 20);

					foreach ($items as $item): ?>
					<tr>
						<td>{{ $item->datetimestart }}</td>
						<td>
							@if ($item->datetimestop && $item->datetimestop != '0000-00-00 00:00:00' && $item->datetimestop != '-0001-11-30 00:00:00')
								<time datetime="{{ $item->datetimestop }}">{{ $item->datetimestop }}</time>
							@else
								<span class="never">{{ trans('global.never') }}</span>
							@endif
						</td>
						<td>
							<?php
							$what = '';
							$cls = '';
							if ($item->type == 1)
							{
								$what = 'Loan';
								if ($item->corecount < 0)
								{
									$what .= " to";
									$cls = 'decrease';
								}
								else if ($item->corecount >= 0)
								{
									$what .= " from";
									$cls = 'increase';
								}
							}
							else
							{
								if ($item->sellerqueueid == $row->id || $item->corecount < 0)
								{
									$what = 'Sale to';
									$cls = 'decrease';
								}
								else if ($item->corecount >= 0)
								{
									$what = 'Purchase from';
									$cls = 'increase';
								}
							}

							//$title  = $item->nodecount . " nodes / ";
							//$title .= $item->corecount . " cores; ".$what.": ";
							$amt = $nodecores ? round($item->corecount / $nodecores, 1) : 0;

							echo $what;
							?>
						</td>
						<td>
							@if ($item->sellerqueueid == $row->id)
								{{ $item->queue->group ? $item->queue->group->name : '(ITaP Owned)' }}
							@elseif ($item->source)
								{{ $item->source->group ? $item->source->group->name : '(ITaP Owned)' }}
							@else
								trans('New hardware')
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
						<td class="text-right">
							<span class="{{ $cls }}">{{ ($cls == 'increase' ? '+' : '-') }} {{ $amt }}</span>
						</td>
						<td class="text-right">
							{{ $item->total }}
						</td>
						<td>
							<button class="btn btn-sm btn-danger delete"
								data-confirm="{{ trans('global.confirm delete') }}"
								data-success="{{ trans('queues::queues.item deleted') }}"
								data-api="{{ route('api.queues.' . ($item->type == 1 ? 'loans' : 'sizes'). '.delete', ['id' => $item->id]) }}"
								data-id="{{ $item->id }}">
								<span class="icon-trash"></span><span class="sr-only">{{ trans('global.button.delete') }}</span>
							</button>
						</td>
					</tr>
					<?php
				endforeach; ?>
				</tbody>
			</table>
		</div>

		<div class="dialog" id="dialog-sell" title="{{ trans('queues::queues.sell nodes') }}">
			<form method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.queues.sizes.create') }}">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="sell-nodes">{{ trans('queues::queues.nodes') }}</label>
							<input type="text" class="form-control nodes" size="4" id="sell-nodes" name="nodecount" data-nodes="{{ $row->subresource->nodecores }}" data-cores-field="sell-cores" value="" />
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="sell-cores">{{ trans('queues::queues.cores') }}</label>
							<input type="text" class="form-control cores" size="4" id="sell-cores" name="corecount" data-cores="{{ $row->subresource->nodecores }}" data-nodes-field="sell-nodes" value="" />
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="sell-datetimestart">{{ trans('queues::queues.start') }}</label>
							<input type="text" class="form-control datetime" id="sell-datetimestart" name="datetimestart" value="" />
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="sell-datetimestop">{{ trans('queues::queues.end') }}</label>
							<input type="text" class="form-control datetime" id="sell-datetimestop" name="datetimestop" disabled="disabled" placeholder="{{ trans('queues::queues.end of life') }}" value="" />
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="sell-group">{{ trans('queues::queues.sell to') }}</label>
					<span class="input-group">
						<input type="text" name="groupid" id="sell-group"
							class="form-control form-group-queues"
							data-update="sell-queue"
							data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s"
							data-queue-api="{{ route('api.queues.index') }}"
							data-subresource="{{ $row->subresourceid }}"
							value="" />
						<span class="input-group-append"><span class="input-group-text icon-users"></span></span>
					</span>
				</div>

				<div class="form-group">
					<label for="sell-queue">{{ trans('queues::queues.queue') }}</label>
					<select id="sell-queue" name="queueid" class="form-control" disabled="true">
						<option>{{ trans('queues::queues.select queue') }}</option>
					</select>
				</div>

				<div class="dialog-footer text-right">
					<input type="submit" class="btn btn-success dialog-submit" value="{{ trans('global.button.create') }}" data-success="{{ trans('queues::queues.item created') }}" />
				</div>

				<input type="hidden" name="sellerqueueid" value="{{ $row->id }}" />
				@csrf
			</form>
		</div>

		<div class="dialog" id="dialog-loan" title="{{ trans('queues::queues.loan nodes') }}">
			<form method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.queues.loans.create') }}">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="loan-nodes">{{ trans('queues::queues.nodes') }}</label>
							<input type="number" name="nodecount" class="form-control nodes" size="4" id="loan-nodes" name="nodes" data-nodes="{{ $row->subresource->nodecores }}" data-cores-field="loan-cores" value="" />
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="loan-cores">{{ trans('queues::queues.cores') }}</label>
							<input type="number" name="corecount" class="form-control cores" size="4" id="loan-cores" name="cores" data-cores="{{ $row->subresource->nodecores }}" data-nodes-field="loan-nodes" value="" />
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="loan-datetimestart">{{ trans('queues::queues.start') }}</label>
							<input type="text" name="datetimestart" class="form-control datetime" id="loan-datetimestart" name="datetimestart" value="" />
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="loan-datetimestop">{{ trans('queues::queues.end') }}</label>
							<input type="text" name="datetimestop" class="form-control datetime" id="loan-datetimestop" name="datetimestop" value="" />
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="loan-group">{{ trans('queues::queues.loan to') }}</label>
					<span class="input-group">
						<input type="text" name="groupid" id="loan-group"
							class="form-control form-group-queues"
							data-update="loan-queue"
							data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s"
							data-queue-api="{{ route('api.queues.index') }}"
							data-subresource="{{ $row->subresourceid }}"
							value="" />
						<span class="input-group-append"><span class="input-group-text icon-users"></span></span>
					</span>
				</div>

				<div class="form-group">
					<label for="loan-queue">{{ trans('queues::queues.queue') }}</label>
					<select id="loan-queue" name="queueid" class="form-control" disabled="true">
						<option>{{ trans('queues::queues.select queue') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="loan-comment">{{ trans('queues::queues.comment') }}</label>
					<textarea id="loan-comment" name="comment" class="form-control" rows="3" cols="40"></textarea>
				</div>

				<div class="dialog-footer text-right">
					<input type="submit" class="btn btn-success dialog-submit" value="{{ trans('global.button.create') }}" data-success="{{ trans('queues::queues.item created') }}" />
				</div>

				<input type="hidden" name="lenderqueueid" value="{{ $row->id }}" />
				@csrf
			</form>
		</div>
	</div><!-- / #queue-nodes -->
</div><!-- / .tabs -->
	@endif
@stop