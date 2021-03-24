
<nav class="container-fluid" aria-label="{{ trans('users::users.module sections') }}">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (!trim($slot) || trim($slot) == 'edit') active @endif" href="{{ route('admin.users.index') }}">{{ trans('users::users.users') }}</a>
		</li>
		<!-- <li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'notes') active @endif" href="{{ route('admin.users.notes') }}">{{ trans('users::users.notes') }}</a>
		</li> -->
	@if (auth()->user()->can('admin'))
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'roles') active @endif" href="{{ route('admin.users.roles') }}">{{ trans('users::users.roles') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'levels') active @endif" href="{{ route('admin.users.levels') }}">{{ trans('users::users.levels') }}</a>
		</li>
	@endif
	</ul>
</nav><!-- / .sub-navigation -->
