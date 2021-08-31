@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.css?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/handlebars/handlebars.min-v4.7.6.js?v=' . filemtime(public_path() . '/modules/core/vendor/handlebars/handlebars.min-v4.7.6.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/datatables.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/datatables.min.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.js')) }}"></script>
<script src="{{ asset('modules/courses/js/site.js?v=' . filemtime(public_path() . '/modules/courses/js/site.js')) }}"></script>
@endpush

<div class="contentInner">

	<div class="row">
		<div class="col-md-9">
			<h2>{{ trans('courses::courses.my courses') }}</h2>
		</div>
		<div class="col-md-3 text-right">
			<a href="#add-account" data-hide="#account-list" data-icon="fa-times" data-text="<span class='fa fa-times' aria-hidden='true'></span> {{ trans('global.cancel') }}" class="btn btn-secondary add-account">
				<span class="fa fa-plus" aria-hidden="true"></span>
				{{ trans('courses::courses.add account') }}
			</a>
		</div>
	</div>

	<div id="account-list">
	@if (count($courses) == 0)
		<div class="card card-help">
			<div class="card-body">
				<h3 class="card-title">What is this page?</h3>
				<p>Here you can find courses that you're an instructor for that have been set up with access to <a href="{{ route('site.resources.compute.show', ['name' => 'scholar']) }}">Scholar</a>. When set up, all isntructors and students registered for the course will gain access to Scholar. To begin, click the "Add Class" button and select a course.</p>
			</div>
		</div>
	@else
		<div id="counthelp" class="dialog dialog-help" title="Account Counts">
			<p>This shows a count of all student accounts associated with this course. The numbers are the number of accounts currently active out of the enrolled students.</p>

			<p>If you just added a new course, accounts are processed overnight, so at first you will see 0 accounts (or a small number active through another course). A small number of missing accounts may be due to students who just registered for the course.</p>

			<p>Note: The total count is a union of all students in all sections/CRNs of your course (even if you just added one CRN), and not the count of each individual section. As well, we may not receive complete enrollment data until the start of the semester.</p>
		</div>

		<table class="table">
			<caption class="sr-only">Class accounts for {{ $user->name }}</caption>
			<thead>
				<tr>
					<th scope="col">Resource</th>
					<th scope="col">Class</th>
					<th scope="col">Semester</th>
					<th scope="col">Starts</th>
					<th scope="col">Ends</th>
					<th scope="col" class="text-center">
						Accounts / Enrolled
						<a href="#counthelp" class="help icn tip" title="Help">
							<span class="fa fa-question-circle" aria-hidden="true"></span> Help
						</a>
					</th>
					<th scope="col"<?php if (auth()->user()->can('manage courses')) { echo ' colspan="2"'; } ?>></th>
				</th>
			</thead>
			<tbody id="accounts">
				<?php
				$now = Carbon\Carbon::now();
				$total = 0;

				foreach ($courses as $class)
				{
					if ($class->datetimestop > $now
					 || $class->semester == 'Workshop')
					{
						$class_data = null;

						// Find class data
						foreach ($classes as $c)
						{
							if ($c->classExternalId == $class['crn'])
							{
								$class_data = $c;
								$class_data->accounts = 0;

								$class->classid = $c->courseId;
								event($e = new App\Modules\Courses\Events\AccountEnrollment($class));

								$class_data->enrollment = $e->enrollments;

								if (is_array($class_data->enrollment))
								{
									foreach ($class_data->enrollment as $student)
									{
										// Attempt to look up student in our records
										$u = App\Modules\Users\Models\User::findByOrganizationId($student->externalId);

										if ($u)
										{
											//$username = $u->username;

											// See if the they have host entry yet
											event($e = new App\Modules\Users\Events\UserLookup(['username' => $u->username, 'host' => $class->resource->rolename . '.rcac.purdue.edu']));

											if (count($e->results) > 0)
											{
												$class_data->accounts++;
											}
										}
									}
								}
								break;
							}
						}

						$resource = $class->resource;
						?>
						<tr>
							<td>
								{{ $resource ? $resource->name : trans('global.unknown') }}
							</td>
							<td>
								@if ($class->semester == 'Workshop')
									{{ $class->classname }}
								@else
									{{ $class->department . ' ' . $class->coursenumber . ' (' . $class->crn . ')' }}
								@endif
							</td>
							<td>
								{{ $class->semester }}
							</td>
							<td>
								{{ $class->datetimestart->format('Y-m-d') }}
							</td>
							<td>
								{{ $class->datetimestop->format('Y-m-d') }}
							</td>
							<td class="text-center">
								<a class="tip" data-toggle="collapse" data-parent="#accounts" href="#collapse{{ $class->id }}" title="View Accounts">
								@if ($class->semester != 'Workshop' && $class_data)
									{{ $class_data->accounts }}
									@if (isset($class_data->enrollment))
										{{ ' / ' . count($class_data->enrollment) }}
									@else
										{{ ' / --' }}
									@endif
								@else
									{{ ($class->studentcount ? $class->studentcount : $class->members()->withTrashed()->whereIsActive()->count()) . ' / --' }}
								@endif
								</a>
							</td>
							@if (auth()->user()->can('manage courses'))
							<td>
								<a href="#class_dialog_{{ $class->crn }}_edit" class="edit help" title="{{ trans('global.edit') }}">
									<span class="fa fa-pencil" aria-hidden="true"></span><span class="sr-only">{{ trans('global.edit') }}</span>
								</a>
							</td>
							@endif
							<td>
								<a href="#class_dialog_{{ $class->crn }}_edit" class="text-danger account-delete" data-confirm="Are you sure you wish to delete this class account?" data-id="{{ $class->id }}" data-api="{{ route('api.courses.delete', ['id' => $class->id]) }}">
									<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">Delete</span>
								</a>
								<?php /*<div class="dropdown dropleft">
									<button class="btn btn-options fa fa-ellipsis-h" type="button" id="dropdownMenuButton{{ $member->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<span class="sr-only">Options</span>
									</button>
									<div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $member->id }}">
										@if (count($queues))
										<a href="#member{{ $member->id }}" class="dropdown-item btn membership-allqueues allqueues" title="Enable all queues for this user">
											<span class="fa fa-fw fa-check-square" aria-hidden="true"></span> Enable all queues
										</a>
										@endif
										<a href="#member{{ $member->id }}" class="dropdown-item btn membership-move change" data-api="{{ route('api.groups.members.update', ['id' => $member->id]) }}" data-target="3" title="Grant usage viewer privleges">
											<span class="fa fa-fw fa-bar-chart" aria-hidden="true"></span> Grant usage viewer privleges
										</a>
										<a href="#member{{ $member->id }}" class="dropdown-item btn membership-move promote" data-api="{{ route('api.groups.members.create') }}" data-target="2" data-userid="{{ $member->userid }}" title="Grant manager privleges">
											<span class="fa fa-fw fa-arrow-up" aria-hidden="true"></span> Grant manager privleges
										</a>
										<a href="#member{{ $member->id }}" class="dropdown-item btn membership-remove delete" data-api="{{ $member->groupid ? route('api.groups.members.delete', ['id' => $member->id]) : '' }}" title="Remove from group">
											<span class="fa fa-fw fa-trash" aria-hidden="true"></span> Remove from group
										</a>
										<a href="#USER_{{ $usr->id }}_{{ $class->crn }}" class="dropdown-item btn user-delete delete" data-api="{{ route('api.courses.members.delete', ['id' => $usr->id]) }}" data-confirm="Are you sure you wish to remove this user?" data-user="{{ $usr->id }}" data-crn="{{ $class->crn }}">
											<span class="fa fa-fw fa-trash" aria-hidden="true"></span><span class="sr-only">Delete</span>
										</a>
									</div>
								</div>*/ ?>

								<input type="hidden" id="HIDDEN_{{ $class->crn }}" value="{{ $class->id }}" />
							</td>
						</tr>
						<tr class="collapse" id="collapse{{ $class->id }}">
							<td colspan="7">

								<div class="float-right">
									<a href="#class_dialog_{{ $class->crn }}_add" class="btn btn-info help">
										<span class="fa fa-plus" aria-hidden="true"></span>
										Add users
									</a>
								</div>
								<table class="table table-hover datatable">
									<caption class="sr-only">Account Users</caption>
									<thead>
										<th scope="col">Name</th>
										<th scope="col">Username</th>
										<!-- <th scope="col">Type</th> -->
										<th scope="col" class="text-right">Options</th>
									</thead>
									<tbody>
										<?php
										$members = $class->members()
											->withTrashed()
											->whereIsActive()
											->where('membertype', '>=', 0)
											->orderBy('datetimecreated', 'asc')
											->get();

										if (count($members)):
											foreach ($members as $usr):
												?>
												<tr id="USER_{{ $usr->id }}_{{ $class->crn }}">
													<td>
														@if (auth()->user()->can('manage users'))
															<a href="{{ route('site.users.account', ['u' => $usr->userid]) }}">
														@endif
														{{ $usr->user ? $usr->user->name : $usr->userid }}
														@if (auth()->user()->can('manage users'))
															</a>
														@endif
													</td>
													<td>
														@if (auth()->user()->can('manage users'))
															<a href="{{ route('site.users.account', ['u' => $usr->userid]) }}">
														@endif
														{{ $usr->user ? $usr->user->username : trans('global.unknown') }}
														@if (auth()->user()->can('manage users'))
															</a>
														@endif
													</td>
													<!-- <td>
														@if ($class->semester == 'Workshop')
															@if ($usr->membertype > 0)
																<span class="badge badge-success">Manual addition</span>
															@else
																<span class="badge badge-secondary">Automatic addition</span>
															@endif
														@else
															@if ($usr->membertype > 0)
																<span class="badge badge-success">Instructor/TA</span>
															@else
																<span class="badge badge-secondary">Student</span>
															@endif
														@endif
													</td> -->
													<td class="text-right">
														<input type="hidden" id="HIDDEN_{{ $usr->id }}_{{ $class->crn }}" value="{{ $usr->id }}" />
														<a href="#USER_{{ $usr->id }}_{{ $class->crn }}" class="user-delete delete" data-api="{{ route('api.courses.members.delete', ['id' => $usr->id]) }}" data-confirm="Are you sure you wish to remove this user?" data-user="{{ $usr->id }}" data-crn="{{ $class->crn }}">
															<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">Delete</span>
														</a>
													</td>
												</tr>
												<?php
											endforeach;
										else:
											?>
											<span class="none">{{ trans('global.none') }}</span><br />
											<?php
										endif;
										?>
									</tbody>
								</table>

							</td>
						</tr>
						<?php
						$total++;
					}
				}

				if (!$total):
					?>
					<tr>
						<td class="text-center text-muted" colspan="7">
							{{ trans('global.none') }}
						</td>
					</tr>
					<?php
				endif;
				?>
			</tbody>
		</table>

		@foreach ($courses as $class)
		@if (auth()->user()->can('manage courses'))
			<div id="class_dialog_{{ $class->crn }}_edit" title="Edit Class Account" class="dialog dialog-class">
				<form id="class_{{ $class->crn }}" method="post" class="edit-form" action="{{ route('site.users.account.section', ['section' => 'class']) }}{{ request()->has('u') ? '?u=' . request()->input('u') : '' }}">

					<div class="form-group">
						<label for="resourceid-{{ $class->crn }}">{{ trans('courses::courses.resource') }}: <span class="required">*</span></label>
						<select name="resourceid" id="resourceid-{{ $class->crn }}" class="form-control" required>
							<option value="0">{{ trans('global.none') }}</option>
							<?php foreach ($resources as $resource): ?>
								<?php
								$selected = ($resource->id == $class->resourceid ? ' selected="selected"' : '');
								?>
								<option value="{{ $resource->id }}"<?php echo $selected; ?>>{{ str_repeat('- ', $resource->level) . $resource->name }}</option>
							<?php endforeach; ?>
						</select>
						<span class="invalid-feedback">{{ trans('courses::courses.invalid.resource') }}</span>
					</div>

					<div class="form-group">
						<label for="classname-{{ $class->crn }}">{{ trans('courses::courses.course name') }}:</label>
						<input type="text" name="classname" id="classname-{{ $class->crn }}" <?php if ($class->semester != 'Workshop') { echo 'disabled'; } ?> class="form-control" maxlength="255" value="{{ $class->classname }}" />
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group type-course<?php if ($class->semester == 'Workshop') { echo ' hide'; } ?><?php if ($class->semester == 'Workshop') { echo ' hide'; } ?>">
								<label for="crn-{{ $class->crn }}">{{ trans('courses::courses.crn') }}:</label>
								<input type="text" name="crn" id="crn-{{ $class->crn }}" <?php if ($class->semester != 'Workshop') { echo 'disabled'; } ?> class="form-control" maxlength="8" value="{{ $class->crn }}" />
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group type-course<?php if ($class->semester == 'Workshop') { echo ' hide'; } ?>">
								<label for="coursenumber-{{ $class->crn }}">{{ trans('courses::courses.course number') }}:</label>
								<input type="text" name="coursenumber" id="coursenumber-{{ $class->crn }}" <?php if ($class->semester != 'Workshop') { echo 'disabled'; } ?> class="form-control" maxlength="8" value="{{ $class->coursenumber }}" />
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group type-course<?php if ($class->semester == 'Workshop') { echo ' hide'; } ?>">
								<label for="department-{{ $class->crn }}">{{ trans('courses::courses.department') }}:</label>
								<input type="text" name="department" id="department-{{ $class->crn }}" <?php if ($class->semester != 'Workshop') { echo 'disabled'; } ?> class="form-control" maxlength="4" value="{{ $class->department }}" />
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group type-course<?php if ($class->semester == 'Workshop') { echo ' hide'; } ?>">
								<label for="reference-{{ $class->crn }}">{{ trans('courses::courses.reference') }}:</label>
								<input type="text" name="reference" id="reference-{{ $class->crn }}" <?php if ($class->semester != 'Workshop') { echo 'disabled'; } ?> class="form-control" maxlength="64" value="{{ $class->reference }}" />
							</div>
						</div>
					</div>

					<div class="form-group type-course<?php if ($class->semester == 'Workshop') { echo ' hide'; } ?>">
						<label for="semester-{{ $class->crn }}">{{ trans('courses::courses.semester') }}: <span class="required">*</span></label>
						<input type="text" name="semester" id="semester-{{ $class->crn }}" <?php if ($class->semester != 'Workshop') { echo 'disabled'; } ?> class="form-control" required maxlength="16" value="{{ $class->semester }}" />
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="datetimestart-{{ $class->datetimestart }}">{{ trans('courses::courses.start') }}:</label>
								<input type="text" name="datetimestart" id="datetimestart-{{ $class->crn }}" <?php if ($class->semester != 'Workshop') { echo 'disabled'; } ?> class="form-control date-pick" maxlength="8" value="{{ $class->datetimestart->format('Y-m-d') }}" />
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="datetimestop-{{ $class->crn }}">{{ trans('courses::courses.stop') }}:</label>
								<input type="text" name="datetimestop" id="datetimestop-{{ $class->crn }}" <?php if ($class->semester != 'Workshop') { echo 'disabled'; } ?> class="form-control date-pick" maxlength="8" value="{{ $class->datetimestop->format('Y-m-d') }}" />
							</div>
						</div>
					</div>

					<div class="alert alert-danger hide" id="error-{{ $class->crn }}"></div>

					<div class="dialog-footer text-right">
						<button class="btn btn-success account-save" data-crn="{{ $class->crn }}" data-api="{{ route('api.courses.update', ['id' => $class->id]) }}">
							<span class="spinner-border spinner-border-sm" role="status"></span> Save
						</button>
					</div>
				</form>
				<?php
				/*<table class="table table-hover">
					<caption class="sr-only">Edit Accounts for Class</caption>
					<tbody>
					<tr>
						<th scope="row">Class</th>
						<td>
							@if ($class->semester == 'Workshop')
								{{ $class->classname }}
							@else
								{{ $class->department . ' ' . $class->coursenumber . ' (' . $class->crn . ') - ' . $class->semester }}
									</td>
								</tr>
								<tr>
									<td>Class Name</td>
									<td>{{ $class->classname }}
							@endif
						</td>
					</tr>
					<tr>
						<th scope="row">Resource</th>
						<td>
							{{ $class->resource ? $class->resource->name : trans('global.unknown') }}
						</td>
					</tr>
					<tr>
						<th scope="row">Account Users</th>
						<td>
							All registered Instructors, TAs:

							<?php
							$members = $class->members()
								->withTrashed()
								->whereIsActive()
								->where('membertype', '>', 0)
								->get();

							if (count($members))
							{
								?>
								<ul id="class_people_{{ $class->crn }}" class="student-list">
									<?php
									foreach ($members as $usr)
									{
										?>
										<li id="USER_{{ $usr->id }}_{{ $class->crn }}">
											<a href="#USER_{{ $usr->id }}_{{ $class->crn }}" class="user-delete delete" data-api="{{ route('api.courses.members.delete', ['id' => $user->id]) }}" data-confirm="Are you sure you wish to remove this user?" data-user="{{ $user->id }}" data-crn="{{ $class->crn }}">
												<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">Delete</span>
											</a>
											{{ $usr->user ? $usr->user->name . ' (' . $usr->user->username . ')' : $usr->userid . ' ' . trans('global.unknown') }}
											<input type="hidden" id="HIDDEN_{{ $usr->id }}_{{ $class->crn }}" value="{{ $usr->id }}" />
										</li>
										<?php
									}
									?>
								</ul>
								<?php
							}
							else
							{
								?>
								<span class="none">{{ trans('global.none') }}</span><br />
								<?php
							}
							?>

							All registered students: <a href="#class_students_{{ $class->crn }}" class="show-students" data-crn="{{ $class->crn }}">[ View List ]</a><br/>

							<ul id="class_students_{{ $class->crn }}" class="student-list hide">
								<?php
								$members = $class->members()
									->withTrashed()
									->whereIsActive()
									->where('membertype', '=', 0)
									->get();

								foreach ($members as $usr)
								{
									?>
									<li id="USER_{{ $usr->id }}_{{ $class->crn }}">
										<a href="#USER_{{ $usr->id }}_{{ $class->crn }}" class="user-delete delete" data-api="{{ route('api.courses.members.delete', ['id' => $user->id]) }}" data-confirm="Are you sure you wish to remove this user?" data-user="{{ $user->id }}" data-crn="{{ $class->crn }}">
											<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">Delete</span>
										</a>
										{{ $usr->user ? $usr->user->name . ' (' . $usr->user->username . ')' : $usr->userid . ' ' . trans('global.unknown') }}
										<input type="hidden" id="HIDDEN_{{ $usr->id }}_{{ $class->crn }}" value="{{ $usr->id }}" />
									</li>
									<?php
								}
								?>
							</ul>

							@if ($class->semester != 'Workshop')
								<div class="form-group">
									<label for="searchuser_{{ $class->crn }}">Add instructors, TAs, or others:</label>
									<input id="searchuser_{{ $class->crn }}" class="form-control search-user" data-id="{{ $class->crn }}" data-api="{{ route('api.users.index') }}?search=%s" value="" />
									<div class="alert hide" id="searchuser_alert_{{ $class->crn }}" data-success="Successfully added person."></div>
								</div>
							@endif
						</td>
					</tr>
					@if ($class->semester == 'Workshop')
						<tr>
							<td colspan="2">
								<div class="form-group">
									<label for="bulkadd_{{ $class->crn }}">Bulk add users:</label>
									<textarea class="bulkAdd form-control" id="bulkadd_{{ $class->crn }}" rows="8" cols="40" placeholder="Username or email, comma or line seperated." id="users"></textarea>
								</div>
								<button class="btn btn-secondary account-add" data-crn="{{ $class->crn }}" data-id="{{ $class->id }}">Bulk Add Accounts</button>
							</td>
						</tr>
					@endif 
					</tbody>
				</table>
				<div class="form-group text-right">
					<button class="btn btn-danger account-delete" data-confirm="Are you sure you wish to delete this class account?" data-id="{{ $class->id }}">
						<span class="fa fa-trash" aria-hidden="true"></span> Delete
					</button>
				</div>*/ ?>
			</div>
		@endif

			<div id="class_dialog_{{ $class->crn }}_add" title="Add Users to Class Account" class="dialog dialog-class">
				<form action="{{ route('site.users.account.section', ['section' => 'class']) }}{{ request()->has('u') ? '?u=' . request()->input('u') : '' }}" method="post">
					<!-- <div class="form-group">
						<label for="searchuser_{{ $class->crn }}">Add instructors, TAs, or others:</label>
						<input id="searchuser_{{ $class->crn }}" class="form-control search-user" data-id="{{ $class->crn }}" data-api="{{ route('api.users.index') }}?search=%s" value="" />
						<div class="alert hide" id="searchuser_alert_{{ $class->crn }}" data-success="Successfully added person."></div>
					</div> -->

					<div class="form-group">
						<label for="bulkadd_{{ $class->crn }}">Bulk add users:</label>
						<textarea class="bulkAdd form-control" id="bulkadd_{{ $class->crn }}" rows="5" cols="40"></textarea>
						<span class="form-text text-muted">Username or email, comma or line seperated.</span>
					</div>

					<div class="form-group text-center">
						<button class="btn btn-secondary account-add" data-crn="{{ $class->crn }}" data-id="{{ $class->id }}">
							<span class="spinner-border spinner-border-sm" role="status"></span>
							Add
						</button>
					</div>

					@csrf
				</form>
				<?php /*
				<form action="{{ route('site.orders.import') }}" method="post" enctype="multipart/form-data">
					<p>Currently, only CSV files are accepted. Required columns are either <code>username</code> or <code>email</code>.</p>

					<div class="form-group dropzone">
						<div id="uploader" class="fallback" data-instructions="Click or Drop files" data-list="#uploader-list">
							<label for="upload">Choose a file<span class="dropzone__dragndrop"> or drag it here</span></label>
							<input type="file" name="file" id="upload" class="form-control-file" multiple="multiple" />
						</div>
						<div class="file-list" id="uploader-list"></div>
						<input type="hidden" name="tmp_dir" id="ticket-tmp_dir" value="{{ ('-' . time()) }}" />
					</div>

					<div class="text-center">
						<button class="order btn btn-primary" type="submit">
							Import
						</button>
					</div>

					@csrf
				</form>*/ ?>
			</div>
		@endforeach
	@endif
	</div>

	<form id="add-account" method="post" class="create-form hide editform" action="{{ route('site.users.account.section', ['section' => 'class']) }}{{ request()->has('u') ? '?u=' . request()->input('u') : '' }}">
		@if (auth()->user()->can('manage courses'))
			<div class="form-group">
				<label for="field-type">{{ trans('courses::courses.type') }}:</label>
				<select name="type" id="field-type" class="form-control">
					<option value="course">{{ trans('courses::courses.course') }}</option>
					<option value="workshop"{{ (count($classes) == 0 ? ' selected="selected"' : '') }}>{{ trans('courses::courses.workshop') }}</option>
				</select>
			</div>

			<fieldset class="type-workshop type-dependant">
				<legend>
					Create New Workshop
				</legend>

				<div class="form-group row">
					<label for="new_workshop_name" class="col-sm-2 col-form-label">Workshop Name</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" id="new_workshop_name" />
						<span class="invalid-feedback">{{ trans('courses::courses.invalid.name') }}</span>
					</div>
				</div>

				<div class="form-group row">
					<label for="new_workshop_resource" class="col-sm-2 col-form-label">{{ trans('courses::courses.resource') }}</label>
					<div class="col-sm-10">
						<select class="form-control" id="new_workshop_resource">
							@foreach ($resources as $resource)
								<option value="{{ $resource->id }}">{{ $resource->name }}</option>
							@endforeach
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="new_workshop_start" class="col-sm-2 col-form-label">Start Date</label>
					<div class="col-sm-10">
						<input type="text" name="start" class="form-control date-pick" id="new_workshop_start" placeholder="YYYY-MM-DD" />
						<span class="invalid-feedback">{{ trans('courses::courses.invalid.start date') }}</span>
					</div>
				</div>

				<div class="form-group row">
					<label for="new_workshop_end" class="col-sm-2 col-form-label">End Date</label>
					<div class="col-sm-10">
						<input type="text" name="end" class="form-control date-pick" id="new_workshop_end" placeholder="YYYY-MM-DD" />
						<span class="invalid-feedback">{{ trans('courses::courses.invalid.end date') }}</span>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-sm-2">
						<input type="hidden" id="new_workshop_reference" value="Workshop" />
						<input type="hidden" id="new_workshop_semester" value="Workshop" />
						<input type="hidden" id="new_workshop_crn" value="-1" />
						<input type="hidden" id="new_workshop_classid" value="-1" />
					</div>
					<div class="col-sm-10 offset-sm-2">
						<button class="btn btn-success btn-create-workshop" data-api="{{ route('api.courses.create') }}">Create Workshop</button>
					</div>
				</div>
			</fieldset>
		@else
			<input type="hidden" name="type" id="field-type" value="course" />
		@endif

		<fieldset class="type-course type-dependant">
			<legend>
				Create New Accounts for Classes
				<a href="#createhelp" class="help icn tip" title="Help"><span class="fa fa-question-circle" aria-hidden="true"></span> Help</a>
			</legend>

			<div id="createhelp" class="dialog dialog-help" title="Create Class">
				<p>To create accounts for your course, complete the following steps:</p>

				<ol>
					<li>Select from the list of your courses.</li>
					<li>Verify the Class Name and registration count (this is current so this may be zero if adding before the semester)</li>
					<li>Add any additional instructors, TAs, or auditors. This can be updated at any later time so you can skip if these are not known.</li>
					<li>Fill in as much of the extra information as possible. This information is helpful to us for capacity and software planning purposes.</li>
					<li>Click Create Accounts</li>
				</ol>

				<p>Please review any <a href="{{ route('page', ['uri' => 'policies']) }}">policies</a> for the chosen resource. Student accounts are created during the week prior to the start of the semester and removed one week after the grades deadline. Instructor and TA accounts are created overnight so you may log in and start planning your course.</p>

				<p>It is not strictly necessary to enter every single CRN for your course here, though you may. The system will include students enrolled in all sections of the course, including those taught by other instructors and that may not show here.</p>
			</div>

			@if (count($classes) == 0)
				<p class="alert alert-warning">You are not instructing any upcoming classes. Accounts for classes can only be created by instructors.</p>
			@else
				<div class="form-group row">
					<label for="new_class_select" class="col-sm-2 col-form-label">Class</label>
					<div class="col-sm-10">
						<select class="form-control" id="new_class_select" required>
							<option value="">(Select Class)</option>
							@foreach ($classes as $class)
								<option id="option_class_{{ $class->classExternalId }}"
									data-api="{{ route('api.courses.enrollments', ['crn' => $class->classExternalId, 'classid' => $class->courseId]) }}"
									data-crn="{{ $class->classExternalId }}"
									data-classid="{{ $class->classId }}"
									data-userid="{{ $user->id }}"
									data-semester="{{ $class->semester }}"
									data-start="{{ $class->start }}"
									data-stop="{{ $class->stop }}"
									data-classname="{{ $class->courseTitle }}"
									data-count="{{ $class->enrollment ? count($class->enrollment) : 0 }}"
									data-reference="{{ $class->reference }}"
									data-instructors="{{ json_encode($class->instructors) }}"
									data-students="<?php echo e('{ "students": ' . json_encode($class->student_list) . '}'); ?>">
									{{ $class->subjectArea . ' ' . $class->courseNumber . ' (' . $class->classExternalId . ') - ' . $class->semester }}
								</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-2 col-form-label">Class Name</div>
					<div class="col-sm-10">
						<!-- <input type="text" readonly class="form-control-plaintext"id="new_class_name" data-href="{{ route('site.users.account.section', ['section' => 'class']) }}{{ request()->has('u') ? '?u=' . request()->input('u') : '' }}" placeholder="(Select Class)" value="" /> -->
						<span id="new_class_name" data-href="{{ route('site.users.account.section', ['section' => 'class']) }}{{ request()->has('u') ? '?u=' . request()->input('u') : '' }}">(Select Class)</span>
					</div>
				</div>
				<div class="form-group row">
					<label for="new_classs_resource" class="col-sm-2 col-form-label">{{ trans('courses::courses.resource') }}</label>
					<div class="col-sm-10">
						<select class="form-control" id="new_class_resource">
							@foreach ($resources as $resource)
								<option value="{{ $resource->id }}">{{ $resource->name }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-2 col-form-label">Registration Count</div>
					<div class="col-sm-10">
						<span id="new_class_count"></span>
						<span class="spinner-border spinner-border-sm" role="status"></span>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-2 col-form-label">Account Users</div>
					<div class="col-sm-10">
						All registered students <a href="#class_students" class="btn btn-sm btn-default show-students" data-crn="new">View List</a><br/>
						Instructor: {{ $user->name }}<br/>
						Others: <br/>

						<ul id="class_people"></ul>

						<br/>
						<div class="form-group">
							<label for="searchuser">Add instructors, TAs, or others:</label><br/>
							<input type="text" id="searchuser" class="form-control" data-api="{{ route('api.users.index') }}?search=%s" value="" />
						</div>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-2 col-form-label">Addtional Information</div>
					<div class="col-sm-10">
						<div class="form-group">
							<label for="estNum">
								Expected number of students:
								<a href="#createhelp1" class="help icn tip" title="Help"><span class="fa fa-question-circle" aria-hidden="true"></span> Help</a>
							</label>
							<input type="text" class="form-control" size="30" id="estNum" value="" />

							<div id="createhelp1" class="dialog dialog-help" title="Expected number of students">
								<p>Please provide the number of students you expect to enroll in this course. This is especially helpful when creating the class prior to the semester when we have no enrollment data.</p>
							</div>
						</div>

						<div class="form-group">
							<label for="classMeetings">
								Class meeting times (and size if multiple sections):
								<a href="#createhelp2" class="help icn tip" title="Help"><span class="fa fa-question-circle" aria-hidden="true"></span></a>
							</label>
							<textarea class="form-control" cols="60" rows="3" id="classMeetings"></textarea>

							<div id="createhelp2" class="dialog dialog-help" title="Class meeting times">
								<p>Providing your class schedule, and how many students per class (if you have multiple labs/lectures),  gives us an idea of how classes will be connecting throughout the school day. This is especially helpful if you have large meetings so we can anticipate large bursts of activity. Enter in any convenient format.</p>
							</div>
						</div>

						<div class="form-group">
							<label for="courseResources">
								Specific applications, tools and resources required:
								<a href="#createhelp3" class="help icn tip" title="Help"><span class="fa fa-question-circle" aria-hidden="true"></span></a>
							</label>
							(e.g. Matlab or R Studio Server)<br/>
							<textarea class="form-control" cols="60" rows="3" id="courseResources"></textarea>

							<div id="createhelp3" class="dialog dialog-help" title="Software and resources">
								<p>Please provide a list of applications and software you expect to use. This gives us an idea of what software we should provide. If you do not see software installed you would like us to look at installing please <a href="mailto:{{ config('mail.from.address') }}">contact us</a>.</p>
							</div>
						</div>

						<div class="form-group">
							<label for="dueDates">
								Anticipated assignment due dates:
								<a href="#createhelp4" class="help icn tip" title="Help"><span class="fa fa-question-circle" aria-hidden="true"></span> Help</a>
							</label>
							<textarea class="form-control" cols="60" rows="3" id="dueDates"></textarea>

							<div id="createhelp4" class="dialog dialog-help" title="Due dates">
								<p>Please provide any expected due dates for major projects that will use Scholar. This is especially helpful for large courses where you might have a large number of students logging in at the last minute to complete projects.</p>
							</div>
						</div>

						<div class="form-group">
							<label for="additional">Additional information:</label>
							<textarea class="form-control" cols="60" rows="3" id="additional"></textarea>
						</div>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-sm-2"></div>
					<div class="col-sm-10 offset-sm-2">
						<input type="submit" value="{{ trans('courses::courses.create accounts') }}" data-api="{{ route('api.courses.create') }}" class="btn btn-success account-create" />
					</div>
				</div>
			@endif
		</fieldset>
	</form>
</div>
<script id="new-course-message" type="text/x-handlebars-template">
Scholar Class Account Request

| Field | Value |
|-------|-------|
| **Class:** | [<?php echo '{{ class_name }}'; ?>](<?php echo '{{ class_name_href }}'; ?>) |
| **Estimated Number of Students:** | <?php echo '{{ estNum }}'; ?> |
| **Class Meeting Times:** | <?php echo '{{ classMeetings }}'; ?> |
| **Course applications, tools, and resources:** | <?php echo '{{ courseResources }}'; ?> |
| **Estimated Due Dates:** | <?php echo '{{ dueDates }}'; ?> |
| **Additional Information:** | <?php echo '{{ additional }}'; ?> |
</script>
<script id="new-workshop-message" type="text/x-handlebars-template">
Scholar Workshop Account Request

| Field | Value |
|-------|-------|
| **Workshop name:** | <?php echo '{{ name }}'; ?> |
| **Start:** | <?php echo '{{ start }}'; ?> |
| **End:** | <?php echo '{{ end }}'; ?> |
</script>
