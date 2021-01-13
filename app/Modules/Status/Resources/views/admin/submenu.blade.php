<nav role="navigation" class="container-fluid">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (!trim($slot) || trim($slot) == 'issues') active @endif" href="{{ route('admin.issues.index') }}">{{ trans('issues::issues.issues') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'todos') active @endif" href="{{ route('admin.issues.todos') }}">{{ trans('issues::issues.todos') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->