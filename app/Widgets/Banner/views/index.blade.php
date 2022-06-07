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
									<span class="badge badge-info">Today</span>
								@endif
							</p>
							@if ($update = $item->updates()->orderBy('datetimecreated', 'desc')->first())
								<p class="newsupdated">Updated: {{ $update->datetimecreated->format('M d, Y h:ia') }}</p>
							@endif
						</li>
					@endforeach
				</ul>
			@else
				<p>There are no outages at this time.</p>
			@endif

			@if ($params->get('readmore'))
			<div class="more">
				<a href="{{ route('site.news.type', ['name' => $type->alias]) }}">previous…</a>
			</div>
			@endif
		</div>
	</div><!-- /.tileRow -->
</div><!-- /.audienceTiles -->

<div class="audienceTiles col-lg-4 col-md-6 col-sm-12 col-xs-12 tiles-1">
	<div class="tileRow">
		<div class="tileContainer col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<h2 class="tile">Upcoming Maintenance</h2>

			@if (count($maintenance))
				<ul class="newslist">
					@foreach ($maintenance as $item)
						<li class="first">
							<a href="{{ route('site.news.show', ['id' => $item->id]) }}">{{ $item->headline }}</a>
							<p class="date">
								<span class="text-nowrap">{{ $item->formatDate($item->datetimenews, $item->datetimenewsend) }}</span> 
								@if ($item->isToday())
									<span class="badge badge-info">Today</span>
								@endif
							</p>
							@if ($update = $item->updates()->orderBy('datetimecreated', 'desc')->first())
								<p class="newsupdated">Updated: {{ $update->datetimecreated->format('M d, Y h:ia') }}</p>
							@endif
						</li>
					@endforeach
				</ul>
			@else
				<p>There is no upcoming maintenance scheduled at this time.</p>
			@endif

			@if ($params->get('readmore'))
			<div class="more">
				<a href="{{ route('site.news.type', ['name' => $type2->alias]) }}">previous…</a>
			</div>
			@endif
		</div>
	</div><!-- /.tileRow -->
</div><!-- /.audienceTiles -->
