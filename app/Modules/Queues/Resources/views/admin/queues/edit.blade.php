@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css?v=' . filemtime(public_path('/modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css'))) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js?v=' . filemtime(public_path('/modules/core/vendor/tom-select/js/tom-select.complete.min.js'))) }}"></script>
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
{{ trans('queues::queues.module name') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')

@if ($row->id)
	<nav class="container-fluid">
		<ul id="queue-tabs" class="nav nav-tabs" role="tablist">
			<li class="nav-item" role="presentation"><a class="nav-link active" href="#queue-details" data-toggle="tab" role="tab" id="queue-details-tab" aria-controls="queue-details" aria-selected="true">{{ trans('queues::queues.queue') }}</a></li>
			<li class="nav-item" role="presentation"><a class="nav-link" href="#queue-allocations" data-toggle="tab" role="tab" id="queue-allocation-tab" aria-controls="queue-allocations" aria-selected="false">{{ trans('queues::queues.purchases and loans') }}</a></li>
			<li class="nav-item" role="presentation"><a class="nav-link" href="#queue-history" data-toggle="tab" role="tab" id="queue-history-tab" aria-controls="queue-history" aria-selected="false">{{ trans('queues::queues.history') }}</a></li>
		</ul>
	</nav>
	<div class="tab-content" id="queue-tabs-contant">
		<div class="tab-pane show active" id="queue-details" role="tabpanel" aria-labelledby="queue-details-tab">
@endif

	@if ($row->trashed())
		<div class="alert alert-warning">{{ trans('queues::queues.entry marked as trashed') }}</div>
	@endif

	<form action="{{ route('admin.queues.store') }}" method="post" name="adminForm" id="adminForm" class="editform form-validate">
		<div class="row">
			<div class="col-md-7">
				<fieldset class="adminform">
					<legend>{{ trans('global.details') }}</legend>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="field-queuetype">{{ trans('queues::queues.type') }}</label>
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
						<label for="field-groupid">{{ trans('queues::queues.group') }}</label>
						<!-- <span class="input-group">
							<input type="text" name="fields[groupid]" id="field-groupid" class="form-control form-groups" data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&amp;search=%s" data-multiple="false" placeholder="{{ trans('queues::queues.search for group') }}" value="{{ ($row->group ? $row->group->name . ':' . $row->groupid : '') }}" />
							<span class="input-group-append"><span class="input-group-text icon-users"></span></span>
						</span> -->
						<select name="fields[groupid]" id="field-groupid" class="form-control searchable-select">
							<option	value="-1"<?php if ($row->groupid == '-1') { echo ' selected="selected"'; } ?>>{{ trans('global.none') . ' - ' . trans('queues::queues.system queues') }}</option>
							@foreach (App\Modules\Groups\Models\Group::query()->orderBy('name', 'asc')->get() as $group)
								<option value="{{ $group->id }}"<?php if ($row->groupid == $group->id) { echo ' selected="selected"'; } ?>>{{ $group->name }}</option>
							@endforeach
						</select>
					</div>

					<div class="form-group">
						<label for="field-name">{{ trans('queues::queues.name') }} <span class="required">{{ trans('global.required') }}</span></label>
						<input type="text" name="fields[name]" id="field-name" class="form-control{{ $errors->has('fields.name') ? ' is-invalid' : '' }}" required pattern="[a-zA-Z0-9_\-]{1,64}" maxlength="64" value="{{ $row->name }}" data-invalid-msg="{{ trans('queues::queues.name error') }}" />
						<span class="invalid-feedback">{{ trans('queues::queues.error.invalid name') }}</span>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="field-schedulerid">{{ trans('queues::queues.scheduler') }}  <span class="required">{{ trans('global.required') }}</span></label>
								<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('global.loading') }}</span></span>
								<select name="fields[schedulerid]" id="field-schedulerid" class="form-control{{ $errors->has('fields.schedulerid') ? ' is-invalid' : '' }}" required>
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
								<label for="field-schedulerpolicyid">{{ trans('queues::queues.scheduler policy') }}</label>
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
								$unit = 'nodes';

								$dlabel = trans('queues::queues.cluster');
								$clabel = $dlabel;
								?>
								<label for="field-subresourceid">{{ trans('queues::queues.subresource') }} <span class="required">{{ trans('global.required') }}</span></label>
								<select name="fields[subresourceid]" id="field-subresourceid" class="form-control{{ $errors->has('fields.subresourceid') ? ' is-invalid' : '' }}" required>
									<option value="0">{{ trans('global.none') }}</option>
									<?php foreach ($resources as $resource): ?>
										<?php
										$children = $resource->children()->get();
										if (count($children)): ?>
											<optgroup data-resourceid="{{ $resource->id }}" label="{{ $resource->name }}">
												<?php foreach ($children as $child):
													$selected = '';
													$label = $dlabel;
													if ($facet = $resource->getFacet('cluster_label')):
														$label = $facet->value;
													endif;
													if ($row->subresourceid == $child->subresourceid):
														$cores = $child->subresource ? $child->subresource->nodecores : 0;
														$mem = $child->subresource ? $child->subresource->nodemem : 0;
														$selected = ' selected="selected"';

														if ($facet = $resource->getFacet('allocation_unit')):
															$unit = $facet->value;
														endif;

														$clabel = $label;
													endif;
													?>
													<option value="{{ $child->subresourceid }}"<?php echo $selected; ?>
														data-clusterlabel="{{ $label }}"
														data-nodecores="{{ $child->subresource ? $child->subresource->nodecores : 0 }}"
														data-nodemem="{{ $child->subresource ? $child->subresource->nodemem : 0 }}"
														data-cluster="{{ $child->subresource ? $child->subresource->cluster : '' }}">{{ $child->subresource ? $child->subresource->name : trans('global.unknown') }}</option>
												<?php endforeach; ?>
											</optgroup>
										<?php endif; ?>
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
								<label for="field-cluster" id="field-clusterlabel" data-label="{{ $dlabel }}">{{ $clabel }}</label>
								<input type="text" name="fields[cluster]" id="field-cluster" class="form-control" maxlength="32" value="{{ $row->cluster }}" />
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label for="field-defaultwalltime">{{ trans('queues::queues.default walltime') }}</label>
								<span class="input-group">
									<input type="number" name="fields[defaultwalltime]" id="field-defaultwalltime" class="form-control" min="0" step="0.25" value="{{ ($row->defaultwalltime/60/60) }}" />
									<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.hours', 2) }}</span></span>
								</span>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<?php
								$val = 336;
								$wid = '';
								$walltime = $row->walltimes()->orderBy('id', 'desc')->first();
								if ($walltime):
									$val = ($walltime->walltime/60/60);
									$wid = $walltime->id;
								endif;
								?>
								<label for="field-maxwalltime">{{ trans('queues::queues.max walltime') }}</label>
								<span class="input-group">
									<input type="number" name="maxwalltime" id="field-maxwalltime" class="form-control" min="0" step="0.25" value="{{ $val }}" />
									<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.hours', 2) }}</span></span>
								</span>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="field-priority">{{ trans('queues::queues.priority') }}</label>
						<input type="number" name="fields[priority]" id="field-priority" class="form-control" min="0" max="999999" value="{{ $row->priority }}" />
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group form-block mb-0">
								<div class="form-check">
									<input type="checkbox" name="fields[reservation]" id="field-reservation" class="form-check-input" value="1"<?php if ($row->reservation) { echo ' checked="checked"'; } ?> />
									<label for="field-reservation" class="form-check-label">{{ trans('queues::queues.reservation') }}</label>
									<span class="form-text text-muted">{{ trans('queues::queues.reservation desc') }}</span>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group form-block mb-0">
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
								<label for="field-maxjobsqueued">{{ trans('queues::queues.max jobs queued') }}</label>
								<input type="number" name="fields[maxjobsqueued]" id="field-maxjobsqueued" class="form-control" min="0" max="99999" value="{{ $row->maxjobsqueued }}" />
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<label for="field-maxjobsqueueduser">{{ trans('queues::queues.max jobs queued per user') }}</label>
								<input type="number" name="fields[maxjobsqueueduser]" id="field-maxjobsqueueduser" class="form-control" min="0" max="99999" value="{{ $row->maxjobsqueueduser }}" />
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label for="field-maxjobsrun">{{ trans('queues::queues.max jobs run') }}</label>
								<input type="number" name="fields[maxjobsrun]" id="field-maxjobsrun" class="form-control" min="0" max="99999" value="{{ $row->maxjobsrun }}" />
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<label for="field-maxjobsrunuser">{{ trans('queues::queues.max jobs run per user') }}</label>
								<input type="number" name="fields[maxjobsrunuser]" id="field-maxjobsrunuser" class="form-control" min="0" max="99999" value="{{ $row->maxjobsrunuser }}" />
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="field-maxjobcores">{{ trans('queues::queues.max job cores') }}</label>
						<input type="number" name="fields[maxjobcores]" id="field-maxjobcores" class="form-control" min="0" max="99999999" value="{{ $row->maxjobcores }}" />
					</div>

					<div class="row">
						<div class="col-sm-6">
							<div class="form-group mb-0">
								<label for="field-maxijobfactor">{{ trans('queues::queues.max ijob factor') }}</label>
								<input type="number" name="fields[maxijobfactor]" id="field-maxijobfactor" class="form-control" min="0" max="99999" value="{{ $row->maxijobfactor }}" />
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group mb-0">
								<label for="field-maxijobuserfactor">{{ trans('queues::queues.max ijob user factor') }}</label>
								<input type="number" name="fields[maxijobuserfactor]" id="field-maxijobuserfactor" class="form-control" min="0" max="99999" value="{{ $row->maxijobuserfactor }}" />
							</div>
						</div>
					</div>
				</fieldset>

				<fieldset class="adminform">
					<legend>{{ trans('queues::queues.nodes') }}</legend>

					<div class="form-group">
						<label for="field-nodecoresdefault">{{ trans('queues::queues.node cores default') }}</label>
						<input type="number" name="fields[nodecoresdefault]" id="field-nodecoresdefault" class="form-control" min="0" max="999" value="{{ $row->nodecoresdefault }}" />
					</div>

					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label for="field-nodecoresmin">{{ trans('queues::queues.node cores min') }}</label>
								<input type="number" name="fields[nodecoresmin]" id="field-nodecoresmin" class="form-control" min="0" max="999" value="{{ $row->nodecoresmin }}" />
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<label for="field-nodecoresmax">{{ trans('queues::queues.node cores max') }}</label>
								<input type="number" name="fields[nodecoresmax]" id="field-nodecoresmax" class="form-control" min="0" max="999" value="{{ $row->nodecoresmax }}" />
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-6">
							<div class="form-group mb-0">
								<label for="field-nodememmin">{{ trans('queues::queues.node mem min') }}</label>
								<input type="text" name="fields[nodememmin]" id="field-nodememmin" class="form-control" maxlength="5" pattern="[0-9]{1,4}[PTGMKB]" value="{{ $row->nodememmin }}" />
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group mb-0">
								<label for="field-nodememmax">{{ trans('queues::queues.node mem max') }}</label>
								<input type="text" name="fields[nodememmax]" id="field-nodememmax" class="form-control" maxlength="5" pattern="[0-9]{1,4}[PTGMKB]" value="{{ $row->nodememmax }}" />
							</div>
						</div>
					</div>
				</fieldset>

			</div>
			<div class="col-md-5">
				<fieldset class="adminform">
					<legend>{{ trans('queues::queues.access') }}</legend>

					<div class="form-group form-block">
						<div class="form-check">
							<input type="checkbox" name="fields[aclusersenabled]" id="field-aclusersenabled" class="form-check-input" value="1"<?php if ($row->aclusersenabled) { echo ' checked="checked"'; } ?> />
							<label for="field-aclusersenabled" class="form-check-label">{{ trans('queues::queues.acl users enabled') }}</label>
							<span class="form-text text-muted">{{ trans('queues::queues.acl users enabled desc') }}</span>
						</div>
					</div>

					<div class="form-group<?php if ($row->aclusersenabled) { echo ' d-none'; } ?> mb-0">
						<label for="field-aclgroups">{{ trans('queues::queues.acl groups') }}:</label>
						<input type="text" name="fields[aclgroups]" id="field-aclgroups" class="form-control" value="{{ $row->aclgroups }}" />
						<span class="form-text text-muted">{{ trans('queues::queues.acl groups desc') }}</span>
					</div>
				</fieldset>

				<fieldset class="adminform">
					<legend>{{ trans('queues::queues.qos') }}</legend>

					<div id="qoses-0" class="qos-list{{ $row->schedulerid ? ' hide' : '' }}">
						<p class="text-center text-muted">Select a scheduler to view available QoS.</p>
					</div>
					<?php
					$applied_qoses = $row->queueqoses->pluck('qosid')->toArray();
					?>
					@foreach ($schedulers as $scheduler)
						@php
						$cls = ' hide';
						if ($row->schedulerid == $scheduler->id):
							$cls = '';
						endif;
						$qoses = $scheduler->qoses()->orderBy('name', 'asc')->get();
						@endphp
						<div id="qoses-{{ $scheduler->id }}" class="qos-list{{ $cls }}">
							@if (count($qoses))
								@foreach ($qoses as $qos)
									<div class="form-group mb-0">
										<div class="form-check">
											<input type="checkbox" name="qos[]" id="field-qos-{{ $qos->id }}" class="form-check-input" value="{{ $qos->id }}" <?php if (in_array($qos->id, $applied_qoses)) { echo ' checked'; } ?>/>
											<label for="field-qos-{{ $qos->id }}" class="form-check-label">{{ $qos->name }}</label>
										</div>
									</div>
								@endforeach
							@else
								<p class="text-center text-muted">No active QoS found for this scheduler.</p>
							@endif
						</div>
					@endforeach
				</fieldset>

				<fieldset class="adminform">
					<legend>{{ trans('global.publishing') }}</legend>

					<div class="form-group">
						<label for="field-enabled">{{ trans('queues::queues.submission state') }}</label>
						<select class="form-control" name="fields[enabled]" id="field-enabled">
							<option value="0"<?php if ($row->enabled == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.disabled') }}</option>
							<option value="1"<?php if ($row->enabled == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.enabled') }}</option>
						</select>
					</div>

					<div class="form-group">
						<label for="field-started">{{ trans('queues::queues.scheduling') }}</label>
						<select class="form-control" name="fields[started]" id="field-started">
							<option value="0"<?php if ($row->started == 0) { echo ' selected="selected"'; } ?>>{{ trans('queues::queues.stopped') }}</option>
							<option value="1"<?php if ($row->started == 1) { echo ' selected="selected"'; } ?>>{{ trans('queues::queues.started') }}</option>
						</select>
					</div>
				</fieldset>

				@if ($row->id)
					@php
					$creation = $row->history()
						->where('action', '=', 'created')
						->first();
					@endphp
					@if ($creation)
					<table class="meta">
						<tbody>
							<tr>
								<th scope="row">{{ trans('queues::queues.created') }}</th>
								<td>{{ $creation->created_at->format('M j, Y g:ia') }}</td>
							</tr>
							<tr>
								<th scope="row">{{ trans('storage::storage.creator') }}</th>
								<td>{{ $creation->user ? $creation->user->name . ' (' . $creation->user->username . ')' : 'ID #' . $creation->user_id }}</td>
							</tr>
						</tbody>
					</table>
					@endif
				@endif
			</div>
		</div>

		<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
		@csrf
	</form>

@if ($row->id)
	</div><!-- / #queue-details -->
	<div class="tab-pane" id="queue-allocations" role="tabpanel" aria-labelledby="queue-allocations-tab">
		<p class="text-right">
			<a href="#dialog-sell" id="node-sell" data-toggle="modal" data-target="#dialog-sell" class="btn btn-secondary dialog-btn icon-dollar-sign">{{ trans('queues::queues.sell') }}</a>
			<a href="#dialog-loan" id="node-loan" data-toggle="modal" data-target="#dialog-loan" class="btn btn-secondary dialog-btn icon-shuffle">{{ trans('queues::queues.loan') }}</a>
		</p>

		<div class="card">
			<?php
			$purchases = $row->sizes;
			//$sold  = $row->sold;
			$loans = $row->loans;
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

					$items = $items->sortByDesc('datetimestart')->slice(0, 20);

					foreach ($items as $item): ?>
					<tr<?php if ($item->hasEnd() && $item->hasEnded()) { echo ' class="trashed"'; } ?>>
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
							<!-- <span class="{{ $cls }}">{{ ($cls == 'increase' ? '+' : '-') }} {{ number_format(abs($amt), 1) }}</span> -->
							@if ($unit == 'sus')
								<span class="{{ $cls }}">{{ ($cls == 'increase' ? '+' : '-') }} {{ number_format(abs($amt), 1) }}</span>
							@elseif ($unit == 'gpus')
								<span class="{{ $cls }}">{{ ($cls == 'increase' ? '+' : '-') }} {{ number_format(abs($amt)) }}</span> <span class="text-muted">{{ strtolower(trans('queues::queues.cores')) }}</span>,
								<span class="{{ $cls }}">{{ ($cls == 'increase' ? '+' : '-') }} {{ number_format($gpus) }}</span> <span class="text-muted">{{ strtolower(trans('queues::queues.' . $unit)) }}</span>
							@else
								<span class="{{ $cls }}">{{ ($cls == 'increase' ? '+' : '-') }} {{ number_format(abs($amt)) }}</span> <span class="text-muted">{{ strtolower(trans('queues::queues.cores')) }}</span>,
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
											<input type="hidden" name="nodecount" class="form-control nodes" size="4" id="loan-nodes{{ $item->id }}" data-nodes="{{ $row->subresource->nodecores }}" data-cores-field="loan-cores{{ $item->id }}" value="{{ $nodecores ? round($item->corecount / $nodecores, 1) : $item->nodecount }}" step="0.5" />
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
														<input type="number" name="nodecount" class="form-control nodes" size="4" id="loan-nodes{{ $item->id }}" data-nodes="{{ $row->subresource->nodecores }}" data-cores-field="loan-cores{{ $item->id }}" value="{{ $nodecores ? round($item->corecount / $nodecores, 1) : $item->nodecount }}" step="0.5" />
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
						<input type="hidden" class="form-control nodes" size="4" id="sell-nodes" name="nodecount" data-nodes="{{ $row->subresource->nodecores }}" data-cores-field="sell-cores" value="0" step="0.5" />
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
									<input type="number" class="form-control nodes" size="4" id="sell-nodes" name="nodecount" data-nodes="{{ $row->subresource->nodecores }}" data-cores-field="sell-cores" value="0" step="0.5" />
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
		</div>

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
						<input type="hidden" name="nodecount" class="form-control nodes" size="4" id="loan-nodes" data-nodes="{{ $row->subresource->nodecores }}" data-cores-field="loan-cores" value="0" step="0.5" />
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
									<input type="number" name="nodecount" class="form-control nodes" size="4" id="loan-nodes" data-nodes="{{ $row->subresource->nodecores }}" data-cores-field="loan-cores" value="0" step="0.5" />
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
		</div>
	</div><!-- / #queue-nodes -->
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
				$ev->name = $user->user ? $user->user->name : trans('global.unknown');
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
						$actor = '<a href="' . route('admin.users.show', ['id' => $action->user_id]) . '">' . e($action->user->name) . '</a>';
					endif;

					$created = $action->created_at ? $action->created_at : trans('global.unknown');

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
@endif
@stop
