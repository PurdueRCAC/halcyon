
@push('scripts')
<script src="{{ asset('modules/queues/js/site.js?v=' . filemtime(public_path() . '/modules/queues/js/site.js')) }}"></script>
<script>
/*
jQuery(document).ready(function($){
	$('#queues').accordion({
		heightStyle: 'content',
		collapsible: true,
		active: false
	});
	$('#queues .stop-propagation').on('click', function(e) {
		e.stopPropagation();
	});
});
*/
</script>
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
?>

@if (auth()->user()->can('create queue'))
<p class="text-right">
	<a href="#dialog-new" id="queue-new" class="btn btn-primary dialog-btn">{{ trans('queues::queues.new queue') }}</a>
</p>
<div class="dialog" id="dialog-new" title="{{ trans('global.create') }}">
	<form method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.queues.create') }}">

		<div class="dialog-footer text-right">
			<input type="submit" class="btn btn-success dialog-submit" value="{{ trans('global.button.save') }}" data-action="create" data-success="{{ trans('queues::queues.item created') }}" />
		</div>

		<input type="hidden" name="groupid" value="{{ $group->id }}" />
		@csrf
	</form>
</div>
@endif

<div class="d-flex flex-row">
	<div class="flex-fill text-center">{{ trans('queues::queues.state') }}</div>
	<div class="flex-fill">{{ trans('queues::queues.resource') }}</div>
	<div class="flex-fill">{{ trans('queues::queues.name') }}</div>
	<div class="flex-fill text-right">{{ trans('queues::queues.cores') }}</div>
	<div class="flex-fill text-right">{{ trans('queues::queues.nodes') }}</div>
	<div class="flex-fill text-right">{{ trans('queues::queues.walltime') }}</div>
	@if (auth()->user()->can('edit.state queues'))
	<div class="flex-fill">{{ trans('queues::queues.options') }}</div>
	@endif
	@if (auth()->user()->can('delete queues'))
	<div class="flex-fill"></div>
	@endif
</div>
<div id="queues" class="panel-table">
<?php
if (count($queues) > 0)
		{
			foreach ($queues as $q)
			{
				if (!$canManage && !$q->users()->where('userid', '=', auth()->user()->id)->count())
				{
					continue;
				}
				?>
		<div class="card">
			<div class="card-header">
				<div class="d-flex flex-row">
					<div class="flex-fill text-center">
						@if ($q->enabled && $q->started && $q->active)
							@if ($q->reservation)
								<span class="text-info tip" title="{{ trans('Queue has dedicated reservation.') }}">
									<span class="fa fa-circle" aria-hidden="true"></span><span class="sr-only">{{ trans('Queue has dedicated reservation.') }}</span>
								</span>
							@else
								<span class="text-success tip" title="{{ trans('Queue is running.') }}">
									<span class="fa fa-check" aria-hidden="true"></span><span class="sr-only">{{ trans('Queue is running.') }}</span>
								</span>
							@endif
						@elseif ($q->active)
							<span class="text-danger tip" title="{{ trans('Queue is stopped or disabled.') }}">
								<span class="fa fa-minus-circle" aria-hidden="true"></span><span class="sr-only">{{ trans('Queue is stopped or disabled.') }}</span>
							</span>
						@elseif (!$q->active)
							<span class="text-warning tip" title="{{ trans('Queue has no active resources. Remove queue or sell/loan nodes.') }}">
								<span class="fa fa-exclamation-triangle" aria-hidden="true"></span><span class="sr-only">{{ trans('Queue has no active resources. Remove queue or sell/loan nodes.') }}</span>
							</span>
						@endif
					</div>
					<div class="td flex-fill">
						{{ $q->subresource ? $q->subresource->name : '' }}
					</div>
					<div class="td flex-fill">
						@if (auth()->user()->can('manage queues'))
							<a href="{{ route('admin.queues.edit', ['id' => $q->id]) }}">
								{{ $q->name }}
							</a>
						@else
							{{ $q->name }}
						@endif
					</div>
				@if (!$q->active && $upcoming = $q->getUpcomingLoanOrPurchase())
					<div class="td flex-fill text-right">
						<span class="text-success">{{ $upcoming->type ? 'loan' : 'purchase' }} starts {{ $upcoming->datetimestart->diffForHumans() }}</span>
					</div>
				@else
					<div class="td flex-fill text-right">
						{{ $q->totalcores }}
					</div>
					<div class="td flex-fill text-right">
						@if ($q->subresource && $q->subresource->nodecores > 0)
							{{ round($q->totalcores/$q->subresource->nodecores, 1) }}
						@endif
					</div>
				@endif
					<div class="td flex-fill text-right">
						<?php
						if (count($q->walltimes) > 0)
						{
							$walltime = $q->walltimes->first()->walltime;
							$unit = '';
							if ($walltime < 60)
							{
								$unit = 'sec';
							}
							elseif ($walltime < 3600)
							{
								$walltime /= 60;
								$unit = 'min';
							}
							elseif ($walltime < 86400)
							{
								$walltime /= 3600;
								$unit = 'hrs';
							}
							else
							{
								$walltime /= 86400;
								$unit = 'days';
							}
							echo $walltime . ' ' . $unit;
						}
						?>
					</div>
					@if (auth()->user()->can('manage queues'))
					<div class="td flex-fill text-right">
						<a data-toggle="collapse" data-parent="#queues" href="#collapse{{ $q->id }}">
							<span class="fa fa-cog" aria-hidden="true"></span><span class="sr-only">Options</span>
						</a>
					</div>
					@endif
				</div>
			</div>
			<div class="card-body collapse" id="collapse{{ $q->id }}">
			<div class="row">
				@if (auth()->user()->can('edit.state queues'))
					<div class="col-md-6">
						@if ($q->enabled)
							<a class="set-queue-status btn btn-enable"
								href="{{ route('admin.queues.disable', ['id' => $q->id]) }}"
								data-api="{{ route('api.queues.update', ['id' => $q->id]) }}"
								data-queue="{{ $q->id }}"
								data-status="0"
								title="{{ trans('queues::queues.stop scheduling') }}">
								<span class="fa fa-ban" aria-hidden="true"></span><span class="sronly"> {{ trans('queues::queues.stop scheduling') }}</span>
							</a>
						@else
							<a class="set-queue-status btn btn-disable"
								href="{{ route('admin.queues.enable', ['id' => $q->id]) }}"
								data-api="{{ route('api.queues.update', ['id' => $q->id]) }}"
								data-queue="{{ $q->id }}"
								data-status="1"
								title="{{ trans('queues::queues.start scheduling') }}">
								<span class="fa fa-check" aria-hidden="true"></span><span class="sronly"> {{ trans('queues::queues.start scheduling') }}</span>
							</a>
						@endif

						<a href="#dialog-sell" id="node-sell" class="btn btn-secondary dialog-btn icon-dollar-sign">{{ trans('queues::queues.sell nodes') }}</a>
						<a href="#dialog-loan" id="node-loan" class="btn btn-secondary dialog-btn icon-shuffle">{{ trans('queues::queues.loan nodes') }}</a>
					</div>
					@endif
					@if (auth()->user()->can('delete queues'))
					<div class="col-md-6 text-right">
						<a class="delete-queue btn text-danger tip"
							href="{{ route('admin.queues.delete', ['id' => $q->id]) }}"
							data-api="{{ route('api.queues.delete', ['id' => $q->id]) }}"
							data-queue="{{ $q->id }}"
							data-confirm="Are you sure you want to delete this queue?"
							title="{{ trans('global.button.delete') }}">
							<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only"> {{ trans('global.button.delete') }}</span>
						</a>
					</div>
					@endif
				</div>

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
							<th scope="col" class="text-right">{{ trans('queues::queues.nodes') }}</th>
							<th scope="col" class="text-right">{{ trans('queues::queues.total') }}</th>
							<th scope="col" class="text-right" colspan="2">{{ trans('queues::queues.options') }}</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$purchases = $q->sizes;
						//$sold  = $row->sold;
						$loans = $q->loans;
						$nodecores = $q->subresource->nodecores;
						$total = 0;

						$items = $purchases;//$purchases->merge($sold);
						$items = $items->merge($loans)->sortBy('datetimestart');

						foreach ($items as $item)
						{
							if ($item->hasEnded())
							{
								$item->total = $total;
								continue;
							}

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
							$total += $nodecores ? round($item->corecount / $nodecores, 1) : 0;

							$item->total = $total;
						}

						$items = $items->sortByDesc('datetimestart')->slice(0, 20);

						foreach ($items as $item): ?>
						<tr<?php if ($item->hasEnd() && $item->hasEnded()) { echo ' class="trashed"'; } ?>>
							<td>
								@if ($item->hasStart())
									<time datetime="{{ $item->datetimestop }}">{{ $item->datetimestart->format('Y-m-d') }}</time>
								@endif
							</td>
							<td>
								@if ($item->hasEnd())
									<time datetime="{{ $item->datetimestop }}">{{ $item->datetimestop->format('Y-m-d') }}</time>
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
										$cls = 'text-danger';
									}
									else if ($item->corecount >= 0)
									{
										$what .= " from";
										$cls = 'text-success';
									}
								}
								else
								{
									if ($item->sellerqueueid == $q->id || $item->corecount < 0)
									{
										$what = 'Sale to';
										$cls = 'text-danger';
									}
									else if ($item->corecount >= 0)
									{
										$what = 'Purchase from';
										$cls = 'text-success';
									}
								}

								//$title  = $item->nodecount . " nodes / ";
								//$title .= $item->corecount . " cores; ".$what.": ";
								$amt = $nodecores ? round($item->corecount / $nodecores, 1) : 0;

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
											<div class="col-md-6">
												<div class="form-group">
													<label for="loan-nodes{{ $item->id }}">{{ trans('queues::queues.nodes') }}</label>
													<input type="number" name="nodecount" class="form-control nodes" size="4" id="loan-nodes{{ $item->id }}" name="nodes" data-nodes="{{ $q->subresource->nodecores }}" data-cores-field="loan-cores{{ $item->id }}" value="{{ $amt }}" />
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="loan-cores{{ $item->id }}">{{ trans('queues::queues.cores') }}</label>
													<input type="number" name="corecount" class="form-control cores" size="4" id="loan-cores{{ $item->id }}" name="cores" data-cores="{{ $q->subresource->nodecores }}" data-nodes-field="loan-nodes{{ $item->id }}" value="{{ $item->corecount }}" />
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
				<?php
			}
		}
?>
</div>
