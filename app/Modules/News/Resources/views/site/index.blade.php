@extends('layouts.master')

@section('meta')
		<meta name="description" content="{{ trans('news::news.news') }}" />
@stop

@if ($page->metadata)
	@foreach ($page->metadata->all() as $k => $v)
		@if ($v)
			@if ($v == '__comment__')
				@push('meta')
		{!! $k !!}
@endpush
			@else
				@push('meta')
		{!! $v !!}
@endpush
			@endif
		@endif
	@endforeach
@endif

@section('title'){{ route('site.news.index') }}@stop

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/news/css/news.css') }}" />
@endpush

@php
app('pathway')->append(
	config('module.news.module name', trans('news::news.news')),
	route('site.news.index')
);
@endphp

@section('content')
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => 0])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div class="wrapper-news">
	<?php if ($types->count() > 0): ?>
		<div class="row">
		<?php
		$count = 0;
		foreach ($types as $type):
			?>
			<div class="news-container col-lg-6 col-md-6 col-sm-14 col-xs-4">
				<div class="row">
					<div class="col-md-8">
						<h2 class="newsheader">
							{{ $type->name }}
						</h2>
					</div>
					<div class="col-md-4 text-right">
						<nav class="btn-group" aria-label="Calendar options for {{ $type->name }}">
							<a class="btn btn-default tip" href="{{ $type->rssLink }}" title="{{ trans('news::news.rss feed') }}">
								<span class="fa fa-rss-square" aria-hidden="true"></span><span class="sr-only">{{ trans('news::news.rss feed') }} for {{ $type->name }}</span>
							</a>
						@if ($type->calendar)
							<a target="_blank" class="btn btn-default calendar calendar-subscribe tip" href="{{ $type->subscribeCalendarLink }}" title="{{ trans('news::news.subscribe calendar', ['name' => $type->name]) }}"><!--
								--><span class="fa fa-fw fa-calendar" aria-hidden="true"></span><span class="sr-only">{{ trans('news::news.subscribe calendar', ['name' => $type->name]) }}</span><!--
							--></a>
							<a target="_blank" class="btn btn-default calendar calendar-download tip" href="{{ $type->downloadCalendarLink }}" title="{{ trans('news::news.download calendar', ['name' => $type->name]) }}"><!--
								--><span class="fa fa-fw fa-download" aria-hidden="true"></span><span class="sr-only">{{ trans('news::news.download calendar', ['name' => $type->name]) }}</span><!--
							--></a>
						@endif
						</nav>
					</div>
				</div>
				<?php
				$articles = $type->allArticles()
					->wherePublished()
					->orderBy('datetimenews', 'desc')
					->limit(config('modules.news.limit', 5))
					->get();

				if ($articles->count() > 0): ?>
					<ul class="news-list">
						<?php foreach ($articles as $article): ?>
							<li>
								<article id="article-{{ $article->id }}" aria-labelledby="article-{{ $article->id }}-title" itemscope itemtype="https://schema.org/<?php echo ($type->calendar ? 'Event' : 'NewsArticle'); ?>">
									<h3 id="article-{{ $article->id }}-title" class="news-title">
										<a href="{{ route('site.news.show', ['id' => $article->id]) }}"><span class="sr-only">{{ trans('news::news.article id', ['id' => $article->id]) }}:</span> <span itemprop="name">{{ $article->headline }}</span></a>
									</h3>
									<ul class="news-meta text-muted">
										<li>
											<span class="fa fa-fw fa-clock-o" aria-hidden="true"></span>
											<time datetime="{{ $article->datetimenews->toDateTimeLocalString() }}">{{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }}</time>
											@if ($article->isToday())
												@if ($article->isNow())
													<span class="badge badge-success">{{ trans('news::news.happening now') }}</span>
												@else
													<span class="badge badge-info">{{ trans('news::news.today') }}</span>
												@endif
											@elseif ($article->isTomorrow())
												<span class="badge">{{ trans('news::news.tomorrow') }}</span>
											@endif
											<?php
											$lastupdate = $article->updates()
												->orderBy('datetimecreated', 'desc')
												->limit(1)
												->first();
											?>
											@if ($lastupdate)
												<span class="badge badge-warning"><span class="fa fa-exclamation-circle" aria-hidden="true"></span> {{ trans('news::news.updated at', ['time' => $lastupdate->datetimecreated->format('M d, Y h:ia')]) }}</span>
											@endif
										</li>
									</ul>
								</article>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else: ?>
					<p>{{ trans('news::news.no results for category', ['category' => $type->name]) }}</p>
				<?php endif; ?>
				<div class="more">
					<a href="{{ route('site.news.type', ['name' => $type->alias]) }}" title="See more {{ $type->name }} articles">{{ trans('news::news.see more') }}</a>
				</div>
			</div><!-- / .news-container -->
			<?php
			$count++;
			if ($count == 2):
				$count = 0;
				?>
		</div>
		<div class="row">
				<?php
			endif;
		endforeach; ?>
		</div>
	<?php else: ?>
		<p>{{ trans('news::news.no categories') }}</p>
	<?php endif; ?>
	</div>
</div>
</div>
@stop