@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/css/news.css') }}" />
@stop

@section('scripts')
<script src="{{ asset('modules/news/js/site.js') }}"></script>
@stop

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">

	<h2 class="newsheader">
		<a class="icn tip" href="{{ route('site.news.rss', ['name' => $type->name]) }}" title="{{ trans('news::news.rss feed') }}">
			<i class="fa fa-rss-square" aria-hidden="true"></i> {{ trans('news::news.rss feed') }}
		</a>
		{{ $type->name }}
	</h2>

	<?php /*if (!$type->future) { ?>
		<p>Here are <?php echo strtolower($type->name); ?> from this week. Older <?php echo strtolower($type->name); ?> are listed at the bottom.</p>
	<?php } else { ?>
		<p>Here are <?php echo strtolower($type->name); ?> coming up this week and beyond. Past <?php echo strtolower($type->name); ?> are listed at the bottom.</p>
	<?php } ?>

	<?php
	$day = date('w');
	$week_start = Carbon\Carbon::now();
	$week_end   = Carbon\Carbon::now();
	$start = $week_start->modify('-' . $day . ' days');
	$stop  = $week_end->modify('+' . (6 - $day) . ' days');

	$recent = $type->articles()
		->wherePublished()
		->where('datetimenews', '>', $start->toDateTimeString())
		->where('datetimenews', '<', $stop->toDateTimeString())
		->orderBy('datetimenews', 'desc')
		->limit(config('modules.news.limit', 5))
		->get();
	?>
	<h3>This Week</h3>
	<?php if ($recent->count() > 0): ?>
		<ul class="newslist">
			<?php foreach ($recent as $article): ?>
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
		<p>{{ trans('news::news.no items this week', ['type' => $type->name]) }}</p>
	<?php endif; ?>

	<?php if ($type->future) { ?>
		<?php
		$after = Carbon\Carbon::now();
		$after->modify('+' . (7 - $day) . ' days');

		$recent = $type->articles()
			->wherePublished()
			->where('datetimenews', '>', $after->toDateTimeString())
			->orderBy('datetimenews', 'desc')
			->limit(config('modules.news.limit', 5))
			->get();
		?>
		<h3>Upcoming</h3>
		<?php if ($recent->count() > 0): ?>
			<ul class="newslist">
				<?php foreach ($recent as $article): ?>
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
			<p>{{ trans('There are no upcoming :type', ['type' => $type->name]) }}</p>
		<?php endif; ?>
	<?php } ?>

	<h3>Past</h3>
	<?php
	$dt = Carbon\Carbon::now();

	$past = $type->articles()
		->wherePublished()
		->where('datetimenewsend', '<', $dt->toDateTimeString())
		->orderBy('datetimenews', 'desc')
		->limit(config('modules.news.limit', 5))
		->get();

	if ($past->count() > 0): ?>
		<ul class="newslist">
			<?php foreach ($past as $article): ?>
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
		<p>{{ trans('There are no past :type', ['type' => $type->name]) }}</p>
	<?php endif;*/ ?>

	<?php
	$dt = Carbon\Carbon::now();

	$articles = $type->articles()
		->wherePublished()
		->orderBy('datetimenews', 'desc')
		->limit(20)
		->paginate();

	if ($articles->count() > 0): ?>
		<ul class="news-list">
			<?php foreach ($articles as $article): ?>
				<li>
					<article>
					<p>
						<a href="{{ route('site.news.show', ['id' => $article->id]) }}">{{ $article->headline }}</a>

					<?php
					$now = new Carbon\Carbon();
					$news_start = new Carbon\Carbon($article->datetimenews);
					$news_end = new Carbon\Carbon($article->datetimenewsend);

					if ($now->format('Y-m-d') == $news_start->format('Y-m-d'))
					{
						if ($article->datetimenewsend
						 && $article->datetimenewsend != '0000-00-00 00:00:00'
						 && $now->format('Y-m-d h:i:s') > $article->datetimenewsend
						 && $now->format('Y-m-d h:i:s') < $news_end)
						{
							echo ' <span class="badge badge-success">Happening now</span>';
						}
						else
						{
							echo ' <span class="badge badge-info">Today</span>';
						}
					}
					elseif ($now->modify('+1 day')->format('Y-m-d') == $news_start->format('Y-m-d'))
					{
						echo ' <span class="badge">Tomorrow</span>';
					}

					$lastupdate = $article->updates()
						->orderBy('datetimecreated', 'desc')
						->limit(1)
						->first();
					?>
						<br />
						<i class="fa fa-clock-o" aria-hidde="true"></i>
						<time datetime="{{ $article->datetimenews }}">
							<span class="date">{{ $article->datetimenews->format('M d, Y') }}</span>
							<span class="time">{{ $article->datetimenews->format('h:m') }}</span>
						</time>
						@if ($lastupdate)
							<span class="badge badge-warning"><i class="fa fa-exclamation-circle" aria-hidde="true"></i> Updated {{ $lastupdate->datetimecreated->format('h:m') }} {{ $lastupdate->datetimecreated->format('M d, Y') }}</span>
						@endif
					</p>
					<!-- <p>
						{{ Illuminate\Support\Str::limit(strip_tags($article->formattedBody), 150) }}
					</p> -->
					</article>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php echo $articles->render(); ?>
	<?php else: ?>
		<p>{{ trans('There are no :type articles.', ['type' => $type->name]) }}</p>
	<?php endif; ?>
</div>

@stop