@extends('layouts.master')

@section('title'){{ $type->name }}@stop

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/css/news.css?v=' . filemtime(public_path() . '/modules/news/css/news.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/news/js/site.js?v=' . filemtime(public_path() . '/modules/news/js/site.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		config('news.name'),
		route('site.news.index')
	)
	->append(
		$type->name,
		route('site.news.type', ['name' => $type->alias])
	);
@endphp

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => $type->id])

	<!--
	<form method="get" action="{{ route('site.news.type', ['name' => $type->name]) }}">
	<fieldset class="filters">
		<legend class="sr-only">Filter</legend>

		<fieldset>
			<legend>Category</legend>

			@foreach ($types as $t)
				<div class="form-check">
					<input type="checkbox" name="typeid[]" id="typeid-{{ $t->id }}" class="form-check-input" value="{{ $t->id }}" <?php if ($type->id == $t->id) { echo ' checked="checked"'; } ?> />
					<label for="typeid-{{ $t->id }}" class="form-check-label">{{ $t->name }}</label>
				</div>
			@endforeach
		</fieldset>

		<div class="form-group">
			<label for="keywords">Search</label>
			<input type="search" name="keyword" id="keywords" class="form-control" value="" />
		</div>
		<div class="form-group">
			<label for="resource">Resource</label>
			<input type="text" name="resource" id="resource" class="form-control" value="" />
		</div>
		<div class="form-group">
			<label for="datetimenews">Date from</label>
			<span class="input-group">
				<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
				<input type="text" class="date-pick form-control" name="start" id="datetimenews" placeholder="YYYY-MM-DD" value="" />
			</span>
		</div>
		<div class="form-group">
			<label for="datetimenewsend">Date to</label>
			<span class="input-group">
				<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
				<input type="text" class="date-pick form-control" name="stop" id="datetimenewsend" placeholder="YYYY-MM-DD" value="" />
			</span>
		</div>
		@csrf
	</fieldset>
	</form>
-->
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
				<a class="btn btn-default tip" href="{{ route('site.news.feed', ['name' => $type->name]) }}" title="{{ trans('news::news.rss feed') }}">
					<span class="fa fa-rss-square" aria-hidden="true"></span><span class="sr-only">{{ trans('news::news.rss feed') }}</span>
				</a>
			@if ($type->calendar)
				<a target="_blank" class="btn btn-default calendar calendar-subscribe tip" href="{{ preg_replace('/^https?:\/\//', 'webcal://', route('site.news.calendar', ['name' => strtolower($type->name)])) }}" title="Subscribe to calendar"><!--
					--><span class="fa fa-fw fa-calendar" aria-hidden="true"></span><span class="sr-only">Subscribe</span><!--
				--></a>
				<a target="_blank" class="btn btn-default calendar calendar-download tip" href="{{ route('site.news.calendar', ['name' => strtolower($type->name)]) }}" title="Download calendar"><!--
					--><span class="fa fa-fw fa-download" aria-hidden="true"></span><span class="sr-only">Download</span><!--
				--></a>
			@endif
			</nav>
		</div>
	</div>

	<?php /*<form method="get" action="{{ route('site.news.type', ['name' => $type->name]) }}">
		<fieldset class="filters">
			<legend class="sr-only">Filter</legend>

			<div class="form-group">
				<label for="keywords">Search</label>
				<input type="search" name="keyword" id="keywords" class="form-control" value="{{ $filters['keyword'] }}" />
			</div>

			<div class="form-group">
				<label for="resource">Resource</label>
				<input type="text" name="resource" id="resource" class="form-control" value="{{ $filters['resource'] }}" />
			</div>

			<div class="form-group">
				<label for="datetimenews">Date from</label>
				<span class="input-group">
					<input type="text" class="date-pick form-control" name="start" id="datetimenews" placeholder="YYYY-MM-DD" value="{{ $filters['start'] }}" />
					<span class="input-group-append"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
				</span>
			</div>

			<div class="form-group">
				<label for="datetimenewsend">Date to</label>
				<span class="input-group">
					<input type="text" class="date-pick form-control" name="stop" id="datetimenewsend" placeholder="YYYY-MM-DD" value="{{ $filters['end'] }}" />
					<span class="input-group-append"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
				</span>
			</div>
			@csrf
		</fieldset>
	</form>*/ ?>

	<?php
	$dt = Carbon\Carbon::now();

	if ($articles->count() > 0): ?>
		<ul class="news-list">
			@foreach ($articles as $article)
				<li>
					@if (auth()->user() && auth()->user()->can('edit news'))
						<a class="btn float-right tip" href="{{ route('site.news.manage', ['id' => $article->id]) }}&edit" title="Edit"><!--
							--><span class="fa fa-fw fa-pencil" aria-hidden="true"></span><span class="sr-only">Edit</span><!--
						--></a>
					@endif
					<article id="article-{{ $article->id }}" aria-labelledby="article-{{ $article->id }}-title">
						<h3 id="article-{{ $article->id }}-title" class="news-title">
							<a href="{{ route('site.news.show', ['id' => $article->id]) }}"><span class="sr-only">Article #{{ $article->id }}:</span> {{ $article->headline }}</a>
						</h3>
						<ul class="news-meta text-muted">
							<li>
							<span class="fa fa-fw fa-clock-o" aria-hidden="true"></span>
							<time datetime="{{ $article->datetimenews }}">
								{{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }}
							</time>
							@if ($article->isToday())
								@if ($article->isNow())
									<span class="badge badge-success">Happening now</span>
								@else
									<span class="badge badge-info">Today</span>
								@endif
							@elseif ($article->isTomorrow())
								<span class="badge">Tomorrow</span>
							@endif
							<?php
							$lastupdate = $article->updates()
								->orderBy('datetimecreated', 'desc')
								->limit(1)
								->first();
							?>
							@if ($lastupdate)
								<span class="badge badge-warning"><span class="fa fa-exclamation-circle" aria-hidden="true"></span> Updated {{ $lastupdate->datetimecreated->format('M d, Y h:ia') }}</span>
							@endif
							</li>
							<?php
							$resources = $article->resourceList()->get();
							if (count($resources) > 0):
								$resourceArray = array();
								foreach ($resources as $resource):
									if (!$resource->resource):
										continue;
									endif;
									$resourceArray[] = '<a href="' . route('site.news.type', ['name' => strtolower($resource->name)]) . '">' . $resource->name . '</a>';
								endforeach;

								echo '<li><span class="fa fa-fw fa-tags" aria-hidden="true"></span> ' .  implode(', ', $resourceArray) . '</li>';
							endif;
							?>
						</ul>
						<p>
							{{ Illuminate\Support\Str::limit(strip_tags($article->formattedBody), 150) }}
						</p>
					</article>
				</li>
			@endforeach
		</ul>
		<?php echo $articles->render(); ?>
	<?php else: ?>
		<p>{{ trans('There are no :type articles.', ['type' => $type->name]) }}</p>
	<?php endif; ?>
</div>

@stop