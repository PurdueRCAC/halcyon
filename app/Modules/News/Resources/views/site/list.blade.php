
@if ($articles->count() > 0)
<div class="alert alert-info">
	<ul class="newslist">
		@foreach ($articles as $i => $article)
			<li<?php if ($i == 0) { echo ' class="first"'; } ?>>
				<a href="{{ route('site.news.show', ['id' => $article->id]) }}">{{ $article->headline }}</a>
				<p class="news-metadata text-muted">
					@if ($article->isToday())
						@if ($article->isNow())
							<span class="badge badge-success">{{ trans('news::news.happening now') }}</span>
						@else
							<span class="badge badge-info">{{ trans('news::news.today') }}</span>
						@endif
					@elseif ($article->isTomorrow())
						<span class="badge">{{ trans('news::news.tomorrow') }}</span>
					@endif

					<time datetime="{{ $article->datetimenews }}">
						{{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }}
					</time>
					<?php
					$lastupdate = $article->updates()
						->orderBy('datetimecreated', 'desc')
						->limit(1)
						->first();
					?>
					@if ($lastupdate)
						<span class="badge badge-warning"><span class="fa fa-exclamation-circle" aria-hidden="true"></span> {{ trans('news::news.updated at', ['time' => $lastupdate->datetimecreated->format('M d, Y h:ia')]) }}</span>
					@endif
				</p>
			</li>
		@endforeach
	</ul>
</div>
@endif
