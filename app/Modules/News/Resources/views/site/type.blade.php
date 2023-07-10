@extends('layouts.master')

@section('meta')
		<meta name="description" content="{{ trans('news::news.news') . ': ' . $type->name . ($articles->total() > $filters['limit'] ? ': Page ' . $filters['page'] : '') }}" />
@stop

@if ($type->metadata)
	@foreach ($type->metadata->all() as $k => $v)
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

@section('title'){{ trans('news::news.news') . ': ' . $type->name . ($articles->total() > $filters['limit'] ? ': Page ' . $filters['page'] : '') }}@stop

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/news/css/news.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/news/js/site.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		config('module.news.module name', trans('news::news.news')),
		route('site.news.index')
	)
	->append(
		$type->name,
		route('site.news.type', ['name' => $type->alias])
	);
@endphp

@section('content')
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => $type->id])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">

	<div class="row">
		<div class="col-md-8">
			<h2>
				{{ $type->name }}
			</h2>
		</div>
		<div class="col-md-4 text-right">
			<nav class="btn-group" aria-label="Calendar options for {{ $type->name }}">
				<a class="btn btn-default tip" href="{{ $type->rssLink }}" title="{{ trans('news::news.rss feed') }}">
					<span class="fa fa-rss-square" aria-hidden="true"></span><span class="sr-only">{{ trans('news::news.rss feed') }}</span>
				</a>
			@if ($type->calendar)
				<a target="_blank" class="btn btn-default calendar calendar-subscribe tip" href="{{ $type->subscribeCalendarLink }}" title="{{ trans('news::news.subscribe calendar', ['name' => $type->name]) }}"><!--
					--><span class="fa fa-fw fa-calendar" aria-hidden="true"></span><span class="sr-only">{{ trans('news::news.subscribe') }}</span><!--
				--></a>
				<a target="_blank" class="btn btn-default calendar calendar-download tip" href="{{ $type->downloadCalendarLink }}" title="{{ trans('news::news.download calendar', ['name' => $type->name]) }}"><!--
					--><span class="fa fa-fw fa-download" aria-hidden="true"></span><span class="sr-only">{{ trans('news::news.download') }}</span><!--
				--></a>
			@endif
			</nav>
		</div>
	</div>

	<div id="filter-bar">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<div class="btn-group mr-2" role="group" aria-label="Filter options">
					<a href="{{ route('site.news.type', ['name' => $type->alias, 'state' => 'all', 'order_dir' => 'desc']) }}" class="btn btn-outline-secondary<?php if ($filters['state'] == 'all'): echo ' active'; endif;?>">{{ trans('news::news.all') }}</a>
					<a href="{{ route('site.news.type', ['name' => $type->alias, 'state' => 'upcoming', 'order_dir' => 'asc']) }}" class="btn btn-outline-secondary<?php if ($filters['state'] == 'upcoming'): echo ' active'; endif;?>">{{ trans('news::news.upcoming') }}</a>
					<a href="{{ route('site.news.type', ['name' => $type->alias, 'state' => 'ended', 'order_dir' => 'desc']) }}" class="btn btn-outline-secondary<?php if ($filters['state'] == 'ended'): echo ' active'; endif;?>">{{ trans('news::news.ended') }}</a>
				</div>
			</div>
			<div class="col col-md-8 filter-select text-right">
				<div class="btn-group mr-2" role="group" aria-label="Sort options">
					<a href="{{ route('site.news.type', ['name' => $type->alias, 'state' => $filters['state'], 'order_dir' => 'asc']) }}" class="btn btn-outline-secondary<?php if ($filters['order_dir'] == 'asc'): echo ' active'; endif;?>">{{ trans('news::news.sort asc') }}</a>
					<a href="{{ route('site.news.type', ['name' => $type->alias, 'state' => $filters['state'], 'order_dir' => 'desc']) }}" class="btn btn-outline-secondary<?php if ($filters['order_dir'] == 'desc'): echo ' active'; endif;?>">{{ trans('news::news.sort desc') }}</a>
				</div>
			</div>
		</div>
	</div>

	<?php
	$dt = Carbon\Carbon::now();

	if ($articles->count() > 0): ?>
		<ul class="news-list">
			@foreach ($articles as $article)
				<li>
					@if (auth()->user() && auth()->user()->can('edit news'))
						<a class="btn float-right tip" href="{{ route('site.news.manage', ['id' => $article->id]) }}&edit" title="{{ trans('global.edit') }}"><!--
							--><span class="fa fa-fw fa-pencil" aria-hidden="true"></span><span class="sr-only">{{ trans('global.edit') }}</span><!--
						--></a>
					@endif
					<article id="article-{{ $article->id }}" aria-labelledby="article-{{ $article->id }}-title" itemscope itemtype="https://schema.org/<?php echo ($type->calendar ? 'Event' : 'NewsArticle'); ?>">
						<h3 id="article-{{ $article->id }}-title" class="news-title">
							<a href="{{ route('site.news.show', ['id' => $article->id]) }}"><span class="sr-only">{{ trans('news::news.article id', ['id' => $article->id]) }}:</span> <span itemprop="name">{{ $article->headline }}</span></a>
						</h3>
						<ul class="news-meta text-muted">
							<li>
								<span class="fa fa-fw fa-clock-o" aria-hidden="true"></span>
								@if ($article->datetimenews)
								<time datetime="{{ $article->datetimenews->toDateTimeLocalString() }}">
									{{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }}
								</time>
								@endif
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
									<span class="badge badge-warning">
										<span class="fa fa-exclamation-circle" aria-hidden="true"></span>
										<time datetime="{{ $lastupdate->datetimecreated->toDateTimeLocalString() }}">{{ trans('news::news.updated at', ['time' => $lastupdate->datetimecreated->format('M d, Y h:ia')]) }}</time>
									</span>
								@endif
							</li>
							<?php
							$resources = $article->resourceList()->get();
							if (count($resources) > 0):
								$resourceArray = array();
								foreach ($resources as $resource):
									if (!$resource->name):
										continue;
									endif;
									$resourceArray[] = '<a href="' . route('site.news.type', ['name' => strtolower($resource->name)]) . '">' . $resource->name . '</a>';
								endforeach;

								echo '<li><span class="fa fa-fw fa-tags" aria-hidden="true"></span> ' .  implode(', ', $resourceArray) . '</li>';
							endif;
							?>
						</ul>
						<p itemprop="description">
							{{ Illuminate\Support\Str::limit(strip_tags($article->toHtml()), 150) }}
						</p>
					</article>
				</li>
			@endforeach
		</ul>
		<?php echo $articles->render(); ?>
	<?php else: ?>
		<p class="mt-4">{{ trans('news::news.no type articles', ['type' => $type->name]) }}</p>
	<?php endif; ?>
</div>
</div>
@stop