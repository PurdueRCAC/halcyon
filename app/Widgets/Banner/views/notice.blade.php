<?php
/**
 * @package  Banner widget
 */

 // Filter out ended items
$outages = $outages->reject(function($value, $key)
{
	return $value->ended();
});

$warnings = array();
$infos = array();
foreach ($outages as $item):
	if ($item->isOutage()):
		$warnings[] = $item;
	else:
		$infos[] = $item;
	endif;
endforeach;
?>
@if (count($warnings) > 0)
	<div class="notice-banner" role="banner">
		<div class="alert alert-warning mb-0">
			@foreach ($warnings as $item)
				<div>
					<a href="{{ route('site.news.show', ['id' => $item->id]) }}">{{ $item->headline }}</a>
					&mdash;
					<span class="text-nowrap">{{ $item->formatDate($item->datetimenews, $item->datetimenewsend) }}</span>

					@if ($update = $item->updates()->orderBy('datetimecreated', 'desc')->first())
						<span class="badge badge-secondary">{{ trans('widget.banner::banner.updated') }}: {{ $update->datetimecreated->format('M d, Y h:ia') }}</span>
					@endif
				</div>
			@endforeach
		</div>
	</div><!-- /.notice-banner -->
@endif
@if (count($infos) > 0)
	<div class="notice-banner" role="banner">
		<div class="alert alert-info mb-0">
			@foreach ($infos as $item)
				<div>
					<a href="{{ route('site.news.show', ['id' => $item->id]) }}">{{ $item->headline }}</a>
					&mdash;
					<span class="text-nowrap">{{ $item->formatDate($item->datetimenews, $item->datetimenewsend) }}</span>

					@if ($update = $item->updates()->orderBy('datetimecreated', 'desc')->first())
						<span class="badge badge-secondary">{{ trans('widget.banner::banner.updated') }}: {{ $update->datetimecreated->format('M d, Y h:ia') }}</span>
					@endif
				</div>
			@endforeach
		</div>
	</div><!-- /.notice-banner -->
@endif
