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
		<a href="#add-account" data-hide=".table" data-icon="fa-times" data-text="<i class='fa fa-times' aria-hidden='true'></i> {{ trans('global.cancel') }}" class="btn btn-default add-account">
			<i class="fa fa-plus" aria-hidden="true"></i>
			{{ trans('courses::courses.add account') }}
		</a>
	</div>
</div>

@if (count($courses) == 0)
	<p class="alert alert-info">There are no active accounts for classes.</p>
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
				<th scope="col" class="text-right">
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
										event($e = new App\Modules\Users\Events\UserLookup($u, ['host' => 'scholar.rcac.purdue.edu']));
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
							{{ $resource->name }}
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
						<td class="align-right">
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
							?>
						</td>
						<td>
							<a href="#class_dialog_{{ $class->crn }}" class="help tip" title="{{ trans('global.edit') }}">
								<i class="fa fa-pencil" aria-hidden="true"></i><span class="sr-only">{{ trans('global.edit') }}</span>
							</a>
							<input type="hidden" id="HIDDEN_{{ $class->crn }}" value="{{ $class->id }}" />
						</td>
					</tr>
					<?php
				}
			}
			?>
		</tbody>
	</table>
@endif

	<form id="add-account" method="post" class="create-form hidden" action="{{ route('site.users.account.section', ['section' => 'class']) }}{{ request()->has('u') ? '?u=' . request()->input('u') : '' }}">
		<fieldset>
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

			<?php if (count($classes) != 0) { ?>
				<p class="alert alert-warning">You are not instructing any upcoming classes. Accounts for classes can only be created by instructors.</p>
			<?php } else { ?>
				<div class="form-group row">
					<label for="new_class_select" class="col-sm-2 col-form-label">Class</label>
					<div class="col-sm-10">
						<select class="form-control" id="new_class_select" required>
							<option value="">(Select Class)</option>
							<?php foreach ($classes as $class) { ?>
								<option id="option_class_<?php echo $class->classExternalId; ?>"
									data-crn="<?php echo $class->classExternalId; ?>"
									data-classid="<?php echo $class->classId; ?>"
									data-userid="<?php echo $user->id; ?>"
									data-semester="<?php echo escape($class->semester); ?>"
									data-start="<?php echo $class->start; ?>"
									data-stop="<?php echo $class->stop; ?>"
									data-classname="<?php echo escape($class->courseTitle); ?>"
									data-count="<?php echo $class->enrollment ? count($class->enrollment) : 0; ?>"
									data-reference="<?php echo $class->reference; ?>"
									data-instructors="<?php echo escape(json_encode($class->instructors)); ?>"
									data-students="<?php echo escape('{ "students": ' . json_encode($class->student_list) . '}'); ?>">
									<?php echo $class->subjectArea . ' ' . $class->courseNumber . ' (' . $class->classExternalId . ') - ' . $class->semester; ?>
								</option>
							<?php } ?>
																	<option id="option_class_65511" data-crn="65511" data-classid="939438188" data-userid="/ws/user/89578" data-semester="Fall 2020" data-start="2020-08-24" data-stop="2020-12-19" data-classname="Intermed Fluid Mech" data-count="33" data-reference="Fall2020PWL" data-instructors="[]" data-students="{ &quot;students&quot;: [&quot;acusator@purdue.edu&quot;,&quot;coolc@purdue.edu&quot;,&quot;damlen@purdue.edu&quot;,&quot;davi1381@purdue.edu&quot;,&quot;evans360@purdue.edu&quot;,&quot;ewestph@purdue.edu&quot;,&quot;guo468@purdue.edu&quot;,&quot;hess35@purdue.edu&quot;,&quot;ikatsamb@purdue.edu&quot;,&quot;jacks537@purdue.edu&quot;,&quot;jrivaspa@purdue.edu&quot;,&quot;jstibore@purdue.edu&quot;,&quot;kennelt@purdue.edu&quot;,&quot;leon28@purdue.edu&quot;,&quot;ltuite@purdue.edu&quot;,&quot;ma620@purdue.edu&quot;,&quot;nelso269@purdue.edu&quot;,&quot;nlucarel@purdue.edu&quot;,&quot;nsuriana@purdue.edu&quot;,&quot;obalican@purdue.edu&quot;,&quot;patel426@purdue.edu&quot;,&quot;pgarriso@purdue.edu&quot;,&quot;psardana@purdue.edu&quot;,&quot;quanz@purdue.edu&quot;,&quot;regan21@purdue.edu&quot;,&quot;smithe@purdue.edu&quot;,&quot;snyde172@purdue.edu&quot;,&quot;song692@purdue.edu&quot;,&quot;tomlin0@purdue.edu&quot;,&quot;ubajwa@purdue.edu&quot;,&quot;victor1@purdue.edu&quot;,&quot;yu864@purdue.edu&quot;,&quot;zdoerger@purdue.edu&quot;]}">
									ME 50900 (65511) - Fall 2020										</option>
																	<option id="option_class_26581" data-crn="26581" data-classid="1038038730" data-userid="/ws/user/89578" data-semester="Fall 2020" data-start="2020-08-24" data-stop="2020-12-19" data-classname="Intermed Fluid Mech" data-count="4" data-reference="Fall2020PWL" data-instructors="[]" data-students="{ &quot;students&quot;: [&quot;huan1483@purdue.edu&quot;,&quot;kulkar70@purdue.edu&quot;,&quot;mart1282@purdue.edu&quot;,&quot;wang2846@purdue.edu&quot;]}">
									ME 50900OL (26581) - Fall 2020										</option>
																	<option id="option_class_28221" data-crn="28221" data-classid="1053207956" data-userid="/ws/user/89578" data-semester="Fall 2020" data-start="2020-08-24" data-stop="2020-12-19" data-classname="Continuum Sim-Multiphase Flows" data-count="1" data-reference="Fall2020PWL" data-instructors="[]" data-students="{ &quot;students&quot;: [&quot;gupta598@purdue.edu&quot;]}">
									ME 59700ZC (28221) - Fall 2020										</option>
																	<option id="option_class_28605" data-crn="28605" data-classid="1058242693" data-userid="/ws/user/89578" data-semester="Fall 2020" data-start="2020-08-24" data-stop="2020-12-19" data-classname="Math Methods Fluid Mech" data-count="1" data-reference="Fall2020PWL" data-instructors="[]" data-students="{ &quot;students&quot;: [&quot;pandes@purdue.edu&quot;]}">
									ME 49800ZD (28605) - Fall 2020										</option>
																	<option id="option_class_65511" data-crn="65511" data-classid="1027859631" data-userid="/ws/user/89578" data-semester="Fall 2021" data-start="2021-08-23" data-stop="2021-12-18" data-classname="Intermed Fluid Mech" data-count="0" data-reference="Fall2021PWL" data-instructors="[]" data-students="{ &quot;students&quot;: []}">
									ME 50900 (65511) - Fall 2021										</option>
																	<option id="option_class_21488" data-crn="21488" data-classid="1064192006" data-userid="/ws/user/89578" data-semester="Spring 2022" data-start="2022-01-10" data-stop="2022-05-07" data-classname="Fluid Mechanics" data-count="0" data-reference="Spring2022PWL" data-instructors="[]" data-students="{ &quot;students&quot;: []}">
									ME 30900 (21488) - Spring 2022										</option>
						</select>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-2 col-form-label">Class Name</div>
					<div class="col-sm-10">
						<!-- <input type="text" id="new_class_name" readonly class="form-control-plaintext" data-href="{{ route('site.users.account.section', ['section' => 'class']) }}{{ request()->has('u') ? '?u=' . request()->input('u') : '' }}" placeholder="(Select Class)" value="" /> -->
						<span id="new_class_name" data-href="{{ route('site.users.account.section', ['section' => 'class']) }}{{ request()->has('u') ? '?u=' . request()->input('u') : '' }}">(Select Class)</span>
					</div>
				</div>
				<div class="form-group row">
					<label for="new_classs_resource" class="col-sm-2 col-form-label">{{ trans('courses::courses.resource') }}</label>
					<div class="col-sm-10">
						<select class="form-control" id="new_classs_resource">
							<?php foreach ($resources as $resource): ?>
								<option value="{{ $resource->id }}">{{ $resource->name }}</option>
							<?php endforeach; ?>
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
						All registered students <a href="{{ route('site.users.account') }}#showstudents" class="show-students" data-crn="new">[ View List ]</a><br/>
						Instructor: {{ $user->name }}<br/>
						Others: <br/>

						<div id="class_people"></div>

						<?php if ($class->semester != 'Workshop') { ?>
							<br/>
							<div class="form-group">
								<label for="searchuser">Add instructors, TAs, or others:</label><br/>
								<input type="text" id="searchuser" class="form-control" size="30" value="" />
							</div>
						<?php } ?>

						<br/>
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
						<input type="submit" value="{{ trans('courses::courses.create accounts') }}" data-uri="{{ route('api.users.create') }}" class="btn btn-success account-create" />
					</div>
				</div>
			<?php } ?>
		</fieldset>
	</form>
<script id="new-class-message" type="text/x-handlebars-template">
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
</div>