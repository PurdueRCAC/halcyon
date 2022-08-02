
@push('scripts')
<script src="{{ asset('modules/queues/js/site.js?v=' . filemtime(public_path() . '/modules/queues/js/site.js')) }}"></script>
@endpush

<?php
$canManage = auth()->user()->can('edit groups') || (auth()->user()->can('edit.own groups') && $group->ownerid == auth()->user()->id);

$q = (new App\Modules\Queues\Models\Queue)->getTable();
$s = (new App\Modules\Queues\Models\Scheduler)->getTable();
$r = (new App\Modules\Resources\Models\Subresource)->getTable();
$c = (new App\Modules\Resources\Models\Child)->getTable();
$a = (new App\Modules\Resources\Models\Asset)->getTable();

$queues = $group->queues()
	->select($q . '.*')
	->join($s, $s . '.id', $q . '.schedulerid')
	->join($r, $r . '.id', $q . '.subresourceid')
	->join($c, $c . '.subresourceid', $r . '.id')
	->join($a, $a . '.id', $c . '.resourceid')
	->whereNull($s . '.datetimeremoved')
	->whereNull($r . '.datetimeremoved')
	->whereNull($a . '.datetimeremoved')
	->orderBy($r . '.name', 'asc')
	->orderBy($q . '.name', 'asc')
	->get();

$queues = $queues->reject(function($q) use ($canManage)
{
	// The user is not a manager and is not a member of the queue
	return (!$canManage && !$q->users()->where('userid', '=', auth()->user()->id)->count());
});
?>

@if (auth()->user()->can('manage groups'))
<div class="row mb-3">
	<div class="col-md-12 text-right">
		<a href="#add_queue_dialog" class="add_queue btn btn-secondary btn-sm dialog-pl-btn">
			<span class="fa fa-plus-circle"></span> Add Queue
		</a>
	</div>
</div>
<div class="modl dialog queue-dialog" id="add_queue_dialog" data-id="{{ $group->id }}" title="Add queue to {{ $group->name }}">
	<form class="modl-content dialog-content" id="form_queue_{{ $group->id }}" method="post" data-api="{{ route('api.queues.create') }}">
		<?php
		$types = App\Modules\Queues\Models\Type::orderBy('name', 'asc')->get();
		$schedulers = App\Modules\Queues\Models\Scheduler::orderBy('hostname', 'asc')->get();
		$schedulerpolicies = App\Modules\Queues\Models\SchedulerPolicy::orderBy('name', 'asc')->get();
		$subresources = array();
		$resources = (new App\Modules\Resources\Models\Asset)->tree();
		?>
		<div class="modl-body dialog-body">

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="queue-queuetype">{{ trans('queues::queues.type') }}</label>
								<select name="queuetype" id="queue-queuetype" class="form-control">
									@foreach ($types as $type)
										<option value="{{ $type->id }}">{{ $type->name }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="queue-queueclass">{{ trans('queues::queues.class') }}</label>
								<select name="queueclass" id="queue-queueclass" class="form-control">
									<option value="owner">{{ trans('queues::queues.owner') }}</option>
									<option value="standby">{{ trans('queues::queues.standby') }}</option>
									<option value="workq">{{ trans('queues::queues.work') }}</option>
									<option value="debug">{{ trans('queues::queues.debug') }}</option>
								</select>
							</div>
						</div>
					</div>

					<input type="hidden" name="groupid" id="queue-groupid" value="{{ $group->id }}" />

					<div class="form-group">
						<label for="queue-name">{{ trans('queues::queues.name') }} <span class="required">*</span></label>
						<input type="text" name="name" id="queue-name" class="form-control{{ $errors->has('fields.name') ? ' is-invalid' : '' }}" required pattern="[a-zA-Z0-9_\-]{1,64}" maxlength="64" value="" data-invalid-msg="{{ trans('queues::queues.name error') }}" />
						<span class="invalid-feedback">{{ trans('queues::queues.error.invalid name') }}</span>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="queue-schedulerid">{{ trans('queues::queues.scheduler') }}  <span class="required">*</span></label>
								<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('global.loading') }}</span></span>
								<select name="schedulerid" id="queue-schedulerid" class="form-control{{ $errors->has('fields.schedulerid') ? ' is-invalid' : '' }}" required>
									<option value="0">{{ trans('global.none') }}</option>
									@foreach ($schedulers as $scheduler)
										<option value="{{ $scheduler->id }}"
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
								<label for="queue-schedulerpolicyid">{{ trans('queues::queues.scheduler policy') }}</label>
								<select name="schedulerpolicyid" id="queue-schedulerpolicyid" class="form-control">
									<option value="0">{{ trans('global.none') }}</option>
									@foreach ($schedulerpolicies as $schedulerpolicy)
										<option value="{{ $schedulerpolicy->id }}">{{ $schedulerpolicy->name }}</option>
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
								$dlabel = trans('queues::queues.cluster');
								$clabel = $dlabel;
								?>
								<label for="queue-subresourceid">{{ trans('queues::queues.subresource') }} <span class="required">*</span></label>
								<select name="subresourceid" id="queue-subresourceid" class="form-control{{ $errors->has('fields.subresourceid') ? ' is-invalid' : '' }}" required>
									<option value="0">{{ trans('global.none') }}</option>
									<?php foreach ($resources as $resource): ?>
										<?php
										$children = $resource->children()->get();
										if (count($children)):
											$label = $dlabel;
											if ($facet = $resource->getFacet('cluster_label')):
												$label = $facet->value;
											endif;
											?>
											<optgroup data-resourceid="{{ $resource->id }}" label="{{ $resource->name }}">
												<?php foreach ($children as $child): ?>
													<option value="{{ $child->subresourceid }}"
														data-clusterlabel="{{ $label }}"
														data-nodecores="{{ $child->subresource && $child->subresource->nodecores ? $child->subresource->nodecores : 0 }}"
														data-nodemem="{{ $child->subresource && $child->subresource->nodemem ? $child->subresource->nodemem : 0 }}"
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
								<label for="queue-cluster" id="queue-clusterlabel" data-label="{{ $dlabel }}">{{ $clabel }}</label>
								<input type="text" name="cluster" id="queue-cluster" class="form-control" maxlength="32" value="" />
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label for="queue-defaultwalltime">{{ trans('queues::queues.default walltime') }}</label>
								<span class="input-group">
									<input type="number" name="defaultwalltime" id="queue-defaultwalltime" class="form-control" min="0" step="0.25" value="0.5" />
									<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.hours', 2) }}</span></span>
								</span>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<label for="queue-maxwalltime">{{ trans('queues::queues.max walltime') }}</label>
								<span class="input-group">
									<input type="number" name="maxwalltime" id="queue-maxwalltime" class="form-control" min="0" step="0.25" value="336" />
									<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.hours', 2) }}</span></span>
								</span>
							</div>
						</div>
					</div>

					<input type="hidden" name="priority" id="queue-priority" class="form-control" min="0" max="999999" value="1000" />
					<input type="hidden" name="reservation" id="queue-reservation" class="form-check-input" value="0" />
					<input type="hidden" name="free" id="queue-free" class="form-check-input" value="0" />

					<input type="hidden" name="maxjobsqueued" id="queue-maxjobsqueued" class="form-control" min="0" max="99999" value="12000" />
					<input type="hidden" name="maxjobsqueueduser" id="queue-maxjobsqueueduser" class="form-control" min="0" max="99999" value="5000" />
					<input type="hidden" name="maxjobsrun" id="queue-maxjobsrun" class="form-control" min="0" max="99999" value="0" />
					<input type="hidden" name="maxjobsrunuser" id="queue-maxjobsrunuser" class="form-control" min="0" max="99999" value="0" />
					<input type="hidden" name="maxjobcores" id="queue-maxjobcores" class="form-control" min="0" max="99999999" value="0" />
					<input type="hidden" name="maxijobfactor" id="queue-maxijobfactor" class="form-control" min="0" max="99999" value="2" />
					<input type="hidden" name="maxijobuserfactor" id="queue-maxijobuserfactor" class="form-control" min="0" max="99999" value="1" />
					<input type="hidden" name="nodecoresdefault" id="queue-nodecoresdefault" class="form-control" min="0" max="999" value="0" />
					<input type="hidden" name="nodecoresmin" id="queue-nodecoresmin" class="form-control" min="0" max="999" value="128" />
					<input type="hidden" name="nodecoresmax" id="queue-nodecoresmax" class="form-control" min="0" max="999" value="128" />
					<input type="hidden" name="nodememmin" id="queue-nodememmin" class="form-control" pattern="[0-9]{1,4}[PTGMKB]" value="256G" />
					<input type="hidden" name="nodememmax" id="queue-nodememmax" class="form-control" pattern="[0-9]{1,4}[PTGMKB]" value="256G" />
					<input type="hidden" name="aclusersenabled" id="queue-aclusersenabled" class="form-check-input" value="0" />
					<input type="hidden" name="aclgroups" id="queue-aclgroups" class="form-control" value="" />

					<input type="hidden" name="enabled" id="queue-enabled" class="form-control" value="1" />
					<input type="hidden" name="started" id="queue-started" class="form-control" value="1" />

				<!-- <fieldset class="adminform hide">
					<legend>{{ trans('global.publishing') }}</legend>

					<div class="form-group">
						<label for="queue-enabled">{{ trans('queues::queues.submission state') }}</label>
						<select class="form-control" name="enabled" id="queue-enabled">
							<option value="0">{{ trans('global.disabled') }}</option>
							<option value="1" selected="selected">{{ trans('global.enabled') }}</option>
						</select>
					</div>

					<div class="form-group">
						<label for="queue-started">{{ trans('queues::queues.scheduling') }}</label>
						<select class="form-control" name="started" id="queue-started">
							<option value="0">{{ trans('queues::queues.stopped') }}</option>
							<option value="1" selected="selected">{{ trans('queues::queues.started') }}</option>
						</select>
					</div>
				</fieldset> -->

				<div class="alert alert-danger hide" id="add_queue_error"></div>
			</div>

			<div class="modl-footer dialog-footer text-right">
				<input type="submit" id="add_queue_save" class="btn btn-success queue-dialog-submit"
					data-group="{{ $group->id }}"
					data-api="{{ route('api.queues.create') }}"
					value="{{ trans('global.button.create') }}" />
			</div>

			@csrf
	</form>
</div>
@endif

<table class="table">
	<caption class="sr-only">{{ trans('queues::queues.list of queues') }}</caption>
	<thead>
		<tr>
			<th scope="col" class="text-center">{{ trans('queues::queues.state') }}</th>
			<th scope="col">{{ trans('queues::queues.resource') }}</th>
			<th scope="col">{{ trans('queues::queues.name') }}</th>
			<th scope="col" class="text-right" colspan="2">{{ trans('queues::queues.total') }}</th>
			<th scope="col" class="text-right">{{ trans('queues::queues.walltime') }}</th>
			@if (auth()->user()->can('edit.state queues'))
			<th scope="col">{{ trans('queues::queues.options') }}</th>
			@endif
			@if (auth()->user()->can('delete queues'))
			<th scope="col"></th>
			@endif
		</tr>
	</thead>
	<tbody id="queues">
		@if (count($queues) > 0)
			@foreach ($queues as $q)
				<?php
				$unit = 'nodes';
				if ($facet = $q->resource->getFacet('allocation_unit')):
					$unit = $facet->value;
				endif;
				?>
				<tr>
					<td class="text-center">
						@if ($q->enabled && $q->started && $q->active)
							@if ($q->reservation)
								<span class="text-info tip" title="{{ trans('queues::queues.queue has dedicated reservation') }}">
									<span class="fa fa-circle" aria-hidden="true"></span><span class="sr-only">{{ trans('queues::queues.queue has dedicated reservation') }}</span>
								</span>
							@else
								<span class="text-success tip" title="{{ trans('queues::queues.queue is running') }}">
									<span class="fa fa-check" aria-hidden="true"></span><span class="sr-only">{{ trans('queues::queues.queue is running') }}</span>
								</span>
							@endif
						@elseif ($q->active)
							<span class="text-danger tip" title="{{ trans('queues::queues.queue is stopped') }}">
								<span class="fa fa-minus-circle" aria-hidden="true"></span><span class="sr-only">{{ trans('queues::queues.queue is stopped') }}</span>
							</span>
						@elseif (!$q->active)
							<span class="text-warning tip" title="{{ trans('queues::queues.queue has not active resources') }}">
								<span class="fa fa-exclamation-triangle" aria-hidden="true"></span><span class="sr-only">{{ trans('queues::queues.queue has not active resources') }}</span>
							</span>
						@endif
					</td>
					<td>
						{{ $q->subresource ? $q->subresource->name : '' }}
					</td>
					<td>
						@if (auth()->user()->can('manage queues'))
							<a href="{{ route('admin.queues.edit', ['id' => $q->id]) }}">
								{{ $q->name }}
							</a>
						@else
							{{ $q->name }}
						@endif
					</td>
				@if (!$q->active && $upcoming = $q->getUpcomingLoanOrPurchase())
					<td class="text-right" colspan="2">
						<span class="text-success">{{ $upcoming->type ? 'loan' : 'purchase' }} starts {{ $upcoming->datetimestart->diffForHumans() }}</span>
					</div>
				@else
					@if ($unit == 'sus')
						<td class="text-right" colspan="2">
							{{ $q->serviceunits }} <span class="text-muted">SUs</span>
						</div>
					@else
						<td class="text-right">
							{{ $q->totalcores }} <span class="text-muted">{{ strtolower(trans('queues::queues.cores')) }}</span>
						</td>
						<td class="text-right">
							@if ($q->subresource && $q->subresource->nodecores > 0)
								{{ round($q->totalcores/$q->subresource->nodecores, 1) }} <span class="text-muted">{{ strtolower(trans('queues::queues.' . $unit)) }}</span>
							@endif
						</td>
					@endif
				@endif
					<td class="text-right">
						{{ $q->humanWalltime }}
					</td>
					@if (auth()->user()->can('edit.state queues'))
					<td>
						@if ($q->enabled)
							<a class="set-queue-status btn-enable"
								href="{{ route('admin.queues.disable', ['id' => $q->id]) }}"
								data-api="{{ route('api.queues.update', ['id' => $q->id]) }}"
								data-queue="{{ $q->id }}"
								data-status="0"
								title="{{ trans('queues::queues.stop scheduling') }}">
								<span class="fa fa-ban" aria-hidden="true"></span> {{ trans('queues::queues.stop scheduling') }}
							</a>
						@else
							<a class="set-queue-status btn-disable"
								href="{{ route('admin.queues.enable', ['id' => $q->id]) }}"
								data-api="{{ route('api.queues.update', ['id' => $q->id]) }}"
								data-queue="{{ $q->id }}"
								data-status="1"
								title="{{ trans('queues::queues.start scheduling') }}">
								<span class="fa fa-check" aria-hidden="true"></span> {{ trans('queues::queues.start scheduling') }}
							</a>
						@endif
					</td>
					@endif
					@if (auth()->user()->can('delete queues') || auth()->user()->can('edit queues'))
					<td class="text-right">
						@if (auth()->user()->can('edit queues'))
						<a class="btn tip" data-toggle="collapse" data-parent="#queues" href="#collapse{{ $q->id }}" title="{{ trans('queues::queues.purchases and loans') }}">
							<span class="fa fa-list"></span><span class="sr-only">{{ trans('queues::queues.purchases and loans') }}</span>
						</a>
						@endif
						@if (auth()->user()->can('delete queues'))
						<a class="delete-queue btn text-danger"
							href="{{ route('admin.queues.delete', ['id' => $q->id]) }}"
							data-api="{{ route('api.queues.delete', ['id' => $q->id]) }}"
							data-queue="{{ $q->id }}"
							data-confirm="{{ trans('queues::queues.confirm delete queue') }}"
							title="{{ trans('global.button.delete') }}">
							<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.button.delete') }}</span>
						</a>
						@endif
					</td>
					@endif
				</tr>
				@if (auth()->user()->can('edit queues'))
					<tr class="collapse" id="collapse{{ $q->id }}">
						<td colspan="8">

							<div class="card">
								<div class="card-header">
									<div class="row">
										<div class="col-md-6">
											{{ trans('queues::queues.purchases and loans') }}
										</div>
										<div class="col-md-6 text-right">
											<a href="#dialog-sell{{ $q->id }}" id="node-sell{{ $q->id }}" class="btn btn-secondary btn-sm dialog-pl-btn">{{ trans('queues::queues.sell') }}</a>
											<a href="#dialog-loan{{ $q->id }}" id="node-loan{{ $q->id }}" class="btn btn-secondary btn-sm dialog-pl-btn">{{ trans('queues::queues.loan') }}</a>
										</div>
									</div>
								</div>
								<div class="card-body">
									<?php
									$purchases = $q->sizes;
									//$sold  = $q->sold;
									$loans = $q->loans;
									$nodecores = $q->subresource->nodecores;
									$total = 0;

									$items = $purchases;//$purchases->merge($sold);
									$items = $items->merge($loans)->sortBy('datetimestart');
									?>
									<table class="table table-hover">
										<caption class="sr-only">{{ trans('queues::queues.purchases and loans') }}</caption>
										<thead>
											<tr>
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
											foreach ($items as $item)
											{
												if ($item->hasEnded())
												{
													$item->total = $total;
													continue;
												}

												/*if (($item->sellerqueueid == $q->id && $item->corecount > 0)
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
												if ($item->serviceunits > 0)
												{
													$total += $item->serviceunits;
												}
												else
												{
													$total += $nodecores ? round($item->corecount / $nodecores, 1) : 0;
												}

												$item->total = $total;
											}

											$items = $items->sortByDesc('datetimestart')->slice(0, 20);

											foreach ($items as $item): ?>
											<tr<?php if ($item->hasEnd() && $item->hasEnded()) { echo ' class="trashed"'; } ?>>
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
													if ($item->type == 1)
													{
														if ($item->corecount < 0 || $item->serviceunits < 0)
														{
															$what = 'Loan to';
															$cls = 'text-danger';
														}
														elseif ($item->corecount >= 0)
														{
															$what = 'Loan from';
															$cls = 'text-success';
														}
													}
													else
													{
														if ($item->sellerqueueid == $q->id || $item->corecount < 0 || $item->serviceunits < 0)
														{
															$what = 'Sale to';
															$cls = 'text-danger';
														}
														elseif ($item->corecount >= 0)
														{
															$what = 'Purchase from';
															$cls = 'text-success';
														}
													}

													if ($item->serviceunits > 0)
													{
														$amt = $item->serviceunits;
													}
													else
													{
														$amt = $item->nodecount;
														if ($item->corecount)
														{
															$amt = $nodecores ? round($item->corecount / $nodecores, 1) : 0;
														}
													}

													echo $what;
													?>
													@if ($comment = $item->comment)
														<span class="fa fa-comment tip" title="{{ $comment }}"></span>
													@endif
												</td>
												<td>
													@if ($item->sellerqueueid == $q->id)
														{{ $item->queue->group ? $item->queue->group->name : '(ITaP Owned)' }}
													@elseif ($item->source)
														{{ $item->source->group ? $item->source->group->name : '(ITaP Owned)' }}
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
												<td class="text-right">
													<span class="{{ $cls }}">{{ ($cls == 'text-success' ? '+' : '-') }} {{ number_format(abs($amt), 1) }}</span>
													@if ($unit == 'sus')
														<span class="text-muted">SUs</span>
													@else
														<span class="text-muted">{{ strtolower(trans('queues::queues.' . $unit)) }}</span>
													@endif
												</td>
												<td class="text-right">
													{{ number_format($item->total, 1) }}
												</td>
												<td class="text-right">
													<a href="#dialog-edit{{ $item->id }}" class="btn btn-sm queue-pl-edit"
														data-success="{{ trans('global.messages.item updated') }}"
														data-api="{{ route('api.queues.' . ($item->type == 1 ? 'loans' : 'sizes'). '.update', ['id' => $item->id]) }}"
														data-id="{{ $item->id }}">
														<span class="fa fa-pencil" aria-hidden="true"></span><span class="sr-only">{{ trans('global.button.edit') }}</span>
													</a>
												</td>
												<td class="text-right">
													@if (auth()->user()->can('admin queues'))
													<button class="btn btn-sm text-danger queue-pl-delete"
														data-confirm="{{ trans('global.confirm delete') }}"
														data-success="{{ trans('global.messages.item deleted', ['count' => 1]) }}"
														data-api="{{ route('api.queues.' . ($item->type == 1 ? 'loans' : 'sizes'). '.delete', ['id' => $item->id]) }}"
														data-id="{{ $item->id }}">
														<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.button.delete') }}</span>
													</button>
													@endif

													<div class="modl dialog" id="dialog-edit{{ $item->id }}" title="{{ trans('queues::queues.edit ' . ($item->type == 1 ? 'loan' : 'size')) }} #{{ $item->id }}">
														<form method="post" class="modl-content dialog-content" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.queues.' . ($item->type == 1 ? 'loans' : 'sizes') . '.update', ['id' => $item->id]) }}">
															<div class="modl-body dialog-body">
															@if ($unit == 'sus')
															<div class="row">
																<div class="col-md-12">
																	<div class="form-group">
																		<label for="loan-serviceunits{{ $item->id }}">{{ trans('queues::queues.service units') }}</label>
																		<input type="number" class="form-control serviceunits" size="4" id="loan-serviceunits{{ $item->id }}" name="serviceunits" value="{{ $item->serviceunits }}" step="0.25" />
																	</div>
																</div>
															</div>
															<input type="hidden" name="nodecount" class="form-control nodes" size="4" id="loan-nodes{{ $item->id }}" data-nodes="{{ $q->subresource->nodecores }}" data-cores-field="loan-cores{{ $item->id }}" value="{{ $nodecores ? round($item->corecount / $nodecores, 1) : $item->nodecount }}" step="0.5" />
															<input type="hidden" name="corecount" class="form-control cores" size="4" id="loan-cores{{ $item->id }}" data-cores="{{ $q->subresource->nodecores }}" data-nodes-field="loan-nodes{{ $item->id }}" value="{{ $item->corecount }}" />
															@else
															<div class="row">
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="loan-nodes{{ $item->id }}">{{ trans('queues::queues.' . $unit) }}</label>
																		<input type="number" name="nodecount" class="form-control nodes" size="4" id="loan-nodes{{ $item->id }}" data-nodes="{{ $q->subresource->nodecores }}" data-cores-field="loan-cores{{ $item->id }}" value="{{ $nodecores ? round($item->corecount / $nodecores, 1) : $item->nodecount }}" step="0.5" />
																	</div>
																</div>
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="loan-cores{{ $item->id }}">{{ trans('queues::queues.cores') }}</label>
																		<input type="number" name="corecount" class="form-control cores" size="4" id="loan-cores{{ $item->id }}" data-cores="{{ $q->subresource->nodecores }}" data-nodes-field="loan-nodes{{ $item->id }}" value="{{ $item->corecount }}" />
																	</div>
																</div>
															</div>
															<input type="hidden" class="form-control serviceunits" size="4" id="loan-serviceunits{{ $item->id }}" name="serviceunits" value="{{ $item->serviceunits }}" step="0.25" />
															@endif

															<div class="row">
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="loan-datetimestart{{ $item->id }}">{{ trans('queues::queues.start') }}</label>
																		<input type="text" name="datetimestart" class="form-control datetime" id="loan-datetimestart{{ $item->id }}" name="datetimestart" value="{{ $item->datetimestart->toDateTimeString() }}" />
																	</div>
																</div>
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="loan-datetimestop{{ $item->id }}">{{ trans('queues::queues.end') }}</label>
																		@if ($item->type == 1)
																			<input type="text" name="datetimestop" class="form-control datetime" id="loan-datetimestop{{ $item->id }}" value="{{ $item->hasEnd() ? $item->datetimestop->toDateTimeString() : '' }}" placeholder="{{ trans('global.never') }}" />
																		@else
																			<input type="text" name="datetimestop" class="form-control datetime" id="sell-datetimestop{{ $item->id }}" disabled="disabled" placeholder="{{ trans('queues::queues.end of life') }}" value="" />
																		@endif
																	</div>
																</div>
															</div>

															<div class="form-group">
																<label for="loan-comment{{ $item->id }}">{{ trans('queues::queues.comment') }}</label>
																<textarea id="loan-comment{{ $item->id }}" name="comment" class="form-control" rows="3" cols="40">{{ $item->comment }}</textarea>
															</div>

															<div class="dialog-footer text-right">
																<input type="submit" class="btn btn-success queue-dialog-submit" value="{{ trans('global.button.update') }}" data-action="update" data-success="{{ trans('queues::queues.item updated') }}" />
															</div>

															<input type="hidden" name="id" value="{{ $item->id }}" />
															@csrf
															</div>
														</form>
													</div>
												</td>
											</tr>
											<?php
										endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>

							<div class="modl dialog" id="dialog-sell{{ $q->id }}" title="{{ trans('queues::queues.sell') }}">
								<form class="modl-content dialog-content" method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.queues.sizes.create') }}">
									<div class="modl-body dialog-body">
										@if ($unit == 'sus')
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="sell-serviceunits{{ $q->id }}">{{ trans('queues::queues.service units') }}</label>
													<input type="number" class="form-control serviceunits" size="4" id="sell-serviceunits{{ $q->id }}" name="serviceunits" value="0.00" step="0.25" />
												</div>
											</div>
										</div>
										<input type="hidden" class="form-control nodes" size="4" id="sell-nodes{{ $q->id }}" name="nodecount" data-nodes="{{ $q->subresource->nodecores }}" data-cores-field="sell-cores{{ $q->id }}" value="0" step="0.5" />
										<input type="hidden" class="form-control cores" size="4" id="sell-cores{{ $q->id }}" name="corecount" data-cores="{{ $q->subresource->nodecores }}" data-nodes-field="sell-nodes{{ $q->id }}" value="0" />
										@else
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="sell-nodes{{ $q->id }}">{{ trans('queues::queues.' . $unit) }}</label>
													<input type="number" class="form-control nodes" size="4" id="sell-nodes{{ $q->id }}" name="nodecount" data-nodes="{{ $q->subresource->nodecores }}" data-cores-field="sell-cores{{ $q->id }}" value="0" step="0.5" />
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="sell-cores{{ $q->id }}">{{ trans('queues::queues.cores') }}</label>
													<input type="number" class="form-control cores" size="4" id="sell-cores{{ $q->id }}" name="corecount" data-cores="{{ $q->subresource->nodecores }}" data-nodes-field="sell-nodes{{ $q->id }}" value="0" />
												</div>
											</div>
										</div>
										<input type="hidden" class="form-control serviceunits" size="4" id="sell-serviceunits{{ $q->id }}" name="serviceunits" value="0.00" step="0.25" />
										@endif

										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="sell-datetimestart{{ $q->id }}">{{ trans('queues::queues.start') }}</label>
													<span class="input-group input-datetime">
														<input type="text" class="form-control datetime" id="sell-datetimestart{{ $q->id }}" name="datetimestart" value="{{ Carbon\Carbon::now()->modify('+3 minutes')->toDateTimeString() }}" />
														<span class="input-group-append"><span class="input-group-text fa fa-calendar"></span></span>
													</span>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="sell-datetimestop{{ $q->id }}">{{ trans('queues::queues.end') }}</label>
													<span class="input-group input-datetime">
														<input type="text" class="form-control datetime" id="sell-datetimestop{{ $q->id }}" name="datetimestop" disabled="disabled" placeholder="{{ trans('queues::queues.end of life') }}" value="" />
														<span class="input-group-append"><span class="input-group-text fa fa-calendar"></span></span>
													</span>
												</div>
											</div>
										</div>

										<div class="form-group">
											<label for="seller-group{{ $q->id }}">{{ trans('queues::queues.seller') }} <span class="required">*</span></label>
											<select name="sellergroupid" id="seller-group{{ $q->id }}"
												class="form-control form-group-queues"
												data-update="seller-queue{{ $q->id }}"
												data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s"
												data-queue-api="{{ route('api.queues.index') }}"
												data-queueid="{{ $q->id }}"
												data-subresource="{{ $q->subresourceid }}">
												<option value="0">{{ trans('queues::queues.select group') }}</option>
												<?php
												$groups = array();
												$first = null;
												foreach ($q->subresource->queues as $queue)
												{
													if (isset($groups[$queue->groupid]))// || $queue->groupid == $q->groupid)
													{
														continue;
													}

													if ($queue->groupid < 0 && !$first)
													{
														$first = App\Modules\Groups\Models\Group::find(1);
														$first->id = -1;
													}

													if (!$queue->group)
													{
														continue;
													}

													$groups[$queue->groupid] = $queue->group;
												}
												$groups = collect($groups)->sortBy('name');
												if ($first)
												{
													$groups->prepend($first);
												}
												?>
												@foreach ($groups as $grp)
													<option value="{{ $grp->id }}"<?php if ($grp->id == '-1') { echo ' selected="selected"'; } ?>>{{ $grp->name }}</option>
												@endforeach
											</select>
										</div>

										<div class="form-group">
											<label for="seller-queue{{ $q->id }}">{{ trans('queues::queues.queue') }} <span class="required">*</span></label>
											<select id="seller-queue{{ $q->id }}" name="sellerqueueid" class="form-control" required>
												<option value="0">{{ trans('queues::queues.select queue') }}</option>
												@foreach ($groups as $grp)
													@if ($grp->id == -1)
														@foreach ($grp->queues()->where('subresourceid', '=', $q->subresourceid)->get() as $i => $queue)
															<option value="{{ $queue->id }}"<?php if ($i == 0) { echo ' selected="selected"'; } ?>>{{ $queue->name }} ({{ $q->subresource->name }})</option>
														@endforeach
													@endif
												@endforeach
											</select>
											<span class="invalid-feedback">{{ trans('queues::queues.error.invalid queue') }}</span>
										</div>

										<div class="form-group">
											<label for="sell-group{{ $q->id }}">{{ trans('queues::queues.sell to') }} <span class="required">*</span></label>
											<select name="groupid" id="sell-group{{ $q->id }}"
												class="form-control form-group-queues"
												data-update="sell-queue{{ $q->id }}"
												data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s"
												data-queue-api="{{ route('api.queues.index') }}"
												data-queueid="{{ $q->id }}"
												data-subresource="{{ $q->subresourceid }}">
												<option value="0">{{ trans('queues::queues.select group') }}</option>
												@foreach ($groups as $grp)
													<option value="{{ $grp->id }}"<?php if ($grp->id == $q->groupid) { echo ' selected="selected"'; } ?>>{{ $grp->name }}</option>
												@endforeach
											</select>
										</div>

										<div class="form-group">
											<label for="sell-queue{{ $q->id }}">{{ trans('queues::queues.queue') }} <span class="required">*</span></label>
											<select id="sell-queue{{ $q->id }}" name="queueid" class="form-control" required>
												<option value="0">{{ trans('queues::queues.select queue') }}</option>
												@foreach ($groups as $grp)
													@if ($grp->id == $q->groupid)
														@foreach ($grp->queues()->where('subresourceid', '=', $q->subresourceid)->get() as $queue)
															<option value="{{ $queue->id }}"<?php if ($q->id == $queue->id) { echo ' selected="selected"'; } ?>>{{ $queue->name }} ({{ $q->subresource->name }})</option>
														@endforeach
													@endif
												@endforeach
											</select>
											<span class="invalid-feedback">{{ trans('queues::queues.error.invalid queue') }}</span>
										</div>

										<div class="form-group">
											<label for="sell-comment{{ $q->id }}">{{ trans('queues::queues.comment') }}</label>
											<textarea id="sell-comment{{ $q->id }}" name="comment" class="form-control" cols="35" rows="2"></textarea>
										</div>
									</div>

									<div class="modl-footer dialog-footer text-right">
										<input type="submit" class="btn btn-success queue-dialog-submit" value="{{ trans('global.button.create') }}" data-success="{{ trans('queues::queues.item created') }}" />
									</div>

									@csrf
								</form>
							</div>

							<div class="modl dialog" id="dialog-loan{{ $q->id }}" title="{{ trans('queues::queues.loan') }}">
								<form class="modl-content dialog-content" method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.queues.loans.create') }}">
									<div class="modl-body dialog-body">
										@if ($unit == 'sus')
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="loan-serviceunits{{ $q->id }}">{{ trans('queues::queues.service units') }}</label>
													<input type="number" name="serviceunits" class="form-control serviceunits" size="4" id="loan-serviceunits{{ $q->id }}" value="0.00" step="0.25" />
												</div>
											</div>
										</div>
										<input type="hidden" name="nodecount" class="form-control nodes" size="4" id="loan-nodes{{ $q->id }}" data-nodes="{{ $q->subresource->nodecores }}" data-cores-field="loan-cores{{ $q->id }}" value="0" step="0.5" />
										<input type="hidden" name="corecount" class="form-control cores" size="4" id="loan-cores{{ $q->id }}" data-cores="{{ $q->subresource->nodecores }}" data-nodes-field="loan-nodes{{ $q->id }}" value="0" />
										@else
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="loan-nodes{{ $q->id }}">{{ trans('queues::queues.' . $unit) }}</label>
													<input type="number" name="nodecount" class="form-control nodes" size="4" id="loan-nodes{{ $q->id }}" data-nodes="{{ $q->subresource->nodecores }}" data-cores-field="loan-cores{{ $q->id }}" value="0" step="0.5" />
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="loan-cores{{ $q->id }}">{{ trans('queues::queues.cores') }}</label>
													<input type="number" name="corecount" class="form-control cores" size="4" id="loan-cores{{ $q->id }}" data-cores="{{ $q->subresource->nodecores }}" data-nodes-field="loan-nodes{{ $q->id }}" value="0" />
												</div>
											</div>
										</div>
										<input type="hidden" name="serviceunits" class="form-control serviceunits" size="4" id="loan-serviceunits{{ $q->id }}" value="0.00" step="0.25" />
										@endif

										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="loan-datetimestart{{ $q->id }}">{{ trans('queues::queues.start') }}</label>
													<span class="input-group input-datetime">
														<input type="text" name="datetimestart" class="form-control datetime" id="loan-datetimestart{{ $q->id }}" value="{{ Carbon\Carbon::now()->modify('+10 minutes')->toDateTimeString() }}" />
														<span class="input-group-append"><span class="input-group-text fa fa-calendar"></span></span>
													</span>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="loan-datetimestop{{ $q->id }}">{{ trans('queues::queues.end') }}</label>
													<span class="input-group input-datetime">
														<input type="text" name="datetimestop" class="form-control datetime" id="loan-datetimestop{{ $q->id }}" value="" placeholder="{{ trans('global.never') }}" />
														<span class="input-group-append"><span class="input-group-text fa fa-calendar"></span></span>
													</span>
												</div>
											</div>
										</div>

										<div class="form-group">
											<label for="loan-group{{ $q->id }}">{{ trans('queues::queues.lender') }} <span class="required">*</span></label>
											<select name="lendergroupid" id="lender-group{{ $q->id }}"
												class="form-control form-group-queues"
												data-update="lender-queue{{ $q->id }}"
												data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s"
												data-queue-api="{{ route('api.queues.index') }}"
												data-queueid="{{ $q->id }}"
												data-subresource="{{ $q->subresourceid }}">
												<option value="0">{{ trans('queues::queues.select group') }}</option>
												@foreach ($groups as $grp)
													<option value="{{ $grp->id }}"<?php if ($grp->id == -1) { echo ' selected="selected"'; } ?>>{{ $grp->name }}</option>
												@endforeach
											</select>
										</div>

										<div class="form-group">
											<label for="lender-queue{{ $q->id }}">{{ trans('queues::queues.queue') }} <span class="required">*</span></label>
											<select id="lender-queue{{ $q->id }}" name="lenderqueueid" class="form-control">
												<option value="0">{{ trans('queues::queues.select queue') }}</option>
												@foreach ($groups as $grp)
													@if ($grp->id == -1)
														@foreach ($grp->queues()->where('subresourceid', '=', $q->subresourceid)->get() as $i => $queue)
															<option value="{{ $queue->id }}"<?php if ($i == 0) { echo ' selected="selected"'; } ?>>{{ $queue->name }} ({{ $q->subresource->name }})</option>
														@endforeach
													@endif
												@endforeach
											</select>
										</div>

										<div class="form-group">
											<label for="loan-group{{ $q->id }}">{{ trans('queues::queues.loan to') }} <span class="required">*</span></label>
											<select name="groupid" id="loan-group{{ $q->id }}"
												class="form-control form-group-queues"
												data-update="loan-queue{{ $q->id }}"
												data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s"
												data-queue-api="{{ route('api.queues.index') }}"
												data-queueid="{{ $q->id }}"
												data-subresource="{{ $q->subresourceid }}">
												<option value="0">{{ trans('queues::queues.select group') }}</option>
												@foreach ($groups as $grp)
													<option value="{{ $grp->id }}"<?php if ($grp->id == $q->groupid) { echo ' selected="selected"'; } ?>>{{ $grp->name }}</option>
												@endforeach
											</select>
										</div>

										<div class="form-group">
											<label for="loan-queue{{ $q->id }}">{{ trans('queues::queues.queue') }} <span class="required">*</span></label>
											<select id="loan-queue{{ $q->id }}" name="queueid" class="form-control">
												<option value="0">{{ trans('queues::queues.select queue') }}</option>
												@foreach ($groups as $group)
													@if ($group->id == $q->groupid)
														@foreach ($group->queues()->where('subresourceid', '=', $q->subresourceid)->get() as $queue)
															<option value="{{ $queue->id }}"<?php if ($q->id == $queue->id) { echo ' selected="selected"'; } ?>>{{ $queue->name }} ({{ $q->subresource->name }})</option>
														@endforeach
													@endif
												@endforeach
											</select>
										</div>

										<div class="form-group">
											<label for="loan-comment{{ $q->id }}">{{ trans('queues::queues.comment') }}</label>
											<textarea id="loan-comment{{ $q->id }}" name="comment" class="form-control" rows="2" cols="40"></textarea>
										</div>
									</div>

									<div class="modl-footer dialog-footer text-right">
										<input type="submit" class="btn btn-success queue-dialog-submit" value="{{ trans('global.button.create') }}" data-success="{{ trans('queues::queues.item created') }}" />
									</div>

									@csrf
								</form>
							</div>
						</td>
					</tr>
				@endif
			@endforeach
		@else
			<tr>
				<td colspan="{{ auth()->user()->can('edit.state queues') ? 7 : 6 }}" class="text-center">
					<span class="none">No queues found.</span>
				</td>
			</tr>
		@endif
	</tbody>
</table>
