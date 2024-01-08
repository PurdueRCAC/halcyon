@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/queues/js/admin.js') }}"></script>
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
		Toolbar::cancel($row->id ? route('admin.queues.show', ['id' => $row->id]) : route('admin.queues.index'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('queues::queues.module name') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')

	@if ($row->trashed())
		<div class="alert alert-warning">{{ trans('queues::queues.entry marked as trashed') }}</div>
	@endif

	<form action="{{ route('admin.queues.store') }}" method="post" name="adminForm" id="adminForm" class="editform form-validate">
		@if ($errors->any())
			<div class="alert alert-danger">
				<ul>
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

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
							<span class="input-group-append"><span class="input-group-text fa fa-users"></span></span>
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
						<div class="col-md-4">
							<div class="form-group form-block mb-0">
								<div class="form-check">
									<input type="checkbox" name="fields[reservation]" id="field-reservation" class="form-check-input" value="1"<?php if ($row->reservation) { echo ' checked="checked"'; } ?> />
									<label for="field-reservation" class="form-check-label">{{ trans('queues::queues.reservation') }}</label>
									<span class="form-text text-muted">{{ trans('queues::queues.reservation desc') }}</span>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group form-block mb-0">
								<div class="form-check">
									<input type="checkbox" name="fields[free]" id="field-free" class="form-check-input" value="1"<?php if ($row->free) { echo ' checked="checked"'; } ?> />
									<label for="field-free" class="form-check-label">{{ trans('queues::queues.free') }}</label>
									<span class="form-text text-muted">{{ trans('queues::queues.free desc') }}</span>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group form-block mb-0">
								<div class="form-check">
									<input type="checkbox" name="fields[shared]" id="field-shared" class="form-check-input" value="1"<?php if ($row->shared) { echo ' checked="checked"'; } ?> />
									<label for="field-shared" class="form-check-label">{{ trans('queues::queues.shared') }}</label>
									<span class="form-text text-muted">{{ trans('queues::queues.shared desc') }}</span>
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
@stop
