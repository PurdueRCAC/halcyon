
<div class="contentInner">
	<h2>{{ trans('listener.users.sessions::sessions.sessions') }}</h2>

	<div class="card panel panel-default session mb-3">
		<div class="card-header panel-heading">
			{{ trans('listener.users.sessions::sessions.sessions') }}
		</div>
		<ul class="list-group list-group-flush">
			@if (count($user->sessions))
				@foreach ($user->sessions as $session)
					<li class="list-group-item">
						<div class="row">
							<div class="col-md-1">
								<span class="fa fa-desktop"></span>
							</div>
							<div class="col-md-11">
								<div class="session-ip card-title">
									<div class="row">
										<div class="col-md-4">
											<strong>{{ $session->ip_address == '::1' ? 'localhost' : $session->ip_address }}</strong>
										</div>
										<div class="col-md-4">
											{{ $session->last_activity->diffForHumans() }}
										</div>
										<div class="col-md-4 text-right">
											@if ($session->id == session()->getId())
												<span class="badge badge-info float-right">{{ trans('listener.users.sessions::sessions.your current session') }}</span>
											@endif
										</div>
									</div>
								</div>
								<div class="session-current card-text text-muted">
									{{ $session->user_agent }}
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
</div>