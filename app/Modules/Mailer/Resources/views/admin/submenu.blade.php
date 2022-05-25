
<nav class="container-fluid" aria-label="{{ trans('mailer::mail.module sections') }}">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'messages') active @endif" href="{{ route('admin.mailer.index') }}">{{ trans('mailer::mail.messages') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'templates') active @endif" href="{{ route('admin.mailer.templates') }}">{{ trans('mailer::mail.templates') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
