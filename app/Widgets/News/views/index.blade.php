<?php
/**
 * News widget layout
 */
?>
<div class="news">
	<?php if ($params->get('show_title')): ?>
		<{{ $params->get('item_heading', 'h3') }}>
			<?php if ($params->get('catid')): ?>
				{{ $params->get('title') ? $params->get('title') : $type->name }}
			<?php else: ?>
				{{ $params->get('title') ? $params->get('title') : trans('widget.news::news.news') }}
			<?php endif; ?>
		</{{ $params->get('item_heading', 'h3') }}>
	<?php endif; ?>
	<?php if (count($articles)): ?>
		<ul class="newslist">
			<?php foreach ($articles as $article): ?>
				<li>
					<a href="{{ route('site.news.show', ['id' => $article->id]) }}">{{ $article->headline }}</a>
					<p class="date">{{ $article->formatDate($article->datetimenews->toDateTimeString(), $article->hasEnd() ? $article->datetimenewsend->toDateTimeString() : '0000-00-00 00:00:00') }}</p>
					@if ($article->location)
						<p class="date">{{ $article->location }}</p>
					@endif
					@if ($article->isUpdated() && $article->datetimeupdate != $article->datetimenewscreated)
						<p class="newsupdated">{{ trans('widget.news::news.updated') }}: {{ $article->formatDate($article->datetimeupdate->toDateTimeString()) }}</p>
					@endif
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else: ?>
		<ul class="newslist">
			@if ($params->get('catid'))
				<li class="first">{{ trans('widget.news::news.no type found', ['type' => $type->name]) }}</li>
			@else
				<li class="first">{{ trans('widget.news::news.no articles found') }}</li>
			@endif
		</ul>
	<?php endif; ?>
	<?php if ($params->get('more')): ?>
		<div class="more">
			<a href="<?php echo ($params->get('catid') ? route('site.news.type', ['name' => $type->name]) : route('site.news.index')); ?>">
				{{ trans('widget.news::news.more') }}
			</a>
		</div>
	<?php endif; ?>
</div>
