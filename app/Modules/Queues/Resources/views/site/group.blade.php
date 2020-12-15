
							<table class="table table-hover">
								<caption class="sr-only">Below is a list of all queues:</caption>
								<thead class="resource">
									<tr>
										<th scope="col">{{ trans('queues::queues.resource') }}</th>
										<th scope="col">{{ trans('queues::queues.name') }}</th>
										<th scope="col" class="text-right">{{ trans('queues::queues.cores') }}</th>
										<th scope="col" class="text-right">{{ trans('queues::queues.nodes') }}</th>
										<th scope="col" class="text-right">{{ trans('queues::queues.walltime') }}</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$canManage = auth()->user()->can('edit groups') || (auth()->user()->can('edit.own groups') && $group->ownerid == auth()->user()->id);

									$queues = $group->queues;

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
												<?php
												$title = '';
												if ($q->subresource->nodecores)
												{
													$title .= $q->subresource->nodecores . ' cores, ';
												}
												else
												{
													$title .= '-- cores, ';
												}

												if ($q->subresource->nodemem)
												{
													$title .= $q->subresource->nodemem . ' memory';
												}
												else
												{
													$title .= '-- memory';
												}
												?>
												<td title="{{ $title }}">
													{{ $q->subresource->name }}
												</td>
												<td>
													@if (auth()->user()->can('manage queues'))
														<a href="{{ route('admin.queues.edit', ['id' => $q->id]) }}">{{ $q->name }}</a>
													@else
														{{ $q->name }}
													@endif
												</td>
												<?php
												/*$title = '';
												if (count($q->loans) > 0)
												{
													foreach ($q->loans as $loan)
													{
														if (strtotime($loan->start) <= time())
														{
															$lender = $loan->lender;

															if ($loan->corecount < 0)
															{
																$title .= abs($loan->corecount) . ' cores to ';
															}
															else
															{
																$title .= $loan->corecount . ' cores from ';
															}

															if ($lender)
															{
																$title .= $lender->name . ', ';
															}
														}
													}
												}
												$title = rtrim($title, ', ');*/
												?>
												<td class="text-right">
													{{ $q->totalcores }}
												</td>
												<td class="text-right">
													@if ($q->subresource->nodecores > 0)
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
											</tr>
										<?php } ?>
									<?php } else { ?>
										<tr>
											<td colspan="6" class="text-center">(No queues found)</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
