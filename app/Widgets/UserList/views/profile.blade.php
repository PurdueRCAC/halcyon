<?php
/**
 * User profile
 */
?>
@if ($user)
	<div class="profile mb-3">
		<div class="row">
			<div class="col-md-2">
				<div class="avatar mx-auto">
					<img class="profile_teaser_photo vertical" src="{{ $user->thumb }}" alt="{{ $user->name }}'s Profile Photo" />
				</div>
			</div>
			<div class="col-md-10">
				@if ($user->title)
					<p class="profile_title">{{ $user->title }}</p>
				@endif

				<ul class="profile_teaser_contact">
					@if ($params->get('show_office') && $user->office)
						<li><span class="fa fa-fw fa-building text-muted" aria-hidden="true"></span> {{ $user->office }}</li>
					@endif
					@if ($params->get('show_phone') && $user->phone)
						<li><span class="fa fa-fw fa-phone text-muted" aria-hidden="true"></span> {{ $user->phone }}</li>
					@endif
					@if ($params->get('show_email', 1) && $user->email)
						<li><span class="fa fa-fw fa-envelope text-muted" aria-hidden="true"></span> <a href="mailto:{{ $user->email }}">{{ $user->email }}</a></li>
					@endif
					@if ($params->get('show_specialty') && $user->specialty)
						<li><span class="fa fa-fw fa-info text-muted" aria-hidden="true"></span> {{ $user->specialty }}</li>
					@endif
				</ul>
			</div>
		</div>
		@if ($params->get('show_bio') && $user->bio)
			{!! $user->bio !!}
		@endif
	</div>
@else
	<p>{{ trans('widget.userlist::userlist.no users found') }}</p>
@endif
