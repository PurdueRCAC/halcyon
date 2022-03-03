

	<div class="card session mb-3">
		<div class="card-header">
			<h3 class="card-title">{{ trans('listener.users.sessions::sessions.sessions') }}</h3>
			<p class="mt-0">{{ trans('listener.users.sessions::sessions.explanation') }}</p>
		</div>
		<ul class="list-group list-group-flush">
			@if (count($user->sessions))
				@foreach ($user->sessions as $session)
					<li class="list-group-item">
						<div class="row">
							<div class="col-md-1">
								<span class="fa fa-desktop" aria-hidden="true"></span>
							</div>
							<div class="col-md-11">
								<div class="session-ip card-title">
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
											@php
											$agent = new Jenssegers\Agent\Agent();
											$agent->setUserAgent($session->user_agent);
											@endphp
											<strong>{{ trans('listener.users.sessions::sessions.device') }}:</strong>
											@if ($agent->isDesktop())
												{{ $agent->browser() }} on {{ $agent->platform() }}
											@else
												{{ $agent->browser() }} on {{ $agent->device() }}
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
