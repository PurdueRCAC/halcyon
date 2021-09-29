<?php
/**
 * User list
 */
?>
<div class="users">
	@if (count($users))
		<div class="row services profiles">
			@foreach ($users as $user)
				<div class="content col-lg-4 col-md-4 col-sm-6 col-xs-12 profile mb-3" data-specialty="{{ $user->specialty }}">
					<div class="profile-inner">
						<div class="profile-front">
							<div class="profile-wrap">
								<div class="avatar mx-auto"><img alt="{{ $user->name }}'s Profile Photo" class="profile_teaser_photo vertical" src="{{ $user->thumb }}" /></div>

								<p class="card-title profile_name">{{ $user->name }}</p>

								@if ($user->title)
									<p class="profile_teaser_title">{{ $user->title }}</p>
								@endif
							</div>
						</div>

						<div class="profile-back">
							<div class="profile-wrap">
								<ul class="profile_teaser_contact">
									@if ($params->get('show_office') && $user->office)
										<li>{{ $user->office }}</li>
									@endif
									@if ($params->get('show_phone') && $user->phone)
										<li>{{ $user->phone }}</li>
									@endif
									@if ($params->get('show_email', 1) && $user->email)
										<li><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></li>
									@endif
									<li> </li>
									@if ($params->get('show_specialty') && $user->specialty)
										<li>{{ $user->specialty }}</li>
									@endif
								</ul>

								<p><a class="profile-full" href="/about/staff/{{ $user->username }}">Full profile <span class="sr-only">for {{ $user->name }}</span> ›</a></p>
							</div>
						</div>
					</div>
				</div>
			@endforeach
		</div>
	@else
		<p>{{ trans('widget.userlist::userlist.no users found') }}</p>
	@endif
</div>
