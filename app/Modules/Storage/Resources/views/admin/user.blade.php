
@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/jquery-timepicker/jquery.timepicker.css?v=' . filemtime(public_path() . '/modules/core/vendor/jquery-timepicker/jquery.timepicker.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/jquery-timepicker/jquery.timepicker.js?v=' . filemtime(public_path() . '/modules/core/vendor/jquery-timepicker/jquery.timepicker.js')) }}"></script>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		$('.time-pick').timepicker({
			timeFormat: "h:i A",
			minTime: '8:00am',
			maxTime: '5:00pm',
			change: function() {
				$(this).trigger('change');
			}
		});

		$('.confirm-delete').on('click', function(e){
			e.preventDefault();

			if (confirm($(this).data('confirm'))) {
				$.ajax({
					url: $(this).data('api'),
					type: 'DELETE',
					success: function(result) {
						location.reload(true);
					},
					error: function (result) {
						alert("An error occurred. Please reload the page and try again");
					}
				});
			}
		});

		$('#newalert').dialog({
			modal: true,
			width: '400px',
			autoOpen: false,
			buttons : {
				OK: {
					text: 'Create Alert',
					'class': 'btn btn-success',
					autofocus: true,
					click: function() {

						var val = $('input:radio[name=newalert]:checked').val();

						if (typeof(val) == 'undefined') {
							return;
						}

						var postdata = {
							value: $( "#newalertvalue" ).val(),
							storagedirquotanotificationtypeid: val,
							userid: $( '#HIDDEN_user' ).val(),
							storagedirid: $('[name=newalertstorage]:selected').val()
						};

						//$('#newalert_error').addClass('hide').text('');

						$.ajax({
							url: $('#newalert').data('api'),
							type: 'POST',
							data: postdata,
							success: function(result) {
								//$(this).dialog('close');
								location.reload(true);
							},
							error: function (result) {
								var response = result.responseJSON;
								var msg = 'An error occurred. Please reload the page and try again.';
								if (response.message) {
									msg = response.message;
									if (typeof msg === 'object') {
										var errors = Object.values(msg);
										msg = errors.join('<br />');
									}
								}
								$('#newalert_error').removeClass('hide').html(msg);
							}
						});
					}
				},
				Cancel: {
					text: 'Cancel',
					'class': 'btn btn-link',
					click: function() {
						$(this).dialog('close');
					}
				}
			}
		});

		$('#create-newalert').on('click', function(e) {
			e.preventDefault();

			$('#newalert_error').addClass('hide').text('');
			$('#newalert').dialog('open');
		});

		$('#newreport').dialog({
			modal: true,
			width: '400px',
			autoOpen: false,
			buttons : {
				OK: {
					text: 'Create Report',
					'class': 'btn btn-success',
					click: function() {
						postdata = {};
						postdata['storagedirquotanotificationtypeid'] = '1';
						postdata['userid'] = $('#HIDDEN_user').val();
						postdata['timeperiodid'] = $( '#newreportperiod' ).val();
						postdata['periods'] = $( '#newreportnumperiods').val();
						postdata['value'] = '0';
						postdata['storagedirid'] = $('[name=newreportstorage]:selected').val();
						postdata['datetimelastnotify'] = $('#newreportdate').val();

						//$('#newreport_error').addClass('hide').text('');

						$.ajax({
							url: $('#newreport').data('api'),
							type: 'POST',
							data: postdata,
							success: function(result) {
								$(this).dialog('close');
								location.reload(true);
							},
							error: function (result) {
								var response = result.responseJSON;
								var msg = 'An error occurred. Please reload the page and try again.';
								if (response.message) {
									msg = response.message;
									if (typeof msg === 'object') {
										var errors = Object.values(msg);
										msg = errors.join('<br />');
									}
								}
								$('#newreport_error').removeClass('hide').text(msg);
							}
						});
					}
				},
				Cancel: {
					text: 'Cancel',
					'class': 'btn btn-link',
					click: function() {
						$(this).dialog('close');
					}
				}
			}
		});

		$('#create-newreport').on('click', function(e) {
			e.preventDefault();

			$('#newreport').dialog('open');
			$('#newreport_error').addClass('hide').text('');
		});

		$('.updatequota').on('click', function(event) {
			var btn = $(this),
				did = btn.data('id');

			$('#' + did + '_dialog_error').addClass('hide').html('');

			btn.addClass('processing');
			btn.find('.fa').addClass('hide');
			btn.find('.spinner-border').removeClass('hide');

			$.ajax({
				url: btn.data('api'),
				type: 'GET',
				success: function(data) {
					if (typeof(data) === 'string') {
						data = JSON.parse(data);
					}

					$.ajax({
						url: btn.data('api'),
						type: 'PUT',
						data: {'quotaupdate' : '1'},
						success: function(result) {

							var oldtime = data['latestusage'] ? data['latestusage']['datetimerecorded'] : 0;
							var currtime = data['latestusage'] ? data['latestusage']['datetimerecorded'] : 0;
							var checkcount = 0;

							function check() {
								setTimeout(function() {
									$.get(btn.data('api'), function (data) {
										if (typeof(data) === 'string') {
											data = JSON.parse(data);
										}

										currtime = data['latestusage'] ? data['latestusage']['datetimerecorded'] : 0;
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
							var response = result.responseJSON;
							var msg = 'An error occurred. Please reload the page and try again.';
							if (response.message) {
								msg = response.message;
								if (typeof msg === 'object') {
									var errors = Object.values(msg);
									msg = errors.join('<br />');
								}
							}

							btn.find('.fa').removeClass('hide');
							btn.find('.spinner-border').addClass('hide');
						}
					});
				},
				error: function (result) {
					var response = result.responseJSON;
					var msg = 'An error occurred. Please reload the page and try again.';
					if (response.message) {
						msg = response.message;
						if (typeof msg === 'object') {
							var errors = Object.values(msg);
							msg = errors.join('<br />');
						}
					}

					btn.find('.fa').removeClass('hide');
					btn.find('.spinner-border').addClass('hide');
				}
			});
		});

		$("input[name='newalert']").on('change', function() {
			$("#newalertvalue").val($(this).data('value'));
			$("#newalertvalueunit").html($(this).data('unit'));
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
				$($(this).attr('href') + '_not_error').addClass('hide').text('');
				$($(this).attr('href')).dialog('open');
			}
		});

		$('.details-save').on('click', function(e){
			e.preventDefault();

			var btn = $(this);

			$.ajax({
				url: btn.data('api'),
				type: 'PUT',
				data: {
					'value': ($('#value_' + btn.data('id')).length ? $('#value_' + btn.data('id')).val() : 0),
					'enabled': ($('#enabled_' + btn.data('id') + ':checked').length ? 1 : 0),
					'periods': ($('#periods_' + btn.data('id')).length ? $('#periods_' + btn.data('id')).val() : 0),
					'timeperiodid': ($('#timeperiod_' + btn.data('id')).length ? $('#timeperiod_' + btn.data('id')).val() : 0)
				},
				success: function(result) {
					location.reload(true);
				},
				error: function (result) {
					var response = result.responseJSON;
					var msg = 'An error occurred. Please reload the page and try again.';
					if (response.message) {
						msg = response.message;
						if (typeof msg === 'object') {
							var errors = Object.values(msg);
							msg = errors.join('<br />');
						}
					}
					$('#' + btn.data('id') + '_not_error').removeClass('hide').text(msg);
				}
			});
		});

		$('.property-multi-edit').on('click', function(e){
			e.preventDefault();
			MultiEditProperty($(this).data('props').split(','), $(this).data('id'));
		});
	});
</script>
@endpush

<div class="contentInner">
	<h2 class="sr-only">{{ trans('users::users.quotas') }}</h2>

	<input type="hidden" id="HIDDEN_user" value="{{ $user->id }}" />

	<div class="card">
		<div class="card-header">
			<div class="card-title">
				Storage Spaces
				<a href="#storagespacehelp" data-toggle="modal" class="text-info tip" title="Help">
					<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only">Help</span>
				</a>
			</div>
		</div>
		<div class="card-body panel-body">
			<div class="modal" id="storagespacehelp" tabindex="-1" aria-labelledby="storagespacehelp-title" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content shadow-sm">
						<div class="modal-header">
							<div class="modal-title" id="storagespacehelp-title">Storage Spaces</div>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body dialog-body">
							<p>This table shows the storage spaces you have access to and your usage of these spaces. The data shown may not be immediately up to date but is updated periodically when you load this page.</p>
							<p>Wait a few minutes and refresh this page to see the updated numbers.</p>
						</div>
					</div>
				</div>
			</div>

			<?php
			$sdirs = array();

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
									{{ $dir->resourcepath . '/' . $dir->path }}
								</td>
								<td class="text-center">
									<?php if (!$usage->quota) { ?>
										<span class="none text-muted">- / -</span>
									<?php } else { ?>
										<?php
										$val = round(($usage->space / $usage->quota) * 100, 1);

										$cls = 'bg-success';
										$cls = $val > 50 ? 'bg-info' : $cls;
										$cls = $val > 70 ? 'bg-warning' : $cls;
										$cls = $val > 90 ? 'bg-danger' : $cls;

										echo $usage->formattedSpace . ' / ' . $usage->formattedQuota;
										?>
										<div class="progress" style="height: 3px">
											<div class="progress-bar <?php echo $cls; ?>" role="progressbar" style="width: <?php echo $val; ?>%;" aria-valuenow="<?php echo $val; ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php echo $val; ?>% space used">
												<span class="sr-only"><?php echo $val; ?>%</span>
											</div>
										</div>
									<?php } ?>
								</td>
								<td class="text-center">
									<?php if (!$usage->filequota || $usage->filequota == 1) { ?>
										<span class="none text-muted">- / -</span>
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
											<div class="progress-bar <?php echo $cls; ?>" role="progressbar" style="width: <?php echo $val; ?>%;" aria-valuenow="<?php echo $val; ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php echo $val; ?>% files allowed used">
												<span class="sr-only"><?php echo $val; ?>%</span>
											</div>
										</div>
									<?php } ?>
								</td>
								<td class="text-center">
									@if ($usage->datetimerecorded)
										{{ $usage->datetimerecorded->diffForHumans() }}
									@else
										<span class="none text-muted">-</span>
									@endif
								</td>
								<td class="text-center">
									<a href="#{{ $dir->id }}_dialog" class="details updatequota tip" data-api="{{ route('api.storage.directories.update', ['id' => $dir->id]) }}" data-id="{{ $dir->id }}" title="Update usage now"><!--
									--><span class="fa fa-undo updater" aria-hidden="true"></span><!--
									--><span class="spinner-border spinner-border-sm hide" role="status"><span class="sr-only">Loading...</span></span><!--
									--><span class="sr-only">Update usage now</span><!--
								--></a>
								</td>
							</tr>
							<?php
							// Save for easy access later
							//$dir->ago = $ago;
							$sdirs[$dir->id] = $dir;
						}
						?>
					</tbody>
				</table>

				<p class="alert alert-info">Please allow up to 15 minutes for these numbers to update.</p>
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

	<div class="card">
		<div class="card-header">
			<div class="row">
				<div class="col col-md-6">
					<div class="card-title">
						Storage Alerts
						<a href="#storagealerthelp" data-toggle="modal" class="text-info tip" title="Help"><span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only">Help</span></a>
					</div>
				</div>
				<div class="col col-md-6 text-right">
					<button class="btn btn-secondary btn-sm accountsbtn" id="create-newalert"><span class="fa fa-plus" aria-hidden="true"></span> Create New Alert</button>
				</div>
			</div>
		</div>
		<div class="card-body panel-body">
			<div class="modal" id="storagealerthelp" tabindex="-1" aria-labelledby="storagealerthelp-title" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content shadow-sm">
						<div class="modal-header">
							<div class="modal-title" id="storagealerthelp-title">Storage Alerts</div>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body dialog-body">
							<p>You may define email alerts for your storage spaces. These alerts will send you email when your storage usage crosses the defined threshold. They may be set on an absolute value or on a percentage of your allocated space.</p>
						</div>
					</div>
				</div>
			</div>
			<?php
			$alerts = $storagenotifications->where('storagedirquotanotificationtypeid', '!=', 1);

			$als = array();
			foreach ($alerts as $not)
			{
				if (!isset($sdirs[$not->storagedirid]))
				{
					$sdirs[$not->storagedirid] = $not->directory;
				}

				$dir = $sdirs[$not->storagedirid];

				if ($dir && $dir->resourceid == 64)
				{
					$als[] = $not;
				}
			}

			if (count($als) > 0)
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
						foreach ($als as $not)
						{
							$dir = null;
							if (isset($sdirs[$not->storagedirid]))
							{
								$dir = $sdirs[$not->storagedirid];
							}

							$dir = $sdirs[$not->storagedirid];

								?>
								<tr>
									<td>
										@if ($dir)
											{{ ($dir->storageResource ? $dir->storageResource->path . '/' : '') . $dir->path }}
										@endif
									</td>
									<td>
										<?php echo $not->type->name; ?>
									</td>
									<td class="text-center">
										<?php
										if ($not->type->valuetype == '1')
										{
										}
										elseif ($not->type->valuetype == '2')
										{
											echo $not->formattedValue;
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
									</td>
									<td class="text-center">
										<?php if ($not->enabled == 1) { ?>
											<span class="badge badge-success">{{ trans('global.yes') }}</span>
										<?php } else { ?>
											<span class="badge badge-danger">{{ trans('global.no') }}</span>
										<?php } ?>
									</td>
									<td>
										<?php if (!$not->wasNotified()) { ?>
											<span class="none">-</span>
										<?php } else { ?>
											<time datetime="{{ $not->datetimelastnotify->toDateTimeLocalString() }}">{{ $not->datetimelastnotify->format("m/d/Y") }}</time>
										<?php } ?>
									</td>
									<?php if ($user->id == auth()->user()->id || auth()->user()->can('manage users')) { ?>
										<td class="text-right">
											<a href="#<?php echo $not->id; ?>_not_dialog" class="details tip" title="{{ trans('global.button.edit') }}"><!--
												--><span class="fa fa-pencil"></span><span class="sr-only">{{ trans('global.button.edit') }}</span><!--
											--></a>
										</td>
										<td class="text-right">
											<a href="#dialog-confirm-delete"
												class="confirm-delete delete text-danger tip"
												title="{{ trans('global.button.delete') }}"
												data-id="{{ $not->id }}"
												data-api="{{ route('api.storage.notifications.delete', ['id' => $not->id]) }}"
												data-confirm="Are you sure you wish to delete this notification?"><!--
												--><span class="fa fa-trash"></span><span class="sr-only">{{ trans('global.button.delete') }}</span><!--
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
				foreach ($als as $not)
				{
					$dir = null;
					if (isset($sdirs[$not->storagedirid]))
					{
						$dir = $sdirs[$not->storagedirid];
					}
					?>
					<div id="{{ $not->id }}_not_dialog" title="Storage Alert Detail" class="dialog dialog-details">
						<form method="post" action="{{ route('api.storage.notifications.update', ['id' => $not->id]) }}">
						<input type="hidden" id="HIDDEN_property_{{ $not->id }}" value="{{ $not->id }}" />

						<div class="form-group row">
							<label for="path_{{ $not->id }}" class="col-sm-4">Path</label>
							<div class="col-sm-8">
								<input type="text" id="path_{{ $not->id }}" class="form-control form-control-plaintext" readonly="readonly" value="{{ ($dir ? ($dir->storageResource ? $dir->storageResource->path . '/' : '') . $dir->path : '') }}" />
							</div>
						</div>
						<div class="form-group row">
							<label for="type_{{ $not->id }}" class="col-sm-4">Type</label>
							<div class="col-sm-8">
								<input type="text" id="type_{{ $not->id }}" class="form-control form-control-plaintext" readonly="readonly" value="{{ $not->type->name }}" />
							</div>
						</div>
						<div class="form-group row">
							<label for="value_{{ $not->id }}" class="col-sm-4">Threshold</label>
							<div class="col-sm-8">
								<?php
								$unit = '';
								$number = '';
								if ($not->type->valuetype == 1)
								{
								}
								else if ($not->type->valuetype == 2)
								{
									$number = $not->formattedValue;
								}
								else if ($not->type->valuetype == 3)
								{
									$number = $not->value;
									$unit = '%';
								}
								else if ($not->type->valuetype == 4)
								{
									$number = number_format($not->value);
								}
								?>
								@if ($unit)
								<span class="input-group">
								@endif
									<input type="text" class="form-control" id="value_{{ $not->id }}" value="{{ $number }}" />
								@if ($unit)
									<span class="input-group-addon">
										<span class="input-group-text">{{ $unit }}</span>
									</span>
								</span>
								@endif
							</div>
						</div>
						<div class="form-group row">
							<label for="enabled_{{ $not->id }}" class="col-sm-4">Enabled</label>
							<div class="col-sm-8">
								<div class="form-check">
									<input type="checkbox" class="form-check-input" id="enabled_{{ $not->id }}" <?php echo ($not->enabled == '0') ? '' : ' checked="true"'; ?> />
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label for="datetimelastnotify_{{ $not->id }}" class="col-sm-4">Last Notified</label>
							<div class="col-sm-8">
								<input type="text" id="datetimelastnotify_{{ $not->id }}" class="form-control form-control-plaintext" readonly="readonly" value="{{ $not->wasNotified() ? $not->datetimelastnotify->format('m/d/Y') : trans('global.never') }}" />
							</div>
						</div>
						<div class="ui-dialog-buttonpane ui-widget-content row">
							<div class="col-sm-12 text-right">
								<input type="submit" class="btn btn-success details-save" value="{{ trans('global.save') }}" data-api="{{ route('api.storage.notifications.update', ['id' => $not->id]) }}" id="save_{{ $not->id }}" data-id="{{ $not->id }}" />
							</div>
						</div>

						<div class="alert alert-danger hide" id="{{ $not->id }}_not_error"></div>
						</form>
					</div>
					<?php
				}
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
		<form class="form-inlin" method="post">
			<p>
				<label for="newalertstorage">Monitor</label>
				<select id="newalertstorage" class="form-control">
					<?php
					foreach ($storagedirs as $storagedir)
					{
						if ($storagedir->resourceid != 64)
						{
							continue;
						}
						?>
						<option name="newalertstorage" value="{{ $storagedir->id }}">{{ $storagedir->resourcepath . '/' . $storagedir->path }}</option>
						<?php
					}
					?>
				</select>
			</p>
			<p>
				by a<br />
				<?php
				$types = App\Modules\Storage\Models\Notification\Type::where('id', '>', 1)->get();
				foreach ($types as $type)
				{
					if ($type->id == 2)
					{
						$type->value = '500 GB';
						$type->unit = '';
					}
					else if ($type->id == 3)
					{
						$type->value = '80';
						$type->unit = '%';
					}
					else if ($type->id == 4)
					{
						$type->value = '50000';
						$type->unit = ' files';
					}
					else if ($type->id == 5)
					{
						$type->value = '80';
						$type->unit = '%';
					}
					?>
					<span class="form-check">
						<input type="radio" name="newalert" class="form-check-input" value="{{ $type->id }}" id="newalert-{{ $type->id }}" data-value="{{ $type->value }}" data-unit="{{ $type->unit }}" />
						<label for="newalert-{{ $type->id }}" class="form-check-label">{{ $type->name }}</label>
					</span>
					<?php
				}
				?>
			</p>
			<p>
				<label for="newalertvalue">at</label>
				<span class="input-group">
					<input type="number" class="form-control" id="newalertvalue" placeholder="0" />
					<span class="input-group-append"><span class="input-group-text" id="newalertvalueunit"></span></span>
				</span>
			</p>

			<div id="newalert_error" class="alert alert-danger hide"></div>
		</form>
	</div><!-- / #newalert -->

	<div class="card panel panel-default">
		<div class="card-header panel-heading">
			<div class="row">
				<div class="col col-md-6">
					<div class="card-title">
						Storage Usage Reports
						<a href="#storageusagehelp" data-toggle="modal" class="text-info tip" title="Help">
							<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only">Help</span>
						</a>
					</div>
				</div>
				<div class="col col-md-6 text-right">
					<button class="btn btn-secondary btn-sm accountsbtn" id="create-newreport"><span class="fa fa-plus" aria-hidden="true"></span> Create New Usage Report</button>
				</div>
			</div>
		</div>
		<div class="card-body panel-body">
			<div class="modal" id="storageusagehelp" tabindex="-1" aria-labelledby="storageusagehelp-title" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content shadow-sm">
						<div class="modal-header">
							<div class="modal-title" id="storageusagehelp-title">Storage Usage Reports</div>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body dialog-body">
							<p>You may request usage reports for a storage space be sent to you on regular basis. You may specify which space, when the first report be sent, and how often after that the report should be sent. For example, you may request a usage report be sent starting on Monday at 8am and then every Monday after that.</p>
						</div>
					</div>
				</div>
			</div>
			<?php
			$storagedirquotanotifications = array();

			if (count($storagenotifications) > 0)
			{
				foreach ($storagenotifications as $not)
				{
					if ($not->storagedirquotanotificationtypeid == 1)
					// && $sdirs[$not->storagedirid]->resourceid == 64)
					{
						if (!isset($sdirs[$not->storagedirid]))
						{
							$sdirs[$not->storagedirid] = $not->directory;
						}

						if (!$sdirs[$not->storagedirid] || !$sdirs[$not->storagedirid]->storageResource)
						{
							continue;
						}

						$storagedirquotanotifications[] = $not;
					}
				}
			}

			if (count($storagedirquotanotifications) > 0)
			{
				//$dir = $sdirs[$not->storagedirid];
				?>
				<table class="table table-hover storage">
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
							$dir = null;

							if (isset($sdirs[$not->storagedirid]))
							{
								$dir = $sdirs[$not->storagedirid];
							}
							?>
							<tr>
								<td>
									@if ($dir)
										{{ ($dir->storageResource ? $dir->storageResource->path . '/' : '') . $dir->path }}
									@endif
								</td>
								<td>
									{{ $not->type->name }}
								</td>
								<td>
									<?php
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
									<time datetime="{{ $not->datetimelastnotify->toDateTimeLocalString() }}"><?php echo $not->wasNotified() ? $not->datetimelastnotify->format('m/d/Y') : trans('global.unknown'); ?></time>
								</td>
								<?php if ($user->id == auth()->user()->id || auth()->user()->can('manage storage')) { ?>
									<td class="text-right">
										<a href="#<?php echo $not->id; ?>_not_dialog" class="details tip" title="Edit usage report"><!--
											--><span class="fa fa-pencil"></span><span class="sr-only">Edit</span><!--
										--></a>
									</td>
									<td class="text-right">
										<a href="#dialog-confirm-delete"
											class="confirm-delete delete text-danger tip"
											title="{{ trans('global.button.delete') }}"
											data-id="<?php echo $not->id; ?>"
											data-api="{{ route('api.storage.notifications.delete', ['id' => $not->id]) }}"
											data-confirm="Are you sure you wish to delete this report?"><!--
											--><span class="fa fa-trash"></span><span class="sr-only">{{ trans('global.button.delete') }}</span><!--
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
				foreach ($storagedirquotanotifications as $not)
				{
					if (!$dir)
					{
						continue;
					}
					?>
					<div id="{{ $not->id }}_not_dialog" title="Storage Usage Report Detail" class="dialog dialog-details">
						<form method="post" action="{{ route('api.storage.notifications.update', ['id' => $not->id]) }}">
						<input type="hidden" id="HIDDEN_property_{{ $not->id }}" value="{{ $not->id }}" />

						<div class="form-group row">
							<label for="path_{{ $not->id }}" class="col-sm-4">Path</label>
							<div class="col-sm-8">
								<input type="text" id="path_{{ $not->id }}" class="form-control form-control-plaintext" readonly="readonly" value="{{ ($dir->storageResource ? $dir->storageResource->path . '/' : '') . $dir->path }}" />
							</div>
						</div>
						<div class="form-group row">
							<label for="type_{{ $not->id }}" class="col-sm-4">Type</label>
							<div class="col-sm-8">
								<input type="text" id="type_{{ $not->id }}" class="form-control form-control-plaintext" readonly="readonly" value="{{ $not->type->name }}" />
							</div>
						</div>
						<div class="form-group row">
							<label for="value_{{ $not->id }}" class="col-sm-4">Every</label>
							<div class="col-sm-4">
								<input type="number" id="periods_{{ $not->id }}" class="form-control" value="{{ $not->periods }}" />
							</div>
							<div class="col-sm-4">
								<select class="form-control" id="timeperiod_{{ $not->id }}">
									<?php
									foreach (App\Halcyon\Models\Timeperiod::all() as $period)
									{
										$selected = '';
										if ($period->id == $not->timeperiodid)
										{
											$selected = 'selected="true"';
										}
										echo '<option ' . $selected . ' value="' . $period->id . '">' . $period->plural . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group row">
							<label for="enabled_{{ $not->id }}" class="col-sm-4">Enabled</label>
							<div class="col-sm-8">
								<input type="checkbox" class="form-check-input" id="enabled_{{ $not->id }}" <?php echo ($not->enabled == '0') ? '' : ' checked="true"'; ?> />
							</div>
						</div>
						<div class="form-group row">
							<label for="datetimelastnotify_{{ $not->id }}" class="col-sm-4">Next Report</label>
							<div class="col-sm-8">
								<input type="text" id="datetimelastnotify_{{ $not->id }}" class="form-control form-control-plaintext" readonly="readonly" value="{{ $not->nextnotify }}" />
							</div>
						</div>
						<div class="ui-dialog-buttonpane ui-widget-content row">
							<div class="col-sm-12 text-right">
								<input type="submit" class="btn btn-success details-save" value="{{ trans('global.save') }}" data-api="{{ route('api.storage.notifications.update', ['id' => $not->id]) }}" id="save_{{ $not->id }}" data-id="{{ $not->id }}" />
							</div>
						</div>

						<div class="alert alert-danger hide" id="{{ $not->id }}_not_error"></div>
						</form>
					</div>
					<?php
				}
			}
			else
			{
				?>
				<p class="text-muted text-center">No storage usage reports found.</p>
				<?php
			}
			?>

			<div id="newreport" title="New Usage Report" class="dialog dialog-edit" role="dialog" data-api="{{ route('api.storage.notifications.create') }}">
				<form method="post" action="{{ route('site.users.account.section', ['section' => 'quotas']) }}" class="form">
					<div class="form-group row">
						<label for="newreportstorage" class="col-sm-5">Report on</label>
						<div class="col-sm-7">
							<select id="newreportstorage" class="form-control">
								<?php
								foreach ($storagedirs as $storagedir)
								{
									echo '<option name="newreportstorage" value="' . $storagedir->id . '"/> ' . e($storagedir->resourcepath) . '/' . e($storagedir->path) . '</option>';
								}
								?>
							</select>
						</div>
					</div>

					<div class="form-group row">
						<label for="newreportdate" class="col-sm-5">Starting</label>
						<div class="col-sm-7">
							<input id="newreportdate" type="text" class="form-control date-pick" size="12" value="{{ Carbon\Carbon::now()->modify('+1 day')->format('Y-m-d') }}" placeholder="YYYY-MM-DD hh:mm:ss" />
						</div>
					</div>

					<div class="form-group row">
						<label for="newreportnumperiods" class="col-sm-5">Report every</label>
						<div class="col-sm-3">
							<input type="number" id="newreportnumperiods" size="3" min="1" value="1" class="form-control" />
						</div>
						<div class="col-sm-4">
							<select id="newreportperiod" class="form-control">
								<?php
								foreach (App\Halcyon\Models\Timeperiod::all() as $period)
								{
									echo '<option value="' . $period->id . '">' . e($period->plural) . '</option>';
								}
								?>
							</select>
						</div>
					</div>

					<div class="alert alert-danger hide" id="newreport_error"></div>
				</form>
			</div><!-- / #newreport -->

		</div>
	</div><!-- / .panel -->
</div><!-- / .contentInner -->
