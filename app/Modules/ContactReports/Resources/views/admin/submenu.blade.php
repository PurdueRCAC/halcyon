
<nav class="container-fluid" aria-label="{{ trans('contactreports::contactreports.module sections') }}">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (!trim($slot) || trim($slot) == 'reports') active @endif" href="{{ route('admin.contactreports.index') }}">{{ trans('contactreports::contactreports.reports') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'types') active @endif" href="{{ route('admin.contactreports.types') }}">{{ trans('contactreports::contactreports.types') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'stats') active @endif" href="{{ route('admin.contactreports.stats') }}">{{ trans('contactreports::contactreports.stats') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
