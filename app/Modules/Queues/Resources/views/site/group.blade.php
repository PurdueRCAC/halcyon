
@push('scripts')
<script src="{{ asset('modules/queues/js/site.js?v=' . filemtime(public_path() . '/modules/queues/js/site.js')) }}"></script>
@endpush

<?php
$canManage = auth()->user()->can('edit groups') || (auth()->user()->can('edit.own groups') && $group->ownerid == auth()->user()->id);

$q = (new App\Modules\Queues\Models\Queue)->getTable();
$s = (new App\Modules\Queues\Models\Scheduler)->getTable();
$r = (new App\Modules\Resources\Models\Subresource)->getTable();

$queues = $group->queues()
	->select($q . '.*')
	->join($s, $s . '.id', $q . '.schedulerid')
	->join($r, $r . '.id', $q . '.subresourceid')
	->whereNull($s . '.datetimeremoved')
	->whereNull($r . '.datetimeremoved')
	->orderBy($r . '.name', 'asc')
	->orderBy($q . '.name', 'asc')
	->get();

$queues = $queues->reject(function($q) use ($canManage)
{
	// The user is not a manager and is not a member of the queue
	return (!$canManage && !$q->users()->where('userid', '=', auth()->user()->id)->count());
});
?>
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
					@if ($q->serviceunits > 0)
						<td class="text-right" colspan="2">
							{{ $q->serviceunits }} <span class="text-muted">SUs</span>
						</div>
					@else
						<td class="text-right">
							{{ $q->totalcores }} <span class="text-muted">{{ strtolower(trans('queues::queues.cores')) }}</span>
						</td>
						<td class="text-right">
							@if ($q->subresource && $q->subresource->nodecores > 0)
								{{ round($q->totalcores/$q->subresource->nodecores, 1) }} <span class="text-muted">{{ strtolower(trans('queues::queues.nodes')) }}</span>
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
						<td colspan="9">

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
															in <time datetime="{{ $item->datetimestart }}">{{ $item->willStart() }}</time>
														@else
															<time datetime="{{ $item->datetimestart }}">{{ $item->datetimestart->format('Y-m-d') }}</time>
														@endif
													@else
														<span class="never">{{ trans('global.immediately') }}</span>
													@endif
												</td>
												<td>
													@if ($item->hasEnd())
														@if (!$item->hasEnded())
															<span class="fa fa-exclamation-triangle text-warning" aria-hidden="true"></span>
															in <time datetime="{{ $item->datetimestop }}">{{ $item->willEnd() }}</time>
														@else
															<time datetime="{{ $item->datetimestop }}">{{ $item->datetimestop->format('Y-m-d') }}</time>
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
													<span class="{{ $cls }}">{{ ($cls == 'text-success' ? '+' : '-') }} {{ abs($amt) }}</span>
												</td>
												<td class="text-right">
													{{ $item->total }}
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

													<div class="dialog" id="dialog-edit{{ $item->id }}" title="{{ trans('queues::queues.edit ' . ($item->type == 1 ? 'loan' : 'size')) }} #{{ $item->id }}">
														<form method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.queues.' . ($item->type == 1 ? 'loans' : 'sizes') . '.update', ['id' => $item->id]) }}">
															<div class="row">
																<div class="col-md-4">
																	<div class="form-group">
																		<label for="loan-nodes{{ $item->id }}">{{ trans('queues::queues.nodes') }}</label>
																		<input type="number" name="nodecount" class="form-control nodes" size="4" id="loan-nodes{{ $item->id }}" data-nodes="{{ $q->subresource->nodecores }}" data-cores-field="loan-cores{{ $item->id }}" value="{{ $nodecores ? round($item->corecount / $nodecores, 1) : $item->nodecount }}" step="0.5" />
																	</div>
																</div>
																<div class="col-md-4">
																	<div class="form-group">
																		<label for="loan-cores{{ $item->id }}">{{ trans('queues::queues.cores') }}</label>
																		<input type="number" name="corecount" class="form-control cores" size="4" id="loan-cores{{ $item->id }}" data-cores="{{ $q->subresource->nodecores }}" data-nodes-field="loan-nodes{{ $item->id }}" value="{{ $item->corecount }}" />
																	</div>
																</div>
																<div class="col-md-4">
																	<div class="form-group">
																		<label for="loan-serviceunits{{ $item->id }}">{{ trans('queues::queues.service units') }}</label>
																		<input type="number" class="form-control serviceunits" size="4" id="loan-serviceunits{{ $item->id }}" name="serviceunits" value="{{ $item->serviceunits }}" step="0.25" />
																	</div>
																</div>
															</div>

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
																<input type="submit" class="btn btn-success dialog-submit" value="{{ trans('global.button.update') }}" data-action="update" data-success="{{ trans('queues::queues.item updated') }}" />
															</div>

															<input type="hidden" name="id" value="{{ $item->id }}" />
															@csrf
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

							<div class="modal dialog" id="dialog-sell{{ $q->id }}" title="{{ trans('queues::queues.sell') }}">
								<form class="modl-content dialog-content" method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.queues.sizes.create') }}">
									<div class="modl-body dialog-body">
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="sell-nodes{{ $q->id }}">{{ trans('queues::queues.nodes') }}</label>
													<input type="number" class="form-control nodes" size="4" id="sell-nodes{{ $q->id }}" name="nodecount" data-nodes="{{ $q->subresource->nodecores }}" data-cores-field="sell-cores{{ $q->id }}" value="0" step="0.5" />
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="sell-cores{{ $q->id }}">{{ trans('queues::queues.cores') }}</label>
													<input type="number" class="form-control cores" size="4" id="sell-cores{{ $q->id }}" name="corecount" data-cores="{{ $q->subresource->nodecores }}" data-nodes-field="sell-nodes{{ $q->id }}" value="0" />
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="sell-serviceunits{{ $q->id }}">{{ trans('queues::queues.service units') }}</label>
													<input type="number" class="form-control serviceunits" size="4" id="sell-serviceunits{{ $q->id }}" name="serviceunits" value="0.00" step="0.25" />
												</div>
											</div>
										</div>

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
												@foreach ($groups as $group)
													<option value="{{ $group->id }}"<?php if ($group->id == '-1') { echo ' selected="selected"'; } ?>>{{ $group->name }}</option>
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
										<input type="submit" class="btn btn-success dialog-submit" value="{{ trans('global.button.create') }}" data-success="{{ trans('queues::queues.item created') }}" />
									</div>

									@csrf
								</form>
							</div>

							<div class="modl dialog" id="dialog-loan{{ $q->id }}" title="{{ trans('queues::queues.loan') }}">
								<form class="modl-content dialog-content" method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.queues.loans.create') }}">
									<div class="modl-body dialog-body">
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="loan-nodes{{ $q->id }}">{{ trans('queues::queues.nodes') }}</label>
													<input type="number" name="nodecount" class="form-control nodes" size="4" id="loan-nodes{{ $q->id }}" data-nodes="{{ $q->subresource->nodecores }}" data-cores-field="loan-cores{{ $q->id }}" value="0" step="0.5" />
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="loan-cores{{ $q->id }}">{{ trans('queues::queues.cores') }}</label>
													<input type="number" name="corecount" class="form-control cores" size="4" id="loan-cores{{ $q->id }}" data-cores="{{ $q->subresource->nodecores }}" data-nodes-field="loan-nodes{{ $q->id }}" value="0" />
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="loan-serviceunits{{ $q->id }}">{{ trans('queues::queues.service units') }}</label>
													<input type="number" name="serviceunits" class="form-control serviceunits" size="4" id="loan-serviceunits{{ $q->id }}" value="0.00" step="0.25" />
												</div>
											</div>
										</div>

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
													<option value="{{ $group->id }}"<?php if ($grp->id == -1) { echo ' selected="selected"'; } ?>>{{ $grp->name }}</option>
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
										<input type="submit" class="btn btn-success dialog-submit" value="{{ trans('global.button.create') }}" data-success="{{ trans('queues::queues.item created') }}" />
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
