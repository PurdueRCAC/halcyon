
<nav role="navigation" class="sub-navigation">
	<ul id="submenu">
		<li>
			<a @if (!trim($slot) || trim($slot) == 'reports') class="active" @endif href="{{ route('admin.contactreports.index') }}">{{ trans('contactreports::contactreports.reports') }}</a>
		</li>
		<li>
			<a @if (trim($slot) == 'comments') class="active" @endif href="{{ route('admin.contactreports.comments') }}">{{ trans('contactreports::contactreports.comments') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
