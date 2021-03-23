
<nav role="navigation">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (!trim($slot) || trim($slot) == 'storage') active @endif" href="{{ route('admin.storage.index') }}">{{ trans('storage::storage.storage') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'directories') active @endif" href="{{ route('admin.storage.directories') }}">{{ trans('storage::storage.directories') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'types') active @endif" href="{{ route('admin.storage.types') }}">{{ trans('storage::storage.notification types') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
