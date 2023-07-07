@push('scripts')
	<script src="{{ asset('listeners/users/request/js/request.js?v=' . filemtime(public_path() . '/listeners/users/request/js/request.js')) }}"></script>
@endpush

	<div class="contentInner">
		<h2>{{ trans('listener.users.request::request.request access') }}</h2>

		<form method="get" action="{{ route('site.users.account.section', ['section' => 'request']) }}">
			<div id="request_header">
				<p>
					Researchers collaborating with faculty, staff, or departments who have purchased access to cluster
					nodes or research storage through the <a href="{{ url('/services/communityclusters/') }}">Community Cluster Program</a>
					will be able to select one of the Community Clusters or storage resources on the request form.
				</p>

				<p>
					Once the request is submitted, an email notification will be sent to your faculty advisor requesting approval
					of your request. Account request approvals are left to the discretion of the faculty or appropriate Community
					Cluster partners. <strong>Please contact them directly regarding the status of your request.</strong>
				</p>

				<p>
					To request an {{ config('app.name') }} account please complete the following steps.
				</p>
			</div><!-- / #request_header -->

			<div id="searchbox" class="card">
				<div class="card-header">
					<strong>1)</strong> Use the search box below to find the research group, faculty, or staff member you will be using {{ config('app.name') }} resources to conduct research for.
				</div>
				<div class="card-body">

						<div class="form-group">
							<label for="newuser">Search faculty or research group name:</label><br/>
							<div class="input-group">
								<input type="text" name="newuser" id="newuser" class="form-control searchgroups" data-source="{{ route('api.groups.index', ['api_token' => auth()->user()->api_token, 'search' => '']) }}" autocorrect="off" autocapitalize="off" />
								<!-- <div id="user_results" class="searchMain usersearch_results"></div>
								<select name="newuser" id="newuser" class="form-control searchuser"></select> -->
								<span class="input-group-append">
									<span class="input-group-text">
										<span class="fa fa-search" aria-hidden="true" id="add_button_a"></span>
									</span>
								</span>
							</div>
							<!-- <span id="add_errors"></span> -->
						</div>

				</div>
			</div><!-- / #request_header -->

			<div id="selection_info">
				<div id="person" class="card request-selection" style="display: none">
					<div class="card-header">
						<strong>1)</strong> Person Selected:
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col col-sm-12 col-md-8">
								<span id="personname"></span><br/>
								<span id="title"></span>
							</div>
							<div class="col col-sm-12 col-md-4">
								<a href="{{ route('site.users.account.section', ['section' => 'request']) }}" class="request-clear btn" title="Choose another person.">
									<span class="fa fa-undo" aria-hidden="true"></span> Change selection
								</a>
							</div>
						</div>
					</div>
					<input type="hidden" id="selected-user" value="" />
				</div>
				<div id="group" class="card request-selection" style="display: none">
					<div class="card-header">
						<strong>1)</strong> Group Selected:
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col col-sm-12 col-md-8">
								<span id="groupname">Name here</span><br/>
								<span id="dept"></span>
							</div>
							<div class="col col-sm-12 col-md-4">
								<a href="{{ route('site.users.account.section', ['section' => 'request']) }}" class="request-clear btn" title="Choose another group.">
									<span class="fa fa-undo" aria-hidden="true"></span> Change selection
								</a>
							</div>
						</div>
					</div>
					<input type="hidden" id="selected-group" value="" />
				</div>
			</div><!-- / #selection_info -->

			<div id="resources" class="card request-selection" style="display: none">
				<div class="card-header">
					<strong>2)</strong> Select which resources to request an account on:
				</div>
				<div class="card-body">
					<div id="resourcelist">
						<span class="text-muted">{{ trans('global.none') }}</span>
					</div>
				</div>
			</div><!-- / #resources -->

			<div id="queues" class="card request-selection" style="display: none">
				<div class="card-header">
					<strong>2)</strong> Select which queues and resources to request access to:
				</div>
				<div class="card-body">
					<div id="queuelist">
						<span class="text-muted">{{ trans('global.none') }}</span>
					</div>
				</div>
			</div><!-- / #queues -->

			<div id="no-resources" class="alert alert-warning d-none">
				<p>The faculty or group you select does not participate in any current <a href="/services/communityclusters/">Community Clusters</a>.</p>
				<p><strong>NOTE:</strong> This request form does not support requesting Data Depot at this time. You will need to ask your faculty member/advisor directly to add you to the appropriate groups from the <a href="/account/user/">Manage Users</a> page (they should have access to this link).</p>
				<p>You may try searching by your Department as some have queues they may grant you access to. If you are collaborating with another faculty member, try searching their name instead.</p>
				<p>Otherwise, your faculty may purchase access to the <a href="/services/communityclusters/">Community Cluster Program</a>.</p>
			</div>

			<div id="comments" class="card request-selection" style="display: none">
				<div class="card-header">
					<strong>3)</strong> Enter comments to send with your request <span class="badge badge-secondary">optional</span>:
				</div>
				<div class="card-body">
					<textarea rows="5" cols="60" id="commenttext" class="form-control"></textarea>
				</div>
			</div><!-- / #comments -->

			<div id="controls" class="card request-selection" style="display: none">
				<div class="card-header">
					<strong>4)</strong> Review your request before submitting.
				</div>
				<div class="card-body">
					<p>An email notification will be sent to the managers of the research group or faculty member for approval.</p>

					<p class="alert alert-warning">
						NOTE: Your request will need to be approved by the research group or faculty you have entered.
						Please direct any questions regarding approval status to those people.
					</p>

					<p>
						<input type="button" value="Submit Request" class="request-submit btn btn-primary" />
						<input type="button" value="Cancel" class="request-clear btn btn-default" />
					</p>
				</div>
			</div><!-- / #controls -->

			<div id="confirmation">
				<div id="cluster_confirmation" style="display: none">
					<p>
						Your request has been submitted. An email notification has been sent to the managers of this group
						to approve your request. You will be notified once your request is approved. You may view the status
						of your request from the "My Accounts" tab. <strong>Your request will need to be approved by the
						research group or faculty you have entered. Please direct any questions regarding approval status to
						those people.</strong> Below is a summary of your request.
					</p>
				</div>

				<div id="free_confirmation" style="display: none">
					<p>
						Your request has been submitted. An email notification has been sent to the faculty or staff member or
						managers of this group to approve your request. You will be notified once your request is approved. You
						may view the status of your request from the "Account" tab. <strong>Your request will need to be
						approved by the research group or faculty you have entered. Please direct any questions regarding
						approval status to those people.</strong> Below is a summary of your request.
					</p>
				</div>

				<div id="person_confirmation" class="card" style="display: none">
					<div class="card-header">Faculty/staff member selected:</div>
					<div class="card-body">
						<span id="personname_confirmation"></span>
					</div>
				</div>

				<div id="group_confirmation" class="card" style="display: none">
					<div class="card-header">Research group selected:</div>
					<div class="card-body">
						<span id="groupname_confirmation"></span>
					</div>
				</div>

				<div id="resources_confirmation" class="card" style="display: none">
					<div class="card-header">Requested resources:</div>
					<div class="card-body">
						<div id="resourcelist_confirmation">
						</div>
					</div>
				</div>

				<div id="queues_confirmation" class="card" style="display: none">
					<div class="card-header">Requested queues:</div>
					<div class="card-body">
						<div id="queuelist_confirmation">
						</div>
					</div>
				</div>

				<div id="comment_confirmation" class="card" style="display: none">
					<div class="card-header">Comments:</div>
					<div class="card-body">
						<div id="commenttext_confirmation">
						</div>
					</div>
				</div>
			</div><!-- / #confirmation -->

			<div id="errors"></div>

			@csrf
		</form>
	</div>
