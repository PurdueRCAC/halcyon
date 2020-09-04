
@section('scripts')
<script>
	$(document).ready(function() {
		$('.time-pick').timepicker({
			timeFormat: "h:i A",
			minTime: '8:00am',
			maxTime: '5:00pm',
			change: function() {
				$(this).trigger('change');
			}
		});

		$("#dialog-confirm-delete").html("Are you sure you wish to delete this notification?");

		// Define the Dialog and its properties.
		$("#dialog-confirm-delete").dialog({
			autoOpen: false,
			resizable: false,
			modal: true,
			title: "Delete notification",
			buttons: {
				"Yes": function () {
					$(this).dialog('close');

					var id = $(this).data('id');

					$.ajax({
						url: '/api/storage/notification/' + id,
						type: 'DELETE',
						success: function(result) {
							location.reload(true);
						},
						error: function(result) {
							alert("An error occurred. Please reload the page and try again");
						}
					});
				},
				"No": function () {
					$(this).dialog('close');
				}
			}
		});
		$('.confirm-delete').on('click', function(e){
			e.preventDefault();

			$('#dialog-confirm-delete').dialog('open');
			$('#dialog-confirm-delete').data('id', $(this).data('id'));
		});

		$('#newalert').dialog({
			modal: true,
			width: '350px',
			autoOpen: false,
			buttons : {
				OK: {
					text: 'Create Alert',
					'class': 'btn btn-success',
					//icon: 'fa fa-plus',
					autofocus: true,
					click: function() {

						var val = $('input:radio[name=newalert]:checked').val();

						if (typeof(val) == 'undefined') {
							return;
						}

						var postdata = {};
						postdata = { value: $( "#newalertvalue" ).val() }

						postdata['type'] = val;
						postdata['user'] = $( '#HIDDEN_user' ).val();
						postdata['storagedir'] = $('[name=newalertstorage]:selected').val();

						postdata = JSON.stringify(postdata);

						$(this).dialog('close'); 

						$.ajax({
							url: $('#newalert').data('api'),
							type: 'POST',
							data: postdata,
							success: function(result) {
								location.reload(true);
							},
							error: function (result) {
								alert("An error occurred. Please reload the page and try again");
							}
						});
					}
				},
				Cancel: {
					text: 'Cancel',
					click: function() {
						$(this).dialog('close');
					}
				}
			}
		});

		$('#create-newalert').on('click', function(e) {
			e.preventDefault();

			$('#newalert').dialog('open');
		});


		$('#newreport').dialog({
			modal: true,
			width: '350px',
			autoOpen: false,
			buttons : {
				OK: {
					text: 'Create Report',
					click: function() {
						$(this).dialog('close');

						postdata = {};
						postdata['type'] = '/ws/storagedirquotanotificationtype/1';
						postdata['user'] = $( '#HIDDEN_user' ).val();
						postdata['timeperiod'] = $( '#newreportperiod' ).val();
						postdata['periods'] = $( '#newreportnumperiods').val();
						postdata['value'] = '0';
						postdata['storagedir'] = $('[name=newreportstorage]:selected').val();
						postdata['nextreportdate'] = $( '#newreportdate' ).val();
						postdata['nextreporttime'] = $( '#newreporttime' ).val();

						postdata = JSON.stringify(postdata);

						$.ajax({
							url: $('#newreport').data('api'),
							type: 'POST',
							data: postdata,
							success: function(result) {
								location.reload(true);
							},
							error: function (result) {
								alert("An error occurred. Please reload the page and try again");
							}
						});
					}
				},
				Cancel: {
					text: 'Cancel',
					click: function() {
						$(this).dialog('close'); 
					}
				}
			}
		});

		$('#create-newreport').on('click', function(e) {
			e.preventDefault();

			$('#newreport').dialog('open');
		});

		$('.updatequota').on('click', function(event) {
			var did = event.currentTarget.id.split("_");
			did = did[1];

			//$( '#update_' + did + '_img' ).attr('src', "/include/images/loading.gif");
			$(this).addClass('processing');
			$(this).find('i').addClass('hide');

			var postdata = JSON.stringify({'quotaupdate' : '1' });

			$.get( "/ws/storagedir/" + did, function (data) {
				if (typeof(data) === 'string') {
					data = JSON.parse(data);
				}

				$.ajax({
					url: '/ws/storagedir/' + did,
					type: 'POST',
					data: postdata,
					success: function(result) {

						var oldtime = data['latestusage']['time'];
						var currtime = data['latestusage']['time'];
						var checkcount = 0;

						function check() {
							setTimeout(function() {
								$.get( "/ws/storagedir/" + did, function (data) {
									if (typeof(data) === 'string') {
										data = JSON.parse(data);
									}
									currtime = data['latestusage']['time'];
								});

								if (currtime != oldtime) {
									location.reload(true);
								}

								checkcount++;

								if (checkcount < 45 && currtime == oldtime) {
									check();
								}

								if (checkcount >= 45) {
									alert("Quota checking system is busy or filesystem is unavailable at the moment. Quota refresh has been scheduled so check back on this page later.");
									location.reload(true);
								}
							}, 5000);
						}

						check();
					},
					error: function (result) {
						alert("An error occurred. Please reload the page and try again");
					}
				});
			});
		});

		$("input[name='newalert']").on('change', function() {
			if (this.value == "/ws/storagedirquotanotificationtype/2") {
				$( "#newalertvalue" ).val("500 GB")
				$( "#newalertvalueunit" ).html("");
			} else if (this.value == "/ws/storagedirquotanotificationtype/3") {
				$( "#newalertvalue" ).val("80")
				$( "#newalertvalueunit" ).html("%");
			} else if (this.value == "/ws/storagedirquotanotificationtype/4") {
				$( "#newalertvalue" ).val("50000")
				$( "#newalertvalueunit" ).html(" files");
			} else if (this.value == "/ws/storagedirquotanotificationtype/5") {
				$( "#newalertvalue" ).val("80")
				$( "#newalertvalueunit" ).html("%");
			}
		});

		// Details dialogs
		$('.dialog-details').dialog({
			autoOpen: false,
			modal: true,
			width: '450px'
		});

		$('.details').on('click', function(e){
			e.preventDefault();

			if ($($(this).attr('href')).length) {
				$($(this).attr('href')).dialog('open');
			}
		});

		$('.update-usage').on('click', function(e){
			$($(this).attr('href') + ' .updatequota').trigger('click');
		});

		$('.property-multi-edit').on('click', function(e){
			e.preventDefault();
			MultiEditProperty($(this).data('props').split(','), $(this).data('id'));
		});
	});
</script>
@stop

<div class="contentInner">
	<h2>{{ trans('users::users.quotas') }}</h2>

	<input type="hidden" id="HIDDEN_user" value="{{ $user->id }}" />

	<div class="card panel">
		<div class="card-header">
			<div class="card-title">
				Storage Spaces
				<a href="#storagespacehelp" class="help tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i><span class="sr-only">Help</span></a>
			</div>
		</div>
		<div class="card-body">
			<div id="storagespacehelp" class="dialog dialog-help" title="Storage Spaces">
				<p>This table shows the storage spaces you have access to and your usage of these spaces. The data shown may not be immediately up to date but is updated periodically when you load this page.</p>
				<p>Wait a few minutes and refresh this page to see the updated numbers.</p>
			</div>

			<p>Please allow up to 15 minutes for these numbers to update.</p>

			<?php
			$storagedirs = array();

			if ($storagedirquota)
			{
				?>
				<table class="table table-hover storage">
					<caption class="sr-only">
						Resource Storage Spaces
					</caption>
					<thead>
						<tr>
							<th scope="col">Resource</th>
							<th scope="col" class="text-center">Space<br />Used / Limit</th>
							<th scope="col" class="text-center">Files<br />Used / Limit</th>
							<th scope="col" class="text-center">Last Check</th>
							<th scope="col" class="text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($storagedirquota as $dir)
						{
							$usage = $dir->usage()->orderBy('datetimerecorded', 'desc')->first();
							if (!$usage)
							{
								$usage = new App\Modules\Storage\Models\Usage;
							}
							?>
							<tr>
								<td class="left">
									<a href="#<?php echo $dir->id; ?>_dialog" class="details">
										<?php echo $dir->resourcepath . '/' . $dir->path; ?>
									</a>
								</td>
								<td class="text-center">
									<?php if (!$usage->quota) { ?>
										<span class="none">- / -</span>
									<?php } else { ?>
										<?php
										$val = round(($usage->space / $usage->quota) * 100, 1);

										$cls = 'bg-success';
										$cls = $val > 50 ? 'bg-info' : $cls;
										$cls = $val > 70 ? 'bg-warning' : $cls;
										$cls = $val > 90 ? 'bg-danger' : $cls;

										echo App\Halcyon\Utility\Number::formatBytes($usage->space); ?> / <?php echo App\Halcyon\Utility\Number::formatBytes($usage->quota);
										?>
										<div class="progress" style="height: 3px">
											<div class="progress-bar <?php echo $cls; ?>" role="progressbar" style="width: <?php echo $val; ?>%;" aria-valuenow="<?php echo $val; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $val; ?>%</div>
										</div>
									<?php } ?>
								</td>
								<td class="text-center">
									<?php if (!$usage->filequota || $usage->filequota == 1) { ?>
										<span class="none">- / -</span>
									<?php } else { ?>
										<?php
										$val = round(($usage->files / $usage->filequota) * 100, 1);
										$cls = 'bg-success';
										$cls = $val > 50 ? 'bg-info' : $cls;
										$cls = $val > 70 ? 'bg-warning' : $cls;
										$cls = $val > 90 ? 'bg-danger' : $cls;

										echo number_format($usage->files); ?> / <?php echo number_format($usage->filequota);
										?>
										<div class="progress" style="height: 3px">
											<div class="progress-bar <?php echo $cls; ?>" role="progressbar" style="width: <?php echo $val; ?>%;" aria-valuenow="<?php echo $val; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $val; ?>%</div>
										</div>
									<?php } ?>
								</td>
								<td class="text-center">
									@if ($usage->datetimerecorded)
										{{ $usage->datetimerecorded->diffForHumans() }}
									@else
										<span class="none">-</span>
									@endif
								</td>
								<td class="text-center">
									<a href="#<?php echo $dir->id; ?>_dialog" class="details update-usage tip" title="Update usage now"><!--
									--><i class="fa fa-undo updater" aria-hidden="true"></i><!--
									--><span class="sr-only">Update usage now</span><!--
								--></a>
								</td>
							</tr>
							<?php
							// Save for easy access later
							//$dir->ago = $ago;
							$storagedirs[$dir->id] = $dir;
						}
						?>
					</tbody>
				</table>
				<?php
			}
			else
			{
				?>
				<p class="text-center">(No storage spaces found.)</p>
				<?php
			}
			?>
		</div>
	</div><!-- / .card -->

	<div class="card panel">
		<div class="card-header">
			<div class="row">
				<div class="col col-md-6 card-title">
					Storage Alerts
					<a href="#storagealerthelp" class="help tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i><span class="sr-only">Help</span></a>
				</div>
				<div class="col col-md-6 align-right">
					<button class="btn btn-default btn-sm accountsbtn" id="create-newalert"><i class="fa fa-plus-circle" aria-hidden="true"></i> Create New Alert</button>
				</div>
			</div>
		</div>
		<div class="card-body">
			<div id="storagealerthelp" class="dialog dialog-help" title="Storage Spaces">
				<p>You may define email alerts for your storage spaces. These alerts will send you email when your storage usage crosses the defined threshold. They may be set on an absolute value or on a percentage of your allocated space.</p>
			</div>
			<?php
			if (count($storagenotifications) > 0)
			{
				?>
				<table class="table table-hover storage">
					<caption class="sr-only">Current Storage Alerts</caption>
					<thead>
						<tr>
							<th scope="col">Filesystem</th>
							<th scope="col">Alert Type</th>
							<th scope="col" class="text-center">Threshold</th>
							<th scope="col" class="text-center">Enabled</th>
							<th scope="col">Last Notify</th>
							<?php if ($user->id == auth()->user()->id || auth()->user()->can('manage users')) { ?>
								<th scope="col" colspan="2" class="text-right">Actions</th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($storagenotifications as $not)
						{
							if (!isset($storagedirs[$not->storagedirid]))
							{
								continue;
							}

							$dir = $storagedirs[$not->storagedirid];

							if ($not->storagedirquotanotificationtypeid != 1 && $dir->resourceid == 64)
							{
								/*$usage = $dir->usage()->orderBy('datetimerecorded', 'desc')->first();
							if (!$usage)
							{
								$usage = new App\Modules\Storage\Models\Usage;
							}
								$resource = $dir->resource;*/
								?>
								<tr>
									<td class="left">
										<a href="#<?php echo $not->id; ?>_not_dialog" class="details" title="Edit alert"><?php echo $dir->storageResource->path . '/' . $dir->path; ?></a>
									</td>
									<td class="left">
										<?php echo $not->type->name; ?>
									</td>
									<td class="text-center">
										<?php
										if ($not->type->valuetype == '1')
										{
										}
										elseif ($not->type->valuetype == '2')
										{
											echo App\Halcyon\Utility\Number::formatBytes($not->value);
										}
										elseif ($not->type->valuetype == '3')
										{
											echo $not->value . '%';
										}
										elseif ($not->type->valuetype == '4')
										{
											echo number_format($not->value);
										}
										?>
									<td class="text-center">
										<?php if ($not->enabled == 1) { ?>
											<span class="badge badge-success">{{ trans('global.yes') }}</span>
										<?php } else { ?>
											<span class="badge badge-danger">{{ trans('global.no') }}</span>
										<?php } ?>
									</td>
									<td>
										<?php if ($not->datetimelastnotify == '0000-00-00 00:00:00') { ?>
											<span class="none">-</span>
										<?php } else { ?>
											<time datetime="{{ $not->datetimelastnotify->format('Y-m-d\TH:i:s\Z') }}"><?php echo $not->datetimelastnotify->format("m/d/Y"); ?></time>
										<?php } ?>
									</td>
									<?php if ($user->id == auth()->user()->id || auth()->user()->can('manage users')) { ?>
									<td class="text-right">
										<a href="#<?php echo $not->id; ?>_not_dialog" class="details tip" title="{{ trans('global.button.edit') }}"><!--
											--><i class="fa fa-pencil"></i><span class="sr-only">{{ trans('global.button.edit') }}</span><!--
										--></a>
									</td>
									<td class="text-right">
										<a href="#dialog-confirm-delete"
											class="confirm-delete delete tip"
											title="{{ trans('global.button.delete') }}"
											data-id="{{ $not->id }}"
											data-api="{{ route('api.storage.usage.delete', ['id' => $not->id]) }}"
											data-confirm="Are you sure you wish to delete this notification?"><!--
											--><i class="fa fa-trash"></i><span class="sr-only">{{ trans('global.button.delete') }}</span><!--
										--></a>
									</td>
									<?php } ?>
								</tr>
								<?php
							}
						}
						?>
					</tbody>
				</table>
				<?php
			}
			else
			{
				?>
				<p class="text-muted text-center">No storage alerts found.</p>
				<?php
			}
			?>
		</div>
	</div><!-- / .card -->

	<div id="newalert" title="New Quota Alert" class="dialog dialog-edit" data-api="{{ route('api.storage.notifications.create') }}">
		<form class="form-inline" method="post">
			<p>
				<label for="newalertstorage">Monitor</label>
				<select id="newalertstorage" class="form-control">
					<?php
					foreach ($storagedirquota as $storagedir)
					{
						if ($storagedir->resourceid != 64)
						{
							//continue;
						}
						?>
						<option name="newalertstorage" value="<?php echo $storagedir->id; ?>"/><?php echo $storagedir->resourcepath . '/' . $storagedir->path; ?></option>
						<?php
					}
					?>
				</select> by a
			</p>
			<p>
				<?php
				$types = App\Modules\Storage\Models\Notification\Type::where('id', '>', 1)->get();
				foreach ($types as $type)
				{
					?>
					<input type="radio" name="newalert" value="{{ $type->id }}" id="newalert-{{ $type->id }}" />
					<label for="newalert-{{ $type->id }}">{{ $type->name }}</label>
					<br/>
					<?php
				}
				?>
			</p>
			<p>
				<label for="newalertvalue">at</label> <input type="number" class="form-control" id="newalertvalue" placeholder="0" /><span id="newalertvalueunit"></span>
			</p>
		</form>
	</div><!-- / #newalert -->

	<div class="card">
		<div class="card-header">
			<div class="row">
				<div class="col col-md-6 card-title">
					Storage Usage Reports
					<a href="#storageusagehelp" class="help tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i><span class="sr-only">Help</span></a>
				</div>
				<div class="col col-md-6 align-right">
					<button class="btn btn-default btn-sm accountsbtn" id="create-newreport"><i class="fa fa-plus-circle" aria-hidden="true"></i> Create New Usage Report</button>
				</div>
			</div>
		</div>
		<div class="card-body">
			<div id="storageusagehelp" class="dialog dialog-help" title="Storage Spaces">
				<p>You may request usage reports for a storage space be sent to you on regular basis. You may specify which space, when the first report be sent, and how often after that the report should be sent. For example, you may request a usage report be sent starting on Monday at 8am and then every Monday after that.</p>
			</div>
			<?php
			$storagedirquotanotifications = array();

			if (count($storagenotifications) > 0)
			{
				foreach ($storagenotifications as $not)
				{
					if ($not->storagedirquotanotificationtypeid == 1
					 && isset($storagedirs[$not->storagedirid])
					 && $storagedirs[$not->storagedirid]->resourceid == 64)
					{
						$storagedirquotanotifications[] = $not;
					}
				}
			}

			if (count($storagedirquotanotifications) > 0)
			{
				?>
				<table class="simpleTable storage">
					<caption class="sr-only">
						Current Storage Usage Reports
					</caption>
					<thead>
						<tr>
							<th scope="col">Filesystem</th>
							<th scope="col">Alert Type</th>
							<th scope="col">Frequency</th>
							<th scope="col" class="text-center">Enabled</th>
							<th scope="col">Next Report</th>
							<?php if ($user->id == auth()->user()->id || auth()->user()->can('manage users')) { ?>
							<th scope="col" colspan="2" class="text-right">Actions</th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($storagedirquotanotifications as $not)
						{
							?>
							<tr>
								<td>
									<a href="#<?php echo $not->id; ?>_not_dialog" class="details" title="Edit usage report">
										<?php echo $storagedirs[$not->storagedirid]->path; ?>
									</a>
								</td>
								<td>
									<?php echo $not->type->name; ?>
								</td>
								<td>
									<?php
									//$timeperiod = $ws->get($not['timeperiod']);

									echo 'Every ';
									if ($not->periods > 1)
									{
										echo $not->periods . ' ' . $not->timeperiod->plural;
									}
									else
									{
										echo $not->timeperiod->singular;
									}
									?>
								</td>
								<td class="text-center">
									@if ($not->enabled == 1)
										<span class="badge badge-success">{{ trans('global.yes') }}</span>
									@else
										<span class="badge badge-danger">{{ trans('global.no') }}</span>
									@endif
								</td>
								<td>
									<?php echo date("m/d/Y", strtotime($not->nextreport)); ?>
								</td>
								<?php if ($user->id == auth()->user()->id || auth()->user()->can('manage users')) { ?>
								<td class="text-right">
									
										<a href="#<?php echo $not->id; ?>_not_dialog" class="details tip" title="Edit usage report"><!--
											--><i class="fa fa-pencil"></i><span class="sr-only">Edit</span><!--
										--></a>
									</td>
									<td class="text-right">
										<a href="#dialog-confirm-delete"
											class="confirm-delete delete tip"
											title="{{ trans('global.button.delete') }}"
											data-id="<?php echo $not->id; ?>"
											data-api="{{ route('api.storage.reports.delete', ['id' => $not->id]) }}"
											data-confirm="Are you sure you wish to delete this report?"><!--
											--><i class="fa fa-trash"></i><span class="sr-only">{{ trans('global.button.delete') }}</span><!--
										--></a>
									
								</td>
								<?php } ?>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
				<?php
			}
			else
			{
				?>
				<p class="text-muted text-center">No storage usage reports found.</p>
				<?php
			}
			?>

			<div id="newreport" title="New Usage Report" class="dialog dialog-edit" role="dialog" data-api="{{ route('api.storage.usage.create') }}">
				<!-- <div class="modal-dialog" role="document"><div class="modal-content"> -->
				<form method="post" action="{{ route('site.users.account.section', ['section' => 'quotas']) }}" class="form-inline">
					<!-- <div class="modal-body"> -->
					<p>
						<label for="newreportstorage">Report on</label>
						<select id="newreportstorage" class="form-control">
							<?php
							foreach ($storagedirs as $storagedir)
							{
								echo '<option name="newreportstorage" value="' . $storagedir->id . '"/> ' . $storagedir->resourcepath . '/' . $storagedir->path . '</option>';
							}
							?>
						</select>
					</p>

					<p>
						<label for="newreportdate">starting</label>
						<input id="newreportdate" type="text" class="form-control date-pick" size="12" value="<?php echo Carbon\Carbon::now()->modify('+1 day')->format('Y-m-d'); ?>" placeholder="YYYY-MM-DD hh:mm:ss" />
						<!-- <input id="newreporttime" type="text" class="form-control time-pick" size="10" value="12:00 AM" /> -->
					</p>

					<p>
						<label for="newreportnumperiods">then report every</label>
						<input type="number" id="newreportnumperiods" size="3" min="1" value="1" class="form-control" />
						<select id="newreportperiod" class="form-control">
							<?php
							foreach (App\Halcyon\Models\Timeperiod::all() as $period)
							{
								echo '<option value="' . $period->id . '">' . $period->plural . '</option>';
							}
							?>
						</select>
					</p>
					<!--</div>
					<div class="modal-footer">
						<button class="btn btn-success accountsbtn" id="create-newreport"><i class="fa fa-plus-circle" aria-hidden="true"></i> Create Report</button>
						<button class="btn">Cancel</button>
					</div> -->
				</form>
					<!-- </div>
				</div> -->
			</div><!-- / #newreport -->

		</div>
	</div><!-- / .panel -->

	<div id="dialog-confirm-delete"></div>

</div><!-- / .contentInner -->
