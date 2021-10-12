<nav aria-label="News Categories">
	<ul class="nav">
		@if (auth()->user() && auth()->user()->can('manage news'))
			<li class="nav-item<?php if (!is_numeric($active) && $active == 'manage') { echo ' active'; } ?>"><a class="nav-link" href="{{ route('site.news.manage') }}">{{ trans('news::news.manage news') }}</a></li>
		@endif
		<li class="nav-item<?php if (!is_numeric($active) && $active == 'search') { echo ' active'; } ?>"><a class="nav-link" href="{{ route('site.news.search') }}">{{ trans('news::news.search news') }}</a></li>
		<li class="nav-item<?php if (!is_numeric($active) && $active == 'feeds') { echo ' active'; } ?>"><a class="nav-link" href="{{ route('site.news.rss') }}">{{ trans('news::news.rss feeds') }}</a></li>
		<li><div class="separator"></div></li>
		@foreach ($types as $type)
			<li class="nav-item<?php if ($active == $type->id) { echo ' active'; } ?>">
				<a class="nav-link" href="{{ route('site.news.type', ['name' => $type->alias]) }}">
					{{ $type->name }}
				</a>
			</li>
		@endforeach
	</ul>
</nav>
