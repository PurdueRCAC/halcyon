
					<p>Any actions taken by managers of this group are listed below. There may be a short delay in actions showing up in the log.</p>

							<?php
							// Get manager adds
							$l = App\Modules\History\Models\Log::query()
								->where('groupid', '=', $group->id)
								//->where('app', '=', 'ws')
								->whereIn('classname', ['groupowner', 'groupviewer', 'queuemember', 'groupqueuemember', 'unixgroupmember', 'unixgroup', 'userrequest'])
								->where('classmethod', '!=', 'read')
								//->where('datetime', '>', Carbon\Carbon::now()->modify('-1 month')->toDateTimeString())
								->orderBy('datetime', 'desc')
								->limit(20)
								->paginate();

							if (count($l))
							{
								?>
								<table class="table table-hover history">
									<caption class="sr-only">Group history</caption>
									<thead>
										<tr>
											<th scope="col">Date</th>
											<th scope="col">Time</th>
											<th scope="col">Manager</th>
											<th scope="col">User</th>
											<th scope="col">Action Taken</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ($l as $log)
										{
											switch ($log->classname)
											{
												case 'groupowner':
													if ($log->classmethod == 'create')
													{
														$log->action = 'Promoted to manager';
													}

													if ($log->classmethod == 'delete')
													{
														$log->action = 'Demoted as manager';
													}
												break;

												case 'groupviewer':
													if ($log->classmethod == 'create')
													{
														$log->action = 'Promoted to group usage viewer';
													}

													if ($log->classmethod == 'delete')
													{
														$log->action = 'Demoted as group usage viewer';
													}
												break;

												case 'queuemember':
												case 'groupqueuemember':
													$queue = App\Modules\Queues\Models\Queue::find($log->targetobjectid);
													if ($log->classmethod == 'create')
													{
														$log->action = 'Added to queue ' . ($queue ? $queue->name : trans('global.unknown')) . ' (' . ($queue ? $queue->subresource->name : trans('global.unknown')) . ')';
													}

													if ($log->classmethod == 'delete')
													{
														$log->action = 'Removed from queue ' . ($queue ? $queue->name : trans('global.unknown')) . ' (' . ($queue ? $queue->subresource->name : trans('global.unknown')) . ')';
													}
												break;

												case 'unixgroupmember':
													$g = App\Modules\Groups\Models\UnixGroup::find($log->targetobjectid);
													$groupname = '#' . $log->targetobjectid;
													if ($g)
													{
														$groupname = $g->longname;
													}

													if ($log->classmethod == 'create')
													{
														$log->action = 'Added to Unix group ' . $groupname;
													}

													if ($log->classmethod == 'delete')
													{
														$log->action = 'Removed from Unix group ' . $groupname;
													}
												break;

												case 'unixgroup':
													$g = App\Modules\Groups\Models\UnixGroup::find($log->targetobjectid);
													$groupname = '#' . $log->targetobjectid;
													if ($g)
													{
														$groupname = $g->longname;
													}

													if ($log->classmethod == 'create')
													{
														$log->action = 'Created Unix group ' . $groupname;
													}

													if ($log->classmethod == 'delete')
													{
														$log->action = 'Deleted Unix group ' . $groupname;
													}
												break;

												case 'userrequest':
													$queue = App\Modules\Queues\Models\Queue::find($log->targetobjectid);
													$queuename = '#' . $log->targetobjectid;
													if ($queue)
													{
														$queuename = $queue->name;
													}

													if ($log->classmethod == 'create')
													{
														$log->action = 'Submitted request to queue ' . $queuename . ' (' . ($queue ? $queue->subresource->name : trans('global.unknown')) . ')';
													}

													if ($log->classmethod == 'update')
													{
														$log->action = 'Approved request to queue ' . $queuename . ' (' . ($queue ? $queue->subresource->name : trans('global.unknown')) . ')';
													}

													if ($log->classmethod == 'delete')
													{
														$log->action = 'Canceled request to queue ' . $queuename . ' (' . ($queue ? $queue->subresource->name : trans('global.unknown')) . ')';
													}
												break;

												case 'order':
													if ($log->classmethod == 'create')
													{
														$log->action = 'Order #' . $log->objectid . ' created';
													}

													if ($log->classmethod == 'update')
													{
														$log->action = 'Order #' . $log->objectid . ' updated';
													}

													if ($log->classmethod == 'delete')
													{
														$log->action = 'Order #' . $log->objectid . ' cancelled';
													}
												break;
											}
											?>
											<tr>
												<td><?php echo $log->datetime->format('M j, Y'); ?></td>
												<td><?php echo $log->datetime->format('g:ia'); ?></td>
												<td><?php echo $log->user ? $log->user->name : trans('global.unknown'); ?></td>
												<td><?php echo $log->targetuser ? $log->targetuser->name : trans('global.unknown'); ?></td>
												<td>
													<?php if (substr($log->status, 0, 1) != '2') { ?>
														<i class="fa fa-exclamation-circle" aria-hidden="true"></i> An error occurred while performing this action. Action may not have completed.
													<?php } ?>
													{{ $log->action }}
												</td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>

								<?php
								echo $l->render();
							}
							else
							{
								?>
								<p class="alert alert-warning">No activity found.</p>
								<?php
							}
							?>
