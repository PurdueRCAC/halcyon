
<nav class="container-fluid" aria-label="{{ trans('history::history.module sections') }}">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (!trim($slot) || trim($slot) == 'history') active @endif" href="{{ route('admin.history.index') }}">{{ trans('history::history.changes') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'activity') active @endif" href="{{ route('admin.history.activity') }}">{{ trans('history::history.activity') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'notifications') active @endif" href="{{ route('admin.history.notifications') }}">{{ trans('history::history.notifications') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'api') active @endif" href="{{ route('admin.history.api') }}">{{ trans('history::history.api') }}</a>
		</li>
	</ul>
</nav>
