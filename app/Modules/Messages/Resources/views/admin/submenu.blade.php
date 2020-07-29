
<nav role="navigation" class="container-fluid">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'messages') active @endif" href="{{ route('admin.messages.index') }}">{{ trans('messages::messages.messages') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'types') active @endif" href="{{ route('admin.messages.types') }}">{{ trans('messages::messages.types') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
