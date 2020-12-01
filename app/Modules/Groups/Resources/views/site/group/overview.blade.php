
					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Details
						</div>
						<div class="card-body panel-body">
							<div class="form-inline row">
								<label class="col-md-3" for="INPUT_name_<?php echo $group->id; ?>">Research Group Name:</label>

								<div class="col-md-7">
									<span id="SPAN_name_<?php echo $group->id; ?>">{{ $group->name }}</span>
									<input type="text" class="stash edit-property-input" id="INPUT_name_<?php echo $group->id; ?>" data-prop="name" data-value="<?php echo $group->id; ?>" />
								</div>
								<div class="col-md-2 text-right">
									@if ($canManage)
									<a href="{{ route('site.users.account.section', ['section' => 'groups', 'edit' => 'name']) }}" class="edit-property tip" data-prop="name" data-value="<?php echo $group->id; ?>" title="{{ trans('global.edit') }}"><!--
										--><i class="fa fa-pencil" id="IMG_name_<?php echo $group->id; ?>"></i><span class="sr-only">{{ trans('global.edit') }}</span><!--
									--></a>
									<a href="{{ route('site.users.account.section', ['section' => 'groups']) }}" class="cancel-edit-property tip stash" data-prop="name" data-value="<?php echo $group->id; ?>" title="{{ trans('global.cancel') }}"><!--
										--><i class="fa fa-ban" id="CANCELIMG_name_<?php echo $group->id; ?>"></i><span class="sr-only">{{ trans('global.cancel') }}</span><!--
									--></a>
									@endif
								</div>
							</div>
						</div>
					</div>

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Departments
						</div>
						<ul class="list-group list-group-flush">
					@if ($group->departments()->count())
						@foreach ($group->departments as $dept)
							<li class="list-group-item" id="department-{{ $dept->id }}" data-id="{{ $dept->id }}">
								<div class="row">
									<div class="col-md-11">
										@foreach ($dept->department->ancestors() as $ancestor)
											<?php if (!$ancestor->parentid) { continue; } ?>
											{{ $ancestor->name }} <span class="text-muted">&rsaquo;</span>
										@endforeach
										{{ $dept->department->name }}
									</div>
									<div class="col-md-1 text-right">
										@if ($canManage)
											<a href="#department-{{ $dept->id }}"
												class="delete delete-department remove-category"
												data-confirm="{{ trans('groups::groups.confirm delete') }}"
												data-api="{{ route('api.groups.groupdepartments.delete', ['group' => $group->id, 'id' => $dept->id]) }}">
												<i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">{{ trans('global.trash') }}</span>
											</a>
										@endif
									</div>
								</div>
							</li>
						@endforeach
					@elseif (!$canManage)
						<li class="list-group-item"><span class="none">{{ trans('global.none') }}</span></li>
					@endif
						@if ($canManage)
							<li class="list-group-item hidden" id="department-{id}" data-id="{id}">
								<div class="row">
									<div class="col-md-11">
										{name}
									</div>
									<div class="text-right">
										<a href="#department-{id}"
											class="delete delete-department remove-category"
											data-api="{{ route('api.groups.groupdepartments.create', ['group' => $group->id]) }}/{id}"
											data-confirm="{{ trans('groups::groups.confirm delete') }}">
											<span class="icon-trash glyph">{{ trans('global.trash') }}</span>
										</a>
									</div>
								</div>
							</li>
							<li class="list-group-item">
								<div class="row">
									<div class="col-md-11">
										<select name="department" id="new-department" data-category="collegedeptid" class="form-control searchable-select">
											<option value="0">{{ trans('groups::groups.select department') }}</option>
											<?php
											$departments = App\Modules\Groups\Models\Department::tree();
											?>
											@foreach ($departments as $d)
												@php
												if ($d->level == 0):
													continue;
												endif;
												@endphp
												<option value="{{ $d->id }}">{{ $d->prefix . $d->name }}</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-1 text-right">
										<a href="#new-department"
											class="add add-category add-row"
											data-row="#new-department-row"
											data-group="{{ $group->id }}"
											data-api="{{ route('api.groups.groupdepartments.create', ['group' => $group->id]) }}">
											<i class="fa fa-plus-circle" aria-hidden="true"></i><span class="sr-only">{{ trans('global.add') }}</span>
										</a>
									</div>
								</div>
							</li>
						@endif
						</ul>
						<!-- <script id="new-department-row" type="text/x-handlebars-template">
							<li class="list-group-item" id="department-<?php echo '{{ id }}'; ?>" data-id="<?php echo '{{ id }}'; ?>">
								<div class="row">
									<div class="col-md-11">
										<?php echo '{{#each ancestors}}'; ?>
											<?php echo '{{ this.name }}'; ?> <span class="text-muted">&rsaquo;</span>
										<?php echo '{{/each}}'; ?>
										<?php echo '{{ name }}'; ?>
									</div>
									<div class="col-md-1 text-right">
										<a href="#department-<?php echo '{{ id }}'; ?>"
											class="delete delete-department remove-category"
											data-confirm="{{ trans('groups::groups.confirm delete') }}"
											data-api="{{ route('api.groups.groupdepartments.create', ['group' => $group->id]) }}/{id}">
											<i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">{{ trans('global.trash') }}</span>
										</a>
									</div>
								</div>
							</li>
						</script> -->
					</div>

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Field of Science
						</div>
						<ul class="list-group list-group-flush">
					@if ($group->fieldsOfScience()->count())
						@foreach ($group->fieldsOfScience as $field)
							<li class="list-group-item" id="fieldofscience-{{ $field->id }}" data-id="{{ $field->id }}">
								<div class="row">
									<div class="col-md-11">
										@foreach ($field->field->ancestors() as $ancestor)
											<?php if (!$ancestor->parentid) { continue; } ?>
											{{ $ancestor->name }} <span class="text-muted">&rsaquo;</span>
										@endforeach
										{{ $field->field->name }}
									</div>
									<div class="col-md-1 text-right">
										@if ($canManage)
											<a href="#fieldofscience-{{ $field->id }}"
												class="delete delete-fieldofscience remove-category"
												data-confirm="{{ trans('groups::groups.confirm delete') }}"
												data-api="{{ route('api.groups.groupfieldsofscience.delete', ['group' => $group->id, 'id' => $field->id]) }}">
												<i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">{{ trans('global.trash') }}</span>
											</a>
										@endif
									</div>
								</div>
							</li>
						@endforeach
					@elseif (!$canManage)
						<li class="list-group-item"><span class="none">{{ trans('global.none') }}</span></li>
					@endif
						@if ($canManage)
							<li class="list-group-item hidden" id="fieldofscience-{id}" data-id="{id}">
								<div class="row">
									<div class="col-md-11">
										{name}
									</div>
									<div class="col-md-1 text-right">
										<a href="#fieldofscience-{id}"
											class="delete delete-fieldofscience remove-category"
											data-api="{{ route('api.groups.groupfieldsofscience.create', ['group' => $group->id]) }}/{id}"
											data-confirm="{{ trans('groups::groups.confirm delete') }}">
											<i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">{{ trans('global.trash') }}</span>
										</a>
									</div>
								</div>
							</li>
							<li class="list-group-item">
								<div class="row">
									<div class="col-md-11">
										<select name="fieldofscience" id="new-fieldofscience" data-category="fieldofscienceid" class="form-control searchable-select">
											<option value="0">{{ trans('groups::groups.select field of science') }}</option>
											<?php $fields = App\Halcyon\Models\FieldOfScience::tree(); ?>
											@foreach ($fields as $f)
												@php
												if ($f->level == 0):
													continue;
												endif;
												@endphp
												<option value="{{ $f->id }}">{{ $f->prefix . $f->name }}</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-1 text-right">
										<a href="#new-fieldofscience" class="add add-fieldofscience-row add-category"
											data-row="#new-fieldofscience-row"
											data-api="{{ route('api.groups.groupfieldsofscience.create', ['group' => $group->id]) }}">
											<i class="fa fa-plus-circle" aria-hidden="true"></i><span class="sr-only">{{ trans('global.add') }}</span>
										</a>
									</div>
								</div>
							</li>
						@endif
						</ul>
						<script id="new-fieldofscience-row" type="text/x-handlebars-template">
							<li class="list-group-item" id="fieldofscience-<?php echo '{{ id }}'; ?>" data-id="<?php echo '{{ id }}'; ?>">
								<div class="row">
									<div class="col-md-11">
										<?php echo '{{#each ancestors}}'; ?>
											<?php echo '{{ this.name }}'; ?> <span class="text-muted">&rsaquo;</span>
										<?php echo '{{/each}}'; ?>
										<?php echo '{{ name }}'; ?>
									</div>
									<div class="col-md-1 text-right">
										<a href="#fieldofscience-<?php echo '{{ id }}'; ?>"
											class="delete delete-fieldofscience remove-category"
											data-confirm="{{ trans('groups::groups.confirm delete') }}"
											data-api="{{ route('api.groups.groupfieldsofscience.create', ['group' => $group->id]) }}/{id}">
											<i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">{{ trans('global.trash') }}</span></a>
									</div>
								</div>
							</li>
						</script>
					</div>

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							<div class="row">
								<div class="col col-md-6">
									Unix Groups
									<a href="#box2_<?php echo $group->id; ?>" class="help tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i><span class="sr-only">Help</span></a>
								</div>
								<div class="col col-md-6 text-right">
									@if ($canManage)
										<?php if (count($group->unixgroups) > 0) { ?>
											<a href="#new-unixgroup_{{ $group->id }}" class="btn btn-default btn-sm add-unix-group help"><i class="fa fa-plus-circle" aria-hidden="true"></i> Add New Unix Group</a>
										<?php } elseif ($group->unixgroup != '') { ?>
											<button class="btn btn-default btn-sm create-default-unix-groups" data-group="<?php echo $group->id; ?>" data-value="<?php echo $group->unixgroup; ?>" id="INPUT_groupsbutton_<?php echo $group->id; ?>"><i class="fa fa-plus-circle" aria-hidden="true"></i> Create Default Unix Groups</button>
										<?php } ?>
									@endif
								</div>
							</div>
						</div>
						<div class="card-body panel-body">
							<div class="card panel panel-default">
								<div class="card-body panel-body">
									<?php if (count($group->unixgroups) == 0) { ?>
										<div class="form-inline row">
											<label class="col-md-3" for="INPUT_unixgroup_<?php echo $group->id; ?>">Base Name: <a href="#box1_<?php echo $group->id; ?>" class="help tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i><span class="sr-only">Help</span></a></label>

											<div class="col-md-5">
												<span id="SPAN_unixgroup_<?php echo $group->id; ?>">{{ trans('global.none') }}</span>

												<input type="text" class="hide form-control edit-property-input" id="INPUT_unixgroup_<?php echo $group->id; ?>" data-prop="unixgroup" data-value="<?php echo $group->id; ?>" placeholder="{{ trans('global.none') }}" value="" />
											</div>
											<div class="col-md-4 text-right">
												@if ($canManage)
												<a href="{{ route('site.users.account.section', ['section' => 'groups']) }}#edit-property" class="edit-property tip" data-prop="unixgroup" data-value="<?php echo $group->id; ?>" title="{{ trans('global.edit') }}"><!--
													--><i class="fa fa-pencil" id="IMG_unixgroup_<?php echo $group->id; ?>"></i><span class="sr-only">{{ trans('global.edit') }}</span><!--
												--></a>
												@endif
											</div>
										</div>
									<?php } else { ?>
										<div class="form-inline row">
											<label class="col-md-3" for="INPUT_unixgroup_<?php echo $group->id; ?>">Base Name: <a href="#box1_<?php echo $group->id; ?>" class="help tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i><span class="sr-only">Help</span></a></label>
											<div class="col-md-9">
												<?php echo $group->unixgroup ? $group->unixgroup : '<span class="text-muted">' . trans('global.none') . '</span>'; ?>
											</div>
										</div>
									<?php } ?>
								</div>
							</div>

							<div class="dialog dialog-help" id="box1_<?php echo $group->id; ?>" title="Base Unix Group">
								<p>This is the base name for all of your group's Unix groups. Once set, this name is not easily changed so please carefully consider your choice. If you wish to change it, email <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a> to discuss your options. Group base names may be named with the following guidelines.</p>
								<ul>
									<li>Should typically be the same as your queue name for consistency.</li>
									<li>May only contain lower case letters or numbers and must not begin with a number. Upper case letters and other characters are not permitted.</li>
									<li>Names must be at least 2 characters and no more than 10 characters.</li>
									<li>Must be unique.</li>
								</ul>
							</div>

							<?php if (count($group->unixgroups) > 0) { ?>
								<table id="actmaint_info" class="table table-hover">
									<caption class="sr-only">Unix Groups</caption>
									<thead>
										<tr>
											<th scope="col">Name</th>
											<th scope="col" class="extendedinfo hide">ACMaint Name</th>
											<th scope="col" class="extendedinfo hide">Short Name</th>
											<th scope="col" class="extendedinfo hide text-right">GID Number</th>
											<th scope="col" class="text-right">Actions</th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<td colspan="5" class="text-right">
												<button class="btn btn-default btn-sm reveal" data-toggle=".extendedinfo" data-text="<i class='fa fa-eye-slash'></i> Hide Extended Info</button>"><i class="fa fa-eye"></i> Show Extended Info</button>
											</td>
										</tr>
									</tfoot>
									<tbody>
										@foreach ($group->unixgroups as $unixgroup)
											<tr id="unixgroup-{{ $unixgroup->id }}" data-id="{{ $unixgroup->id }}">
												<td>{{ $unixgroup->longname }}</td>
												<td class="extendedinfo hide">{{ config('modules.groups.unix_prefix', 'rcac-') . $unixgroup->longname }}</td>
												<td class="extendedinfo hide">{{ $unixgroup->shortname }}</td>
												<td class="extendedinfo hide text-right">{{ $unixgroup->unixgid }}</td>
												<td class="text-right">
													<!--  && !preg_match("/rcs[0-9]{4}[0-9]/", $unixgroup->shortname)) -->
													@if ($canManage)
														<a href="{{ route('site.users.account.section', ['section' => 'groups', 'delete' => $unixgroup->id]) }}"
															class="delete delete-unix-group remove-unixgroup"
															data-api="{{ route('api.unixgroups.delete', ['id' => $unixgroup->id]) }}"
															data-confirm="{{ trans('groups::groups.confirm delete') }}"><!--
															--><i class="fa fa-trash"></i><span class="sr-only">{{ trans('global.delete') }}</span><!--
														--></a>
													@endif
												</td>
											</tr>
										@endforeach
										@if ($canManage)
										<tr class="hidden" id="unixgroup-{id}" data-id="{id}">
											<td>{longname}</td>
											<td class="extendedinfo hide">{{ config('modules.groups.unix_prefix', 'rcac-') }}{longname}</td>
											<td class="extendedinfo hide">{shortname}</td>
											<td class="extendedinfo hide text-right">0</td>
											<td class="text-right">
												<a href="#unixgroup-{id}"
													class="delete delete-unix-group remove-unixgroup"
													data-api="{{ route('api.unixgroups.create') }}/{id}"
													data-confirm="{{ trans('groups::groups.confirm delete') }}"><!--
													--><i class="fa fa-trash"></i><span class="sr-only">{{ trans('global.delete') }}</span><!--
												--></a>
											</td>
										</tr>
										@endif
									</tbody>
								</table>
							<?php } ?>

							<div class="dialog dialog-help" id="new-unixgroup_{{ $group->id }}" title="New Unix Group">
								<div class="form-group">
									<label for="longname" class="sr-only">{{ trans('groups::groups.name') }}</label>
									<span class="input-group">
										<span class="input-group-addon input-group-prepend"><span class="input-group-text">{{ $group->unixgroup }}-</span></span>
										<input type="text" name="longname" id="longname" class="form-control input-unixgroup" placeholder="{{ trans('groups::groups.name') }}" />
									</span>
								</div>
								<div class="text-right">
									<a href="#longname" class="btn btn-secondary btn-success add-unixgroup"
										data-group="{{ $group->id }}"
										data-container="#actmaint_info"
										data-api="{{ route('api.unixgroups.create') }}">
										<span class="icon-plus glyph">{{ trans('global.create') }}</span>
									</a>
								</div>
							</div>

							<div class="dialog dialog-help" id="box2_{{ $group->id }}" title="Unix Groups">
								<?php
								$doc = '';
								if (count($group->unixgroups) > 0)
								{
									?>
									<p>These are your group's Unix groups. You may create and delete additional custom groups as you need them. Any custom groups will be prefixed by your base name. Groups may be named with the following guidelines.</p>
									<ul>
										<li>May only contain lower case letters and numbers. Upper case letters and other characters are not permitted.</li>
										<li>Total name length, including prefix and hyphen may not exceed 17 characters.</li>
										<li>Must be a unique name.</li>
									</ul>
									<?php
								}
								else
								{
									?>
									<p>Your group's default groups may be created by pressing this button. You will need to create the default before creating any custom groups. Three groups will be created, a base group, apps, and data group. The names will be prefixed by your chosen group base name. Once these are created they are not easily changed to please carefully consider your base name choice.</p>
									<p>If you have any existing Unix groups, please do not continue with creating the defaults. Contact <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a> and ITaP Research Computing staff will assist in importing existing groups into the management system and create any remaining groups.<p>
									<?php
								}
								?>
							</div>
						</div><!-- / .card-body -->
					</div><!-- / .card -->
