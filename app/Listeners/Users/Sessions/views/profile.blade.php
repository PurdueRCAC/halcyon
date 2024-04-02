
<div class="card session mb-3">
	<div class="card-header">
		<h3 class="card-title my-0">
			{{ trans('listener.users.sessions::sessions.sessions') }}
			<a href="#sessions_help" data-toggle="modal" class="text-info tip" title="Roles Help">
				<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only visually-hidden">Help</span>
			</a>
		</h3>
		<div class="modal" id="sessions_help" tabindex="-1" aria-labelledby="sessions_help-title" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content shadow-sm">
					<div class="modal-header">
						<div class="modal-title" id="sessions_help-title">Sessions Help</div>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body dialog-body">
						<p>{{ trans('listener.users.sessions::sessions.explanation') }}</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<ul class="list-group list-group-flush my-0">
		@if (count($user->sessions))
			@foreach ($user->sessions as $session)
				@php
				$agent = new Jenssegers\Agent\Agent();
				$agent->setUserAgent($session->user_agent);
				@endphp
				<li class="list-group-item">
					<div class="row">
						<div class="col-md-1">
							@if ($agent->isTablet())
								<span class="fa fa-3x fa-tablet" aria-hidden="true"></span>
							@elseif ($agent->isMobile())
								<span class="fa fa-3x fa-mobile-phone" aria-hidden="true"></span>
							@else
								<span class="fa fa-3x fa-desktop" aria-hidden="true"></span>
							@endif
						</div>
						<div class="col-md-11">
							<div class="session-ip">
								<div class="row">
									<div class="col-md-6">
										<strong>{{ $session->ip_address == '::1' ? 'localhost' : $session->ip_address }}</strong>
									</div>
									<div class="col-md-6 text-right">
										{{ $session->last_activity->diffForHumans() }}
									</div>
								</div>
							</div>
							<div class="session-current card-text">
								<div class="row text-muted">
									<div class="col-md-6">
										<strong>{{ trans('listener.users.sessions::sessions.device') }}:</strong>
										@if ($agent->isDesktop())
											{{ trans('listener.users.sessions::sessions.browser on device', ['browser' => $agent->browser(), 'device' => $agent->platform()]) }}
										@else
											{{ trans('listener.users.sessions::sessions.browser on device', ['browser' => $agent->browser(), 'device' => $agent->device()]) }}
										@endif
									</div>
									<div class="col-md-6 text-right">
										@if ($session->id == session()->getId())
											<span class="badge badge-info float-right">{{ trans('listener.users.sessions::sessions.your current session') }}</span>
										@endif
									</div>
								</div>
							</div>
						</div>
					</div>
				</li>
			@endforeach
		@else
			<li class="list-group-item text-center">
				<span class="text-muted none">{{ trans('global.none') }}</span>
			</li>
		@endif
	</ul>
</div>
