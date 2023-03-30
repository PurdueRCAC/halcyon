<?php
/**
 * News widget layout
 */
?>
<div class="news<?php if ($params->get('class')) { echo ' ' .  $params->get('class'); } ?>">
	<?php if ($params->get('item_title')): ?>
		<{{ $params->get('item_heading', 'h3') }}>
			<?php if ($params->get('catid')): ?>
				{{ $params->get('title') ? $params->get('title') : $type->name }}
			<?php else: ?>
				{{ $params->get('title') ? $params->get('title') : trans('widget.news::news.news') }}
			<?php endif; ?>
		</{{ $params->get('item_heading', 'h3') }}>
	<?php endif; ?>
	<?php if (count($articles)): ?>
		<ul class="newslist list-unstyled">
			<?php foreach ($articles as $article): ?>
				<li id="article-{{ $article->id }}" aria-labelledby="article-{{ $article->id }}-title" itemscope itemtype="https://schema.org/<?php echo ($type->calendar ? 'Event' : 'NewsArticle'); ?>">
						<?php if ($params->get('show_image')): ?>
							<?php if ($src = $article->firstImage): ?>
								<div class="news-img float-left mr-3">
									<img src="<?php echo $src; ?>" alt="" width="150" />
								</div>
							<?php endif; ?>
						<?php endif; ?>
						<p id="article-{{ $article->id }}-title" class="news-title mb-0">
							<a href="{{ route('site.news.show', ['id' => $article->id]) }}">{{ $article->headline }}</a>
						</p>
						<ul class="news-meta list-inline text-muted">
							<li class="newsdate list-inline-item">
								<span class="fa fa-fw fa-clock-o" aria-hidden="true"></span>
								<time datetime="{{ $article->datetimenews->toDateTimeLocalString() }}">{{ $article->formatDate($article->datetimenews->toDateTimeString(), $article->hasEnd() ? $article->datetimenewsend->toDateTimeString() : null) }}</time>
								@if ($article->isToday())
									@if ($article->isNow())
										<span class="badge badge-success">{{ trans('news::news.happening now') }}</span>
									@else
										<span class="badge badge-info">{{ trans('news::news.today') }}</span>
									@endif
								@elseif ($article->isTomorrow())
									<span class="badge">{{ trans('news::news.tomorrow') }}</span>
								@endif
								@if ($article->isUpdated() && $article->datetimeupdate != $article->datetimenewscreated)
									<span class="badge badge-warning">
										<span class="fa fa-exclamation-circle" aria-hidden="true"></span>
										{{ trans('widget.news::news.updated') }}: <time datetime="{{ $article->datetimeupdate->toDateTimeLocalString() }}">{{ $article->formatDate($article->datetimeupdate->toDateTimeString()) }}</time>
									</span>
								@endif
							</li>
							@if ($params->get('show_location') && $article->location)
								<li class="newslocation list-inline-item">
									{{ $article->location }}
								</li>
							@endif
						</ul>
						@if ($params->get('blurb_length', 150) > 0)
							<p class="newsblurb" itemprop="description">
								{{ Illuminate\Support\Str::limit(strip_tags($article->toHtml()), $params->get('blurb_length', 150)) }}
							</p>
						@endif
				</li>
			<?php endforeach; ?>
		</ul>
		<?php if (!$limit): ?>
			<?php echo $articles->render(); ?>
		<?php endif; ?>
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
