@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/news/js/site.js') }}"></script>
<script>
	$.getJSON("<?php echo route('api.news.views', ['id' => $article->id]); ?>", function(data) {
		if (data) {
			$('#viewcount').html(data.viewcount);
			$('#uniqueviewcount').html(data.uniquecount);
		}
	});
</script>
@endpush

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => $article->newstypeid])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h2>{{ $article->headline }}</h2>

	<div class="wrapper-news">
		<p class="newsheader">
			@if (!$article->template)
				<i class="fa fa-fw fa-clock-o" aria-hidden="true"></i> {{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }}
			@endif

			@if ($article->location)
				<br /><i class="fa fa-fw fa-map-marker" aria-hidden="true"></i> {{ $article->location }}
			@endif

			@if ($article->url)
				<br /><i class="fa fa-fw fa-link" aria-hidden="true"></i> <a href="' . $article->url . '">{{ $article->url }}</a>
			@endif

			@if ($article->type)
				<br /><i class="fa fa-fw fa-folder" aria-hidden="true"></i> {{ $article->type->name }}
			@endif

			<?php
			$resourceArray = array();
			if (count($article->resources) > 0)
			{
				$resourceArray = array();
				foreach ($article->resources as $resource)
				{
					$resourceArray[] = '<a href="' . route('site.news.type', ['name' => strtolower($resource->resource->name)]) . '/">' . $resource->resource->name . '</a>';
				}
				echo '<br /><i class="fa fa-fw fa-tags" aria-hidden="true"></i> ' .  implode(', ', $resourceArray);
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

				echo '<br /><i class="fa fa-fw fa-user" aria-hidden="true"></i> <span id="attendees">' . implode(', ', array_slice($users, 0, 5)) . '</span>';
				if (count($users) > 5)
				{
					echo ' <a id="attendees-reveal" href="#attendees-all">... +' . (count($users) - 5) . ' more</a><span id="attendees-all" class="stash">' . implode(', ', $users) . '</span>';
				}
			}

			// WILL BE USED LATER FOR ADDING TO CALENDAR
			if (!$article->template && $article->newsdateend > Carbon\Carbon::now()->format('Y-m-d h:i:s'))
			{
				if ($type->calendar)
				{
					?>
					<br />
					<i class="fa fa-fw fa-calendar" aria-hidden="true"></i>
					<a target="_blank" class="calendar calendar-subscribe" href="webcal://<?php echo request()->getHttpHost(); ?>/news/calendar/<?php echo $article->id; ?>" title="Subscribe to event"><!--
						-->Subscribe<!--
					--></a>
					&nbsp;|&nbsp;
					<i class="fa fa-fw fa-download" aria-hidden="true"></i>
					<a target="_blank" class="calendar calendar-download" href="/news/calendar/<?php echo $article->id; ?>" title="Download event"><!--
						-->Download<!--
					--></a>
					<?php
				}
			}
			?>
		</p>

		{!! $article->formattedBody !!}

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
			<p class="alert alert-info">
				<a href="{{ route('admin.news.edit', ['id' => $article->id]) }}">Edit Article</a><br /><br />
				View Count: <span id="viewcount"><span class="spinner">Loading...</span></span><br />
				Unique View Count: <span id="uniqueviewcount"><span class="spinner">Loading...</span></span>
			</p>
		@endif
	</div>
</div>

@stop