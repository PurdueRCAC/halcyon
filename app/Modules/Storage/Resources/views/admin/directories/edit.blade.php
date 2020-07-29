@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('vendor/fancytree/skin-xp/ui.fancytree.css') }}" />
@stop

@section('scripts')
<script src="{{ asset('vendor/fancytree/jquery.fancytree-all.js') }}"></script>
<script src="{{ asset('modules/storage/js/admin.js?v=' . filemtime(public_path() . '/modules/storage/js/admin.js')) }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('storage::storage.module name'),
		route('admin.storage.index')
	)
	->append(
		trans('storage::storage.directories'),
		route('admin.storage.directories')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit storage'))
		{!! Toolbar::save(route('admin.storage.directories.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.storage.directories.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('storage.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.storage.directories.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('JGLOBAL_VALIDATION_FORM_FAILED') }}">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-storageresourceid">{{ trans('storage::storage.FIELD_PARENT') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<select name="fields[storageresourceid]" id="field-storageresourceid" class="form-control">
						<option value="0"><?php echo trans('global.none'); ?></option>
						<?php foreach ($storageresources as $s): ?>
							<?php $selected = ($s->id == $row->storageresourceid ? ' selected="selected"' : ''); ?>
							<option value="{{ $s->id }}"<?php echo $selected; ?>>{{ $s->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-name">{{ trans('storage::storage.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" value="{{ $row->name }}" />
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-groupid">{{ trans('storage::storage.group') }}:</label>
							<span class="input-group">
								<input type="text" name="fields[groupid]" id="field-groupid" class="form-control form-groups" data-uri="{{ url('/') }}/api/groups/?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ ($row->group ? $row->group->name . ':' . $row->groupid : '') }}" />
								<span class="input-group-append"><span class="input-group-text icon-users"></span></span>
							</span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-owneruserid">{{ trans('storage::storage.owner') }}:</label>
							<span class="input-group">
								<input type="text" name="fields[owneruserid]" id="field-owneruserid" class="form-control form-users" data-uri="{{ url('/') }}/api/users/?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ ($row->user ? $row->user->name . ':' . $row->userid : '') }}" />
								<span class="input-group-append"><span class="input-group-text icon-user"></span></span>
							</span>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-bytes">{{ trans('storage::storage.quota') }}:</label>
					<input type="text" name="fields[bytes]" id="field-bytes" class="form-control" value="{{ App\Halcyon\Utility\Number::formatBytes($row->bytes) }}" />
				</div>
			</fieldset>
@if ($row->id)
			<fieldset class="adminform">
				<legend>{{ trans('storage::storage.directories') }}</legend>

				<table id="tree" class="tree">
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
				</table>
				<script type="application/json" id="tree-data"><?php
				$data = array($row->tree());
				echo json_encode($data);
				?></script>

				<?php
				$dirhash = array();
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
				$configuring = array();
				$removing = array();
				$directories = []; //$row->group->directories;
				foreach ($row->nested() as $dir)
				{
					$did = $dir->id;
					?>
					<div id="<?php echo $did; ?>_dialog" title="<?php echo $dir->storageResource->path . '/' . $dir->path; ?>" class="dialog">
						<table class="table editStorageTable">
							<caption class="sr-only"><?php echo $dir->name; ?></caption>
							<tbody>
								<tr>
							<?php if ($dir['quotaproblem'] == 1 && $dir->bytes) { ?>
								<td>Desired quota</td>
								<td colspan="3"><?php echo App\Halcyon\Utility\Number::formatBytes($dir->bytes); ?></td>
							</tr>
							<tr>
								<td class="quotaProblem">
									Actual quota <img class="img editicon" src="/include/images/error.png" alt="Storage space is over-allocated. Quotas reduced until allocation balanced." />
								</td>
								<td class="quotaProblem" colspan="3"><?php echo App\Halcyon\Utility\Number::formatBytes($dir->quota); ?></td>
							<?php } else { ?>
								<td>Quota</td>
								<td>
									<?php
									$value = App\Halcyon\Utility\Number::formatBytes($dir->bytes, true);
									if (!$dir->bytes)
									{
										$value = '-';
									}
									?>
									<span id="<?php echo $dir->id; ?>_quota_span"><?php echo $value; ?></span>
									<?php if ($dir->bytes) { ?>
										<input type="text" id="<?php echo $dir->id; ?>_quota_input" class="stash" style="width: 85px;" value="<?php echo $value; ?>" />
									<?php } ?>
									<?php
									$style = '';
									if (!$dir->bytes)
									{
										$style = 'display:none';
									}
									?>
								</td>
								<td></td>
								<td></td>
							<?php } ?>
							</tr>
							<tr>
								<td>Access Unix Group</td>
								<td colspan="3">
									<select id="<?php echo $dir->id; ?>_unixgroup_select" class="form-control">
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
								</td>
							</tr>
						<?php if ($dir->autouser) { ?>
							<tr>
								<td>Populating Unix Group</td>
								<td colspan="3">
									<span id="<?php echo $dir->id; ?>_autouserunixgroup_span"><?php echo $dir->autouserunixgroup->longname; ?></span>
									<select id="<?php echo $dir->id; ?>_autouserunixgroup_select" class="stash">
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
								</td>
							</tr>
						<?php } ?>
						<?php if ($dir->owner && $dir->owner->name != 'root') { ?>
							<tr>
								<td>Owner</td>
								<td colspan="3">
									<?php echo $dir->owner->name; ?>
								</td>
							</tr>
							<tr>
								<td>Type</td>
								<td colspan="3">
									<span id="<?php echo $dir->id; ?>_dir_type">
										<?php
										if ($dir->unixPermissions->group->write)
										{
											echo 'User Owned - Group Writable';
										}
										elseif ($dir->unixPermissions->group->read)
										{
											echo 'User Owned - Group Readable';
										}
										else
										{
											echo 'User Owned - Private';
										}
										?>
									</span>
									<select id="<?php echo $dir->id; ?>_dir_type_select" class="stash">
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
								</td>
							</tr>
						<?php } ?>
						<?php if ($dir->autouser) { ?>
							<tr>
								<td>Auto Populate User Default</td>
								<td colspan="3">
									<span id="<?php echo $dir->id; ?>_dir_type">
										<?php
										if ($dir->autouser == '1')
										{
											echo 'Group Readable';
										}
										else if ($dir->autouser == '2')
										{
											echo 'Private';
										}
										else if ($dir->autouser == '3')
										{
											echo 'Group Readable Writable';
										}
										?>
									</span>
									<select id="<?php echo $dir->id; ?>_dir_type_select" class="stash">
										<?php if ($dir->autouser == '1') { ?>
											<option selected="selected" value="autouser">Auto User - Group Readable</option>
											<option value="autouserreadwrite">Auto User - Group Readable Writable</option>
											<option value="autouserprivate">Auto User - Private</option>
										<?php } else if ($dir->autouser == '2') { ?>
											<option value="autouser">Auto User - Group Readable</option>
											<option value="autouserreadwrite">Auto User - Group Readable Writable</option>
											<option selected="selected" value="autouserprivate">Auto User - Private</option>
										<?php } else if ($dir->autouser == '3') { ?>
											<option value="autouser">Auto User - Group Readable</option>
											<option selected="selected" value="autouserreadwrite">Auto User - Group Readable Writable</option>
											<option value="autouserprivate">Auto User - Private</option>
										<?php } ?>
									</select>
								</td>
							</tr>
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
							<tr>
								<td>Read access for <?php echo $bottle_dirs_string; ?></td>
								<td>
									<?php if ($dir->unixPermissions->other->read) { ?>
										<input type="checkbox" id="<?php echo $dir->id; ?>_other_read_box" checked="checked" class="stash" />
										<span id="<?php echo $dir->id; ?>_other_read_span">{{ trans('global.yes') }}</span>
									<?php } else { ?>
										<input type="checkbox" id="<?php echo $dir->id; ?>_other_read_box" class="stash" />
										<span id="<?php echo $dir->id; ?>_other_read_span">{{ trans('global.no') }}</span>
									<?php } ?>
									to directories:
								</td>
								<td></td>
								<td></td>
							</tr>
							<?php foreach ($child_dirs as $child) { ?>
								<tr>
									<td></td>
									<td colspan="3"><?php echo $child->path; ?></td>
								</tr>
							<?php } ?>
						<?php } else if (!$dir->parentstoragedirid) { ?>
							<tr>
								<td colspan="1">Public read access?</td>
								<td colspan="3">
									<?php if ($dir->unixPermissions->other->read) { ?>
										<input type="checkbox" id="<?php echo $dir->id; ?>_other_read_box" checked="checked" class="stash" />
										<span id="<?php echo $dir->id; ?>_other_read_span">Yes</span>
									<?php } else { ?>
										<input type="checkbox" id="<?php echo $dir->id; ?>_other_read_box" class="stash" />
										<span id="<?php echo $dir->id; ?>_other_read_span">No</span>
									<?php } ?>
								</td>
							</tr>
						<?php } ?>
							<tr>
								<td></td>
								<td colspan="3">
									<?php
									$disabled = '';
									if (in_array($dir->id, $removing))
									{
										$disabled = 'disabled="true"';
									}
									?>
									<input <?php echo $disabled; ?> id="<?php echo $dir->id; ?>_edit_button" class="unixgroup-edit" data-dir="<?php echo $dir->id; ?>" type="button" value="Edit Directory" />
								</td>
							</tr>
							<tr>
								<td>Permissions</td>
								<td>Group</td>
								<td>Read</td>
								<td>Write</td>
							</tr>
							<?php
							$childs = array();

							$highest_read = $dir->id;
							$can_read = true;

							$parent = get_dir($directories, $dirhash, $dir->id);
							array_push($childs, $parent);

							if ($dir->parentstoragedirid)
							{
								do
								{
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

							array_push($childs, $highest);

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

								array_push($childs, $public);
							}

							foreach ($childs as $child)
							{
								?>
								<tr>
									<td></td>
									<td><?php echo $child['unixgroup']['longname']; ?></td>
									<td>
										<?php if ($child['permissions']['group']['read']) { ?>
											<span class="glyph icon-check success dirperm">{{ trans('global.yes') }}</span>
										<?php } else { ?>
											<span class="glyph icon-x failed dirperm">{{ trans('global.no') }}</span>
										<?php } ?>
									</td>
									<td>
										<?php if ($child['permissions']['group']['write']) { ?>
											<span class="glyph icon-check success dirperm">{{ trans('global.yes') }}</span>
										<?php } else { ?>
											<span class="glyph icon-x failed dirperm">{{ trans('global.no') }}</span>
										<?php } ?>
									</td>
								</tr>
								<?php
							}

							if ($dir->children()->count() == 0) { ?>
								<tr>
									<td style="border-bottom:1px solid #666666;"></td>
									<td colspan="3" style="border-bottom:1px solid #666666;">
										<input <?php echo $disabled; ?> id="<?php echo $dir->id; ?>_edit_button" class="permissions-reset" data-dir="<?php echo $dir->id; ?>" data-path="<?php echo $dir->path; ?>" type="button" value="Fix File Permissions" />
									</td>
								</tr>
							<?php } ?>
							<?php if (count($dir->futurequotas) > 0) { ?>
								<tr>
									<td style="border-bottom:1px solid #666666;"></td>
									<td colspan="3" style="border-bottom:1px solid #666666;">
										<input id="<?php echo $dir->id; ?>_edit_button" class="unixgroup-edit" data-dir="<?php echo $dir->id; ?>" type="button" value="Edit Directory" />
									</td>
								</tr>
								<tr>
									<td>Future Quota Changes</td>
									<td>Date</td>
									<td colspan="2">Quota</td>
								</tr>
								<?php foreach ($dir->futurequotas as $change) { ?>
									<tr>
										<td></td>
										<td><?php echo date('M d, Y', strtotime($change['time'])); ?></td>
										<td colspan="2"><?php echo App\Halcyon\Utility\Number::formatBytes($change['quota']); ?></td>
									</tr>
								<?php } ?>
							<?php } ?>
							</tbody>
						</table>

						<span id="<?php echo $dir->id; ?>_error"></span>
						<?php /*<p>
							Unallocated space: <span name="unallocated"><?php echo formatBytes($bucket['unallocatedbytes']); ?></span> / <span name="totalbytes"><?php echo formatBytes($bucket['totalbytes']); ?></span>
							<?php
							if ($dir->bytes)
							{
								$cls = '';
								if ($bucket['unallocatedbytes'] == 0)
								{
									$cls = ' stash';
								}

								if ($dir->quotaproblem == 1 && $dir->bytes && $dir->quota < $dir->bytes)
								{
									if (-$bucket['unallocatedbytes'] < $dir->bytes)
									{
										?>
										<a href="/admin/storage/edit/?g=<?php echo escape($_GET['g']); ?>&amp;r=<?php echo escape($_GET['r']); ?>&amp;dir=<?php echo $dir['id']; ?>&amp;quota=up" id="<?php echo $dir['id']; ?>_quota_upa" class="quota_upa<?php echo $cls; ?>" data-dir="<?php echo $dir['id']; ?>">
											<img id="<?php echo $dir['id']; ?>_quota_up" class="img editicon" src="/include/images/arrow_down.png" alt="Remove over-allocated space from this directory." />
										</a>
										<?php
									}
								}
								else
								{
									?>
									<a href="/admin/storage/edit/?g=<?php echo escape($_GET['g']); ?>&amp;r=<?php echo escape($_GET['r']); ?>&amp;dir=<?php echo $dir['id']; ?>&amp;quota=up" id="<?php echo $dir['id']; ?>_quota_upa" class="quota_upa<?php echo $cls; ?>" data-dir="<?php echo $dir['id']; ?>">
										<img id="<?php echo $dir['id']; ?>_quota_up" class="img editicon" src="/include/images/arrow_up.png" alt="Distribute remaining space" />
									</a>
									<?php
								}
							}
							?>
						</p>

						<?php
						*/
						if ($dir->children()->count() == 0)
						{
							if (in_array($dir->id, $removing) || in_array($dir->id, $configuring))
							{
								echo 'Delete Disabled - Operations Pending';
							}
							else
							{
								?>
								<p>
									<a href="<?php route('api.storage.delete', ['id' => $dir->id]); ?>"
										class="btn btn-danger dir-delete"
										data-dir="<?php echo $dir->id; ?>"
										data-path="<?php echo $dir->path; ?>">
										{{ trans('global.button.delete') }}
									</a>
								</p>
								<?php
							}
						}
						?>
					</div>
				<?php } ?>
			</fieldset>
@endif
		</div>
		<div class="col col-md-5">
			<table class="meta">
				<tbody>
					<tr>
						<th scope="row"><?php echo trans('storage::storage.FIELD_CREATED'); ?>:</th>
						<td>
							<?php if ($row->getOriginal('datetimecreated') && $row->getOriginal('datetimecreated') != '0000-00-00 00:00:00'): ?>
								<?php echo e($row->datetimecreated); ?>
							<?php else: ?>
								<?php echo trans('global.unknown'); ?>
							<?php endif; ?>
						</td>
					</tr>
					<?php if ($row->getOriginal('datetimeremoved') && $row->getOriginal('datetimeremoved') != '0000-00-00 00:00:00'): ?>
						<tr>
							<th scope="row"><?php echo trans('storage::storage.FIELD_REMOVED'); ?>:</th>
							<td>
								<?php echo e($row->datetimeremoved); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop