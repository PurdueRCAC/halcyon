<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */
?>
<div class="news">
	<?php if ($params->get('show_title')): ?>
		<h3>
			<?php if ($params->get('newstypeid')): ?>
				{{ $type->name }}
			<?php else: ?>
				{{ $params->get('title') ? $params->get('title') : trans('widget.news::news.news') }}
			<?php endif; ?>
		</h3>
	<?php endif; ?>
	<?php if (count($articles)): ?>
		<ul class="newslist">
			<?php foreach ($articles as $article): ?>
				<li>
					<a href="{{ route('site.news.show', ['id' => $article->id]) }}">{{ $article->headline }}</a>
					<p class="date">{{ $article->formatDate($article->getOriginal('datetimenews'), $article->getOriginal('datetimenewsend')) }}</p>
					<?php if ($article->location) { ?>
						<p class="date">{{ $article->location }}</p>
					<?php } ?>
					<?php if ($article->getOriginal('datetimeupdate') != '0000-00-00 00:00:00' && $article->datetimeupdate != $article->datetimenewscreated) { ?>
						<p class="newsupdated">{{ trans('widget.news::news.updated') }}: {{ $article->formatDate($article->getOriginal('datetimeupdate')) }}</p>
					<?php } ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else: ?>
		<ul class="newslist">
			<li>{{ trans('widget.news::news.no articles found') }}</li>
		</ul>
	<?php endif; ?>
	<?php if ($params->get('more')): ?>
		<div class="more">
			<a href="<?php echo ($params->get('newstypeid') ? route('site.news.type', ['name' => $type->name]) : route('site.news.index')); ?>">
				{{ trans('widget.news::news.more') }}
			</a>
		</div>
	<?php endif; ?>
</div>
