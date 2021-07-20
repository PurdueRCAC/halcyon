@extends('layouts.master')

@section('title'){{ $article->headline }}@stop

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
		Illuminate\Support\Str::limit($article->headline, 70),
		route('site.news.show', ['id' => $article->id])
	);
@endphp

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => $article->newstypeid])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">

	@if (auth()->user() && auth()->user()->can('edit news'))
	<div class="row">
		<div class="col-sm-12 col-md-10">
			<h2>{{ $article->headline }}</h2>
		</div>
		<div class="col-sm-12 col-md-2">
			<a class="btn float-right tip" href="{{ route('site.news.manage', ['id' => $article->id, 'edit' => 1]) }}" title="Edit"><!--
				--><span class="fa fa-fw fa-pencil" aria-hidden="true"></span><span class="sr-only">Edit</span><!--
			--></a>
		</div>
	</div>
	@else
		<h2>{{ $article->headline }}</h2>
	@endif

	<div class="wrapper-news">
		<p class="newsheader">
			@if (!$article->template)
				<span class="fa fa-fw fa-clock-o" aria-hidden="true"></span> {{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }}
			@endif

			@if ($article->location)
				<br /><span class="fa fa-fw fa-map-marker" aria-hidden="true"></span> {{ $article->location }}
			@endif

			@if ($article->url)
				<br /><span class="fa fa-fw fa-link" aria-hidden="true"></span> <a href="' . $article->url . '">{{ $article->url }}</a>
			@endif

			@if ($article->type)
				<br /><span class="fa fa-fw fa-folder" aria-hidden="true"></span> {{ $article->type->name }}
			@endif

			<?php
			$resourceArray = array();
			$resources = $article->resourceList()->get();
			if (count($resources) > 0)
			{
				$resourceArray = array();
				foreach ($resources as $resource)
				{
					$resourceArray[] = '<a href="' . route('site.news.type', ['name' => strtolower($resource->name)]) . '/">' . $resource->name . '</a>';
				}
				echo '<br /><span class="fa fa-fw fa-tags" aria-hidden="true"></span> ' .  implode(', ', $resourceArray);
			}

			if (auth()->user()
			 && auth()->user()->can('manage news')
			 && count($article->associations))
			{
				$users = array();
				foreach ($article->associations as $i => $assoc)
				{
					if ($associated = $assoc->associated)
					{
						$users[] = $associated->name;
					}
				}
				asort($users);

				echo '<br /><span class="fa fa-fw fa-user" aria-hidden="true"></span> <span id="attendees">' . implode(', ', array_slice($users, 0, 5)) . '</span>';
				if (count($users) > 5)
				{
					echo ' <a id="attendees-reveal" href="#attendees-all">... +' . (count($users) - 5) . ' more</a><span id="attendees-all" class="stash">' . implode(', ', $users) . '</span>';
				}
			}

			// WILL BE USED LATER FOR ADDING TO CALENDAR
			if (!$article->template && $article->datetimenewsend > Carbon\Carbon::now()->format('Y-m-d h:i:s'))
			{
				if ($article->type->calendar)
				{
					?>
					<br />
					<span class="fa fa-fw fa-calendar" aria-hidden="true"></span>
					<a target="_blank" class="calendar calendar-subscribe" href="{{ str_replace(['http:', 'https:'], 'webcal:', route('site.news.calendar', ['name' => $article->id])) }}" title="Subscribe to event"><!--
						-->Subscribe<!--
					--></a>
					&nbsp;|&nbsp;
					<span class="fa fa-fw fa-download" aria-hidden="true"></span>
					<a target="_blank" class="calendar calendar-download" href="{{ route('site.news.calendar', ['name' => $article->id]) }}" title="Download event"><!--
						-->Download<!--
					--></a>
					<?php
				}
			}
			?>
		</p>

		@if (count($article->updates))
			@foreach ($article->updates()->orderBy('datetimecreated', 'desc')->get() as $update)
				<section>
					<h3 class="newsupdate">UPDATE: {!! $update->formattedDatetimecreated($update->datetimecreated) !!}</h3>
					{!! $update->formattedBody !!}
				</section>
			@endforeach

			<section>
				<h3 class="newsupdate">ORIGINAL: {{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }}</h3>
		@endif

		{!! $article->formattedBody !!}

		@if (count($article->updates))
			</section>
		@endif

		<p class="newsfooter">
			Originally posted: {{ $article->formatDate($article->datetimecreated) }}

			@if (auth()->user() && auth()->user()->can('manage news'))
				by {{ $article->creator->name }}

				@if ($article->isModified())
					<br />Last updated: {{ $article->formatDate($article->datetimeedited) }}
					by {{ $article->modifier->name }}
				@endif

				@if ($article->isMailed())
					<br />Last mailing: {{ $article->formatDate($article->datetimemailed) }}
					by {{ $article->mailer ? $article->mailer->name : trans('global.unknown') }}
				@endif
			@endif
		</p>

		@if (auth()->user() && auth()->user()->can('manage news'))
			<p class="alert alert-info" id="articlestats" data-api="{{ route('api.news.views', ['id' => $article->id]) }}">
				<a href="{{ route('site.news.manage', ['id' => $article->id, 'edit' => 1]) }}">Edit Article</a><br /><br />
				View Count: <span id="viewcount"><span class="spinner">Loading...</span></span><br />
				Unique View Count: <span id="uniqueviewcount"><span class="spinner">Loading...</span></span>
			</p>
		@endif
	</div>
</div>

@stop