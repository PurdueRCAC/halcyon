<?php
/**
 * @package  Banner widget
 */

 // Filter out ended items
$outages = $outages->reject(function($value, $key)
{
	return $value->ended();
});
?>
@if (count($outages) > 0)
	@foreach ($outages as $item)
		<div class="notice-banner" role="banner">
			<div class="alert alert-{{ $item->isOutage() ? 'warning' : 'info' }} mb-0">
				<a href="{{ route('site.news.show', ['id' => $item->id]) }}">{{ $item->headline }}</a>
				&mdash;
				<span class="text-nowrap">{{ $item->formatDate($item->datetimenews, $item->datetimenewsend) }}</span>

				@if ($update = $item->updates()->orderBy('datetimecreated', 'desc')->first())
					<span class="badge badge-secondary">Updated: {{ $update->datetimecreated->format('M d, Y h:ia') }}</span>
				@endif
			</div>
		</div><!-- /.audienceTiles -->
	@endforeach
@endif
