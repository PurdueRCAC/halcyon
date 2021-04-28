
<nav class="container-fluid" aria-label="{{ trans('finder::finder.module sections') }}">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (!trim($slot) || trim($slot) == 'facets') active @endif" href="{{ route('admin.finder.index') }}">{{ trans('finder::finder.facets') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'services') active @endif" href="{{ route('admin.finder.services') }}">{{ trans('finder::finder.services') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'fields') active @endif" href="{{ route('admin.finder.fields') }}">{{ trans('finder::finder.fields') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
