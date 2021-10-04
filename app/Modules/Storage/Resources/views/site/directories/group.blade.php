@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/jquery-timepicker-addon/jquery-ui-timepicker-addon.min.css?v=' . filemtime(public_path() . '/modules/core/vendor/jquery-timepicker-addon/jquery-ui-timepicker-addon.min.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/fancytree/skin-xp/ui.fancytree.css?v=' . filemtime(public_path() . '/modules/core/vendor/fancytree/skin-xp/ui.fancytree.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/jquery-timepicker-addon/jquery-ui-timepicker-addon.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/jquery-timepicker-addon/jquery-ui-timepicker-addon.min.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/fancytree/jquery.fancytree-all.js?v=' . filemtime(public_path() . '/modules/core/vendor/fancytree/jquery.fancytree-all.js')) }}"></script>
<script src="{{ asset('modules/storage/js/site.js?v=' . filemtime(public_path() . '/modules/storage/js/site.js')) }}"></script>
<script>
	$(document).ready(function() {
		$('.updatequota').on('click', function(event) {
			var btn = $(this),
				did = btn.data('id');

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
							alert("An error occurred. Please reload the page and try again");

							btn.find('.fa').removeClass('hide');
							btn.find('.spinner-border').addClass('hide');
						}
					});
				},
				error: function (result) {
					alert("An error occurred. Please reload the page and try again");

					btn.find('.fa').removeClass('hide');
					btn.find('.spinner-border').addClass('hide');
				}
			});
		});
	});
</script>
@endpush

<div class="row">
	<div class="col-md-12">
		<?php
		function get_dir($dirs, $dirhash, $id)
		{
			if (count($dirhash) == 0)
			{
				foreach ($dirs as $d)
				{
					$dirhash[$d->id] = $d;
				}
			}

			if (isset($dirhash[$id]))
			{
				return $dirhash[$id];
			}

			return null;
		}

		$directories = $group->directories;

		$canManage = auth()->user() && auth()->user()->can('manage groups');

		$rows = $directories->filter(function($item) use ($canManage)
		{
			//if ($canManage)
			//{
				//return $item->parentstoragedirid == 0;
			//}
			return $item->parentstoragedirid == 0 && $item->storageresourceid == 4;
		});

		if (count($rows)):
			foreach ($rows as $row):
			?>
			<div class="card panel panel-default">
				<div class="card-header panel-heading">
					{{ $row->storageResource->name }}
				</div>
				<div class="card-body panel-body">

					<div id="new_dir_dialog" title="Add new directory" class="dialog">
						<fieldset class="mb-1">
							<div class="form-group">
								<label for="new_dir_type">Name:</label>
								<span class="input-group">
									<span class="input-group-addon input-group-prepend"><span class="input-group-text">{{ $row->storageResource->path }}/<span id="new_dir_path"></span></span></span>
									<input type="text" id="new_dir_input" name="new_dir_input" class="form-control" />
								</span>
							</div>
							<div class="form-group">
								<label for="new_dir_type">Type:</label>
								<select id="new_dir_type" class="form-control">
									<option value="normal">Group Shared</option>
									<option value="autouserread">Auto User - Group Readable</option>
									<option value="autouserreadwrite">Auto User - Group Readable & Writeable</option>
									<option value="autouserprivate">Auto User - Private</option>
									<option value="user">User Owned - Group Readable</option>
									<option value="userwrite">User Owned - Group Writeable</option>
									<option value="userprivate">User Owned - Private</option>
								</select>
							</div>
							<fieldset>
								<legend>Quota:</legend>

								<div class="form-group">
									<div class="form-check form-inline">
										<input type="radio" name="usequota" value="parent" class="form-check-input" checked="true" id="share_radio" />
										<label class="form-check-label" for="share_radio">Share with parent quota (<span id="new_dir_quota_available"></span>)</label>
									</div>
								</div>
								<div class="form-group">
									<div class="form-check form-inline">
										<input type="radio" name="usequota" id="deduct_radio" class="form-check-input" value="deduct" />
										<label class="form-check-label" for="deduct_radio">Deduct from parent quota (<span id="new_dir_quota_available2"></span>):</label>

										<input type="text" id="new_dir_quota_deduct" class="form-control" size="3" />
										<?php
										$bucket = null;
										foreach ($group->storageBuckets as $bucket)
										{
											if ($bucket['resourceid'] == $row->storageResource->parentresourceid)
											{
												break;
											}
										}

										$style = '';
										$disabled = '';
										if ($bucket && $bucket['unallocatedbytes'] == 0)
										{
											$disabled = 'disabled="true"';
											$style = 'color:gray';
										}
										?>
									</div>
								</div>
								<div class="form-group">
									<div class="form-check form-inline">
										<input <?php echo $disabled; ?> type="radio" name="usequota" value="unalloc" id="unalloc_radio" class="form-check-input" />
										<label class="form-check-label" for="unalloc_radio">
											<span style="<?php echo $style; ?>" id="unalloc_span">
												Deduct from unallocated space (<span name="unallocated"><?php echo App\Halcyon\Utility\Number::formatBytes($bucket['unallocatedbytes'], 1); ?></span>):
											</span>
										</label>
										<input <?php echo $disabled; ?> type="text" id="new_dir_quota_unalloc" class="form-control" size="3" />
									</div>
								</div>
							</fieldset>
							<div class="form-group">
								<label for="new_dir_unixgroup_select">Access Unix Group:</label>
								<select id="new_dir_unixgroup_select" class="form-control">
									<option value="">(Select Unix Group)</option>
									<?php foreach ($group->unixgroups as $unixgroup) { ?>
										<option value="<?php echo $unixgroup->id; ?>" data-api="{{ route('api.unixgroups.read', ['id' => $unixgroup->id]) }}"><?php echo $unixgroup->longname; ?></option>
									<?php } ?>
								</select>
								<select id="new_dir_unixgroup_select_decoy" class="form-control hidden">
								</select>
							</div>
							<div id="new_dir_autouserunixgroup_row" class="form-group hidden">
								<label for="new_dir_autouserunixgroup_select">Populating Unix Group</label>
								<select id="new_dir_autouserunixgroup_select" class="form-control">
									<option value="">(Select Unix Group)</option>
									<?php foreach ($group->unixgroups as $unixgroup) { ?>
										<option value="<?php echo $unixgroup->id; ?>"><?php echo $unixgroup->longname; ?></option>
									<?php } ?>
								</select>
							</div>
							<div id="new_dir_user_row" class="form-group hidden">
								<label for="new_dir_user_select">User:</label>
								<select id="new_dir_user_select" class="form-control">
									<option value="">(Select User)</option>
								</select>
							</div>
						</fieldset>

						<div id="new_dir_error" class="alert alert-danger hide"></div>

						<div class="dialog-footer text-right">
							<button id="new_dir" class="btn btn-success" data-resource="{{ $row->storageResource->parentresourceid }}" data-api="{{ route('api.storage.directories.create') }}">
								<span id="new_dir_img" class="icon-plus"></span> Create directory
							</button>
						</div>
					</div>

					<table id="tree{{ $row->id }}" class="tree">
						<thead>
							<tr>
								<th scope="col">Directory</th>
								<th scope="col" class="quota">Current Quota</th>
								<th scope="col" class="quota">Future Quota</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
						<!-- <tfoot>
							<tr>
								<td colspan="3">
									<button class="btn btn-sm btn-secondary"><span class="icon-plus"></span> {{ trans('global.button.create') }}</button>
								</td>
							</tr>
						</tfoot> -->
					</table>

					<input type="hidden" id="selected_dir" />
					<input type="hidden" id="selected_dir_unixgroup" />

					<script type="application/json" id="tree{{ $row->id }}-data"><?php
					$data = array($row->tree());
					echo json_encode($data);
					?></script>

					<?php
					$dirhash = array();
					$configuring = array();
					$removing = array();
					//$directories = $group->directories;
					foreach ($row->nested() as $dir)
					{
						$did = $dir->id;

						$disabled = '';
						if (in_array($dir->id, $removing))
						{
							$disabled = 'disabled="disabled"';
						}
						?>
						<div id="{{ $did }}_dialog" data-id="{{ $did }}" title="{{ $dir->storageResource->path . '/' . $dir->path }}" class="dialog">
							<form method="post">
							<?php if ($dir->quotaproblem == 1 && $dir->bytes) { ?>
								<div class="row mb-3">
									<div class="col-md-4">
										Desired quota
									</div>
									<div class="col-md-8">
										<?php echo App\Halcyon\Utility\Number::formatBytes($dir->bytes, 1); ?>
									</div>
								</div>
								<div class="row mb-3">
									<div class="col-md-4">
										Actual quota <span class="icon-warning" data-tip="Storage space is over-allocated. Quotas reduced until allocation balanced."><span class="sr-only">Storage space is over-allocated. Quotas reduced until allocation balanced.</span></span>
									</div>
									<div class="col-md-8">
										<?php echo App\Halcyon\Utility\Number::formatBytes($dir->bytes, 1); ?>
									</div>
								</div><!--/ .row -->
							<?php } else { ?>
								<div class="row mb-3">
									<div class="col-md-4">
										<label for="{{ $dir->id }}_quota_input">{{ trans('storage::storage.quota') }} (bytes)</label>
									</div>
									<div class="col-md-8">
										@if ($dir->bytes)
											<?php
											$value = App\Halcyon\Utility\Number::formatBytes($dir->bytes, 1);
											?>
											@if (auth()->user()->can('manage storage'))
												<input type="text" id="{{ $dir->id }}_quota_input" class="form-control" value="{{ $dir->bytes ? $value : '' }}" />
											@else
												{{ $value }}
												<input type="hidden" id="{{ $dir->id }}_quota_input" class="form-control" value="{{ $dir->bytes ? $value : '' }}" />
											@endif
										@else
											-
											<input type="hidden" id="{{ $dir->id }}_quota_input" class="form-control" value="{{ $dir->bytes ? $value : '' }}" />
										@endif
									</div>
								</div><!--/ .row -->
							<?php } ?>
							<div class="row mb-3">
								<div class="col-md-4">
									<label for="{{ $dir->id }}_unixgroup_select">Access Unix Group</label>
								</div>
								<div class="col-md-8">
									<select id="{{ $dir->id }}_unixgroup_select" class="form-control">
										<option value="0">{{ trans('global.none') }}</option>
										<?php
										foreach ($dir->group->unixgroups as $unixgroup)
										{
											$selected = '';
											if (isset($dir->unixgroup->id) && $unixgroup->id == $dir->unixgroup->id)
											{
												$selected = 'selected="selected"';
											}

											echo '<option ' . $selected . ' value="' . $unixgroup->id . '">' . $unixgroup->longname . '</option>';
										}
										?>
									</select>
								</div>
							</div><!--/ .row -->
							<?php if ($dir->autouser) { ?>
								<div class="row mb-3">
									<div class="col-md-4">
										<label for="{{ $dir->id }}_autouserunixgroup_select">Populating Unix Group</label>
									</div>
									<div class="col-md-8">
										<select id="{{ $dir->id }}_autouserunixgroup_select" class="form-control">
											<?php foreach ($dir->group->unixgroups as $unixgroup) { ?>
												<?php
												$selected = '';
												if ($dir->autouserunixgroupid && $unixgroup->id == $dir->autouserunixgroupid)
												{
													$selected = 'selected="selected"';
												}
												?>
												<option <?php echo $selected; ?> value="<?php echo $unixgroup->id; ?>"><?php echo $unixgroup->longname; ?></option>
											<?php } ?>
										</select>
									</div>
								</div><!--/ .row -->
							<?php } ?>
							<?php if ($dir->owner && $dir->owner->name != 'root') { ?>
								<div class="row mb-3">
									<div class="col-md-4">
										<label for="{{ $dir->id }}_owner_name">Owner</label>
									</div>
									<div class="col-md-8">
										<input type="text" id="{{ $dir->id }}_owner_name" class="form-control-plaintext" value="{{ $dir->owner->name }}" />
									</div>
								</div><!--/ .row -->

								<div class="row mb-3">
									<div class="col-md-4">
										<label for="{{ $dir->id }}_dir_type_select">Type</label>
									</div>
									<div class="col-md-8">
										<select id="{{ $dir->id }}_dir_type_select" class="form-control">
											<?php if ($dir->unixPermissions->group->write) { ?>
												<option selected="selected" value="userwrite">User Owned - Group Writable</option>
												<option value="user">User Owned - Group Readable</option>
												<option value="userprivate">User Owned - Private</option>
											<?php } elseif ($dir->unixPermissions->group->read) { ?>
												<option selected="selected" value="user">User Owned - Group Readable</option>
												<option value="userwrite">User Owned - Group Writable</option>
												<option value="userprivate">User Owned - Private</option>
											<?php } else { ?>
												<option value="user">User Owned - Group Readable</option>
												<option value="userwrite">User Owned - Group Writable</option>
												<option selected="selected" value="userprivate">User Owned - Private</option>
											<?php } ?>
										</select>
									</div>
								</div><!--/ .row -->
							<?php } ?>
							<?php if ($dir->autouser) { ?>
								<div class="row mb-3">
									<div class="col-md-4">
										<label for="{{ $dir->id }}_dir_type_select">Auto Populate User Default</label>
									</div>
									<div class="col-md-8">
										<select id="{{ $dir->id }}_dir_type_select" class="form-control">
											<option value="autouser"<?php if ($dir->autouser == '1') { ?> selected="selected"<?php } ?>>Auto User - Group Readable</option>
											<option value="autouserreadwrite"<?php if ($dir->autouser == '3') { ?> selected="selected"<?php } ?>>Auto User - Group Readable Writable</option>
											<option value="autouserprivate"<?php if ($dir->autouser == '2') { ?> selected="selected"<?php } ?>>Auto User - Private</option>
										</select>
									</div>
								</div><!--/ .row -->
							<?php } ?>
							<?php
							$child_dirs = array();
							$check = array();

							array_push($check, $dir->id);

							while (count($check) > 0)
							{
								$child = null;
								$child = get_dir($directories, $dirhash, array_pop($check));

								if (!$child)
								{
									break;
								}

								if ($child->unixPermissions->other->read || $child->id == $dir->id)
								{
									array_push($child_dirs, $child);

									foreach ($child->children as $d)
									{
										array_push($check, $d->id);
									}
								}
							}

							// Find bottle necks
							$bottle_dirs = array();

							if ($dir->parentstoragedirid)
							{
								$bottle_dir = get_dir($directories, $dirhash, $dir->parentstoragedirid);

								while (1)
								{
									if (!$bottle_dir)
									{
										break;
									}

									if (!$bottle_dir->unixPermissions->other->read)
									{
										array_push($bottle_dirs, $bottle_dir->unixgroup->longname);
									}

									if (!$bottle_dir->parentstoragedirid)
									{
										break;
									}

									$bottle_dir = get_dir($directories, $dirhash, $bottle_dir->parentstoragedirid);
								}
							}

							$bottle_dirs_string = 'Public';
							if (count($bottle_dirs) > 0)
							{
								$bottle_dirs_string = implode(' + ', $bottle_dirs);
							}

							if (count($child_dirs) > 0 && $dir->parentstoragedirid) { ?>
								<div class="row mb-3">
									<div class="col-md-4">
										<label for="<?php echo $dir->id; ?>_other_read_box" class="form-check-label">Read access for <?php echo $bottle_dirs_string; ?></label>
									</div>
									<div class="col-md-8">
										<span class="form-check">
										<?php if ($dir->unixPermissions->other->read) { ?>
											<input type="checkbox" id="<?php echo $dir->id; ?>_other_read_box" class="form-check-input" checked="checked" />
											<span id="<?php echo $dir->id; ?>_other_read_span" class="hide">{{ trans('global.yes') }}</span> to directories:
										<?php } else { ?>
											<input type="checkbox" id="<?php echo $dir->id; ?>_other_read_box" class="form-check-input" />
											<span id="<?php echo $dir->id; ?>_other_read_span" class="hide">{{ trans('global.no') }}</span> to directories:
										<?php } ?>
										</span>

										<ul>
										<?php foreach ($child_dirs as $child) { ?>
											<li>{{ $child->path }}</li>
										<?php } ?>
										</ul>
									</div>
								</div>
							<?php } else if (!$dir->parentstoragedirid) { ?>
								<div class="row mb-3">
									<div class="col-md-4">
										Public read access?
									</div>
									<div class="col-md-4">
										<span class="form-check">
											<input type="radio" name="{{ $dir->id }}_other_read_box" id="{{ $dir->id }}_other_read_box1" <?php if ($dir->unixPermissions->other->read) { ?>checked="checked"<?php } ?> class="form-check-input" />
											<label class="form-check-label" for="{{ $dir->id }}_other_read_box" id="{{ $dir->id }}_other_read_span">{{ trans('global.yes') }}</label>
										</span>
									</div>
									<div class="col-md-4">
										<span class="form-check">
											<input type="radio" name="{{ $dir->id }}_other_read_box" id="{{ $dir->id }}_other_read_box0" <?php if (!$dir->unixPermissions->other->read) { ?>checked="checked"<?php } ?> class="form-check-input" />
											<label class="form-check-label" for="{{ $dir->id }}_other_read_box" id="{{ $dir->id }}_other_read_span">{{ trans('global.no') }}</label>
										</span>
									</div>
								</div>
							<?php } ?>

							<div class="row mb-3">
								<div class="col-md-4">
									<p class="card-title">{{ trans('storage::storage.permissions') }}</p>
								</div>
								<div class="col-md-8">
									<table class="table table-bordered">
										<caption class="sr-only">{{ trans('storage::storage.permissions') }}</caption>
										<thead>
											<tr>
												<th scope="col">{{ trans('storage::storage.group') }}</th>
												<th scope="col" class="text-center">{{ trans('storage::storage.permission.read') }}</th>
												<th scope="col" class="text-center">{{ trans('storage::storage.permission.write') }}</th>
											</tr>
										</thead>
										<tbody>
										<?php
										$childs = array();

										$highest_read = $dir->id;
										$can_read = true;

										if ($parent = get_dir($directories, $dirhash, $dir->id))
										{
											$p = $parent->toArray();
											$p['permissions'] = json_decode(json_encode($parent->unixPermissions), true);
											$p['unixgroup'] = $parent->unixgroup ? $parent->unixgroup->toArray() : array('longname' => '');
											$childs[] = $p;
										}

										if ($dir->parentstoragedirid)
										{
											do
											{
												if (!$parent)
												{
													break;
												}
												$parent = get_dir($directories, $dirhash, $parent->parentstoragedirid);
												//array_push($childs, $parent);

												if ($parent->unixPermissions->other->read && $can_read)
												{
													$highest_read = $parent['id'];
												}
												else
												{
													$can_read = false;
												}
											}
											while ($parent->parentstoragedirid);
										}

										$highest = array();
										$highest['unixgroup'] = array('longname' => $bottle_dirs_string);
										if ($dir->unixPermissions->other->read)
										{
											$highest['permissions'] = array('group' => array('write' => 0, 'read' => 1));
										}
										else
										{
											$highest['permissions'] = array('group' => array('write' => 0, 'read' => 0));
										}

										$childs[] = $highest;

										if ($bottle_dirs_string != 'Public')
										{
											$public = array();
											$public['unixgroup'] = array('longname' => 'Public');

											if ($parent['id'] == $highest_read && $can_read)
											{
												$public['permissions'] = array('group' => array('write' => 0, 'read' => 1));
											}
											else
											{
												$public['permissions'] = array('group' => array('write' => 0, 'read' => 0));
											}

											$childs[] = $public;
										}

										foreach ($childs as $child)
										{
											?>
											<tr>
												<td>
													{{ $child['unixgroup']['longname'] }}
												</td>
												<td class="text-center">
													@if ($child['permissions']['group']['read'])
														<span class="fa fa-check text-success success dirperm"><span class="sr-only">{{ trans('global.yes') }}</span></span>
													@else
														<span class="fa fa-times text-danger failed dirperm"><span class="sr-only">{{ trans('global.no') }}</span></span>
													@endif
												</td>
												<td class="text-center">
													@if ($child['permissions']['group']['write'])
														<span class="fa fa-check text-success success dirperm"><span class="sr-only">{{ trans('global.yes') }}</span></span>
													@else
														<span class="fa fa-times text-danger failed dirperm"><span class="sr-only">{{ trans('global.no') }}</span></span>
													@endif
												</td>
											</tr>
											<?php
										}
										?>
										</tbody>
										@if (auth()->user()->can('manage storage') && $dir->children()->count() == 0)
											<tfoot>
												<tr>
													<td colspan="3" class="text-center">
														<button <?php echo $disabled; ?> id="{{ $dir->id }}_edit_button"
															class="btn btn-sm btn-secondary permissions-reset"
															data-api="{{ route('api.storage.directories.update', ['id' => $dir->id]) }}"
															data-confirm="This will reset permissions on all files within {{ $dir->path }}. This may take some time to complete. Proceed?"
															data-dir="{{ $dir->id }}"
															data-path="{{ $dir->path }}">{{ trans('storage::storage.fix permissions') }}</button>
													</td>
												</tr>
											</tfoot>
										@endif
									</table>
								</div>
							</div><!--/ .row -->

							@if (count($dir->futurequotas) > 0)
								<div class="row mb-3">
									<div class="col-md-4">
										{{ trans('storage::storage.future quota') }}
									</div>
									<div class="col-md-8">
										<table class="table table-hover">
											<caption class="sr-only">{{ trans('storage::storage.future quota') }}</caption>
											<thead>
												<tr>
													<th scope="col">Date</th>
													<th scope="col">{{ trans('storage::storage.quota') }}</th>
												</tr>
											</thead>
											<tbody>
												@foreach ($dir->futurequotas as $change)
													<tr>
														<td>{{ date('M d, Y', strtotime($change['time'])) }}</td>
														<td>{{ App\Halcyon\Utility\Number::formatBytes($change['quota'], 1) }}</td>
													</tr>
												@endforeach
											</tbody>
										</table>
									</div>
								</div><!--/ .row -->
							@endif

							@if (auth()->user()->can('manage storage'))
							<div class="row mb-3">
								<div class="col-md-4">
									{{ trans('storage::storage.unallocated space') }}
								</div>
								<div class="col-md-8">
									<span name="unallocated"{!! $bucket['unallocatedbytes'] < 0 ? ' class="text-danger"' : '' !!}><?php echo App\Halcyon\Utility\Number::formatBytes($bucket['unallocatedbytes'], 1); ?></span> / <span name="totalbytes"><?php echo App\Halcyon\Utility\Number::formatBytes($bucket['totalbytes'], 1); ?></span>
									<?php
									if ($dir->bytes || (!$dir->bytes && !$dir->parentstoragedirid && $bucket['unallocatedbytes'] != 0))
									{
										$cls = '';
										if ($bucket['unallocatedbytes'] == 0)
										{
											$cls = ' hide';
										}
										if ($bucket['unallocatedbytes'] < 0 && $row->bytes != 0)
										{
											$dir->quotaproblem = 1;
										}

										$dir->realquota = $bucket['totalbytes'] ? $dir->bytes - round((($dir->bytes / $bucket['totalbytes']) * -$bucket['unallocatedbytes'])) : 0;

										if ($dir->quotaproblem == 1 && $dir->bytes && $dir->realquota < $dir->bytes)
										{
											if (-$bucket['unallocatedbytes'] < $dir->bytes)
											{
												?>
												<span class="badge badge-warning">over-allocated</span>
												<button id="{{ $dir->id }}_quota_upa" class="btn tip text-danger quota_upa<?php echo $cls; ?>" data-dir="{{ $dir->id }}" data-api="{{ route('api.storage.directories.update', ['id' => $dir->id]) }}" title="{{ trans('storage::storage.remove overallocated') }}">
													<span id="{{ $dir->id }}_quota_up" class="fa fa-arrow-down" aria-hidden="true"></span><span class="sr-only">{{ trans('storage::storage.remove overallocated') }}</span>
												</button>
												<?php
											}
										}
										else
										{
											?>
											<button id="{{ $dir->id }}_quota_upa" class="btn text-info tip quota_upa<?php echo $cls; ?>" data-dir="{{ $dir->id }}" data-api="{{ route('api.storage.directories.update', ['id' => $dir->id]) }}" title="{{ trans('storage::storage.distribute remaining') }}">
												<span id="{{ $dir->id }}_quota_up" class="fa fa-arrow-up" aria-hidden="true"></span><span class="sr-only">{{ trans('storage::storage.distribute remaining') }}</span>
											</button>
											<?php
										}
									}
									?>
								</div>
							</div><!--/ .row -->
							@endif

							<div class="alert alert-danger hide" id="{{ $dir->id }}_error"></div>

							<div class="dialog-footer">
								<div class="row">
									<div class="col-md-6">
										@if ($dir->children()->count() == 0)
											@if (in_array($dir->id, $removing) || in_array($dir->id, $configuring))
												<p>Delete Disabled - Operations Pending</p>
											@else
												<button data-api="{{ route('api.storage.directories.delete', ['id' => $dir->id]) }}"
													class="btn btn-danger dir-delete"
													data-confirm="Are you sure you want to delete {{ $dir->path }}? All contents will be deleted!"
													data-dir="{{ $dir->id }}"
													data-path="{{ $dir->path }}">
													{{ trans('global.button.delete') }}
												</button>
											@endif
										@endif
									</div>
									<div class="col-md-6 text-right">
										<input disabled="disabled" id="{{ $dir->id }}_save_button" class="btn btn-success unixgroup-edit" data-dir="{{ $dir->id }}" data-api="{{ route('api.storage.directories.update', ['id' => $dir->id]) }}" type="button" value="{{ trans('global.button.save') }}" />
									</div>
								</div><!--/ .row -->
							</div>
							@csrf
							</form>
						</div><!-- / #<?php echo $did; ?>_dialog -->
					<?php } ?>
					</div>
					<div class="card-footer">
						<div class="row">
							<?php
							$usage = $row->usage()->orderBy('datetimerecorded', 'desc')->first();
							if (!$usage)
							{
								$usage = new App\Modules\Storage\Models\Usage;
							}
							?>
							<div class="col-md-4 text-center">
								@if (!$usage->quota)
									<span class="none text-muted">- / -</span>
								@else
									<?php
									$val = $usage->quota ? round(($usage->space / $usage->quota) * 100, 1) : 0;

									$cls = 'bg-success';
									$cls = $val > 50 ? 'bg-info' : $cls;
									$cls = $val > 70 ? 'bg-warning' : $cls;
									$cls = $val > 90 ? 'bg-danger' : $cls;

									echo App\Halcyon\Utility\Number::formatBytes($usage->space, 1); ?> / <?php echo App\Halcyon\Utility\Number::formatBytes($usage->quota, 1);
									?>
									<div class="progress" style="height: 3px">
										<div class="progress-bar <?php echo $cls; ?>" role="progressbar" style="width: <?php echo $val; ?>%;" aria-valuenow="<?php echo $val; ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php echo $val; ?>% space used">
											<span class="sr-only"><?php echo $val; ?>%</span>
										</div>
									</div>
								@endif
							</div>
							<div class="col-md-7 text-right">
								@if ($usage->datetimerecorded)
									Last checked: {{ $usage->datetimerecorded->diffForHumans() }}
								@else
									Never checked: <span class="none text-muted">-</span>
								@endif
							</div>
							<div class="col-md-1 text-right">
								<a href="#{{ $dir->id }}_dialog" class="details updatequota tip" data-api="{{ route('api.storage.directories.update', ['id' => $dir->id]) }}" data-id="{{ $dir->id }}" title="Update usage now"><!--
								--><span class="fa fa-undo updater" aria-hidden="true"></span><!--
								--><span class="spinner-border spinner-border-sm hide" role="status"><span class="sr-only">Loading...</span></span><!--
								--><span class="sr-only">Update usage now</span><!--
							--></a>
							</div>
						</div>
					</div><!-- / .panel-body -->
				</div><!-- / .panel -->
				<?php
			endforeach;
			?>

			<div class="card panel panel-default">
				<div class="card-header panel-heading">
					@if (auth()->user()->can('manage storage'))
					<div class="row">
						<div class="col-md-6">
					@endif
							{{ trans('storage::storage.history') }}
					@if (auth()->user()->can('manage storage'))
						</div>
						<div class="col-md-6 text-right">
							<a href="#dialog-sell" id="space-sell" class="btn btn-sm btn-secondary dialog-btn icon-dollar-sign">{{ trans('storage::storage.sell space') }}</a>
							<a href="#dialog-loan" id="space-loan" class="btn btn-sm btn-secondary dialog-btn icon-shuffle">{{ trans('storage::storage.loan space') }}</a>
						</div>
					</div>
					@endif
				</div>
				<div class="card-body panel-body">
					<?php
					$history = array();
					foreach ($group->purchases()->withTrashed()->get() as $purchase):
						$history[$purchase->datetimestart->timestamp] = $purchase;
					endforeach;

					foreach ($group->loans()->withTrashed()->get() as $loan):
						$history[$loan->datetimestart->timestamp] = $loan;
					endforeach;

					ksort($history);

					$total = 0;
					foreach ($history as $item):
						if ($item->hasEnded()):
							continue;
						endif;

						$total = ($item->bytes > 0 ? $total + $item->bytes : $total - abs($item->bytes));
						$item->total = $total;
					endforeach;

					$history = array_reverse($history);
					?>
					@if (count($history))
						<table class="table table-hover">
							<caption class="sr-only">{{ trans('storage::storage.history') }}</caption>
							<thead>
								<tr>
									<th scope="col">{{ trans('storage::storage.type') }}</th>
									<th scope="col">{{ trans('storage::storage.source') }}</th>
									<th scope="col">{{ trans('storage::storage.start') }}</th>
									<th scope="col">{{ trans('storage::storage.end') }}</th>
									<th scope="col" class="text-right">{{ trans('storage::storage.amount') }}</th>
									<th scope="col" class="text-right">{{ trans('storage::storage.total') }}</th>
									@if (auth()->user()->can('admin storage'))
										<th scope="col" colspan="2" class="text-right">{{ trans('storage::storage.options') }}</th>
									@elseif (auth()->user()->can('manage storage'))
										<th scope="col" class="text-right">{{ trans('storage::storage.options') }}</th>
									@endif
								</tr>
							</thead>
							<tbody>
								@foreach ($history as $item)
									<tr class="{{ $item->type . ($item->hasEnded() ? ' trashed' : '') }}">
										<td>
											@if ($item->bytes > 0)
												<span class="badge badge-success">{{ $item->type }}</span>
											@else
												<span class="badge badge-danger">{{ $item->type }}</span>
											@endif
										</td>
										<td>
											@if ($item->type == 'loan')
												@if ($item->lendergroupid >= 0)
													{{ $item->lender ? $item->lender->name : trans('global.unknown') }}
												@else
													{{ config('app.name') }}
												@endif
											@else
												@if ($item->sellergroupid >= 0)
													{{ $item->seller ? $item->seller->name : trans('global.unknown') }}
												@else
													{{ config('app.name') }}
												@endif
											@endif
										</td>
										<td>
											<time datetime="{{ $item->datetimestart }}">{{ $item->datetimestart->format('Y-m-d') }}</time>
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
												-
											@endif
										</td>
										<td class="text-right">
											@if ($item->hasEnded())
												<del class="decrease text-warning">{!! ($item->bytes > 0 ? '+ ' : '- ') . App\Halcyon\Utility\Number::formatBytes(abs($item->bytes), 1) !!}</del>
											@else
												{!! ($item->bytes > 0 ? '<span class="increase text-success">+ ' : '<span class="decrease text-danger">- ') . App\Halcyon\Utility\Number::formatBytes(abs($item->bytes), 1) . '</span>' !!}
											@endif
										</td>
										<td class="text-right">
											{{ App\Halcyon\Utility\Number::formatBytes($item->total, 1) }}
										</td>
										@if (auth()->user()->can('manage storage'))
										<td class="text-right">
											<a href="#dialog-edit-{{ $item->type . $item->id }}" class="btn btn-sm dialog-btn"
												data-api="{{ route('api.storage.' . ($item->type == 'loan' ? 'loans' : 'purchases'). '.update', ['id' => $item->id]) }}"
												data-id="{{ $item->id }}">
												<span class="fa fa-pencil" aria-hidden="true"></span><span class="sr-only">{{ trans('global.button.edit') }}</span>
											</a>

											<?php
											$t = $item->type;
											?>
											<div class="dialog" id="dialog-edit-{{ $t . $item->id }}" title="{{ trans('storage::storage.edit ' . $t) }}">
												<form method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.storage.' . ($item->type == 'loan' ? 'loans' : 'purchases'). '.update', ['id' => $item->id]) }}">
													<div class="form-group">
														<label for="{{ $t }}-bytes{{ $item->id }}">{{ trans('storage::storage.amount') }} <span class="required">*</span></label>
														<input type="text" class="form-control bytes" size="4" id="{{ $t }}-bytes{{ $item->id }}" name="bytes" required pattern="[0-9]{1,10}\s?[PTGMKB]{1,2}" value="{{ App\Halcyon\Utility\Number::formatBytes(abs($item->bytes)) }}" />
														<span class="form-text text-muted">{{ trans('storage::storage.quota desc') }}</span>
													</div>

													<div class="row">
														<div class="col-md-6">
															<div class="form-group">
																<label for="{{ $t }}-datetimestart{{ $item->id }}">{{ trans('queues::queues.start') }} <span class="required">*</span></label>
																<input type="text" name="datetimestart" class="form-control datetime" id="{{ $t }}-datetimestart{{ $item->id }}" required value="{{ $item->datetimestart->toDateTimeString() }}" />
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group">
																<label for="{{ $t }}-datetimestop{{ $item->id }}">{{ trans('queues::queues.end') }}</label>
																<input type="text" name="datetimestop" class="form-control datetime" id="{{ $t }}-datetimestop{{ $item->id }}" value="{{ $item->hasEnd() ? $item->datetimestop->toDateTimeString() : '' }}" <?php if ($t == 'purchase') { echo 'disabled="disabled"'; } ?> placeholder="{{ trans('storage::storage.end of life') }}" />
															</div>
														</div>
													</div>

													@if ($t == 'loan')
													<div class="form-group">
														<label for="{{ $t }}-lendergroup{{ $item->id }}">{{ trans('storage::storage.lender') }} <span class="required">*</span></label>
														<select name="lendergroupid" id="{{ $t }}-lendergroup{{ $item->id }}"
															class="form-control form-group-storage"
															data-api="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s">
															<option value="0">{{ trans('storage::storage.select group') }}</option>
															@if ($item->lendergroupid == -1)
															<option value="-1" selected="selected">{{ trans('storage::storage.org owned') }}</option>
															@else
															<option value="{{ $item->lendergroupid }}">{{ $item->lender->name }}</option>
															@endif
														</select>
													</div>
													@else
													<div class="form-group">
														<label for="{{ $t }}-sellergroup{{ $item->id }}">{{ $t == 'loan' ? trans('storage::storage.lender') : trans('storage::storage.seller') }} <span class="required">*</span></label>
														<select name="sellergroupid" id="{{ $t }}-sellergroup{{ $item->id }}"
															class="form-control form-group-storage"
															data-api="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s">
															<option value="0">{{ trans('storage::storage.select group') }}</option>
															@if ($item->sellergroupid == -1)
															<option value="-1" selected="selected">{{ trans('storage::storage.org owned') }}</option>
															@else
															<option value="{{ $item->sellergroupid }}">{{ $item->seller->name }}</option>
															@endif
														</select>
													</div>
													@endif

													<div class="form-group">
														<label for="{{ $t }}-group{{ $item->id }}">{{ $t == 'loan' ? trans('storage::storage.loan to') : trans('storage::storage.sell to') }} <span class="required">*</span></label>
														<select name="groupid" id="{{ $t }}-group{{ $item->id }}"
															class="form-control form-group-storage"
															data-update="{{ $t }}-storage"
															data-api="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s">
															@if ($item->groupid == -1)
															<option value="-1" selected="selected">{{ trans('storage::storage.org owned') }}</option>
															@else
															<option value="{{ $item->groupid }}">{{ $item->group->name }}</option>
															@endif
														</select>
													</div>

													<div class="form-group">
														<label for="{{ $t }}-comment">{{ trans('storage::storage.comment') }}</label>
														<textarea id="{{ $t }}-comment" name="comment" class="form-control" maxlength="2000" rows="3" cols="40">{{ $item->comment }}</textarea>
													</div>

													<div id="error_{{ $t }}" class="alert alert-danger hide"></div>

													<div class="dialog-footer text-right">
														<input type="submit" class="btn btn-success dialog-submit" value="{{ trans('global.button.update') }}" data-id="{{ $item->id }}" data-type="{{ $t }}" data-success="{{ trans('queues::queues.item updated') }}" />
													</div>

													<input type="hidden" name="resourceid" value="{{ $item->resourceid }}" />
													<input type="hidden" name="id" value="{{ $item->id }}" />
													@csrf
												</form>
											</div>
										</td>
										@if (auth()->user()->can('admin storage'))
										<td class="text-right">
											<button class="btn btn-sm text-danger storage-delete"
												data-confirm="{{ trans('global.confirm delete') }}"
												data-api="{{ route('api.storage.' . ($item->type == 'loan' ? 'loans' : 'purchases'). '.delete', ['id' => $item->id]) }}"
												data-id="{{ $item->id }}">
												<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.button.delete') }}</span>
											</button>
										</td>
										@endif
										@endif
									</tr>
								@endforeach
							</tbody>
						</table>
					@else
						<p class="text-center text-muted">{{ trans('global.none') }}</p>
					@endif

					@if (auth()->user()->can('manage storage'))
					<div class="dialog" id="dialog-sell" title="{{ trans('storage::storage.sell space') }}">
						<form method="post" action="{{ route('admin.storage.store') }}" data-api="{{ route('api.storage.purchases.create') }}">
							<div class="form-group">
								<label for="sell-bytes">{{ trans('storage::storage.amount') }} <span class="required">*</span></label>
								<input type="text" class="form-control bytes" size="4" id="sell-bytes" name="bytes" required pattern="[0-9]{1,10}\s?[PTGMKB]{1,2}" value="" />
								<span class="form-text text-muted">{{ trans('storage::storage.quota desc') }}</span>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="sell-datetimestart">{{ trans('storage::storage.start') }} <span class="required">*</span></label>
										<input type="text" class="form-control datetime" id="sell-datetimestart" name="datetimestart" required value="{{ Carbon\Carbon::now()->modify('+10 minutes')->toDateTimeString() }}" />
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="sell-datetimestop">{{ trans('storage::storage.end') }}</label>
										<input type="text" class="form-control datetime" id="sell-datetimestop" name="datetimestop" disabled="disabled" placeholder="{{ trans('storage::storage.end of life') }}" value="" />
									</div>
								</div>
							</div>

							<div class="form-group">
								<label for="sell-group">{{ trans('storage::storage.seller') }} <span class="required">*</span></label>
								<select name="sellergroupid" id="sell-sellergroup"
									class="form-control form-group-storage"
									data-api="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s">
									<option value="0">{{ trans('storage::storage.select group') }}</option>
									<option value="-1" selected="selected">{{ trans('storage::storage.org owned') }}</option>
								</select>
							</div>

							<div class="form-group">
								<label for="sell-group">{{ trans('storage::storage.sell to') }} <span class="required">*</span></label>
								<select name="groupid" id="sell-group"
									class="form-control form-group-storage"
									data-api="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s">
									<option value="0">{{ trans('storage::storage.select group') }}</option>
									<option value="-1">{{ trans('storage::storage.org owned') }}</option>
									<option value="{{ $group->id }}" selected="selected">{{ $group->name }}</option>
								</select>
							</div>

							<div class="form-group">
								<label for="sell-comment">{{ trans('storage::storage.comment') }}</label>
								<textarea id="sell-comment" name="comment" class="form-control" maxlength="2000" cols="35" rows="2"></textarea>
							</div>

							<div id="error_purchase" class="alert alert-danger hide"></div>

							<div class="dialog-footer text-right">
								<input type="submit" class="btn btn-success dialog-submit" value="{{ trans('global.button.create') }}" data-type="purchase" data-success="{{ trans('storage::storage.item created') }}" />
							</div>

							<input type="hidden" name="resourceid" value="{{ $row->storageResource->parentresourceid }}" />
							@csrf
						</form>
					</div>

					<div class="dialog" id="dialog-loan" title="{{ trans('storage::storage.loan space') }}">
						<form method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.storage.loans.create') }}">
							<div class="form-group">
								<label for="loan-bytes">{{ trans('storage::storage.amount') }} <span class="required">*</span></label>
								<input type="text" class="form-control bytes" size="4" id="loan-bytes" name="bytes" required pattern="[0-9]{1,10}\s?[PTGMKB]{1,2}" value="" />
								<span class="form-text text-muted">{{ trans('storage::storage.quota desc') }}</span>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="loan-datetimestart">{{ trans('queues::queues.start') }} <span class="required">*</span></label>
										<input type="text" name="datetimestart" class="form-control datetime" id="loan-datetimestart" required value="{{ Carbon\Carbon::now()->modify('+10 minutes')->toDateTimeString() }}" />
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="loan-datetimestop">{{ trans('queues::queues.end') }}</label>
										<input type="text" name="datetimestop" class="form-control datetime" id="loan-datetimestop" value="" />
									</div>
								</div>
							</div>

							<div class="form-group">
								<label for="loan-lendergroup">{{ trans('storage::storage.lender') }} <span class="required">*</span></label>
								<select name="lendergroupid" id="loan-lendergroup"
									class="form-control form-group-storage"
									data-api="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s">
									<option value="0">{{ trans('storage::storage.select group') }}</option>
									<option value="-1" selected="selected">{{ trans('storage::storage.org owned') }}</option>
								</select>
							</div>

							<div class="form-group">
								<label for="loan-group">{{ trans('storage::storage.loan to') }} <span class="required">*</span></label>
								<select name="groupid" id="loan-group"
									class="form-control form-group-storage"
									data-api="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s">
									<option value="0">{{ trans('storage::storage.select group') }}</option>
									<option value="-1">{{ trans('storage::storage.org owned') }}</option>
									<option value="{{ $group->id }}" selected="selected">{{ $group->name }}</option>
								</select>
							</div>

							<div class="form-group">
								<label for="loan-comment">{{ trans('storage::storage.comment') }}</label>
								<textarea id="loan-comment" name="comment" class="form-control" maxlength="2000" rows="2" cols="40"></textarea>
							</div>

							<div id="error_loan" class="alert alert-danger hide"></div>

							<div class="dialog-footer text-right">
								<input type="submit" class="btn btn-success dialog-submit" value="{{ trans('global.button.create') }}" data-type="loan" data-success="{{ trans('queues::queues.item created') }}" />
							</div>

							<input type="hidden" name="resourceid" value="{{ $row->storageResource->parentresourceid }}" />
							@csrf
						</form>
					</div>
					@endif
				</div>
			</div><!-- / .panel -->
		@if (auth()->user()->can('manage storage'))
			<div class="card panel panel-default">
				<div class="card-header panel-heading">
					{{ trans('storage::storage.messages') }}
				</div>
				<div class="card-body panel-body">
					@if ($group->messages->count())
						<table class="table table-hover">
							<caption class="sr-only">{{ trans('storage::storage.messages') }}</caption>
							<thead>
								<tr>
									<th scope="col">{{ trans('storage::storage.status') }}</th>
									<th scope="col">{{ trans('storage::storage.path') }}</th>
									<th scope="col">{{ trans('storage::storage.action') }}</th>
									<th scope="col">{{ trans('storage::storage.submitted') }}</th>
									<th scope="col">{{ trans('storage::storage.completed') }}</th>
									<th scope="col">{{ trans('storage::storage.runtime') }}</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($group->messages->orderBy('datetimesubmitted', 'desc')->limit(10)->get() as $message)
									<tr>
										<td>
											@if ($message->status == 'completed')
												<span class="fa fa-check text-success tip" aria-hidden="true" title="{{ trans('messages::messages.' . $message->status) }}"></span>
											@elseif ($message->status == 'error')
												<span class="fa fa-exclamation-circle text-danger tip" aria-hidden="true" title="{{ trans('messages::messages.' . $message->status) }}"></span>
											@elseif ($message->status == 'deferred')
												<span class="fa fa-clock tip" aria-hidden="true" title="{{ trans('messages::messages.' . $message->status) }}"></span>
											@elseif ($message->status == 'running')
												<span class="fa fa-heartbeat text-warning tip" aria-hidden="true" title="{{ trans('messages::messages.' . $message->status) }}"></span>
											@elseif ($message->status == 'queued')
												<span class="fa fa-ellipsis-h text-info tip" aria-hidden="true" title="{{ trans('messages::messages.' . $message->status) }}"></span>
											@endif
											<span class="sr-only">{{ trans('messages::messages.' . $message->status) }}</span>
										</td>
										<td>{{ $message->target }}</td>
										<td>{{ $message->type->name }}</td>
										<td>{{ $message->datetimesubmitted->format('Y-m-d') }}</td>
										<td>
											@if ($message->completed())
												{{ $message->datetimecompleted->format('Y-m-d') }}
											@else
												-
											@endif
										</td>
										<td>
											@if (strtotime($message->datetimesubmitted) <= date("U"))
												{{ $message->runtime }}
											@else
												-
											@endif
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					@else
						<p class="text-center text-muted">{{ trans('global.none') }}</p>
					@endif
				</div>
			</div><!-- / .panel -->
		@endif
		<?php
		else:
			?>
			<p class="text-center text-muted">{{ trans('global.none') }}</p>

			@if (auth()->user()->can('manage storage'))
				<h3>Create new storage directories:</h3>

				<div class="card panel panel-default step{{ ($group->unixgroup ? 'complete' : '') }}">
					<div class="card-header panel-heading">
						1) Set base name for group
					</div>
					<div class="card-body panel-body">
						@if (!$group->unixgroup)
							<div class="form-group">
								<span class="input-group">
									<input type="text" class="form-control" id="unixgroup" value="" />
									<span class="input-group-append">
										<button class="input-group-text btn btn-primary unixgroup-basename-set" data-api="{{ route('api.groups.update', ['id' => $group->id]) }}" data-id="{{ $group->id }}">
											<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('global.loading') }}</span></span>
											{{ trans('global.button.save') }}
										</button>
									</span>
								</span>
							</div>

							<div id="error_unixgroup" class="alert alert-danger hide"></div>
						@else
							{{ $group->unixgroup }} <span class="fa fa-check text-success" aria-hidden="true"></span>
						@endif
					</div>
				</div>

				<div class="card panel panel-default step{{ (count($group->unixgroups) > 0 ? 'complete' : '') }}">
					<div class="card-header panel-heading">
						2) Create Unix groups
					</div>
					<div class="card-body panel-body">
					@if (count($group->unixGroups) <= 0)
						<?php
						$disabled = '';
						if (!$group->unixgroup)
						{
							$disabled = 'disabled="true"';
						}
						?>
						<p>
							<button <?php echo $disabled; ?> class="btn btn-primary unixgroup-create" data-api="{{ route('api.unixgroups.create') }}" data-value="{{ $group->unixgroup }}" data-group="{{ $group->id }}">
								<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('global.loading') }}</span></span>
								Create Unix Groups
							</button>
						</p>

						<div id="error_unixgroups" class="alert alert-danger hide"></div>

						<div class="alert alert-warning">
							<p><strong>THIS ACTION CANNOT BE UNDONE. PLEASE READ!!</strong></p>
							<p>This will create the default Unix groups. A base group, apps, and data group will be created. These will prefixed by 
							the chosen base name. Once these are created, the groups and base name cannot be easily changed so only continue 
							if you are sure.</p>
							<p>If the group has any existing Unix groups please do not continue and contact support. {{ config('app.name') }} staff 
							will integrate these group(s) into the web application and set up other default groups.</p>
							<p>This process may take several minutes. Please do not close the page.</p>
						</div>
					@else
						Create Unix groups <span class="fa fa-check text-success" aria-hidden="true"></span>
					@endif
					</div>
				</div>

				<div class="card panel panel-default">
					<div class="card-header panel-heading">
						3) Sell or Loan space to the group
					</div>
					<div class="card-body panel-body">
						<?php
						$purchases = $group->purchases()->withTrashed()->count();
						$loans = $group->loans()->withTrashed()->count();
						$storageresources = App\Modules\Storage\Models\StorageResource::query()
							->withTrashed()
							->whereIsActive()
							->orderBy('name', 'asc')
							->get();
						?>
						@if (!$group->unixgroup && count($group->unixGroups) > 0)
							<p class="alert alert-warning">You must create unix groups first.</p>
						@elseif (!$purchases && !$loans)
							<p>
								<a href="#dialog-sell" id="space-sell" class="btn btn-sm btn-secondary dialog-btn icon-dollar-sign">{{ trans('storage::storage.sell space') }}</a>
								<a href="#dialog-loan" id="space-loan" class="btn btn-sm btn-secondary dialog-btn icon-shuffle">{{ trans('storage::storage.loan space') }}</a>
							</p>

							<div class="dialog" id="dialog-sell" title="{{ trans('storage::storage.sell space') }}">
								<form method="post" action="{{ route('admin.storage.store') }}" data-api="{{ route('api.storage.purchases.create') }}">
									<div class="form-group">
										<label for="sell-resource">{{ trans('storage::storage.resource') }} <span class="required">*</span></label>
										<select name="resourceid" id="sell-resource" class="form-control">
											<?php foreach ($storageresources as $s): ?>
												<?php $selected = ($s->parentresourceid == 64 ? ' selected="selected"' : ''); ?>
												<option value="{{ $s->parentresourceid }}" data-storageresource="{{ $s->id }}"<?php echo $selected; ?>>{{ $s->name }}</option>
											<?php endforeach; ?>
										</select>
									</div>

									<div class="form-group">
										<label for="sell-bytes">{{ trans('storage::storage.amount') }} <span class="required">*</span></label>
										<input type="text" class="form-control bytes" size="4" id="sell-bytes" name="bytes" required pattern="[0-9]{1,10}\s?[PTGMKB]{1,2}" value="" />
										<span class="form-text text-muted">{{ trans('storage::storage.quota desc') }}</span>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="sell-datetimestart">{{ trans('storage::storage.start') }} <span class="required">*</span></label>
												<input type="text" class="form-control datetime" id="sell-datetimestart" name="datetimestart" required value="{{ Carbon\Carbon::now()->toDateTimeString() }}" />
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="sell-datetimestop">{{ trans('storage::storage.end') }}</label>
												<input type="text" class="form-control datetime" id="sell-datetimestop" name="datetimestop" disabled="disabled" placeholder="{{ trans('storage::storage.end of life') }}" value="" />
											</div>
										</div>
									</div>

									<div class="form-group">
										<label for="sell-group">{{ trans('storage::storage.seller') }} <span class="required">*</span></label>
										<select name="sellergroupid" id="sell-sellergroup"
											class="form-control form-group-storage"
											data-api="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s">
											<option value="0">{{ trans('storage::storage.select group') }}</option>
											<option value="-1" selected="selected">{{ trans('storage::storage.org owned') }}</option>
										</select>
									</div>

									<div class="form-group">
										<label for="sell-group">{{ trans('storage::storage.sell to') }} <span class="required">*</span></label>
										<select name="groupid" id="sell-group"
											class="form-control form-group-storage"
											data-api="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s">
											<option value="0">{{ trans('storage::storage.select group') }}</option>
											<option value="-1">{{ trans('storage::storage.org owned') }}</option>
											<option value="{{ $group->id }}" selected="selected">{{ $group->name }}</option>
										</select>
									</div>

									<div class="form-group">
										<label for="sell-comment">{{ trans('storage::storage.comment') }}</label>
										<textarea id="sell-comment" name="comment" class="form-control" maxlength="2000" cols="35" rows="2"></textarea>
									</div>

									<div id="error_purchase" class="alert alert-danger hide"></div>

									<div class="dialog-footer text-right">
										<input type="submit" class="btn btn-success dialog-submit" value="{{ trans('global.button.create') }}" data-type="purchase" data-success="{{ trans('storage::storage.item created') }}" />
									</div>

									@csrf
								</form>
							</div>

							<div class="dialog" id="dialog-loan" title="{{ trans('storage::storage.loan space') }}">
								<form method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.storage.loans.create') }}">
									<div class="form-group">
										<label for="loan-resource">{{ trans('storage::storage.resource') }} <span class="required">*</span></label>
										<select name="resourceid" id="loan-resource" class="form-control">
											<?php foreach ($storageresources as $s): ?>
												<?php $selected = ($s->parentresourceid == 64 ? ' selected="selected"' : ''); ?>
												<option value="{{ $s->parentresourceid }}" data-storageresource="{{ $s->id }}"<?php echo $selected; ?>>{{ $s->name }}</option>
											<?php endforeach; ?>
										</select>
									</div>

									<div class="form-group">
										<label for="loan-bytes">{{ trans('storage::storage.amount') }} <span class="required">*</span></label>
										<input type="text" class="form-control bytes" size="4" id="loan-bytes" name="bytes" required pattern="[0-9]{1,10}\s?[PTGMKB]{1,2}" value="" />
										<span class="form-text text-muted">{{ trans('storage::storage.quota desc') }}</span>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="loan-datetimestart">{{ trans('queues::queues.start') }} <span class="required">*</span></label>
												<input type="text" name="datetimestart" class="form-control datetime" id="loan-datetimestart" required value="{{ Carbon\Carbon::now()->toDateTimeString() }}" />
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="loan-datetimestop">{{ trans('queues::queues.end') }}</label>
												<input type="text" name="datetimestop" class="form-control datetime" id="loan-datetimestop" value="" />
											</div>
										</div>
									</div>

									<div class="form-group">
										<label for="loan-lendergroup">{{ trans('storage::storage.lender') }} <span class="required">*</span></label>
										<select name="lendergroupid" id="loan-lendergroup"
											class="form-control form-group-storage"
											data-api="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s">
											<option value="0">{{ trans('storage::storage.select group') }}</option>
											<option value="-1" selected="selected">{{ trans('storage::storage.org owned') }}</option>
										</select>
									</div>

									<div class="form-group">
										<label for="loan-group">{{ trans('storage::storage.loan to') }} <span class="required">*</span></label>
										<select name="groupid" id="loan-group"
											class="form-control form-group-storage"
											data-api="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s">
											<option value="0">{{ trans('storage::storage.select group') }}</option>
											<option value="-1">{{ trans('storage::storage.org owned') }}</option>
											<option value="{{ $group->id }}" selected="selected">{{ $group->name }}</option>
										</select>
									</div>

									<div class="form-group">
										<label for="loan-comment">{{ trans('storage::storage.comment') }}</label>
										<textarea id="loan-comment" name="comment" class="form-control" maxlength="2000" rows="2" cols="40"></textarea>
									</div>

									<div id="error_loan" class="alert alert-danger hide"></div>

									<div class="dialog-footer text-right">
										<input type="submit" class="btn btn-success dialog-submit" value="{{ trans('global.button.create') }}" data-type="loan" data-success="{{ trans('queues::queues.item created') }}" />
									</div>

									@csrf
								</form>
							</div>
						@else
							Sell or Loan space to the group <span class="fa fa-check text-success" aria-hidden="true"></span>
						@endif
					</div>
				</div>

				<?php
				$disabled = '';
				if (!$group->unixgroup)
				{
					$disabled = 'disabled="true"';
				}
				$data = $group->unixGroups->firstWhere('longname', $group->unixgroup . '-data');
				$apps = $group->unixGroups->firstWhere('longname', $group->unixgroup . '-apps');
				?>
				<div class="card panel panel-default">
					<div class="card-header panel-heading">
						4) Create Default Directories
					</div>
					<div class="card-body panel-body">
						@if (!$group->unixgroup && count($group->unixGroups) > 0)
						<p class="alert alert-warning">You must create unix groups first.</p>
						@elseif (!$purchases && !$loans)
						<p class="alert alert-warning">You must sell or loan space to the group first.</p>
						@else
						<form method="post" action="{{ route('admin.queues.store') }}" data-api="{{ route('api.storage.directories.create') }}">
							<div class="form-group">
								<label for="new-resourceid">{{ trans('storage::storage.parent') }}: <span class="required">*</span></label>
								<select name="resourceid" id="new-resourceid" class="form-control required" required>
									<?php foreach ($storageresources as $s): ?>
										<?php $selected = ($s->parentresourceid == 64 ? ' selected="selected"' : ''); ?>
										<option value="{{ $s->parentresourceid }}" data-storageresource="{{ $s->id }}"<?php echo $selected; ?>>{{ $s->name }}</option>
									<?php endforeach; ?>
								</select>
								<span class="invalid-feedback">{{ trans('storage::storage.error.invalid parent') }}</span>
							</div>

							<div class="form-group">
								<label for="new-name">{{ trans('storage::storage.name') }}: <span class="required">*</span></label>
								<input type="text" name="name" id="new-name" class="form-control required" pattern="^([a-zA-Z0-9]+\.?[\-_ ]*)*[a-zA-Z0-9]$" required value="{{ $group->unixgroup }}" />
								<span class="form-text text-muted">{{ trans('storage::storage.name desc') }}</span>
							</div>

							@if ($apps)
							<div class="form-check">
								<input type="checkbox" name="defaults" id="new-apps" class="form-check-input" value="{{ $apps->id }}" checked="checked" />
								<label for="new-defaults" class="form-check-label">{{ trans('Create "(name)-apps" directory') }}</label>
							</div>

							<div class="form-check">
								<input type="checkbox" name="defaults" id="new-etc" class="form-check-input" value="{{ $apps->id }}" checked="checked" />
								<label for="new-defaults" class="form-check-label">{{ trans('Create "(name)-etc" directory') }}</label>
							</div>
							@else
							<div class="form-check">
								<input type="checkbox" name="defaults" id="new-apps" class="form-check-input" value="{{ $apps ? $apps->id : 0 }}" disabled="disabled" />
								<label for="new-defaults" class="form-check-label">{{ trans('Create "(name)-apps" directory') }}</label>
							</div>

							<div class="form-check">
								<input type="checkbox" name="defaults" id="new-etc" class="form-check-input" value="{{ $apps ? $apps->id : 0 }}" disabled="disabled" />
								<label for="new-defaults" class="form-check-label">{{ trans('Create "(name)-etc" directory') }}</label>
							</div>
							<span class="text-danger">No "(name)-apps" unix group found.</span>
							@endif

							@if ($data)
							<div class="form-check">
								<input type="checkbox" name="defaults" id="new-data" class="form-check-input" value="{{ $data->id }}" checked="checked" />
								<label for="new-defaults" class="form-check-label">{{ trans('Create "(name)-data" directory') }}</label>
							</div>
							@else
							<div class="form-check">
								<input type="checkbox" name="defaults" id="new-data" class="form-check-input" value="{{ $data ? $data->id : 0 }}" disabled="disabled" />
								<label for="new-defaults" class="form-check-label">{{ trans('Create "(name)-data" directory') }}</label>
								<span class="text-danger">No "(name)-data" unix group found.</span>
							</div>
							@endif

							<div class="dialog-footer text-center">
								<button class="btn btn-success dir-create-default"
									data-api="{{ route('api.storage.directories.create') }}"
									data-id="{{ $group->id }}"
									data-unixgroup="{{ $group->unixgroup }}"
									data-base="{{ $group->primaryUnixGroup ? $group->primaryUnixGroup->id : 0 }}"
									data-apps="{{ $apps ? $apps->id : 0 }}"
									data-data="{{ $data ? $data->id : 0 }}">
									<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('global.loading') }}</span></span>
									{{ trans('Create Directories') }}
								</button>
							</div>

							<div id="error_new" class="alert alert-danger hide"></div>

							<input type="hidden" name="groupid" value="{{ $group->id }}" />
							<input type="hidden" name="unixgroupid" value="{{ $group->primaryUnixGroup ? $group->primaryUnixGroup->id : 0 }}" />
							<input type="hidden" name="unixgroup" value="{{ $group->unixgroup }}" />
							@csrf
						</form>
						@endif
					</div>
				</div>
			@endif
			<?php
		endif;
		?>
	</div><!-- / .col -->
</div><!-- / .row -->
