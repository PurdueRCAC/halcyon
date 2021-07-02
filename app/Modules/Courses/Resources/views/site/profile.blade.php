@push('scripts')
<script src="{{ asset('modules/core/vendor/handlebars/handlebars.min-v4.7.6.js') }}"></script>
<script src="{{ asset('modules/courses/js/site.js?v=' . filemtime(public_path() . '/modules/courses/js/site.js')) }}"></script>
@endpush

<div class="contentInner">

	<div class="row">
		<div class="col-md-9">
			<h2>{{ trans('courses::courses.my courses') }}</h2>
		</div>
		<div class="col-md-3 text-right">
			<a href="#add-account" data-hide=".table" data-icon="fa-times" data-text="<i class='fa fa-times' aria-hidden='true'></i> {{ trans('global.cancel') }}" class="btn btn-secondary add-account">
				<i class="fa fa-plus" aria-hidden="true"></i>
				{{ trans('courses::courses.add account') }}
			</a>
		</div>
	</div>

	@if (count($courses) == 0)
		<p class="alert alert-info">There are no active accounts for classes.</p>

		<h3>What is this page?</h3>
		<p>Here you can find courses that you're an instructor for that have been set up with access to <a href="{{ route('site.resources.compute.show', ['name' => 'scholar']) }}">Scholar</a>. When set up, all isntructors and students registered for the course will gain access to Scholar. To begin, click the "Add Class" button and select a course.</p>
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
							<i class="fa fa-question-circle" aria-hidden="true"></i> Help
						</a>
					<th scope="col"></th>
				</th>
			</thead>
			<tbody>
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

								if (is_array($class_data->enrollment))
								{
									foreach ($class_data->enrollment as $student)
									{
										// Attempt to look up student in our records
										$u = App\Modules\Users\Models\User::findByOrganizationId($student->externalId);

										if ($u)
										{
											$username = $u->username;

											// See if the they have host entry yet
											event($e = new App\Modules\Users\Events\UserLookup(['username' => $username, 'host' => 'scholar.rcac.purdue.edu']));
											/*$rows = 0;
											if ($rcac_ldap)
											{
												$foo = array();
												$rows = $rcac_ldap->query('(&(uid=' . $username . ')(host=scholar.rcac.purdue.edu))', array('uid'), $foo);
											}*/

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
								<?php
								if ($class->semester != 'Workshop' && $class_data)
								{
									echo $class_data->accounts;
									if (isset($class_data->enrollment))
									{
										echo ' / ' . count($class_data->enrollment);
									}
									else
									{
										echo ' / --';
									}
								}
								else
								{
									echo ($class->studentcount ? $class->studentcount : $class->members()->withTrashed()->whereIsActive()->count()) . ' / --';
								}
								?>
							</td>
							<td>
								<a href="#class_dialog_{{ $class->crn }}" class="edit help tip" title="{{ trans('global.edit') }}">
									<i class="fa fa-pencil" aria-hidden="true"></i><span class="sr-only">{{ trans('global.edit') }}</span>
								</a>
								<input type="hidden" id="HIDDEN_{{ $class->crn }}" value="{{ $class->id }}" />
							</td>
						</tr>
						<?php
						$total++;
					}
				}

				if (!$total)
				{
					?>
					<tr>
						<td class="text-center text-muted" colspan="7">
							{{ trans('global.none') }}
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>

		@foreach ($courses as $class)
			<div id="class_dialog_{{ $class->crn }}" title="Edit Accounts for Class" class="dialog dialog-class">
				<table class="table table-hover">
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
											<i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">Delete</span>
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
											<i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">Delete</span>
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
						<i class="fa fa-trash" aria-hidden="true"></i> Delete
					</button>
				</div>
			</div>
		@endforeach
	@endif

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
						<select class="form-control" id="new_classs_resource">
							@foreach ($resources as $resource)
								<option value="{{ $resource->id }}">{{ $resource->name }}</option>
							@endforeach
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="new_workshop_start" class="col-sm-2 col-form-label">Start Date</label>
					<div class="col-sm-10">
						<input type="text" class="form-control date-pick" id="new_workshop_start" placeholder="YYYY-MM-DD" />
						<span class="invalid-feedback">{{ trans('courses::courses.invalid.start date') }}</span>
					</div>
				</div>

				<div class="form-group row">
					<label for="new_workshop_end" class="col-sm-2 col-form-label">End Date</label>
					<div class="col-sm-10">
						<input type="text" class="form-control date-pick" id="new_workshop_end" placeholder="YYYY-MM-DD" />
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
				<a href="#createhelp" class="help icn tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i> Help</a>
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
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-2 col-form-label">Account Users</div>
					<div class="col-sm-10">
						All registered students <a href="#class_students_{{ $class->crn }}" class="btn btn-sm btn-default show-students" data-crn="new">View List</a><br/>
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
								<a href="#createhelp1" class="help icn tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i> Help</a>
							</label>
							<input type="text" class="form-control" size="30" id="estNum" value="" />
							<!-- <span class="form-text text-muted">Please provide the number of students you expect to enroll in this course. This is especially helpful when creating the class account prior to the semester when no enrollment data is available yet.</span> -->

							<div id="createhelp1" class="dialog dialog-help" title="Expected number of students">
								<p>Please provide the number of students you expect to enroll in this course. This is especially helpful when creating the class prior to the semester when we have no enrollment data.</p>
							</div>
						</div>

						<div class="form-group">
							<label for="classMeetings">
								Class meeting times (and size if multiple sections):
								<a href="#createhelp2" class="help icn tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
							</label>
							<textarea class="form-control" cols="60" rows="3" id="classMeetings"></textarea>
							<!-- <span class="form-text text-muted">Providing your class schedule, and how many students per class (if you have multiple labs/lectures),  gives us an idea of how classes will be connecting throughout the school day. This is especially helpful if you have large meetings so we can anticipate large bursts of activity. Enter in any convenient format.</span> -->

							<div id="createhelp2" class="dialog dialog-help" title="Class meeting times">
								<p>Providing your class schedule, and how many students per class (if you have multiple labs/lectures),  gives us an idea of how classes will be connecting throughout the school day. This is especially helpful if you have large meetings so we can anticipate large bursts of activity. Enter in any convenient format.</p>
							</div>
						</div>

						<div class="form-group">
							<label for="courseResources">
								Specific applications, tools and resources required:
								<a href="#createhelp3" class="help icn tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
							</label>
							(e.g. Matlab or R Studio Server)<br/>
							<textarea class="form-control" cols="60" rows="3" id="courseResources"></textarea>

							<div id="createhelp3" class="dialog dialog-help" title="Software and resources">
								<p>Please provide a list of applications and software you expect to use. This gives us an idea of what software we should provide. If you do not see software installed you would like us to look at installing please <a href="mailto:rcac-help@purdue.edu">contact us</a>.</p>
							</div>
						</div>

						<div class="form-group">
							<label for="dueDates">
								Anticipated assignment due dates:
								<a href="#createhelp4" class="help icn tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i> Help</a>
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
