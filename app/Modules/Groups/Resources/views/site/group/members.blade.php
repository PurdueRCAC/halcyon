@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('vendor/datatables/datatables.bootstrap.min.css') }}" />
@stop

@section('scripts')
<script src="{{ asset('vendor/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/datatables.bootstrap.min.js') }}"></script>
<script src="{{ asset('vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/vendor/select2/js/select2.min.js')) }}"></script>
<script>
/*
document.addEventListener('DOMContentLoaded', function() {
	var dels = document.getElementsByClassName('motd-delete');
	var i;
	for (i = 0; i < dels.length; i++)
	{
		dels[i].addEventListener('click', function(e){
			e.preventDefault();
			motd.delete(this.getAttribute('data-group'));
		});
	}

	var sets = document.getElementsByClassName('motd-set');
	for (i = 0; i < sets.length; i++)
	{
		sets[i].addEventListener('click', function(e){
			e.preventDefault();
			motd.set(this.getAttribute('data-group'));
		});
	}
});*/

	$(document).ready(function() {

		$('.datatable').DataTable({
			pageLength: 20,
			pagingType: 'numbers',
			info: false,
			ordering: false,
			lengthChange: false,
			scrollX: true,
			autoWidth: false,
			language: {
				searchPlaceholder: "Filter users..."
			},/*
			fixedColumns: {
				leftColumns: 1//,
				//rightColumns: 1
			},
			lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
			fixedColumns: {
				leftColumns: 1
			},*/
			initComplete: function () {
				//this.page(0).draw(true);
				$($.fn.dataTable.tables( true ) ).css('width', '100%');
				$($.fn.dataTable.tables( true ) ).DataTable().columns.adjust().draw();
				/*this.api().columns().every(function (i) {
					var column = this;
					var select = $('<select data-index="' + i + '"><option value=""></option></select>')
						.appendTo($(column.footer()).empty());

					column.data().unique().sort().each(function (d, j) {
						select.append('<option value="'+d+'">'+d+'</option>');
					});
				});

				var table = this;

				$(table.api().table().container()).on('change', 'tfoot select', function () {
					var val = $.fn.dataTable.util.escapeRegex(
						$(this).val()
					);

					table.api()
						.column($(this).data('index'))
						.search(val ? '^'+val+'$' : '', true, false)
						.draw();
				});*/
			}
		});

		$('.membership-edit').on('click', function(e){
			e.preventDefault();

			$($(this).attr('href')).toggleClass('hidden');
		});

		/*
		 $('a[data-toggle="tab"]').on( 'shown.bs.tab', function (e) {
			$($.fn.dataTable.tables( true ) ).css('width', '100%');
			$($.fn.dataTable.tables( true ) ).DataTable().columns.adjust().draw();
		});
		*/

		//$('.dataTables_filter input').addClass('form-control');
	});
</script>
@stop

					<?php
					$m = (new \App\Modules\Groups\Models\Member)->getTable();
					$u = (new \App\Modules\Users\Models\User)->getTable();

					$managers = $group->members()->withTrashed()
						->select($m . '.*')
						->join($u, $u . '.id', $m . '.userid')
						->whereNull($u . '.deleted_at')
						->where(function($where) use ($m)
						{
							$where->whereNull($m . '.dateremoved')
								->orWhere($m . '.dateremoved', '=', '0000-00-00 00:00:00');
						})
						->whereIsManager()
						->orderBy($m . '.datecreated', 'desc')
						->get();

					$members = $group->members()->withTrashed()
						->select($m . '.*', $u . '.name')
						->join($u, $u . '.id', $m . '.userid')
						->whereNull($u . '.deleted_at')
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
							->select($q . '.*', $u . '.name')
							->join($u, $u . '.id', $q . '.userid')
							->whereNull($u . '.deleted_at')
							->where(function($where) use ($q)
							{
								$where->whereNull($q . '.datetimeremoved')
									->orWhere($q . '.datetimeremoved', '=', '0000-00-00 00:00:00');
							})
							->whereIsMember()
							->orderBy($q . '.datetimecreated', 'desc')
							->get();
						/*$users = $queue->users()
							->withTrashed()
							->whereIsMember()
							->get();*/

						foreach ($users as $me)
						{
							if (!($found = $members->firstWhere('userid', $me->userid)))
							{
								$members->push($me);
							}
						}
					}

					$disabled = $group->members()->withTrashed()
						->select($m . '.*', $u . '.name')
						->join($u, $u . '.id', $m . '.userid')
						->where(function($where) use ($m, $u)
						{
							$where->whereNotNull($u . '.deleted_at')
								//->orWhere($m . '.dateremoved', '!=', '0000-00-00 00:00:00');
								->orWhere(function($wher) use ($m)
								{
									$wher->whereNotNull($m . '.dateremoved')
										->where($m . '.dateremoved', '!=', '0000-00-00 00:00:00');
								});
						})
						->whereIsMember()
						->whereNotIn($m . '.userid', $members->pluck('userid')->toArray())
						->orderBy($m . '.datecreated', 'desc')
						->get();

					foreach ($group->queues as $queue)
					{
						$users = $queue->users()->withTrashed()
							->select($q . '.*', $u . '.name')
							->join($u, $u . '.id', $q . '.userid')
							->where(function($where) use ($q, $u)
							{
								$where->whereNotNull($u . '.deleted_at')
									//->orWhere($m . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
									->orWhere(function($wher) use ($q)
									{
										$wher->whereNotNull($q . '.datetimeremoved')
											->where($q . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
									});
							})
							->whereIsMember()
							->whereNotIn($q . '.userid', $members->pluck('userid')->toArray())
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
									<a href="#help_managers_span_{{ $group->id }}" class="help icn tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i> Help</a>
									<div class="dialog dialog-help" id="help_managers_span_{{ $group->id }}" title="Managers">
										Managers are the owners or <abbr title="Principle Investigators">PIs</abbr> of this group and any others they may choose to delegate to manage access to this group. Only Managers can access this interface and are able to grant queue access for other people in the group. Managers can also grant and remove Group Management privileges to and from others, although you cannot remove your own Group Management privileges.
									</div>
								</div>
								<div class="col-md-3 text-right">
									<a href="#" class="btn btn-default btn-sm">
										<i class="fa fa-plus-circle"></i> Add Manager
									</a>
								</div>
							</div>
						</div>
						<div class="card-body panel-body">
							<table class="table">
								<caption class="sr-only">Managers</caption>
								<thead>
									<tr>
										<th>User</th>
										<th>Queues</th>
										<th>Unix Groups</th>
										<th class="text-right">Options</th>
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
											<td>
											<?php
											$in = array();
											$qu = array();
											foreach ($group->queues as $queue):
												$qu[$queue->id] = $queue->users->pluck('userid')->toArray();
												// Managers get explicit access to owned queues, but not for free queues.
												if (!$queue->free):
													$in[] = $queue->name . ' (' . $queue->resource->name . ')';
												endif;
												/*foreach ($queue->users as $m):
													if ($m->userid == $member->userid):
														$in[] = $queue->name;
													endif;
												endforeach;*/
											endforeach;
											echo implode(', ', $in);
											?>
											</td>
											<td>
												<?php
											$in = array();
											$uu = array();
											foreach ($group->unixgroups as $unix):
												$uu[$unix->id] = $unix->members->pluck('userid')->toArray();
												foreach ($unix->members as $m):
													if ($m->userid == $member->userid):
														$in[] = $unix->longname;
													endif;
												endforeach;
											endforeach;
											echo implode(', ', $in);
											?>
											</td>
											<td class="text-right text-nowrap">
												<a href="#manager-{{ $member->userid }}-edit" class="btn membership-edit"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sr-only">Edit memberships</span></a>
												<a href="#" class="btn demote"><i class="fa fa-arrow-down" aria-hidden="true"></i><span class="sr-only">Demote</span></a>
												<a href="#" class="btn delete"><i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">Remove from group</span></a>
											</td>
										</tr>
										<tr id="manager-{{ $member->userid }}-edit" class="hidden">
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
										</tr>
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
									<a href="#help_members_span_{{ $group->id }}" class="help icn tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i> Help</a>
									<div class="dialog dialog-help" id="help_members_span_{{ $group->id }}" title="Members">
										Members are people that have access to some or all of this group's queues but have no other special privileges such as Group Usage Reporting privileges or Group Managment privileges. Enabling a queue for someone will also create an account for them on the appropriate resource if they do not already have one. New accounts on a cluster will be processed overnight and be ready use the next morning. The person will receive an email notification once their account is ready.
									</div>
								</div>
								<div class="col-md-3 text-right">
									<a href="#" class="btn btn-default btn-sm">
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
										<th scope="col"></th>
										<th scope="col" colspan="{{ count($group->queues) }}">Queues</th>
										<th scope="col" colspan="{{ count($group->unixgroups) }}">Unix Groups</th>
										<th scope="col"></th>
									</tr>
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

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Disabled Members
							<a href="#help_disabledmembers_span_{{ $group->id }}" class="help icn tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i> Help</a>
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
										<th>Queues</th>
										<th>Unix Groups</th>
										<th class="text-right">Options</th>
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
											<td>{{ $member->user->deleted_at }}</td>
											<td>{{ $member->datetimeremoved }}</td>
											<td></td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>

					<form class="addMany" id="FORM_<?php echo $group->id; ?>">
						<p>
							Adding users to <?php echo $group->name; ?></br>
						</p>
						<div class="form-group">
							<label for="TA_<?php echo $group->id; ?>">Enter usernames or email addresses</label>
							<input type="text" class="bulkAdd form-control" id="TA_<?php echo $group->id; ?>" placeholder="Username, email address, etc." />
						</div>
						<div class="accordion">
							<a href="#queue-selection">Queue Selection</a>
							<div>
								<?php $n = 0; ?>
								<table id="queue-selection" class="groupSelect">
									<tbody>
										<?php
										foreach ($resources as $name => $queues)
										{
											?>
											<tr>
												<td class="rowHead"><?php echo $name; ?></td>
												<?php
												foreach ($queues as $queue)
												{
													?>
													<td class="rowData">
														<input type="checkbox" id="<?php echo $queue->id; ?>" value="<?php echo $queue->name; ?>" />
														<label for="<?php echo $queue->id; ?>"><?php echo $queue->name; ?></label>
													</td>
													<?php
													$n++;
												}
												?>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</div>

							<a href="#unix-group-selection">Unix Group Selection</a>
							<div>
								<?php $n = 0; ?>
								<table id="unix-group-selection" class="groupSelect">
									<tbody>
										<tr>
										<?php
										foreach ($group->unixgroups as $name)
										{
											if ($n%3 == 0 && $n != 0)
											{
												echo '</tr><tr>';
											}

												?>
												<td class="unixData">
													<input type="checkbox" id="<?php echo $name->id; ?>" value="<?php echo $name->longname; ?>"> <label for="<?php echo $name->id; ?>"><?php echo $name->longname; ?></label>
												</td>
												<?php
												$n++;
										}
										?>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</form>
