<?php
/**
 * @package  Banner widget
 */
?>
@if (count($outages) > 0)
	<div class="banner">
		@foreach ($outages as $item)
			<div class="alert alert-{{ $item->isOutage() ? 'warning' : 'info' }} mb-0">
				<a href="{{ route('site.news.show', ['id' => $item->id]) }}">{{ $item->headline }}</a>
				&mdash;
				<span class="text-nowrap">{{ $item->formatDate($item->datetimenews, $item->datetimenewsend) }}</span>

				@if ($update = $item->updates()->orderBy('datetimecreated', 'desc')->first())
					<span class="badge badge-secondary">Updated: {{ $update->datetimecreated->format('M d, Y h:ia') }}</span>
				@endif
			</div>
		@endforeach
	</div><!-- /.audienceTiles -->
@endif
