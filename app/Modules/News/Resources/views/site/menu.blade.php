
	<ul class="dropdown-menu">
		<?php foreach ($types as $type): ?>
			<li<?php if ($active == $type->id) { echo ' class="active"'; } ?>>
				<a href="{{ route('site.news.type', ['name' => $type->name]) }}">
					{{ $type->name }}
				</a>
			</li>
		<?php endforeach; ?>
		<li><div class="separator"></div></li>
		<?php if (auth()->user() && auth()->user()->can('manage news')) { ?>
			<li<?php if ($active == 'manage') { echo ' class="active"'; } ?>><a href="{{ route('site.news.manage') }}">{{ trans('news::news.manage news') }}</a></li>
		<?php } else { ?>
			<li<?php if ($active == 'search') { echo ' class="active"'; } ?>><a href="{{ route('site.news.search') }}">{{ trans('news::news.search news') }}</a></li>
		<?php } ?>
		<li<?php if ($active == 'feeds') { echo ' class="active"'; } ?>><a href="{{ route('site.news.rss') }}">{{ trans('news::news.rss feeds') }}</a></li>
	</ul>
