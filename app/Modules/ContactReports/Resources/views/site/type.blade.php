@extends('layouts.master')

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<ul class="dropdown-menu">
		<li><a href="{{ route('site.news.search') }}">Search ContactReports</a></li>
		<li><a href="{{ route('site.news.rss') }}">RSS Feeds</a></li>
		<li><div class="separator"></div></li>
		<?php foreach ($types as $typ): ?>
			<li>
				<a href="{{ route('site.news.type', ['name' => $typ->name]) }}">
					{{ $typ->name }}
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">

	<h2 class="newsheader">
		<a class="icn tip" href="{{ route('site.news.rss', ['name' => $type->name]) }}" title="{{ trans('news::news.rss feed') }}">
			<i class="fa fa-rss-square" aria-hidden="true"></i> {{ trans('news::news.rss feed') }}
		</a>
		{{ $type->name }}
	</h2>

	<?php if (!$type->future) { ?>
		<p>Here are <?php echo strtolower($type->name); ?> from this week. Older <?php echo strtolower($type->name); ?> are listed at the bottom.</p>
	<?php } else { ?>
		<p>Here are <?php echo strtolower($type->name); ?> coming up this week and beyond. Past <?php echo strtolower($type->name); ?> are listed at the bottom.</p>
	<?php } ?>

	<?php
	$dt = Carbon\Carbon::now();

	$recent = $type->articles()
		->wherePublished()
		->where('datetimenews', '>', $dt->modify('-1 week')->toDateTimeString())
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
		$dt = Carbon\Carbon::now();

		$recent = $type->articles()
			->wherePublished()
			->where('datetimenews', '>', $dt->toDateTimeString())
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

	<?php
	$dt = Carbon\Carbon::now();

	$past = $type->articles()
		->wherePublished()
		->where('datetimenews', '<', $dt->modify('-1 week')->toDateTimeString())
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
	<?php endif; ?>
</div>

@stop