@push('scripts')
<script src="{{ asset('modules/users/js/request.js?v=' . filemtime(public_path() . '/modules/users/js/request.js')) }}"></script>
@endpush

	<div class="contentInner">
		<h2>{{ trans('listener.users.software::software.title') }}</h2>

		<p>Most of the software installed on the clusters are either free or site-licensed for Purdue. However, some licenses have further restrictions such as your academic department or school. The software with additional restrictions for which you are eligible to access are listed below.</p>

		<table class="table">
			<caption>
				Software
			</caption>
			<thead>
				<tr>
					<th scope="col">Package</th>
					<th scope="col">Requirements</th>
					<th scope="col">Status</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($software as $i => $s):
					$active = false;
					// Check the user's unix groups to see if they already have access
					foreach ($unixgroups as $g):
						if ($s['group'] == $g->unixgroup->longname):
							$active = true;
							break;
						endif;
					endforeach;
					?>
					<tr>
						<td>{{ $s['name'] }}</td>
						<td>{{ $s['req'] }}</td>
					@if ($active)
						<td><span class="badge badge-success">Active</span></td>
					@else
						<td>
							@if (in_array(strtolower($user->department), $s['dept_lower']) || in_array(strtolower($user->school), $s['dept_lower']))
								<button class="btn btn-sm btn-secondary btn-software-request" data-group="{{ $s['groupid'] }}" data-user="{{ $user->id }}">Request</button>
							@else
								<span class="tip" title="You do not appear to be a part of an eligible department or school.">
									<span class="fa fa-exclamation-triangle text-warning" aria-hidden="true"></span> Ineligible
								</span>
							@endif
						</td>
					@endif
					</tr>
					<?php
				endforeach;
				?>
			</tbody>
		</table>

		<div id="eligible" class="dilog dilog-help" title="Eligible Departments">
			<div class="row">
				<div class="col-md-6">
					<h3>Eligible Departments</h3>
					<ul>
						<?php
						$depts = array();
						foreach ($software as $s):
							foreach ($s['dept'] as $dept):
								if (!in_array($dept, $depts)):
									$depts[] = $dept;
								endif;
							endforeach;
						endforeach;
						?>
						@foreach ($depts as $dept)
							<li>
								{{ $dept }}
							</li>
						@endforeach
					</ul>
				</div>
				<div class="col-md-6">
					@if (!$user->department && !$user->school)
						<h3>Your Department</h3>
						<p class="alert alert-warning">We are unable to determine your department or school. Please <a href="{{ route('page', ['uri' => 'help']) }}">contact support</a>.</p>
					@else
						@if ($user->department)
							<h3>Your Department</h3>
							<div>{{ $user->department }}</div>
						@endif
						@if ($user->school)
							<h3>Your School</h3>
							<div>{{ $user->school }}</div>
						@endif
					@endif
				</div>
			</div>
		</div>

		<p class="alert alert-info">To request software not already installed on the clusters, see the <a href="{{ route('page', ['uri' => 'policies/software']) }}">software installation policy</a>. If you do not see the software you expect above, or encounter any issues, please contact <a href="{{ route('page', ['uri' => 'help']) }}">support</a>.</p>
	</div>