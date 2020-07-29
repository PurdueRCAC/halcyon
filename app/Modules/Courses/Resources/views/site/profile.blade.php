<div class="contentInner">

<h2>{{ trans('courses::courses.my courses') }}</h2>

@if (count($courses) == 0)
	<p class="alert alert-info">There are no active accounts for classes for {{ $user->name }}. Select from your classes above to activate accounts for a class.</p>
@else
	<div id="counthelp" class="dialog dialog-help" title="Account Counts">
		<p>This shows a count of all student accounts associated with this course. The numbers are the number of accounts currently active out of the enrolled students.</p>

		<p>If you just added a new course, accounts are processed overnight, so at first you will see 0 accounts (or a small number active through another course). A small number of missing accounts may be due to students who just registered for the course.</p>

		<p>Note: The total count is a union of all students in all sections/CRNs of your course (even if you just added one CRN), and not the count of each individual section. As well, we may not receive complete enrollment data until the start of the semester.</p>
	</div>

	<div id="createhelp" class="dialog dialog-help" title="Create Class">
		<p>To create Scholar accounts for your course, complete the following steps:</p>

		<ol>
			<li>Select from the list of your courses.</li>
			<li>Verify the Class Name and registration count (this is current so this may be zero if adding before the semester)</li>
			<li>Add any additional instructors, TAs, or auditors. This can be updated at any later time so you can skip if these are not known.</li>
			<li>Fill in as much of the extra information as possible. This information is helpful to us for capacity and software planning purposes.</li>
			<li>Click Create Accounts</li>
		</ol>

		<p>See the <a href="/policies/scholar/">Scholar Policy</a>. Short story: student accounts are created during the week prior to the start of the semester and removed one week after the grades deadline. Instructor and TA accounts are created overnight so you may log in and start planning your course.</p>

		<p>It is not strictly necessary to enter every single CRN for your course here, though you may. The system will include students enrolled in all sections of the course, including those taught by other instructors and that may not show here.</p>
	</div>

	<table class="table">
		<caption>Class accounts for {{ $user->name }}</caption>
		<thead>
			<tr>
				<th scope="col">Resource</th>
				<th scope="col">Class</th>
				<th scope="col">Semester</th>
				<th scope="col">Starts</th>
				<th scope="col">Ends</th>
				<th scope="col" class="text-right">
					Accounts / Enrolled <a href="#counthelp" class="help icn tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i> Help
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
					/*foreach ($classes as $c)
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
									$sql = "SELECT username FROM users JOIN userusernames ON userusernames.userid = users.id WHERE userusernames.dateremoved = '0000-00-00' AND puid = '" . $ws->db->escape_string($student->externalId) . "'";
									$data = array();
									$rows = $ws->db->query($sql, $data);

									if ($rows == 1)
									{
										$username = $data[0]['username'];
										$foo = array();
										// See if the they have host entry yet

										$rows = 0;
										if ($rcac_ldap)
										{
											$rows = $rcac_ldap->query('(&(uid=' . $username . ')(host=scholar.rcac.purdue.edu))', array('uid'), $foo);
										}

										if ($rows == 1)
										{
											$class_data->accounts++;
										}
									}
								}
							}
							break;
						}
					}*/

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
							<a href="#class_dialog_{{ $class->crn }}" class="help tip" title="Edit">
								<i class="fa fa-pencil" aria-hidden="true"></i><span class="sr-only">Edit</span>
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
</div>