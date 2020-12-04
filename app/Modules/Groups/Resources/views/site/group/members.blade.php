					<?php
					$m = (new \App\Modules\Groups\Models\Member)->getTable();
					$u = (new \App\Modules\Users\Models\UserUsername)->getTable();

					$managers = $group->members()->withTrashed()
						->select($m . '.*')
						->join($u, $u . '.userid', $m . '.userid')
						//->whereNull($u . '.deleted_at')
						->where(function($where) use ($u)
						{
							$where->whereNull($u . '.dateremoved')
								->orWhere($u . '.dateremoved', '=', '0000-00-00 00:00:00');
						})
						->where(function($where) use ($m)
						{
							$where->whereNull($m . '.dateremoved')
								->orWhere($m . '.dateremoved', '=', '0000-00-00 00:00:00');
						})
						->whereIsManager()
						->orderBy($m . '.datecreated', 'desc')
						->get();

					$managerids = $managers->pluck('userid')->toArray();

					$members = $group->members()->withTrashed()
						->select($m . '.*')//, $u . '.name')
						->join($u, $u . '.userid', $m . '.userid')
						//->whereNull($u . '.deleted_at')
						->where(function($where) use ($u)
						{
							$where->whereNull($u . '.dateremoved')
								->orWhere($u . '.dateremoved', '=', '0000-00-00 00:00:00');
						})
						->where(function($where) use ($m)
						{
							$where->whereNull($m . '.dateremoved')
								->orWhere($m . '.dateremoved', '=', '0000-00-00 00:00:00');
						})
						->whereIsMember()
						->orderBy($m . '.datecreated', 'desc')
						->get();

					$q = (new \App\Modules\Queues\Models\User)->getTable();

					$resources = array();

					foreach ($group->queues as $queue)
					{
						if (!isset($resources[$queue->resource->name]))
						{
							$resources[$queue->resource->name] = array();
						}
						$resources[$queue->resource->name][] = $queue;

						$users = $queue->users()->withTrashed()
							->select($q . '.*')//, $u . '.name')
							->join($u, $u . '.userid', $q . '.userid')
							//->whereNull($u . '.deleted_at')
							->where(function($where) use ($u)
							{
								$where->whereNull($u . '.dateremoved')
									->orWhere($u . '.dateremoved', '=', '0000-00-00 00:00:00');
							})
							->where(function($where) use ($q)
							{
								$where->whereNull($q . '.datetimeremoved')
									->orWhere($q . '.datetimeremoved', '=', '0000-00-00 00:00:00');
							})
							->whereIsMember()
							->whereNotIn($q . '.userid', $managerids)
							->orderBy($q . '.datetimecreated', 'desc')
							->get();

						foreach ($users as $me)
						{
							if (!($found = $members->firstWhere('userid', $me->userid)))
							{
								$members->push($me);
							}
						}
					}

					$disabled = $group->members()->withTrashed()
						->select($m . '.*')//, $u . '.name')
						->join($u, $u . '.userid', $m . '.userid')
						->where(function($where) use ($m, $u)
						{
							$where->where(function($wher) use ($u)
								{
									$wher->whereNotNull($u . '.dateremoved')
										->where($u . '.dateremoved', '!=', '0000-00-00 00:00:00');
								})
								->orWhere(function($wher) use ($m)
								{
									$wher->whereNotNull($m . '.dateremoved')
										->where($m . '.dateremoved', '!=', '0000-00-00 00:00:00');
								});
						})
						//->whereIsMember()
						//->whereNotIn($m . '.userid', $members->pluck('userid')->toArray())
						->orderBy($m . '.datecreated', 'desc')
						->get();

					foreach ($group->queues as $queue)
					{
						$users = $queue->users()->withTrashed()
							->select($q . '.*', $u . '.dateremoved')//, $u . '.name')
							->join($u, $u . '.userid', $q . '.userid')
							->where(function($where) use ($q, $u)
							{
								$where->where(function($wher) use ($u)
								{
									$wher->whereNotNull($u . '.dateremoved')
										->where($u . '.dateremoved', '!=', '0000-00-00 00:00:00');
								})
								->orWhere(function($wher) use ($q)
								{
									$wher->whereNotNull($q . '.datetimeremoved')
										->where($q . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
								});
							})
							//->whereIsMember()
							//->whereNotIn($q . '.userid', $members->pluck('userid')->toArray())
							->orderBy($q . '.datetimecreated', 'desc')
							->get();

						foreach ($users as $me)
						{
							if (!($found = $disabled->firstWhere('userid', $me->userid)))
							{
								$disabled->push($me);
							}
						}
					}

					$members = $members->sortBy('name');
					?>

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							<div class="row">
								<div class="col-md-9">
									Managers
									<a href="#help_managers_span_{{ $group->id }}" class="help icn tip" title="Help">
										<i class="fa fa-question-circle" aria-hidden="true"></i> Help
									</a>
									<div class="dialog dialog-help" id="help_managers_span_{{ $group->id }}" title="Managers">
										Managers are the owners or <abbr title="Principle Investigators">PIs</abbr> of this group and any others they may choose to delegate to manage access to this group. Only Managers can access this interface and are able to grant queue access for other people in the group. Managers can also grant and remove Group Management privileges to and from others, although you cannot remove your own Group Management privileges.
									</div>
								</div>
								<div class="col-md-3 text-right">
									<a href="#add_member_dialog" class="add_member btn btn-default btn-sm" data-membertype="2">
										<i class="fa fa-plus-circle"></i> Add Manager
									</a>
								</div>
							</div>
						</div>
						<div class="card-body panel-body">
							<table class="table table-hover datatable">
								<caption class="sr-only">Managers</caption>
								<thead>
									<tr>
										<th scope="col">User</th>
										<?php
										//$qu = array();
										foreach ($group->queues as $queue):
											//$qu[$queue->id] = $queue->users->pluck('userid')->toArray();
											?>
											<th scope="col" class="text-center">{{ $queue->name }} ({{ $queue->resource->name }})</th>
											<?php
										endforeach;

										//$uu = array();
										foreach ($group->unixgroups as $unix):
											//$uu[$unix->id] = $unix->members->pluck('userid')->toArray();
											?>
											<th scope="col" class="text-center">{{ $unix->longname }}</th>
											<?php
										endforeach;
										?>
										<th scope="col" class="text-right">Options</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($managers as $member)
										<tr id="manager-{{ $member->userid }}">
											<td class="text-nowrap">
												@if (auth()->user()->can('manage users'))
													<a href="{{ route('site.users.account', ['u' => $member->userid]) }}">
														{{ $member->user ? $member->user->name : trans('global.unknown') }}
													</a>
												@else
													{{ $member->user ? $member->user->name : trans('global.unknown') }}
												@endif
											</td>
											<!--<td>-->
											<?php
											/*$in = array();
											$qu = array();
											foreach ($group->queues as $queue):
												$qu[$queue->id] = $queue->users->pluck('userid')->toArray();
												// Managers get explicit access to owned queues, but not for free queues.
												if (!$queue->free):
													$in[] = $queue->name . ' (' . $queue->resource->name . ')';
												endif;
											endforeach;
											echo implode(', ', $in);*/
											$in = array();
											$qu = array();
											foreach ($group->queues as $queue):
												$qu[$queue->id] = $queue->users->pluck('userid')->toArray();
												$checked = '';
												if (in_array($member->userid, $qu[$queue->id])):
													$in[] = $queue->name;
													$checked = ' checked="checked"';
												endif;
												?>
												<td class="text-center"><input type="checkbox" name="queue[{{ $queue->id }}]"{{ $checked }} value="1" /></td>
												<?php
											endforeach;
											/*?>
											</td>
											<td>
												<?php*/
												/*$in = array();
												$uu = array();
												foreach ($group->unixgroups as $unix):
													$uu[$unix->id] = $unix->members->pluck('userid')->toArray();
													foreach ($unix->members as $m):
														if ($m->userid == $member->userid):
															$in[] = $unix->longname;
														endif;
													endforeach;
												endforeach;
												echo implode(', ', $in);*/
											$in = array();
											$uu = array();
											foreach ($group->unixgroups as $unix):
												$uu[$unix->id] = $unix->members->pluck('userid')->toArray();
												$checked = '';
												//foreach ($unix->members as $m):
												if (in_array($member->userid, $uu[$unix->id])):
													$in[] = $unix->longname;
													$checked = ' checked="checked"';
												endif;
												?>
												<td class="text-center"><input type="checkbox" name="unix[{{ $unix->id }}]"{{ $checked }} value="1" /></td>
												<?php
													//endif;
												//endforeach;
											endforeach;
												?>
											<!-- </td> -->
											<td class="text-right text-nowrap">
												<!-- <a href="#manager-{{ $member->userid }}-edit" class="btn membership-edit tip" title="Edit memberships"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sr-only">Edit memberships</span></a> -->
												<a href="#" class="btn demote tip" title="Demote"><i class="fa fa-arrow-down" aria-hidden="true"></i><span class="sr-only">Demote</span></a>
												<a href="#" class="btn delete tip" title="Remove from group"><i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">Remove from group</span></a>
											</td>
										</tr>
										<!-- <tr id="manager-{{ $member->userid }}-edit" class="hidden">
											<td></td>
											<td colspan="3">
												<div class="row">
													<div class="col-md-6">
														<fieldset>
															<legend>Queues</legend>

															<?php
															foreach ($group->queues as $queue):
																$checked = '';
																//if (in_array($member->userid, $qu[$queue->id])):
																if (!$queue->free):
																	$checked = ' checked="checked" disabled="disabled"';
																endif;
																?>
															<div class="form-group">
																<input type="checkbox" id="queue-{{ $queue->id }}" name="queue[{{ $queue->id }}]"{{ $checked }} value="1" />
																<label for="queue-{{ $queue->id }}">{{ $queue->name }} ({{ $queue->resource->name }})</label>
															</div>
															<?php
															endforeach;
															?>
														</fieldset>
													</div>
													<div class="col-md-6">
														<fieldset>
															<legend>Unix Groups</legend>

															<?php
															foreach ($group->unixgroups as $unix):
																$checked = '';

																if (in_array($member->userid, $uu[$unix->id])):
																	$checked = ' checked="checked"';
																endif;
																?>
															<div class="form-group">
																<input type="checkbox" id="unix-{{ $unix->id }}" name="unix[{{ $unix->id }}]"{{ $checked }} value="1" />
																<label for="unix-{{ $unix->id }}">{{ $unix->longname }}</label>
															</div>
															<?php
															endforeach;
															?>
														</fieldset>
													</div>
												</div>
											</td>
										</tr> -->
									@endforeach
								</tbody>
							</table>
						</div>
					</div>

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							<div class="row">
								<div class="col-md-9">
									Members
									<a href="#help_members_span_{{ $group->id }}" class="help icn tip" title="Help">
										<i class="fa fa-question-circle" aria-hidden="true"></i> Help
									</a>
									<div class="dialog dialog-help" id="help_members_span_{{ $group->id }}" title="Members">
										Members are people that have access to some or all of this group's queues but have no other special privileges such as Group Usage Reporting privileges or Group Managment privileges. Enabling a queue for someone will also create an account for them on the appropriate resource if they do not already have one. New accounts on a cluster will be processed overnight and be ready use the next morning. The person will receive an email notification once their account is ready.
									</div>
								</div>
								<div class="col-md-3 text-right">
									<a href="#add_member_dialog" data-membertype="1" class="add_member btn btn-default btn-sm">
										<i class="fa fa-plus-circle"></i> Add Member
									</a>
								</div>
							</div>
						</div>
						<div class="card-body panel-body">
							@if (count($members) > 0)
							<table class="table table-hover hover datatable">
								<caption class="sr-only">Members</caption>
								<thead>
									<tr>
										<th scope="col">&nbsp;</th>
										<th scope="col" colspan="{{ count($group->queues) }}">Queues</th>
										<th scope="col" colspan="{{ count($group->unixgroups) }}">Unix Groups</th>
										<th scope="col"></th>
									</tr>
									<tr>
										<th class="text-nowrap" scope="col">User<br />&nbsp;</th>
										<?php
										//$qu = array();
										foreach ($group->queues as $queue):
											//$qu[$queue->id] = $queue->users->pluck('userid')->toArray();
											?>
											<th scope="col" class="text-center">{{ $queue->name }} ({{ $queue->resource->name }})</th>
											<?php
										endforeach;

										//$uu = array();
										foreach ($group->unixgroups as $unix):
											//$uu[$unix->id] = $unix->members->pluck('userid')->toArray();
											?>
											<th scope="col" class="text-center">{{ $unix->longname }}</th>
											<?php
										endforeach;
										?>
										<th scope="col" class="text-right">Options</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($members as $member)
										<tr>
											<td class="text-nowrap">
												@if (auth()->user()->can('manage users'))
													<a href="{{ route('site.users.account', ['u' => $member->userid]) }}">
														{{ $member->user ? $member->user->name : trans('global.unknown') }}
													</a>
												@else
													{{ $member->user ? $member->user->name : trans('global.unknown') }}
												@endif
											</td>
											<!-- <td> -->
											<?php
											$in = array();
											foreach ($group->queues as $queue):
												$checked = '';
												//foreach ($queue->users as $m):
													//if ($m->userid == $member->userid):

													if (in_array($member->userid, $qu[$queue->id])):
														$in[] = $queue->name;
														$checked = ' checked="checked"';
													endif;
														?>
														<td class="text-center"><input type="checkbox" name="queue[{{ $queue->id }}]"{{ $checked }} value="1" /></td>
														<?php
													//endif;
												//endforeach;
											endforeach;
											/*echo implode(', ', $in);
											?>
											</td>
											<td>
												<?php*/
											$in = array();
											foreach ($group->unixgroups as $unix):
												$checked = '';
												//foreach ($unix->members as $m):
													if (in_array($member->userid, $uu[$unix->id])):
														$in[] = $unix->longname;
														$checked = ' checked="checked"';
													endif;
														?>
														<td class="text-center"><input type="checkbox" name="unix[{{ $unix->id }}]"{{ $checked }} value="1" /></td>
														<?php
													//endif;
												//endforeach;
											endforeach;
											//echo implode(', ', $in);
											?>
											<!-- </td> -->
											<td class="text-right">
												<a href="#" class="promote tip" title="Promote to manager"><i class="fa fa-arrow-up" aria-hidden="true"></i><span class="sr-only">Promote</span></a>
												<a href="#" class="delete tip" title="Remove from group"><i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">Remove from group</span></a>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
							@else
							<p class="alert alert-info">No members found.</p>
							@endif
						</div>
					</div>

					@if (count($disabled))
					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Disabled Members
							<a href="#help_disabledmembers_span_{{ $group->id }}" class="help icn tip" title="Help">
								<i class="fa fa-question-circle" aria-hidden="true"></i> Help
							</a>
							<div class="dialog dialog-help" id="help_disabledmembers_span_{{ $group->id }}" title="Disabled Members">
								Disabled Members are people that you have granted access to your queues but who no longer have an active account with ITaP Research Computing or have an active Purdue Career Account. Although queues may be enabled for them, they cannot log into Research Computing resources and use your queues without an active account. If the people listed here have left the University and are no longer participating in research, please remove them from your queues. If people listed here have left Purdue but still require access to your queues then you will need to file a Request for Privileges (R4P). If you believe people are listed here in error, please contact rcac-help@purdue.edu.
							</div>
						</div>
						<div class="card-body panel-body">
							<table class="table">
								<caption class="sr-only">Disabled Members</caption>
								<thead>
									<tr>
										<th>User</th>
										<th>Removed</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($disabled as $member)
										<tr>
											<td class="text-nowrap">
												@if (auth()->user()->can('manage users'))
													<a href="{{ route('site.users.account', ['u' => $member->userid]) }}">
														{{ $member->user ? $member->user->name : trans('global.unknown') }}
													</a>
												@else
													{{ $member->user ? $member->user->name : trans('global.unknown') }}
												@endif
											</td>
											<td>{{ $member->datetimeremoved ? $member->datetimeremoved->format('Y-m-d') : ($member->dateremoved ? $member->dateremoved->format('Y-m-d') : trans('global.unknown')) }}</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					@endif

					<div id="add_member_dialog" data-id="{{ $group->id }}" title="Add users to {{ $group->name }}" class="membership-dialog">
						<form id="form_{{ $group->id }}" method="post">
							<div class="form-group">
								<label for="addmembers">Enter usernames or email addresses</label>
								<!-- <input type="text" class="form-control" name="members" id="addmembers" data-api="{{ route('api.users.index') }}" data-group="{{ $group->id }}" placeholder="Username, email address, etc." /> -->
								<div class="input-group">
									<select class="form-control" name="members" id="addmembers" multiple="multiple" data-api="{{ route('api.users.index') }}" data-group="{{ $group->id }}" placeholder="Username, email address, etc.">
									</select>
									<span class="input-group-addon">
										<span class="input-group-text">
											<i class="fa fa-users" aria-hidden="true" id="add_button_a"></i>
										</span>
									</span>
								</div>
							</div>

							<div class="form-group">
								<label for="new_membertype">Membership type</label>
								<select class="form-control" id="new_membertype">
									<option value="1">Member</option>
									<option value="2">Manager</option>
									<option value="3">Usage Viewer</option>
								</select>
							</div>

							<fieldset>
								<legend>Queue Selection</legend>

								<table id="queue-selection" class="table table-hover mb-0 groupSelect">
									<caption class="sr-only">Queues by Resource</caption>
									<tbody>
										<?php
										foreach ($resources as $name => $queues)
										{
											?>
											<tr>
												<th scope="row" class="rowHead">{{ $name }}</th>
												<td class="rowData">
												<?php
												foreach ($queues as $queue)
												{
													?>
													<div class="form-check">
														<input type="checkbox" class="form-check-input add-queue-member" name="queue[]" id="queue{{ $queue->id }}" value="{{ $queue->id }}" />
														<label class="form-check-label" for="queue{{ $queue->id }}">{{ $queue->name }}</label>
													</div>
													<?php
												}
												?>
												</td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</fieldset>

							<fieldset>
								<legend>Unix Group Selection</legend>

								<div id="unix-group-selection" class="row groupSelect">
									<?php
									foreach ($group->unixgroups as $name)
									{
										?>
										<div class="col-sm-4 unixData">
											<div class="form-check">
												<input type="checkbox" class="form-check-input add-unixgroup-member" name="unixgroup[]" id="unixgroup{{ $name->id }}" value="{{ $name->id }}" />
												<label class="form-check-label" for="unixgroup{{ $name->id }}">{{ $name->longname }}</label>
											</div>
										</div>
										<?php
									}
									?>
								</div>
							</fieldset>

							<div class="dialog-footer">
								<div class="row">
									<div class="col-md-12 text-right">
										<input type="button" disabled="disabled" id="add_member_save" class="btn btn-success" data-group="{{ $group->id }}" data-api="{{ route('api.groups.members.create') }}" value="{{ trans('global.button.save') }}" />
									</div>
								</div>
							</div>
						</form>
					</div>
