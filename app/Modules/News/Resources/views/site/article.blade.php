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
		$article->type->name,
		route('site.news.type', ['name' => $article->type->alias])
	)
	->append(
		Illuminate\Support\Str::limit($article->headline, 70),
		route('site.news.show', ['id' => $article->id])
	);
@endphp

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => $article->newstypeid])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">

	<div class="row">
		@if (!$article->template && !$article->ended() && $article->type->calendar)
			<div class="col-sm-12 col-md-10">
				<h2>{{ $article->headline }}</h2>
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
				<h2>{{ $article->headline }}</h2>
			</div>
		@endif
	</div>

	<div class="wrapper-news">
		<ul class="news-meta text-muted">
			@if (!$article->template)
				<li><span class="fa fa-fw fa-clock-o" aria-hidden="true"></span> {{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }}</li>
			@endif

			@if ($article->location)
				<li><span class="fa fa-fw fa-map-marker" aria-hidden="true"></span> {{ $article->location }}</li>
			@endif

			@if ($article->url)
				<li><span class="fa fa-fw fa-link" aria-hidden="true"></span> <a href="{{ $article->url }}">{{ $article->url }}</a></li>
			@endif

			@if ($article->type)
				<li><span class="fa fa-fw fa-folder" aria-hidden="true"></span> {{ $article->type->name }}</li>
			@endif

			<?php
			$resources = $article->resourceList()->get();
			if (count($resources) > 0):
				$resourceArray = array();
				foreach ($resources as $resource):
					$resourceArray[] = '<a href="' . route('site.news.type', ['name' => strtolower($resource->name)]) . '/">' . $resource->name . '</a>';
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
					echo ' <a id="attendees-reveal" href="#attendees-all">... +' . (count($users) - 5) . ' more</a><span id="attendees-all" class="stash">' . implode(', ', $users) . '</span>';
				endif;
				echo '</li>';
			endif;
			?>
		</ul>

		@if (count($article->updates))
			@foreach ($article->updates()->orderBy('datetimecreated', 'desc')->get() as $update)
				<section>
					<h3 class="newsupdate">{{ strtoupper(trans('news::news.update')) }}: {!! $update->formattedDatetimecreated($update->datetimecreated) !!}</h3>
					{!! $update->formattedBody !!}
				</section>
			@endforeach

			<section>
				<h3 class="newsupdate">{{ strtoupper(trans('news::news.original')) }}: {{ $article->formatDate($article->datetimenews, $article->originalDatetimenewsend) }}</h3>
		@endif

		{!! $article->formattedBody !!}

		@if (count($article->updates))
			</section>
		@endif

		<p class="newsfooter">
			{{ trans('news::news.originally posted') }}: {{ $article->formatDate($article->datetimecreated) }}

			@if (auth()->user() && auth()->user()->can('manage news'))
				{{ trans('news::news.by author', ['author' => $article->creator->name]) }}

				@if ($article->isModified())
					<br />{{ trans('news::news.last updated') }}: {{ $article->formatDate($article->datetimeedited) }}
					{{ trans('news::news.by author', ['author' => $article->modifier ? $article->modifier->name : trans('global.unknown')]) }}
				@endif

				@if ($article->isMailed())
					<br />{{ trans('news::news.last mailing') }}: {{ $article->formatDate($article->datetimemailed) }}
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

@stop