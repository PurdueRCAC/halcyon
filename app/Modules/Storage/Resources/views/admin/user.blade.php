
@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/jquery-timepicker/jquery.timepicker.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/jquery-timepicker/jquery.timepicker.js') }}"></script>
<script src="{{ timestamped_asset('modules/storage/js/quotas.js') }}"></script>
@endpush

<div class="contentInner">
	<h2 class="sr-only visually-hidden">{{ trans('storage::storage.my quotas') }}</h2>

	<input type="hidden" id="HIDDEN_user" value="{{ $user->id }}" />

	<div class="card mb-3">
		<div class="card-header">
			<h3 class="card-title my-0">
				Storage Spaces
				<a href="#storagespacehelp" data-toggle="modal" class="text-info tip" title="Help">
					<span class="fa fa-question-circle" aria-hidden="true"></span>
					<span class="sr-only visually-hidden">Help</span>
				</a>
			</h3>
		</div>
		<div class="card-body">
			<div class="modal modal-help" id="storagespacehelp" tabindex="-1" aria-labelledby="storagespacehelp-title" aria-hidden="true">
				<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
					<div class="modal-content dialog-content shadow-sm">
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

			if ($storagedirquota):
				?>
				<table class="table table-hover storage">
					<caption class="sr-only visually-hidden">
						Resource Storage Spaces
					</caption>
					<thead>
						<tr>
							<th scope="col">Location</th>
							<th scope="col" class="text-center">Space<br />Used / Limit</th>
							<th scope="col" class="text-center">Files<br />Used / Limit</th>
							<th scope="col" class="text-center">Last Check</th>
							<th scope="col" class="text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($storagedirquota as $dir):
							$usage = $dir->usage()->orderBy('datetimerecorded', 'desc')->first();
							if (!$usage):
								$usage = new App\Modules\Storage\Models\Usage;
							endif;
							?>
							<tr>
								<td class="left">
									{{ $dir->resourcepath . '/' . $dir->path }}
								</td>
								<td class="text-center">
									@if (!$usage->quota)
										<span class="none text-muted">- / -</span>
									@else
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
												<span class="sr-only visually-hidden"><?php echo $val; ?>%</span>
											</div>
										</div>
									@endif
								</td>
								<td class="text-center">
									@if (!$usage->filequota || $usage->filequota == 1)
										<span class="none text-muted">- / -</span>
									@else
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
												<span class="sr-only visually-hidden"><?php echo $val; ?>%</span>
											</div>
										</div>
									@endif
								</td>
								<td class="text-center">
									@if ($usage->datetimerecorded)
										{{ $usage->datetimerecorded->diffForHumans() }}
									@else
										<span class="none text-muted">-</span>
									@endif
								</td>
								<td class="text-center">
									<a href="#{{ $dir->id }}_dialog"
										class="details updatequota tip"
										data-api="{{ route('api.storage.directories.update', ['id' => $dir->id]) }}"
										data-id="{{ $dir->id }}"
										title="Update usage now"><!--
									--><span class="fa fa-undo updater" aria-hidden="true"></span><!--
									--><span class="spinner-border spinner-border-sm hide" role="status"><span class="sr-only visually-hidden">Loading...</span></span><!--
									--><span class="sr-only visually-hidden">Update usage now</span><!--
								--></a>
								</td>
							</tr>
							<?php
							// Save for easy access later
							//$dir->ago = $ago;
							$sdirs[$dir->id] = $dir;
						endforeach;
						?>
					</tbody>
				</table>

				<p class="alert alert-info">Please allow up to 15 minutes for these numbers to update.</p>
				<?php
			else:
				?>
				<p class="text-center">(No storage spaces found.)</p>
				<?php
			endif;
			?>
		</div>
	</div><!-- / .card -->

	<div class="card mb-3">
		<div class="card-header">
			<div class="row">
				<div class="col col-md-6">
					<h3 class="card-title my-0">
						Storage Alerts
						<a href="#storagealerthelp" data-toggle="modal" class="text-info tip" title="Help">
							<span class="fa fa-question-circle" aria-hidden="true"></span>
							<span class="sr-only visually-hidden">Help</span>
						</a>
					</h3>
				</div>
				<div class="col col-md-6 text-right">
					@if ($user->enabled && ($user->id == auth()->user()->id || auth()->user()->can('manage storage')))
						<a href="#newalert" data-toggle="modal" class="btn btn-default btn-sm accountsbtn" id="create-newalert">
							<span class="fa fa-plus-circle" aria-hidden="true"></span> Create New Alert
						</a>
					@endif
				</div>
			</div>
		</div>
		<div class="card-body">
			<div class="modal modal-help" id="storagealerthelp" tabindex="-1" aria-labelledby="storagealerthelp-title" aria-hidden="true">
				<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
					<div class="modal-content dialog-content shadow-sm">
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
			foreach ($alerts as $not):
				if (!isset($sdirs[$not->storagedirid])):
					$sdirs[$not->storagedirid] = $not->directory;
				endif;

				$dir = $sdirs[$not->storagedirid];

				if ($dir && $dir->resourceid == 64):
					$als[] = $not;
				endif;
			endforeach;

			if (count($als) > 0):
				?>
				<table class="table table-hover storage">
					<caption class="sr-only visually-hidden">Current Storage Alerts</caption>
					<thead>
						<tr>
							<th scope="col">Location</th>
							<th scope="col">Alert Type</th>
							<th scope="col" class="text-center">Threshold</th>
							<th scope="col" class="text-center">Enabled</th>
							<th scope="col">Last Notify</th>
							@if ($user->enabled && ($user->id == auth()->user()->id || auth()->user()->can('manage storage')))
								<th scope="col" colspan="2" class="text-right">Actions</th>
							@endif
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($als as $not):
							if (!isset($sdirs[$not->storagedirid])):
								continue;
							endif;

							$dir = $sdirs[$not->storagedirid];

							if (!$dir):
								continue;
							endif;
							?>
							<tr>
								<td>
									{{ ($dir->storageResource ? $dir->storageResource->path . '/' : '') . $dir->path }}
								</td>
								<td>
									{{ $not->type->name }}
								</td>
								<td class="text-center">
									<?php
									if ($not->type->valuetype == 1):
										// Nothing here
									elseif ($not->type->valuetype == 2):
										echo $not->formattedValue;
									elseif ($not->type->valuetype == 3):
										echo $not->value . '%';
									elseif ($not->type->valuetype == 4):
										echo number_format($not->value);
									endif;
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
									@if (!$not->wasNotified())
										<span class="none">-</span>
									@else
										<time datetime="{{ $not->datetimelastnotify->toDateTimeLocalString() }}">{{ $not->datetimelastnotify->format("m/d/Y") }}</time>
									@endif
								</td>
								@if ($user->enabled && ($user->id == auth()->user()->id || auth()->user()->can('manage storage')))
									<td class="text-right">
										<a href="#not_dialog_{{ $not->id }}"
											data-toggle="modal"
											class="storagealert-edit tip"
											title="{{ trans('global.button.edit') }}"><!--
											--><span class="fa fa-pencil"></span><span class="sr-only visually-hidden">{{ trans('global.button.edit') }}</span><!--
										--></a>
									</td>
									<td class="text-right">
										<a href="#dialog-confirm-delete"
											class="storagealert-confirm-delete delete tip"
											title="{{ trans('global.button.delete') }}"
											data-id="{{ $not->id }}"
											data-api="{{ route('api.storage.notifications.delete', ['id' => $not->id]) }}"
											data-confirm="Are you sure you wish to delete this notification?"><!--
											--><span class="fa fa-trash"></span><span class="sr-only visually-hidden">{{ trans('global.button.delete') }}</span><!--
										--></a>
									</td>
								@endif
							</tr>
							<?php
						endforeach;
						?>
					</tbody>
				</table>

				<?php
				if ($user->enabled && ($user->id == auth()->user()->id || auth()->user()->can('manage storage'))):
					foreach ($als as $not):
						if (!isset($sdirs[$not->storagedirid])):
							continue;
						endif;

						$dir = $sdirs[$not->storagedirid];

						if (!$dir):
							continue;
						endif;
						?>
						<div class="modal modal-help" id="not_dialog_{{ $not->id }}" tabindex="-1" aria-labelledby="not_dialog_{{ $not->id }}-title" aria-hidden="true">
							<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
								<div class="modal-content dialog-content shadow-sm">
									<div class="modal-header">
										<div class="modal-title" id="not_dialog_{{ $not->id }}-title">Storage Alert Details</div>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<form method="post" action="{{ route('api.storage.notifications.update', ['id' => $not->id]) }}">
										<div class="modal-body dialog-body">

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
												<label for="value_{{ $not->id }}" class="col-sm-4">Threshold</label>
												<div class="col-sm-8">
													<?php
													$unit = '';
													$number = '';
													if ($not->type->valuetype == 1):
														// Nothing here
													elseif ($not->type->valuetype == 2):
														$number = $not->formattedValue;
														$unit = 'bytes';
													elseif ($not->type->valuetype == 3):
														$number = $not->value;
														$unit = '%';
													elseif ($not->type->valuetype == 4):
														$number = $not->value;
														$unit = 'files';
													endif;
													?>
													@if ($unit)
													<span class="input-group">
													@endif
														<input type="number" class="form-control" id="value_{{ $not->id }}" value="{{ $number }}" />
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

											<div class="alert alert-danger hide" id="not_dialog_{{ $not->id }}_not_error"></div>
										</div>
										<div class="modal-footer">
											<input type="submit" class="btn btn-success storagealert-edit-save" value="{{ trans('global.save') }}" data-api="{{ route('api.storage.notifications.update', ['id' => $not->id]) }}" id="save_{{ $not->id }}" data-id="{{ $not->id }}" />
										</div>
									</form>
								</div><!-- / .modal-content -->
							</div><!-- / .modal-dialog -->
						</div><!-- / .modal -->
						<?php
					endforeach;
				endif;
			else:
				?>
				<p class="text-muted text-center">No storage alerts found.</p>
				<?php
			endif;
			?>
		</div>
	</div><!-- / .card -->

	<div class="modal modal-help" id="newalert" tabindex="-1" aria-labelledby="newalert-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
			<div class="modal-content dialog-content shadow-sm">
				<div class="modal-header">
					<div class="modal-title" id="newalert-title">New Quota Alert</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form method="post" action="{{ route('site.users.account.section', ['section' => 'quotas']) }}">
					<div class="modal-body dialog-body">
						<p>
							<label for="newalertstorage">Monitor</label>
							<select id="newalertstorage" class="form-control">
								<?php
								foreach ($storagedirs as $storagedir):
									if ($storagedir->resourceid != 64):
										continue;
									endif;
									?>
									<option name="newalertstorage" value="{{ $storagedir->id }}">{{ $storagedir->resourcepath . '/' . $storagedir->path }}</option>
									<?php
								endforeach;
								?>
							</select>
						</p>
						<p>
							by a<br />
							<?php
							$types = App\Modules\Storage\Models\Notification\Type::where('id', '>', 1)->get();
							foreach ($types as $type):
								if ($type->id == 2):
									$type->value = '500 GB';
									$type->unit = '';
								elseif ($type->id == 3):
									$type->value = '80';
									$type->unit = '%';
								elseif ($type->id == 4):
									$type->value = '50000';
									$type->unit = ' files';
								elseif ($type->id == 5):
									$type->value = '80';
									$type->unit = '%';
								endif;
								?>
								<span class="form-check">
									<input type="radio" name="newalert" class="form-check-input" value="{{ $type->id }}" id="newalert-{{ $type->id }}" data-value="{{ $type->value }}" data-unit="{{ $type->unit }}" />
									<label for="newalert-{{ $type->id }}" class="form-check-label">{{ $type->name }}</label>
								</span>
								<?php
							endforeach;
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
					</div>
					<div class="modal-footer">
						<input type="submit" class="btn btn-success storagealert-save" id="newalert-save" value="Create Alert" data-api="{{ route('api.storage.notifications.create') }}" />
					</div>
				</form>
			</div><!-- / .modal-content -->
		</div><!-- / .modal-dialog -->
	</div><!-- / .modal#newalert -->

	<div class="card mb-3">
		<div class="card-header">
			<div class="row">
				<div class="col col-md-6">
					<h3 class="card-title my-0">
						Storage Usage Reports
						<a href="#storageusagehelp" data-toggle="modal" class="text-info tip" title="Help">
							<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only visually-hidden">Help</span>
						</a>
					</h3>
				</div>
				<div class="col col-md-6 text-right">
					@if ($user->enabled && ($user->id == auth()->user()->id || auth()->user()->can('manage storage')))
						<a href="#newreport" data-toggle="modal" class="btn btn-default btn-sm accountsbtn" id="create-newreport">
							<span class="fa fa-plus-circle" aria-hidden="true"></span> Create New Usage Report
						</a>
					@endif
				</div>
			</div>
		</div>
		<div class="card-body">
			<div class="modal modal-help" id="storageusagehelp" tabindex="-1" aria-labelledby="storageusagehelp-title" aria-hidden="true">
				<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
					<div class="modal-content dialog-content shadow-sm">
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

			if (count($storagenotifications) > 0):
				foreach ($storagenotifications as $not):
					if ($not->storagedirquotanotificationtypeid == 1):
					// && $sdirs[$not->storagedirid]->resourceid == 64)
						if (!isset($sdirs[$not->storagedirid])):
							$sdirs[$not->storagedirid] = $not->directory;
						endif;

						if (!$sdirs[$not->storagedirid] || !$sdirs[$not->storagedirid]->storageResource):
							continue;
						endif;

						$storagedirquotanotifications[] = $not;
					endif;
				endforeach;
			endif;

			if (count($storagedirquotanotifications) > 0):
				//$dir = $sdirs[$not->storagedirid];
				?>
				<table class="table table-hover storage">
					<caption class="sr-only visually-hidden">
						Current Storage Usage Reports
					</caption>
					<thead>
						<tr>
							<th scope="col">Location</th>
							<th scope="col">Alert Type</th>
							<th scope="col">Frequency</th>
							<th scope="col" class="text-center">Enabled</th>
							<th scope="col">Next Report</th>
							@if ($user->enabled && ($user->id == auth()->user()->id || auth()->user()->can('manage storage')))
							<th scope="col" colspan="2" class="text-right">Actions</th>
							@endif
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($storagedirquotanotifications as $not):
							if (!isset($sdirs[$not->storagedirid])):
								continue;
							endif;

							$dir = $sdirs[$not->storagedirid];

							if (!$dir):
								continue;
							endif;
							?>
							<tr>
								<td>
									{{ ($dir->storageResource ? $dir->storageResource->path . '/' : '') . $dir->path }}
								</td>
								<td>
									{{ $not->type->name }}
								</td>
								<td>
									Every {{ ($not->periods > 1 ? $not->periods . ' ' . $not->timeperiod->plural : $not->timeperiod->singular) }}
								</td>
								<td class="text-center">
									@if ($not->enabled == 1)
										<span class="badge badge-success">{{ trans('global.yes') }}</span>
									@else
										<span class="badge badge-danger">{{ trans('global.no') }}</span>
									@endif
								</td>
								<td>
									<time datetime="{{ $not->datetimelastnotify->toDateTimeLocalString() }}">{{ $not->wasNotified() ? $not->datetimelastnotify->format('m/d/Y') : trans('global.unknown') }}</time>
								</td>
								@if ($user->enabled && ($user->id == auth()->user()->id || auth()->user()->can('manage storage')))
									<td class="text-right">
										<a href="#not_dialog_{{ $not->id }}"
											data-toggle="modal"
											class="storagealert-edit tip"
											title="Edit usage report"><!--
											--><span class="fa fa-pencil"></span><span class="sr-only visually-hidden">Edit</span><!--
										--></a>
									</td>
									<td class="text-right">
										<a href="#dialog-confirm-delete"
											class="storagealert-confirm-delete delete tip"
											title="{{ trans('global.button.delete') }}"
											data-id="{{ $not->id }}"
											data-api="{{ route('api.storage.notifications.delete', ['id' => $not->id]) }}"
											data-confirm="Are you sure you wish to delete this report?"><!--
											--><span class="fa fa-trash"></span><span class="sr-only visually-hidden">{{ trans('global.button.delete') }}</span><!--
										--></a>
									</td>
								@endif
							</tr>
							<?php
						endforeach;
						?>
					</tbody>
				</table>
				<?php
				if ($user->enabled && ($user->id == auth()->user()->id || auth()->user()->can('manage storage'))):
					foreach ($storagedirquotanotifications as $not):
						if (!isset($sdirs[$not->storagedirid])):
							continue;
						endif;

						$dir = $sdirs[$not->storagedirid];

						if (!$dir):
							continue;
						endif;
						?>
						<div class="modal modal-help" id="not_dialog_{{ $not->id }}" tabindex="-1" aria-labelledby="not_dialog_{{ $not->id }}-title" aria-hidden="true">
							<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
								<div class="modal-content dialog-content shadow-sm">
									<div class="modal-header">
										<div class="modal-title" id="not_dialog_{{ $not->id }}-title">Storage Usage Report Details</div>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<form method="post" action="{{ route('api.storage.notifications.update', ['id' => $not->id]) }}">
										<div class="modal-body dialog-body">
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
														foreach (App\Halcyon\Models\Timeperiod::all() as $period):
															$selected = '';
															if ($period->id == $not->timeperiodid):
																$selected = 'selected="true"';
															endif;
															echo '<option ' . $selected . ' value="' . $period->id . '">' . $period->plural . '</option>';
														endforeach;
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

											<div class="alert alert-danger hide" id="not_dialog_{{ $not->id }}_not_error"></div>
										</div>
										<div class="modal-footer">
											<input type="submit" class="btn btn-success storagealert-edit-save" value="{{ trans('global.save') }}" data-api="{{ route('api.storage.notifications.update', ['id' => $not->id]) }}" id="save_{{ $not->id }}" data-id="{{ $not->id }}" />
										</div>
									</form>
								</div><!-- / .modal-content -->
							</div><!-- / .modal-dialog -->
						</div><!-- / .modal -->
						<?php
					endforeach;
				endif;
			else:
				?>
				<p class="text-muted text-center">No storage usage reports found.</p>
				<?php
			endif;
			?>

			<div class="modal modal-help" id="newreport" tabindex="-1" aria-labelledby="newreport-title" aria-hidden="true">
				<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
					<div class="modal-content dialog-content shadow-sm">
						<div class="modal-header">
							<div class="modal-title" id="newreport-title">New Usage Report</div>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<form method="post" action="{{ route('site.users.account.section', ['section' => 'quotas']) }}">
							<div class="modal-body dialog-body">
			<!-- <div id="newreport" title="New Usage Report" class="dialog dialog-edit" role="dialog" data-api="{{ route('api.storage.notifications.create') }}">
				<form method="post" action="{{ route('site.users.account.section', ['section' => 'quotas']) }}" class="form"> -->
								<div class="form-group row">
									<label for="newreportstorage" class="col-sm-5">Report on</label>
									<div class="col-sm-7">
										<select id="newreportstorage" class="form-control">
											@foreach ($storagedirs as $storagedir)
												<option name="newreportstorage" value="{{ $storagedir->id }}">{{ $storagedir->resourcepath) . '/' . e($storagedir->path }}</option>
											@endforeach
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
											@foreach (App\Halcyon\Models\Timeperiod::all() as $period)
												<option value="{{ $period->id }}">{{ $period->plural }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="alert alert-danger hide" id="newreport_error"></div>
							</div>
							<div class="modal-footer">
								<input type="submit" class="btn btn-success storagealert-save" id="newreport-save" value="Create Report" data-api="{{ route('api.storage.notifications.create') }}" />
							</div>
						</form>
					</div><!-- / .modal-content -->
				</div><!-- / .modal-dialog -->
			</div><!-- / .modal#newreport -->

		</div>
	</div><!-- / .panel -->
</div><!-- / .contentInner -->
