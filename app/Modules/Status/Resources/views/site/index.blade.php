@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/status/css/site.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/status/js/site.js') }}"></script>
<script>
	$(document).ready(function() {
		$('.card-header').on('click', function (event) {
			event.preventDefault();

			//$('.list-group').addClass('hidden');

			$(this).parent().find('.list-group').toggleClass('hidden');
		});

		$('.section-header a').on('click', function (event) {
			event.preventDefault();

			//$('.list-group').addClass('hidden');

			$($(this).attr('href')).toggleClass('open');
		});
	});
</script>
@endpush

@section('title'){{ trans('status::status.status') }}@stop

@php
app('pathway')->append(
	trans('status::status.status'),
	route('site.status.index')
);
@endphp

@section('content')
<div class="contentInner col-lg-12 col-md-12 col-sm-12 col-xs-12">
<ul class="status-legend mb-5">
	<li><span class="text-success"><span class="fa fa-check" aria-hidden="true"></span></span> {{ trans('status::status.option.operational') }}</li>
	<li><span class="text-info"><span class="fa fa-wrench" aria-hidden="true"></span></span> {{ trans('status::status.option.maintenance') }}</li>
	<li><span class="text-warning"><span class="fa fa-exclamation-triangle" aria-hidden="true"></span></span> {{ trans('status::status.option.impaired') }}</li>
	<li><span class="text-danger"><span class="fa fa-exclamation-circle" aria-hidden="true"></span></span> {{ trans('status::status.option.down') }}</li>
	<li><span class="text-secondary"><span class="fa fa-ellipsis-h" aria-hidden="true"></span></span> {{ trans('status::status.option.offline') }}</li>
</ul>

@foreach ($restypes as $restype)
<div class="resource-statuses">
	<h3>{{ $restype->name }}</h3>

	<div class="row">
		<?php
		$k = 0;
		$now = Carbon\Carbon::now();
		$start = $now->format('Y-m-d h:i:s');
		$end = $now->modify('-1 day')->format('Y-m-d h:i:s');

		$resources = $restype->resources()
			->where('listname', '!=', '')
			->where('display', '>', 0)
			->orderBy('name', 'asc')
			->get();

		foreach ($resources as $resource)
		{
			$resource->statusUpdate = Carbon\Carbon::now();

			event($event = new App\Modules\Status\Events\StatusRetrieval($resource));

			$resource = $event->asset;
			?>
			<div class="col-md-4 pb-3 pl-3 pr-3 mb-3 item">
				<div class="card panel {{ $resource->status . ($resource->hasNews ? ' hasnews ' . ($resource->isNow ? $resource->hasNews : '') : '') . ($resource->data ? ' has-services' : '') }}">
					<div class="card-header p-3">
						<div class="row">
							<div class="card-title col-sm-9 col-md-9 item-name">{{ $resource->name }}</div>
							<div class="card-status col-sm-3 col-md-3 text-right">
								@if ($resource->hasNews)
									<span class="tip text-<?php echo $resource->hasNews == 'outage' ? 'danger' : 'info'; ?>" title="{{ trans('status::status.has announcements') }}">
										<span class="fa fa-<?php echo $resource->hasNews == 'outage' ? 'exclamation-circle' : 'wrench'; ?>" aria-hidden="true"></span><span class="sr-only">{{ trans('status::status.has announcements') }}</span>
									</span>
								@endif

								@if ($resource->status == 'impaired')
									<span class="item-status text-warning tip" title="{{ trans('status::status.state.warning') }}">
										<span id="{{ $resource->id }}_icon" class="fa fa-exclamation-triangle" aria-hidden="true"></span><span class="sr-only">{{ trans('status::status.state.impaired') }}</span>
									</span>
								@elseif ($resource->status == 'down')
									<span class="item-status text-danger tip" title="{{ trans('status::status.state.down') }}">
										<span id="{{ $resource->id }}_icon" class="fa fa-exclamation-circle" aria-hidden="true"></span><span class="sr-only">{{ trans('status::status.state.down') }}</span>
									</span>
								@elseif ($resource->status == 'maintenance')
									<span class="item-status text-info tip" title="{{ trans('status::status.state.maintenance') }}">
										<span id="{{ $resource->id }}_icon" class="fa fa-wrench" aria-hidden="true"></span><span class="sr-only">{{ trans('status::status.state.maintenance') }}</span>
									</span>
								@elseif ($resource->status == 'offline')
									<span class="item-status text-secondary tip" title="{{ trans('status::status.state.offline') }}">
										<span id="{{ $resource->id }}_icon" class="fa fa-ellipsis-h" aria-hidden="true"></span><span class="sr-only">{{ trans('status::status.state.offline') }}</span>
									</span>
								@else
									<span class="item-status text-success tip" title="{{ trans('status::status.state.operational') }}">
										<span id="{{ $resource->id }}_icon" class="fa fa-check" aria-hidden="true"></span><span class="sr-only">{{ trans('status::status.state.operational') }}</span>
									</span>
								@endif
							</div>
						</div>
						<div class="text-muted item-timestamp">Last Update: {{ $resource->statusUpdate->format('M d, h:i a') }}</div>
					</div>
					@if (auth()->user() && auth()->user()->can('manage resources'))
						<div class="card-footer">
							<div class="input-group">
								<span class="input-group-addon">
									<span class="input-group-text"><label for="status-{{ $resource->id }}">{{ trans('status::status.status') }}</label></span>
								</span>
								<select class="form-control resource-status" name="status" id="status-{{ $resource->id }}" data-id="{{ $resource->id }}" data-api="{{ route('api.resources.update', ['id' => $resource->id]) }}">
									<option value=""<?php if (!$resource->status) { echo ' selected="selected"'; } ?> data-status="success tip" data-class="fa fa-check">{{ trans('status::status.option.automatic') }}</option>
									<option value="operational"<?php if ($resource->status == 'operational') { echo ' selected="selected"'; } ?> data-status="success tip" data-class="fa fa-check">{{ trans('status::status.option.operational') }}</option>
									<option value="impaired"<?php if ($resource->status == 'impaired') { echo ' selected="selected"'; } ?> data-status="warning tip" data-class="fa fa-exclamation-triangle">{{ trans('status::status.option.impaired') }}</option>
									<option value="down"<?php if ($resource->status == 'down') { echo ' selected="selected"'; } ?> data-status="danger tip" data-class="fa fa-exclamation-circle">{{ trans('status::status.option.down') }}</option>
									<option value="maintenance"<?php if ($resource->status == 'maintenance') { echo ' selected="selected"'; } ?> data-status="maint tip" data-class="fa fa-wrench">{{ trans('status::status.option.maintenance') }}</option>
									<option value="offline"<?php if ($resource->status == 'offline') { echo ' selected="selected"'; } ?> data-status="offline tip" data-class="fa fa-ellipsis-h">{{ trans('status::status.option.offline') }}</option>
								</select>
							</div>
						</div>

						<?php
						if (!($resource->hasNews == 'maintenance' && $resource->isNow) && $resource->data)
						{
							?>
							<ul class="list-group list-group-flush">
								<?php
							foreach ($resource->data as $section => $items)
							{
								$status = 'success';
								foreach ($items as $item)
								{
									if ($item->value[1] == 1)
									{
										$status = 'danger';
										break;
									}
								}
								?>
								<li class="list-group-item section-{{ $section }}" id="section-{{ $k . '-' . $section }}">
									<div class="card-text">
										<div class="row">
											<div class="col-sm-10 col-md-10 section-header">
												<a href="#section-{{ $k . '-' . $section }}">
													{{ $section }}
												</a>
											</div>
											<div class="col-sm-2 col-md-2 text-right">
												@if ($status == 'warning')
													<span class="text-warning tip" title="{{ trans('status::status.state.impaired') }}">
														<span class="fa fa-exclamation-triangle" aria-hidden="true"></span><span class="sr-only">{{ trans('status::status.state.impaired') }}</span>
													</span>
												@elseif ($status == 'danger')
													<span class="text-danger tip" title="{{ trans('status::status.state.down') }}">
														<span class="fa fa-exclamation-circle" aria-hidden="true"></span><span class="sr-only">{{ trans('status::status.state.down') }}</span>
													</span>
												@else
													<span class="text-success tip" title="{{ trans('status::status.state.operational') }}">
														<span class="fa fa-check" aria-hidden="true"></span><span class="sr-only">{{ trans('status::status.state.operational') }}</span>
													</span>
												@endif
											</div>
										</div>
									</div>
									<ul class="list-unstyled pl-0 mt-4 section-details" id="section-{{ $k . '-' . $section }}-details">
										<?php
										foreach ($items as $item)
										{
											$stts = 0;
											$hasSub = false;
											foreach ((array)$item->metric as $key => $val)
											{
												if (is_numeric($val))
												{
													$hasSub = true;
													$stts = $val <= $stts ?: $val;
												}
											}
											if ($hasSub)
											{
											?>
											<li class="mt-2 d-flex align-items-center justify-content-between">
												<span class="name text-secondary text-nowrap text-truncate">{{ isset($item->metric->frontend) ? $item->metric->frontend : 'unknown' }}</span>
												<span class="value text-nowrap status-operational">
													@if ($stts)
														<span class="text-success tip" title="{{ trans('status::status.state.operational') }}">
															<span class="fa fa-check" aria-hidden="true"></span>
															<span class="sr-only">{{ trans('status::status.state.operational') }}</span>
														</span>
													@else
														<span class="text-danger tip" title="{{ trans('status::status.state.down') }}">
															<span class="fa fa-exclamation-circle" aria-hidden="true"></span>
															<span class="sr-only">{{ trans('status::status.state.down') }}</span>
														</span>
													@endif
												</span>
											</li>
											<?php
											}
										}
										?>
									</ul>
								</li>
								<?php
							}
							?>
							</ul>
							<?php
						}
						?>
					@endif

						<?php
						if (count($resource->news) && $resource->isNow)
						{
							?>
							<ul class="list-group list-group-flush">
								<?php
							foreach ($resource->news as $ns)
							{
								$typ = 'maintenance';
								if (stristr($ns->headline, 'issue')
								|| stristr($ns->headline, 'unavailable')
								|| stristr($ns->headline, 'outage'))
								{
									$typ = 'outage';
								}
								?>
								<li class="list-group-item section-news open" id="section-{{ $k }}-news">
									<ul class="list-unstyled pl-0" id="section-{{ $k }}-news-details">
										@if ($typ == 'outage')
											<li><!-- class="mt-2 d-flex align-items-center justify-content-between">-->
												<span class="fa fa-link" aria-hidden="true"></span> <a href="#news{{ $ns->id }}">{!! $ns->headline !!}</a>
											</li>
										@else
											<?php
											$st = $ns->datetimenews;
											$ed = $ns->datetimenewsend;

											/*if ($st->format('Y-m-d') == $ed->format('Y-m-d'))
											{
												$nw = new DateTime();
												for ($i = 1; $i <= 24; $i++)
												{
													//$dt = new DateTime($st->format('Y-m-d') . $i . ':00:00')
													echo $i . ':00';
													if ($st->format('H') == $i)
													{
														echo ' - start';
													}
													if ($nw->format('H') == $i)
													{
														echo ' - now';
													}
													if ($ed->format('H') == $i)
													{
														echo ' - end';
													}
													echo '<br />';
												}
											}*/
											?>
											<li class="mt-2 d-flex align-items-center justify-content-between">
												<span class="name text-secondary text-nowrap text-truncate">{{ trans('status::status.starts') }}</span>
												<span class="value text-nowrap">{{ $st->format('M d, h:i a') }}</span>
											</li>
											<li class="mt-2 d-flex align-items-center justify-content-between">
												<span class="name text-secondary text-nowrap text-truncate">{{ trans('status::status.ends') }}</span>
												<span class="value text-nowrap">{{ $ed->format('M d, h:i a') }}</span>
											</li>
										@endif
									</ul>
								</li>
								<?php
							}
							?>
							</ul>
							<?php
						}
						?>
					
				</div>
			</div>
			<?php
			$k++;
			if ($k%3 == 0)
			{
				?>
				</div>
				<div class="row">
				<?php
			}
		}
		?>
	</div><!-- / .row -->
</div><!-- / .resource-statuses -->
@endforeach

@if ($type)
	<div class="row">
		<div class="sidenav sidemenu-news col-lg-3 col-md-3 col-sm-12 col-xs-12">
			<h3>Upcoming Items</h3>
			<?php
			$start = Carbon\Carbon::now()->format('Y-m-d');

			$upcoming = $type->articles()
				->wherePublished()
				->where('template', '=', 0)
				->where(function($where) use ($start)
				{
					$where->where('datetimenews', '>', $start)
						->orWhere(function($wher) use ($start)
						{
							$wher->where('datetimenewsend', '>', $start)
								->where('datetimenews', '<', $start);
						});
				})
				->orderBy('datetimenews', 'desc')
				->limit(10)
				->get();
			?>
			@if (count($upcoming))
				<ul class="news-list">
					@foreach ($upcoming as $article)
						<li>
							<article id="article-{{ $article->id }}">
								<h3 class="news-title">
									<a href="{{ route('site.news.show', ['id' => $article->id]) }}">{{ $article->headline }}</a>
								</h3>
								<p class="news-metadata text-muted">
									@if ($article->isToday())
										@if ($article->isNow())
											<span class="badge badge-success">{{ trans('news::news.happening now') }}</span>
										@else
											<span class="badge badge-info">{{ trans('news::news.today') }}</span>
										@endif
									@elseif ($article->isTomorrow())
										<span class="badge">{{ trans('news::news.tomorrow') }}</span>
									@endif
									<span class="fa fa-fw fa-clock-o" aria-hidden="true"></span>
									<time datetime="{{ $article->datetimenews }}">
										{{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }}
									</time>
									<?php
									$lastupdate = $article->updates()
										->orderBy('datetimecreated', 'desc')
										->limit(1)
										->first();
									?>
									@if ($lastupdate)
										<span class="badge badge-warning"><span class="fa fa-exclamation-circle" aria-hidden="true"></span> Updated {{ $lastupdate->datetimecreated->format('h:m') }} {{ $lastupdate->datetimecreated->format('M d, Y') }}</span>
									@endif

									<?php
									if (count($article->resources) > 0)
									{
										$resourceArray = array();
										foreach ($article->resources as $resource)
										{
											if (!$resource->resource)
											{
												continue;
											}
											$resourceArray[] = '<a href="' . route('site.news.type', ['name' => strtolower($resource->resource->name)]) . '">' . $resource->resource->name . '</a>';
										}
										echo '<br /><span class="fa fa-fw fa-tags" aria-hidden="true"></span> ' .  implode(', ', $resourceArray);
									}
									?>
								</p>
								<p>
									{{ Illuminate\Support\Str::limit(strip_tags($article->formattedBody), 150) }}
								</p>
							</article>
						</li>
					@endforeach
				</ul>
			@else
				<p>{{ trans('status::status.no upcoming items') }}</p>
			@endif
		</div>

		<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
			<div class="row">
				<div class="col-md-6">
					<h3 class="newsheader">
						Past {{ $type->name }}
					</h3>
				</div>
				<div class="col-md-6 text-right">
					<div class="btn-group" role="group" aria-label="Options">
						<a target="_blank" class="btn" href="{{ route('site.news.feed', ['name' => $type->name]) }}" title="{{ trans('news::news.rss feed') }}"><span class="fa fa-rss-square" aria-hidden="true"></span><span class="sr-only">RSS Feed</span></a>
						@if ($type->calendar)
							<a target="_blank" class="btn calendar calendar-subscribe" href="{{ preg_replace('/^https?:\/\//', 'webcal://', route('site.news.calendar', ['name' => strtolower($type->name)])) }}" title="Subscribe to calendar"><!--
								--><span class="fa fa-fw fa-calendar" aria-hidden="true"></span><span class="sr-only">Subscribe</span><!--
							--></a>
							<a target="_blank" class="btn calendar calendar-download" href="{{ route('site.news.calendar', ['name' => strtolower($type->name)]) }}" title="Download calendar"><!--
								--><span class="fa fa-fw fa-download" aria-hidden="true"></span><span class="sr-only">Download</span><!--
							--></a>
						@endif
					</div>
				</div>
			</div>

			<div class="issues-container">
				<ul class="issues">
					<?php
					$past = $type->articles()
						->wherePublished()
						->where('template', '=', 0)
						->where(function($where) use ($start)
						{
							$where->where(function($wher) use ($start)
							{
								$wher->where('datetimenewsend', '=', '0000-00-00 00:00:00')
									->where('datetimenews', '<', $start);
							})
							->orWhere('datetimenewsend', '<', $start);
						})
						->orderBy('datetimenews', 'desc')
						->limit(10)
						->get();

					foreach ($past as $news)
					{
						$typ = 'maintenance';
						if (stristr($news->headline, 'issue')
							|| stristr($news->headline, 'unavailable')
							|| stristr($news->headline, 'outage'))
						{
							$typ = 'outage';
						}
						?>
						<li>
							<article id="news{{ $news->id }}" class="card">
								<div class="card-header news-header">
									<h4 class="card-title">
										{{ $news->headline }}
										<!-- <span class="badge badge-{{ $typ == 'outage' ? 'danger' : 'info' }}">{{ $typ }}</span> -->
									</h4>
								</div>
								<div class="card-body news-body">
									<span class="fa fa-fw fa-clock-o" aria-hidden="true"></span> <time datetime="{{ $news->datetimenews->format('Y-m-d h:i:s') }}">{{ $news->formatDate($news->datetimenews, $news->datetimenewsend) }}</time>

									@if ($news->isToday())
										@if ($news->isNow())
											<span class="badge badge-success">Happening now</span>
										@else
											<span class="badge badge-info">Today</span>
										@endif
									@elseif ($news->isTomorrow())
										<span class="badge">Tomorrow</span>
									@endif

									@if ($news->location)
										<br /><span class="fa fa-fw fa-map-marker" aria-hidden="true"></span> {{ $news->location }}
									@endif
									@if ($news->url)
										<br /><span class="fa fa-fw fa-link" aria-hidden="true"></span> <a href="{{ $news->url }}">{{ $news->url }}</a>
									@endif

									<?php
									if (count($news->resources) > 0):
										$resourceArray = array();
										foreach ($news->resources as $resource):
											$resourceArray[] = '<a href="/news/' . strtolower($resource->resource->name) . '/">' . $resource->resource->name . '</a>';
										endforeach;
										echo '<p><span class="fa fa-fw fa-tags" aria-hidden="true"></span> ' .  implode(', ', $resourceArray) . '</p>';
									endif;
									?>

									{!! $news->formattedBody !!}
								</div>
							</article>
						</li>
						<?php
					}
					?>
				</ul>
				<p><a href="{{ route('site.news.type', ['name' => $type->name]) }}">Show More {{ $type->name }}</a></p>
			</div><!-- / .issues-container -->
		</div><!-- / .col-lg-9 col-md-9 col-sm-12 col-xs-12 -->
	</div><!-- / .row -->
@endif
</div>
@stop