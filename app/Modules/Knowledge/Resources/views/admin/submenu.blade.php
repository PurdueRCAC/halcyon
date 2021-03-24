
<nav class="container-fluid" aria-label="{{ trans('knowledge::knowledge.module sections') }}">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'pages') active @endif" href="{{ route('admin.knowledge.index') }}">{{ trans('knowledge::knowledge.pages') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'snippets') active @endif" href="{{ route('admin.knowledge.snippets') }}">{{ trans('knowledge::knowledge.snippets') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
