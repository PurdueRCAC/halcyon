
<div class="card session mb-3">
	<div class="card-header">
		<h3 class="card-title my-0">
			{{ trans('listener.users.sessions::sessions.sessions') }}
			<a href="#sessions_help" class="help help-dialog text-info tip" title="Roles Help">
				<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only"> Help</span>
			</a>
		</h3>
		<div id="sessions_help" class="dialog-help" title="{{ trans('listener.users.sessions::sessions.sessions') }}">
			<p>{{ trans('listener.users.sessions::sessions.explanation') }}</p>
		</div>
	</div>
	<ul class="list-group list-group-flush">
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
			<li class="list-group-item">
				<span class="none">{{ trans('global.none') }}</span>
			</li>
		@endif
	</ul>
</div>
