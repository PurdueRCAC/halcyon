<?php
/**
 * @package  Banner widget
 */
?>
@if (count($maintenance) > 0)
	<div class="tile">
		<{{ $params->get('item_heading') }}>{{ trans('widget.banner::banner.upcoming maintenance') }}</{{ $params->get('item_heading') }}>

		<ul class="newslist">
			@foreach ($maintenance as $item)
				<li class="first">
					<a href="{{ route('site.news.show', ['id' => $item->id]) }}">{{ $item->headline }}</a>
					<p class="date">
						<span>{{ $item->formatDate($item->datetimenews, $item->datetimenewsend) }}</span> 
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

		@if ($params->get('readmore'))
			<div class="more">
				<a href="{{ route('site.news.type', ['name' => $type2->alias]) }}">{{ trans('widget.banner::banner.previous') }}</a>
			</div>
		@endif
	</div><!-- /.audienceTiles -->
@endif
