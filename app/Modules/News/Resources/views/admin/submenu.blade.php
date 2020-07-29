
<nav role="navigation">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (!trim($slot) || trim($slot) == 'articles') active @endif" href="{{ route('admin.news.index') }}">{{ trans('news::news.articles') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'templates') active @endif" href="{{ route('admin.news.templates') }}">{{ trans('news::news.templates') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'types') active @endif" href="{{ route('admin.news.types') }}">{{ trans('news::news.types') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
