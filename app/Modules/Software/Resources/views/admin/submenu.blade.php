
<nav class="container-fluid" aria-label="{{ trans('software::software.module sections') }}">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'applications') active @endif" href="{{ route('admin.software.index') }}">{{ trans('software::software.applications') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'types') active @endif" href="{{ route('admin.software.types') }}">{{ trans('software::software.types') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
