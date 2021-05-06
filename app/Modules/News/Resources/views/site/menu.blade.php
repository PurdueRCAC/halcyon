<nav aria-label="News Categories">
	<ul class="dropdown-menu">
		@if (auth()->user() && auth()->user()->can('manage news'))
			<li<?php if (!is_numeric($active) && $active == 'manage') { echo ' class="active"'; } ?>><a href="{{ route('site.news.manage') }}">{{ trans('news::news.manage news') }}</a></li>
		@endif
		<li<?php if (!is_numeric($active) && $active == 'search') { echo ' class="active"'; } ?>><a href="{{ route('site.news.search') }}">{{ trans('news::news.search news') }}</a></li>
		<li<?php if (!is_numeric($active) && $active == 'feeds') { echo ' class="active"'; } ?>><a href="{{ route('site.news.rss') }}">{{ trans('news::news.rss feeds') }}</a></li>
		<li><div class="separator"></div></li>
		@foreach ($types as $type)
			<li<?php if ($active == $type->id) { echo ' class="active"'; } ?>>
				<a href="{{ route('site.news.type', ['name' => $type->alias]) }}">
					{{ $type->name }}
				</a>
			</li>
		@endforeach
	</ul>
</nav>
