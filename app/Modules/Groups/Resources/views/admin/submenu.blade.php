
<nav role="navigation">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (!trim($slot) || trim($slot) == 'groups') active @endif" href="{{ route('admin.groups.index') }}">{{ trans('groups::groups.groups') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'fieldsofscience') active @endif" href="{{ route('admin.groups.fieldsofscience') }}">{{ trans('groups::groups.fields of science') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'departments') active @endif" href="{{ route('admin.groups.index') }}">{{ trans('groups::groups.departments') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
