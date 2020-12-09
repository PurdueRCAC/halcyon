@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/fancytree/skin-xp/ui.fancytree.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/fancytree/jquery.fancytree-all.js?v=' . filemtime(public_path() . '/modules/core/vendor/fancytree/jquery.fancytree-all.js')) }}"></script>
<script src="{{ asset('modules/storage/js/site.js?v=' . filemtime(public_path() . '/modules/storage/js/site.js')) }}"></script>
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

		$rows = $directories->filter(function($item)
		{
			return $item->parentstoragedirid == 0 && $item->storageresourceid == 4;
		});

	if (count($rows))
	{
		foreach ($rows as $row)
		{
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
								<div class="form-check">
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
											Deduct from unallocated space (<span name="unallocated"><?php echo $bucket['unallocatedbytes']; ?></span>):
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

					<div class="dialog-footer text-right">
						<button id="new_dir" class="btn btn-success" data-api="{{ route('api.storage.directories.create') }}">
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

						<?php if ($dir->quotaproblem == 1 && $dir->bytes) { ?>
							<div class="row mb-3">
								<div class="col-md-4">
									Desired quota
								</div>
								<div class="col-md-8">
									<?php echo App\Halcyon\Utility\Number::formatBytes($dir->bytes); ?>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-md-4">
									Actual quota <span class="icon-warning" data-tip="Storage space is over-allocated. Quotas reduced until allocation balanced."><span class="sr-only">Storage space is over-allocated. Quotas reduced until allocation balanced.</span></span>
								</div>
								<div class="col-md-8">
									<?php echo App\Halcyon\Utility\Number::formatBytes($dir->bytes); ?>
								</div>
							</div><!--/ .row -->
						<?php } else { ?>
							<div class="row mb-3">
								<div class="col-md-4">
									<label for="{{ $dir->id }}_quota_input">{{ trans('storage::storage.quota') }}</label>
								</div>
								<div class="col-md-8">
									<?php
									$value = App\Halcyon\Utility\Number::formatBytes($dir->bytes, true);
									?>
									<input type="text" id="{{ $dir->id }}_quota_input" class="form-control" value="{{ $dir->bytes ? $value : '' }}" />
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
											if ($dir->autouserunixgroup && $unixgroup->id == $dir->autouserunixgroup->id)
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
										<option value="autouser"<?php if ($dir->autouser == '1') { ?> selected="selected"<?php } ?>>Auto User - Group Readable</
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
									<label for="<?php echo $dir->id; ?>_other_read_box">Read access for <?php echo $bottle_dirs_string; ?></label>
								</div>
								<div class="col-md-8">
									<?php if ($dir->unixPermissions->other->read) { ?>
										<input type="checkbox" id="<?php echo $dir->id; ?>_other_read_box" class="form-check-input" checked="checked" />
										<span id="<?php echo $dir->id; ?>_other_read_span">{{ trans('global.yes') }}</span>
									<?php } else { ?>
										<input type="checkbox" id="<?php echo $dir->id; ?>_other_read_box" class="form-check-input" />
										<span id="<?php echo $dir->id; ?>_other_read_span">{{ trans('global.no') }}</span>
									<?php } ?>
									to directories:

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
										$p['unixgroup'] = $parent->unixgroup->toArray();
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
													<span class="glyph icon-check success dirperm">{{ trans('global.yes') }}</span>
												@else
													<span class="glyph icon-x failed dirperm">{{ trans('global.no') }}</span>
												@endif
											</td>
											<td class="text-center">
												@if ($child['permissions']['group']['write'])
													<span class="glyph icon-check success dirperm">{{ trans('global.yes') }}</span>
												@else
													<span class="glyph icon-x failed dirperm">{{ trans('global.no') }}</span>
												@endif
											</td>
										</tr>
										<?php
									}
									?>
									</tbody>
									@if ($dir->children()->count() == 0)
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

						<?php if (count($dir->futurequotas) > 0) { ?>
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
											<?php foreach ($dir->futurequotas as $change) { ?>
												<tr>
													<td><?php echo date('M d, Y', strtotime($change['time'])); ?></td>
													<td><?php echo App\Halcyon\Utility\Number::formatBytes($change['quota']); ?></td>
												</tr>
											<?php } ?>
										</tbody>
									</table>
								</div>
							</div><!--/ .row -->
						<?php } ?>

						<div class="row mb-3">
							<div class="col-md-4">
								{{ trans('storage::storage.unallocated space') }}
							</div>
							<div class="col-md-8">
								<span name="unallocated"><?php echo App\Halcyon\Utility\Number::formatBytes($bucket['unallocatedbytes']); ?></span> / <span name="totalbytes"><?php echo App\Halcyon\Utility\Number::formatBytes($bucket['totalbytes']); ?></span>
								<?php
								if ($dir->bytes)
								{
									$cls = '';
									if ($bucket['unallocatedbytes'] == 0)
									{
										$cls = ' hide';
									}

									if ($dir->quotaproblem == 1 && $dir->bytes && $dir->realquota < $dir->bytes)
									{
										if (-$bucket['unallocatedbytes'] < $dir->bytes)
										{
											?>
											<button id="{{ $dir->id }}_quota_upa" class="btn btn-secondary quota_upa<?php echo $cls; ?>" data-dir="{{ $dir->id }}">
												<span id="{{ $dir->id }}_quota_up" class="icon-arrow-down">{{ trans('storage::storage.remove overallocated') }}</span>
											</button>
											<?php
										}
									}
									else
									{
										?>
										<button id="{{ $dir->id }}_quota_upa" class="btn btn-secondary quota_upa<?php echo $cls; ?>" data-dir="{{ $dir->id }}">
											<span id="{{ $dir->id }}_quota_up" class="icon-arrow-up">{{ trans('storage::storage.distribute remaining') }}</span>
										</button>
										<?php
									}
								}
								?>
							</div>
						</div><!--/ .row -->

						<div class="dialog-footer">
							<div class="row">
								<div class="col-md-6">
									<?php
									if ($dir->children()->count() == 0)
									{
										if (in_array($dir->id, $removing) || in_array($dir->id, $configuring))
										{
											echo '<p>Delete Disabled - Operations Pending</p>';
										}
										else
										{
											?>
											<button data-api="{{ route('api.storage.directories.delete', ['id' => $dir->id]) }}"
												class="btn btn-danger dir-delete"
												data-confirm="Are you sure you want to delete {{ $dir->path }}? All contents will be deleted!"
												data-dir="{{ $dir->id }}"
												data-path="{{ $dir->path }}">
												{{ trans('global.button.delete') }}
											</button>
											<?php
										}
									}
									?>
								</div>
								<div class="col-md-6 text-right">
									<input disabled="disabled" id="{{ $dir->id }}_save_button" class="btn btn-success unixgroup-edit" data-dir="{{ $dir->id }}" type="button" value="{{ trans('global.button.save') }}" />
								</div>
							</div><!--/ .row -->
						</div>

					</div><!-- / #<?php echo $did; ?>_dialog -->
				<?php } ?>
				<?php
			}
			?>
			</div><!-- / .panel-body -->
		</div><!-- / .panel -->

		<div class="card panel panel-default">
			<div class="card-header panel-heading">
				{{ trans('storage::storage.history') }}
			</div>
			<div class="card-body panel-body">
				<?php
				$history = array();
				foreach ($group->purchases as $purchase)
				{
					$history[$purchase->datetimestart->timestamp] = $purchase;
				}
				foreach ($group->loans as $loan)
				{
					$history[$loan->datetimestart->timestamp] = $loan;
				}
				ksort($history);

				$total = 0;
				foreach ($history as $item)
				{
					//$total = $item->type == 'loan' ? ($total - $item->bytes) : ($total + $item->bytes);
					$total = ($item->bytes > 0 ? $total + $item->bytes : $total - abs($item->bytes)); 
					$item->total = $total;
				}
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
						</tr>
					</thead>
					<tbody>
						@foreach ($history as $item)
							<tr class="{{ $item->type }}">
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
									@if ($item->hasEnded())
										<time datetime="{{ $item->datetimestop }}">{{ $item->datetimestop->format('Y-m-d') }}</time>
									@else
										-
									@endif
								</td>
								<td class="text-right">
									{!! ($item->bytes > 0 ? '<span class="increase">+ ' : '<span class="decrease">- ') . App\Halcyon\Utility\Number::formatBytes(abs($item->bytes)) . '</span>' !!}
								</td>
								<td class="text-right">
									{{ App\Halcyon\Utility\Number::formatBytes($item->total) }}
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
											<i class="fa fa-check" aria-hidden="true"></i>
										@elseif ($message->status == 'error')
											<i class="fa fa-exclamation-circle" aria-hidden="true"></i>
										@elseif ($message->status == 'deferred')
											<i class="fa fa-clock" aria-hidden="true"></i>
										@elseif ($message->status == 'running')
											<i class="fa fa-heartbeat" aria-hidden="true"></i>
										@elseif ($message->status == 'queued')
											<i class="fa fa-ellipsis-h" aria-hidden="true"></i>
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
		<?php
	}
	else
	{
		?>
		<p class="text-center text-muted">{{ trans('global.none') }}</p>
		<?php
	}
	?>
	</div><!-- / .col -->
</div><!-- / .row -->
