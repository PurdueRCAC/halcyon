
<nav role="navigation">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (!trim($slot) || trim($slot) == 'history') active @endif" href="{{ route('admin.history.index') }}">{{ trans('history::history.changes') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'activity') active @endif" href="{{ route('admin.history.activity') }}">{{ trans('history::history.activity') }}</a>
		</li>
	</ul>
</nav>
