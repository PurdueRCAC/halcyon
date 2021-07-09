@extends('layouts.master')

@php
app('pathway')->append(
	config('news.name'),
	route('site.news.index')
);
@endphp

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => 0])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div class="wrapper-news">
	<?php if ($types->count() > 0): ?>
		<div class="newsleft row">
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
						<div class="btn-group" role="navigation" aria-label="Calendar options for {{ $type->name }}">
							<a class="btn btn-default tip" href="{{ route('site.news.feed', ['name' => $type->name]) }}" title="{{ trans('news::news.rss feed') }}">
								<i class="fa fa-rss-square" aria-hidden="true"></i><span class="sr-only">{{ trans('news::news.rss feed') }}</span>
							</a>
						@if ($type->calendar)
							<a target="_blank" class="btn btn-default calendar calendar-subscribe tip" href="{{ preg_replace('/^https?:\/\//', 'webcal://', route('site.news.calendar', ['name' => strtolower($type->name)])) }}" title="Subscribe to calendar"><!--
								--><i class="fa fa-fw fa-calendar" aria-hidden="true"></i><span class="sr-only">Subscribe</span><!--
							--></a>
							<a target="_blank" class="btn btn-default calendar calendar-download tip" href="{{ route('site.news.calendar', ['name' => strtolower($type->name)]) }}" title="Download calendar"><!--
								--><i class="fa fa-fw fa-download" aria-hidden="true"></i><span class="sr-only">Download</span><!--
							--></a>
						@endif
						</div>
					</div>
				</div>
				<?php
				$articles = $type->articles()
					->wherePublished()
					->orderBy('datetimenews', 'desc')
					->limit(config('modules.news.limit', 5))
					->get();

				if ($articles->count() > 0): ?>
					<ul class="newslist">
						<?php foreach ($articles as $article): ?>
							<li>
								<a href="{{ route('site.news.show', ['id' => $article->id]) }}">{{ $article->headline }}</a>
								<p class="date">
									<span>{{ $article->formatDate($article->datetimenews->toDateTimeString(), $article->datetimenewsend->toDateTimeString()) }}</span>
									@if ($article->isToday())
										@if ($article->isNow())
											<span class="badge badge-success">{{ trans('news::news.happening now') }}</span>
										@else
											<span class="badge badge-info">{{ trans('news::news.today') }}</span>
										@endif
									@elseif ($article->isTomorrow())
										<span class="badge">{{ trans('news::news.tomorrow') }}</span>
									@endif
								</p>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else: ?>
					<p>{{ trans('news::news.no results for category', ['category' => $type->name]) }}</p>
				<?php endif; ?>
				<div class="more">
					<a href="{{ route('site.news.type', ['name' => $type->alias]) }}">{{ trans('news::news.see more') }}</a>
				</div>
			</div><!-- / .news-container -->
			<?php
			$count++;
			if ($count == 2):
				$count = 0;
				?>
		</div>
		<div class="newsleft row">
				<?php
			endif;
		endforeach; ?>
		</div>
	<?php else: ?>
		<p>{{ trans('news::news.no categories') }}</p>
	<?php endif; ?>
	</div>
</div>

@stop