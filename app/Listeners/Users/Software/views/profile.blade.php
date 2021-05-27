@push('scripts')
<script src="{{ asset('modules/users/js/request.js?v=' . filemtime(public_path() . '/modules/users/js/request.js')) }}"></script>
@endpush

	<div class="contentInner">
		<h2>{{ trans('listener.users.software::software.title') }}</h2>

		<p>Most of the software installed on the clusters are either free or site-licensed for Purdue. However, some licenses have further restrictions such as your academic department or school. The software with additional restrictions for which you are eligible to access are listed below.</p>

		<table class="table simpleTable">
			<caption>
				Eligible Software
				<a href="#eligible" class="tip text-info help" title="Eligible Departments">
					<i class="fa fa-question-circle" aria-hidden="true"></i><span class="sr-only">Eligible Departments</span>
				</a>
			</caption>
			<thead>
				<tr>
					<th scope="col">Software</th>
					<th scope="col">Requirements</th>
					<th scope="col">Status</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$count = 0;

				// What do I have access to already?
				foreach ($software as $s):
					foreach ($unixgroups as $g):
						if ($s['group'] == $g->unixgroup->name):
							?>
							<tr>
								<td>{{ $s['name'] }}</td>
								<td>{{ $s['req'] }}</td>
								<td>Active</td>
							</tr>
							<?php
							$s['access'] = true;
							$count++;
						endif;
					endforeach;
				endforeach;

				if (!$user->department):
					?>
					<tr>
						<td colspan="3">
							<span class="alert alert-warning">You have no account on ITaP Research Computing resources. Please <a href="{{ route('page', ['uri' => 'account/request']) }}">request an account</a> first.</span>
						</td>
					</tr>
					<?php
				else:
					foreach ($software as $s):
						if (!$s['access'] && in_array($user->department, $s['dept_lower'])):
							?>
							<tr>
								<td>{{ $s['name'] }}</td>
								<td>{{ $s['req'] }}</td>
								<td><button class="btn btn-sm btn-secondary btn-software-request" data-group="{{ $s['groupid'] }}" data-user="{{ $user->id }}">Request</button></td>
							</tr>
							<?php
							$count++;
						endif;
					endforeach;
				endif;

				if ($count == 0):
					?>
					<tr>
						<td colspan="3">You have no software requests available.</td>
					</tr>
					<?php
				endif;
				?>
			</tbody>
		</table>

		<div id="eligible" class="dialog dialog-help" title="Eligible Departments">
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
					<li>{{ $dept }}</li>
				@endforeach
			</ul>
		</div>

		<p>To request software not already installed on the clusters, see the <a href="{{ route('page', ['uri' => 'policies/software']) }}">software installation policy</a>. If you do not see the software you expect above, or encounter any issues, please contact us at <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.</p>
	</div>