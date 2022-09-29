<?php
/**
 * @package  Banner widget
 */
?>
<div class="audienceTiles col-lg-4 col-md-6 col-sm-12 col-xs-12 tiles-1">
	<div class="tileRow">
		<div class="tileContainer col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<img src="{{ asset('files/logo_white.png') }}" alt="ITaP Logo" id="itap-logo" height="150" />
		</div>
	</div><!-- /.tileRow -->
</div><!-- /.audienceTiles -->

<div class="audienceTiles col-lg-4 col-md-6 col-sm-12 col-xs-12 tiles-1">
	<div class="tileRow">
		<div class="tileContainer col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<h2 class="tile">Current Outages</h2>

			@if (count($outages))
				<ul class="newslist">
					@foreach ($outages as $item)
						<li class="first">
							<a href="{{ route('site.news.show', ['id' => $item->id]) }}">{{ $item->headline }}</a>
							<p class="date">
								<span class="text-nowrap">{{ $item->formatDate($item->datetimenews, $item->datetimenewsend) }}</span>
								@if ($item->isToday())
									<span class="badge badge-info">{{ trans('news::news.today') }}</span>
								@endif
							</p>
							@if ($update = $item->updates()->orderBy('datetimecreated', 'desc')->first())
								<p class="newsupdated">{{ trans('widget.banner::banner.updated') }}: {{ $update->formatDate($update->datetimecreated) }}</p>
							@endif
						</li>
					@endforeach
				</ul>
			@else
				<p>There are no outages at this time.</p>
			@endif

			@if ($params->get('readmore'))
				<div class="more">
					<a href="{{ route('site.news.type', ['name' => $type->alias]) }}">{{ trans('widget.banner::banner.more') }}</a>
				</div>
			@endif
		</div>
	</div><!-- /.tileRow -->
</div><!-- /.audienceTiles -->

<div class="audienceTiles col-lg-4 col-md-6 col-sm-12 col-xs-12 tiles-1">
	<div class="tileRow">
		<div class="tileContainer col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<h2 class="tile">{{ trans('widget.banner::banner.upcoming maintenance') }}</h2>

			@if (count($maintenance))
				<ul class="newslist">
					@foreach ($maintenance as $item)
						<li class="first">
							<a href="{{ route('site.news.show', ['id' => $item->id]) }}">{{ $item->headline }}</a>
							<p class="date">
								<span class="text-nowrap">{{ $item->formatDate($item->datetimenews, $item->datetimenewsend) }}</span> 
								@if ($item->isToday())
									<span class="badge badge-info">{{ trans('news::news.today') }}</span>
								@endif
							</p>
							@if ($update = $item->updates()->orderBy('datetimecreated', 'desc')->first())
								<p class="newsupdated">{{ trans('widget.banner::banner.updated') }}: {{ $update->formatDate($update->datetimecreated) }}</p>
							@endif
						</li>
					@endforeach
				</ul>
			@else
				<p>{{ trans('widget.banner::banner.no upcoming maintenance') }}</p>
			@endif

			@if ($params->get('readmore'))
				<div class="more">
					<a href="{{ route('site.news.type', ['name' => $type2->alias]) }}">{{ trans('widget.banner::banner.previous') }}</a>
				</div>
			@endif
		</div>
	</div><!-- /.tileRow -->
</div><!-- /.audienceTiles -->
