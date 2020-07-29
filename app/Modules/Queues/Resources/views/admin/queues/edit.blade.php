@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css') }}" />
@stop

@section('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/queues/js/admin.js?v=' . filemtime(public_path() . '/modules/queues/js/admin.js')) }}"></script>
@stop

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
{!! config('queues.name') !!}: <?php echo $row->id ? trans('queues::queues.edit') . ': #' . $row->id : trans('queues::queues.create'); ?>
@stop

@section('content')
<form action="{{ route('admin.queues.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('JGLOBAL_VALIDATION_FORM_FAILED') }}">

	@if ($row->id)
	<div class="tabs">
		<ul>
			<li><a href="#queue-details">Queue</a></li>
			<li><a href="#queue-nodes">Purchases & Loans</a></li>
		</ul>
		<div id="queue-details">
	@endif

	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-queuetype">{{ trans('queues::queues.type') }}:</label>
					<select name="fields[queuetype]" id="field-queuetype" class="form-control">
						<option value="0">{{ trans('global.none') }}</option>
						<?php foreach ($types as $type): ?>
							<option value="{{ $type->id }}"<?php if ($row->queuetype == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-name">{{ trans('queues::queues.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" pattern="[a-zA-Z0-9_]{1,64}" maxlength="64" value="{{ $row->name }}" />
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group">
							<label for="field-schedulerid">{{ trans('queues::queues.scheduler') }}:</label>
							<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></span>
							<select name="fields[schedulerid]" id="field-schedulerid" class="form-control">
								<option value="0">{{ trans('global.none') }}</option>
								<?php foreach ($schedulers as $scheduler): ?>
									<?php $selected = ($scheduler->id == $row->schedulerid ? ' selected="selected"' : ''); ?>
									<option value="{{ $scheduler->id }}"<?php echo $selected; ?>
										data-defaultmaxwalltime="{{ $scheduler->defaultmaxwalltime }}"
										data-schedulerpolicyid="{{ $scheduler->schedulerpolicyid }}"
										data-resourceid="{{ $scheduler->resource->id }}"
										data-api="{{ route('api.resources.read', ['id' => $scheduler->resource->id]) }}">{{ $scheduler->hostname }}</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group">
							<label for="field-schedulerpolicyid">{{ trans('queues::queues.scheduler policy') }}:</label>
							<select name="fields[schedulerpolicyid]" id="field-schedulerpolicyid" class="form-control">
								<option value="0">{{ trans('global.none') }}</option>
								<?php foreach ($schedulerpolicies as $schedulerpolicy): ?>
									<?php $selected = ($schedulerpolicy->id == $row->schedulerpolicyid ? ' selected="selected"' : ''); ?>
									<option value="{{ $schedulerpolicy->id }}"<?php echo $selected; ?>>{{ $schedulerpolicy->name }}</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group">
							<?php
							$cores = '-';
							$mem   = '-';
							?>
							<label for="field-subresourceid">{{ trans('queues::queues.subresource') }}:</label>
							<select name="fields[subresourceid]" id="field-subresourceid" class="form-control">
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
							<span class="form-text text-muted">
								<span id="SPAN_nodecores">{{ $cores }}</span> cores,
								<span id="SPAN_nodemem">{{ $mem }}</span> memory
							</span>
						</div>
					</div>
					<div class="col col-md-6">
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
								<span class="input-group-append"><span class="input-group-text">hours</span></span>
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
								<input type="number" name="fields[maxwalltime]" id="field-maxwalltime" class="form-control" min="0" step="0.25" value="{{ $val }}" />
								<span class="input-group-append"><span class="input-group-text">hours</span></span>
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
						<input type="text" name="fields[groupid]" id="field-groupid" class="form-control form-groups" data-uri="{{ url('/') }}/api/groups/?api_token={{ auth()->user()->api_token }}&search=%s" data-multiple="false" placeholder="Search for group..." value="{{ ($row->group ? $row->group->name . ':' . $row->groupid : '') }}" />
						<span class="input-group-append"><span class="input-group-text icon-users"></span></span>
					</span>
				</div>

				<div class="form-group form-block">
					<div class="form-check">
						<input type="checkbox" name="fields[reservation]" id="field-reservation" class="form-check-input" value="1"<?php if ($row->reservation) { echo ' checked="checked"'; } ?> />
						<label for="field-reservation" class="form-check-label">{{ trans('queues::queues.reservation') }}</label>
						<span class="form-text text-muted">Reservation explanation...</span>
					</div>
				</div>

				<div class="form-group form-block">
					<div class="form-check">
						<input type="checkbox" name="fields[free]" id="field-free" class="form-check-input" value="1"<?php if ($row->free) { echo ' checked="checked"'; } ?> />
						<label for="field-free" class="form-check-label">{{ trans('queues::queues.free') }}</label>
						<span class="form-text text-muted">{{ trans('queues::queues.free desc') }}</span>
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
					<legend>Nodes</legend>

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
		<div class="col col-md-5">
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

	@if ($row->id)
	</div>
	<div id="queue-nodes">
		<p class="text-right">
			<a href="#dialog-sell" class="btn btn-secondary icon-dollar-sign">{{ trans('queues::queues.sell nodes') }}</a>
			<a href="#dialog-loan" class="btn btn-secondary icon-shuffle">{{ trans('queues::queues.loan nodes') }}</a>
		</p>

		<table class="table adminlist">
			<thead>
				<tr>
					<th scope="col">Start</th>
					<th scope="col">End</th>
					<th scope="col">Action</th>
					<th scope="col">Source</th>
					<th scope="col">Resource</th>
					<th scope="col">Queue</th>
					<th scope="col" class="text-right">Nodes</th>
					<th scope="col" class="text-right">Total</th>
					<th scope="col"></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$sizes = $row->sizes;
				$loans = $row->loans;
				$nodecores = $row->subresource->nodecores;
				$total = 0;

				$items = $sizes->merge($loans)->sortBy('datetimestart');

				foreach ($items as $item)
				{
					if ($item->datetimestop && $item->datetimestop != '0000-00-00 00:00:00')
					{
						continue;
					}
					if (($item->corecount < 0 && $item->type == 0)
					 || ($item->corecount < 0 && $item->type == 1))
					{
						$total -= $nodecores ? round($item->corecount / $nodecores, 1) : 0;
					}
					else if (($item->corecount > 0 && $item->type == 0)
						|| ($item->corecount > 0 && $item->type == 1))
					{
						$total += $nodecores ? round($item->corecount / $nodecores, 1) : 0;
					}
				}
				$prev = $total;

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
								$cls = 'down';
							}
							else if ($item->corecount >= 0)
							{
								$what .= " from";
								$cls = 'up';
							}
						}
						else
						{
							if ($item->corecount < 0)
							{
								$what = "Sale to";
								$cls = 'down';
							}
							else if ($item->corecount >= 0)
							{
								$what = "Purchase from";
								$cls = 'up';
							}
						}

						//$title  = $item->nodecount . " nodes / ";
						//$title .= $item->corecount . " cores; ".$what.": ";
						$amt = $nodecores ? round($item->corecount / $nodecores, 1) : 0;

						echo $what;
						?>
					</td>
					<td>
						@if ($item->source)
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
						<span class="{{ $cls }}">{{ ($cls == 'up' ? '+' : '-') }}</span>{{ $amt }}
					</td>
					<td class="text-right">
						@if ($item->datetimestop && $item->datetimestop != '0000-00-00 00:00:00' && $item->datetimestop != '-0001-11-30 00:00:00')
							{{ $prev }}
						@else
							{{ $prev }}
						@endif
					</td>
					<td>
						<a href="#" class="delete glyph icon-trash">{{ trans('global.button.delete') }}</a>
					</td>
				</tr>
				<?php
				$prev = $cls == 'up' ? $prev - $amt : $prev + $amt;
			endforeach; ?>
			</tbody>
		</table>

		<div class="dialog" id="dialog-sell">
			<div class="form-group">
				<label for="nodes">Size</label>
				<input type="text" class="form-control" size="4" id="nodes" name="nodes" value="" />
			</div>

			<div class="form-group">
				<label for="cores">Cores</label>
				<input type="text" class="form-control" size="4" id="cores" name="cores" value="" />
			</div>

			<div class="form-group">
				<label for="datetimestart">Start</label>
				<input type="text" class="form-control date-pick" id="datetimestart" name="start" value="" />
			</div>

			<div class="form-group">
				<label for="datetimestop">End</label>
				<input type="text" class="form-control date-pick" id="datetimestop" name="start" disabled="disabled" placeholder="end of cluster life" value="" />
			</div>

			<div class="form-roup">
				<label>Sell to</label>

				<select id="group" class="form-control">
					<option>(Select Group)</option>
					<option value="-1">(ITaP Owned)</option>
				</select>

				<select id="queue" class="form-control" disabled="true">
					<option>(Select Queue)</option>
				</select>
			</div>
		</div>

		<div class="dialog dialog-loan">
			
		</div>
	</div>
</div>
	@endif

	@csrf
</form>
@stop