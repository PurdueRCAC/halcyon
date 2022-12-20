@extends('layouts.master')

@if ($article->metadesc || $article->metakey)
@section('meta')
	@if ($article->metadesc)
		<meta name="description" content="{{ $article->metadesc }}" />
	@endif
	@if ($article->metakey)
		<meta name="keywords" content="{{ $article->metakey }}" />
	@endif
@stop
@endif

@if ($article->metadata)
	@foreach ($article->metadata->all() as $k => $v)
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

@section('title'){{ $article->headline }} ({{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }})@stop

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/css/news.css?v=' . filemtime(public_path() . '/modules/news/css/news.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/news/js/site.js?v=' . filemtime(public_path() . '/modules/news/js/site.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		config('module.news.module name', trans('news::news.news')),
		route('site.news.index')
	)
	->append(
		$article->type->name,
		route('site.news.type', ['name' => $article->type->alias])
	)
	->append(
		Illuminate\Support\Str::limit($article->headline, 70),
		route('site.news.show', ['id' => $article->id])
	);
@endphp

@section('content')
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => $article->newstypeid])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12" itemscope itemtype="https://schema.org/<?php echo ($article->type->calendar ? 'Event' : 'NewsArticle'); ?>">

	<div class="row">
		@if (!$article->template && !$article->ended() && $article->type->calendar)
			<div class="col-sm-12 col-md-10">
				<h2 itemprop="name">{{ $article->headline }}</h2>
			</div>
			<div class="col-sm-12 col-md-2 text-right">
				<div class="btn-group" role="navigation" aria-label="{{ trans('news::news.calendar options') }}">
					<a target="_blank" class="btn btn-default calendar calendar-subscribe tip" href="{{ $article->subscribeCalendarLink }}" title="{{ trans('news::news.subscribe event') }}"><!--
						--><span class="fa fa-fw fa-calendar" aria-hidden="true"></span><span class="sr-only">{{ trans('news::news.subscribe') }}</span><!--
					--></a>
					<a target="_blank" class="btn btn-default calendar calendar-download tip" href="{{ $article->downloadCalendarLink }}" title="{{ trans('news::news.download event') }}"><!--
						--><span class="fa fa-fw fa-download" aria-hidden="true"></span><span class="sr-only">{{ trans('news::news.download') }}</span><!--
					--></a>
				</div>
			</div>
		@else
			<div class="col-sm-12 col-md-12">
				<h2 itemprop="name">{{ $article->headline }}</h2>
			</div>
		@endif
	</div>

	<div class="wrapper-news">
		@if ($article->url && !$article->ended())
			<div class="float-right">
				@if (auth()->user())
					<?php
					$attending = false;
					foreach ($article->associations as $i => $assoc):
						if (auth()->user()->id == $assoc->associd):
							$attending = $assoc->id;
							break;
						endif;
					endforeach;
					?>
					@if (!$attending)
						<a class="btn-attend btn btn-primary" href="{{ route('site.news.show', ['attend' => 1, 'id' => $article->id]) }}" data-newsid="{{ $article->id }}" data-assoc="{{ auth()->user()->id }}">{{ trans('news::news.interested in attending') }}</a>
					@else
						<a class="btn-notattend btn btn-danger" href="{{ route('site.news.show', ['attend' => 0, 'id' => $article->id]) }}" data-id="{{ $attending }}">{{ trans('news::news.cancel reservation') }}</a>
					@endif
				@else
					<a href="{{ route('login', ['return' => base64_encode(route('site.news.show', ['attend' => 1, 'id' => $article->id]))]) }}" data-newsid="{{ $article->id }}" data-assoc="0">{{ trans('news::news.login required') }}</a>
				@endif
			</div>
		@endif
		<ul class="news-meta text-muted">
			@if (!$article->template)
				<li><span class="fa fa-fw fa-clock-o" aria-hidden="true"></span> <time datetime="{{ $article->datetimenews->toDateTimeLocalString() }}">{{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }}</time></li>
			@endif

			@if ($article->location)
				<li><span class="fa fa-fw fa-map-marker" aria-hidden="true"></span> <span itemprop="location">{{ $article->location }}</span></li>
			@endif

			@if ($article->url)
				<?php
				$url = parse_url($article->url);
				?>
				<li><span class="fa fa-fw fa-link" aria-hidden="true"></span> <a href="{{ $article->visitableUrl }}">{{ Illuminate\Support\Str::limit($url['host'], 70) . ($url['path'] || $url['query'] ? ' ...' : '') }}</a></li>
			@endif

			@if ($article->type)
				<li><span class="fa fa-fw fa-folder" aria-hidden="true"></span> {{ $article->type->name }}</li>
			@endif

			<?php
			$resources = $article->resourceList()->get();
			if (count($resources) > 0):
				$resourceArray = array();
				foreach ($resources as $resource):
					$resourceArray[] = '<a href="' . route('site.news.type', ['name' => strtolower($resource->name)]) . '">' . e($resource->name) . '</a>';
				endforeach;

				echo '<li><span class="fa fa-fw fa-tags" aria-hidden="true"></span> ' .  implode(', ', $resourceArray) . '</li>';
			endif;

			if (auth()->user()
			 && auth()->user()->can('manage news')
			 && count($article->associations)):
				$users = array();
				foreach ($article->associations as $i => $assoc):
					if ($associated = $assoc->associated):
						$users[] = $associated->name;
					endif;
				endforeach;

				asort($users);

				echo '<li><span class="fa fa-fw fa-user" aria-hidden="true"></span> <span id="attendees">' . implode(', ', array_slice($users, 0, 5)) . '</span>';
				if (count($users) > 5):
					echo ' <a id="attendees-reveal" href="#attendees-all">... +' . (count($users) - 5) . ' more</a><span id="attendees-all" class="d-none">' . implode(', ', $users) . '</span>';
				endif;
				echo '</li>';
			endif;
			?>
		</ul>

		<div itemprop="description">
		@if (count($article->updates))
			@foreach ($article->updates()->orderBy('datetimecreated', 'desc')->get() as $update)
				<section id="{{ str_replace(' ', '_', $update->datetimecreated->toDateTimeLocalString()) }}">
					<h3 class="newsupdate">
						<a href="#{{ str_replace(' ', '_', $update->datetimecreated->toDateTimeLocalString()) }}" class="heading-anchor" title="Link to update at {{ $update->formatDate($update->datetimecreated) }}">
							<span class="fa fa-link" aria-hidden="true"></span>
							<span class="sr-only">Link to update at {{ $update->formatDate($update->datetimecreated) }}</span>
						</a>
						{{ strtoupper(trans('news::news.update')) }}: <time datetime="{{ $update->datetimecreated->toDateTimeLocalString() }}">{{ $update->formatDate($update->datetimecreated) }}</time>
					</h3>
					{!! $update->toHtml() !!}
				</section>
			@endforeach

			<section id="{{ str_replace(' ', '_', $article->datetimecreated->toDateTimeLocalString()) }}">
				<h3 class="newsupdate">
					<a href="#{{ str_replace(' ', '_', $article->datetimecreated->toDateTimeLocalString()) }}" class="heading-anchor" title="Link to original posting">
						<span class="fa fa-link" aria-hidden="true"></span>
						<span class="sr-only">Link to original posting</span>
					</a>
					{{ strtoupper(trans('news::news.original')) }}: <time datetime="{{ $article->datetimenews->toDateTimeLocalString() }}">{{ $article->formatDate($article->datetimenews, $article->originalDatetimenewsend) }}</time>
				</h3>
		@endif

		{!! $article->toHtml() !!}

		@if (count($article->updates))
			</section>
		@endif
		</div>

		<p class="newsfooter">
			{{ trans('news::news.originally posted') }}: <time datetime="{{ $article->datetimecreated->toDateTimeLocalString() }}">{{ $article->formatDate($article->datetimecreated) }}</time>

			@if (auth()->user() && auth()->user()->can('manage news'))
				<span itemprop="author">{{ trans('news::news.by author', ['author' => $article->creator->name]) }}</span>

				@if ($article->isModified())
					<br />{{ trans('news::news.last updated') }}: <time datetime="{{ $article->datetimeedited->toDateTimeLocalString() }}">{{ $article->formatDate($article->datetimeedited) }}</time>
					{{ trans('news::news.by author', ['author' => $article->modifier ? $article->modifier->name : trans('global.unknown')]) }}
				@endif

				@if ($article->isMailed())
					<br />{{ trans('news::news.last mailing') }}: <time datetime="{{ $article->datetimemailed->toDateTimeLocalString() }}">{{ $article->formatDate($article->datetimemailed) }}</time>
					{{ trans('news::news.by author', ['author' => $article->mailer ? $article->mailer->name : trans('global.unknown')]) }}
				@endif
			@endif
		</p>

		@if (auth()->user() && auth()->user()->can('manage news'))
			<div class="card card-admin edit-controls" id="articlestats" data-api="{{ route('api.news.views', ['id' => $article->id]) }}">
				<div class="card-body">
					@if (auth()->user() && auth()->user()->can('edit news'))
						<a class="edit float-right btn tip" href="{{ route('site.news.manage', ['id' => $article->id]) }}&edit" title="{{ trans('global.edit') }}">
							<span class="fa fa-fw fa-pencil" aria-hidden="true"></span><span class="sr-only">{{ trans('global.edit') }}</span>
						</a>
					@endif
					<strong>{{ trans('news::news.view count') }}:</strong> <span id="viewcount"><span class="spinner">{{ trans('global.loading') }}</span></span>,
					<strong>{{ trans('news::news.unique view count') }}:</strong> <span id="uniqueviewcount"><span class="spinner">{{ trans('global.loading') }}</span></span>
				</div>
			</div>
		@endif
	</div>

</div>
</div>
@stop