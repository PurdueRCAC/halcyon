
							<table class="table table-hover">
								<caption class="sr-only">Below is a list of all queues:</caption>
								<thead>
									<tr>
										<th scope="col" class="text-center">{{ trans('queues::queues.state') }}</th>
										<th scope="col">{{ trans('queues::queues.resource') }}</th>
										<th scope="col">{{ trans('queues::queues.name') }}</th>
										<th scope="col" class="text-right">{{ trans('queues::queues.cores') }}</th>
										<th scope="col" class="text-right">{{ trans('queues::queues.nodes') }}</th>
										<th scope="col" class="text-right">{{ trans('queues::queues.walltime') }}</th>
										@if (auth()->user()->can('edit.state queues'))
										<th scope="col">{{ trans('queues::queues.options') }}</th>
										@endif
									</tr>
								</thead>
								<tbody>
									<?php
									$canManage = auth()->user()->can('edit groups') || (auth()->user()->can('edit.own groups') && $group->ownerid == auth()->user()->id);

									$q = (new App\Modules\Queues\Models\Queue)->getTable();
									$s = (new App\Modules\Queues\Models\Scheduler)->getTable();
									$r = (new App\Modules\Resources\Entities\Subresource)->getTable();

									$queues = $group->queues()
										->select($q . '.*')
										->join($s, $s . '.id', $q . '.schedulerid')
										->join($r, $r . '.id', $q . '.subresourceid')
										->where(function($where) use ($s)
										{
											$where->whereNull($s . '.datetimeremoved')
												->orWhere($s . '.datetimeremoved', '=', '0000-00-00 00:00:00');
										})
										->where(function($where) use ($r)
										{
											$where->whereNull($r . '.datetimeremoved')
												->orWhere($r . '.datetimeremoved', '=', '0000-00-00 00:00:00');
										})
										->withTrashed()
										->whereIsActive()
										->orderBy($r . '.name', 'asc')
										->orderBy($q . '.name', 'asc')
										->get();

									if (count($queues) > 0)
									{
										foreach ($queues as $q)
										{
											if (!$canManage && !$q->users()->where('userid', '=', auth()->user()->id)->count())
											{
												continue;
											}
											?>
											<tr>
												<td class="text-center">
													@if ($q->enabled && $q->started && $q->active)
														@if ($q->reservation)
															<span class="text-info tip" title="{{ trans('Queue has dedicated reservation.') }}">
																<i class="fa fa-circle" aria-hidden="true"></i><span class="sr-only">{{ trans('Queue has dedicated reservation.') }}</span>
															</span>
														@else
															<span class="text-success tip" title="{{ trans('Queue is running.') }}">
																<i class="fa fa-check" aria-hidden="true"></i><span class="sr-only">{{ trans('Queue is running.') }}</span>
															</span>
														@endif
													@elseif ($q->active)
														<span class="text-danger tip" title="{{ trans('Queue is stopped or disabled.') }}">
															<i class="fa fa-minus-circle" aria-hidden="true"></i><span class="sr-only">{{ trans('Queue is stopped or disabled.') }}</span>
														</span>
													@elseif (!$q->active)
														<span class="text-warning tip" title="{{ trans('Queue has no active resources. Remove queue or sell/loan nodes.') }}">
															<i class="fa fa-exclamation-triangle" aria-hidden="true"></i><span class="sr-only">{{ trans('Queue has no active resources. Remove queue or sell/loan nodes.') }}</span>
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
												<td class="text-right">
													{{ $q->totalcores }}
												</td>
												<td class="text-right">
													@if ($q->subresource && $q->subresource->nodecores > 0)
														{{ round($q->totalcores/$q->subresource->nodecores, 1) }}
													@endif
												</td>
												<td class="text-right">
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
												</td>
												@if (auth()->user()->can('edit.state queues'))
												<td>
													@if ($q->enabled)
														<a class="queue-enable tip" href="{{ route('admin.queues.disable', ['id' => $q->id]) }}" data-api="{{ route('api.queues.update', ['id' => $q->id]) }}" title="{{ trans('queues::queues.disable scheduling') }}">
															<i class="fa fa-ban" aria-hidden="true"></i> {{ trans('queues::queues.disable scheduling') }}
														</a>
													@else
														<a class="queue-disable tip" href="{{ route('admin.queues.enable', ['id' => $q->id]) }}" data-api="{{ route('api.queues.update', ['id' => $q->id]) }}" title="{{ trans('queues::queues.enable scheduling') }}">
															<i class="fa fa-check" aria-hidden="true"></i> {{ trans('queues::queues.enable scheduling') }}
														</a>
													@endif
												</td>
												@endif
											</tr>
										<?php } ?>
									<?php } else { ?>
										<tr>
											<td colspan="{{ auth()->user()->can('edit.state queues') ? 7 : 6 }}" class="text-center">
												<span class="none">No queues found.</span>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
