@extends('layouts.master')

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
				<h2 class="newsheader">
					<a class="icn tip" href="{{ route('site.news.rss', ['name' => $type->name]) }}" title="{{ trans('news::news.rss feed') }}">
						<i class="fa fa-rss-square" aria-hidden="true"></i> {{ trans('news::news.rss feed') }}
					</a>
					{{ $type->name }}
				</h2>
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
									<span>{{ $article->datetimenews->format('M d, Y') }}</span>
									<span>{{ $article->datetimenews->format('h:m') }}</span>
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