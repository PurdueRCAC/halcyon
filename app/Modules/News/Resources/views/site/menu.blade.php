
	<ul class="dropdown-menu">
		<?php if (auth()->user() && auth()->user()->can('manage news')) { ?>
			<li><a href="{{ route('site.news.manage') }}">{{ trans('news::news.manage news') }}</a></li>
		<?php } else { ?>
			<li><a href="{{ route('site.news.search') }}">{{ trans('news::news.search news') }}</a></li>
		<?php } ?>
		<li><a href="{{ route('site.news.rss') }}">{{ trans('news::news.rss feeds') }}</a></li>
		<li><div class="separator"></div></li>
		<?php foreach ($types as $type): ?>
			<li>
				<a href="{{ route('site.news.type', ['name' => $type->name]) }}">
					{{ $type->name }}
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
