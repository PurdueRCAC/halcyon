
<nav class="container-fluid" aria-label="{{ trans('publications::publications.module sections') }}">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'publications') active @endif" href="{{ route('admin.publications.index') }}">{{ trans('publications::publications.publications') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'types') active @endif" href="{{ route('admin.publications.types') }}">{{ trans('publications::publications.types') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
