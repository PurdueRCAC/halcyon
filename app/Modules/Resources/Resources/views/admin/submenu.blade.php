
<nav role="navigation" class="container-fluid">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (!trim($slot) || trim($slot) == 'edit') active @endif" href="{{ route('admin.resources.index') }}">{{ trans('resources::resources.resources') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'subresources') active @endif" href="{{ route('admin.resources.subresources') }}">{{ trans('resources::resources.subresources') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'types') active @endif" href="{{ route('admin.resources.types') }}">{{ trans('resources::resources.types') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'batchsystems') active @endif" href="{{ route('admin.resources.batchsystems') }}">{{ trans('resources::resources.batchsystems') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
