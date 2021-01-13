@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/status/css/site.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/status/js/site.js') }}"></script>
@endpush

@php
app('pathway')->append(
	trans('status::status.status'),
	route('site.status.index')
);
@endphp

@section('content')

<ul class="status-legend">
	<li><span class="text-success"><i class="fa fa-check" aria-hidden="true"></i></span> {{ trans('status::status.option.operational') }}</li>
	<li><span class="text-info"><i class="fa fa-wrench" aria-hidden="true"></i></span> {{ trans('status::status.option.maintenance') }}</li>
	<li><span class="text-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></span> {{ trans('status::status.option.warning') }}</li>
	<li><span class="text-danger"><i class="fa fa-exclamation-circle" aria-hidden="true"></i></span> {{ trans('status::status.option.down') }}</li>
	<li><span class="text-secondary"><i class="fa fa-ellipsis-h" aria-hidden="true"></i></span> {{ trans('status::status.option.offline') }}</li>
</ul>

@foreach ($restypes as $restype)
<div class="resource-statuses">
	<h3>{{ $restype->name }}</h3>

	<div class="row">
		<?php
		$k = 0;
		$now = new DateTime('now');
		$start = $now->format('Y-m-d h:i:s');
		$end = $now->modify('-1 day')->format('Y-m-d h:i:s');

		$resources = $restype->resources()
			->whereIsActive()
			->where('listname', '!=', '')
			->where('display', '>', 0)
			->orderBy('name', 'asc')
			->get();

		foreach ($resources as $resource)
		{
			$resource->statusUpdate = $now;

			event($event = new App\Modules\Status\Events\StatusRetrieval($resource));

			$resource = $event->asset;

			//$hasNews = '';
			//$thisnews = array();
			//$isHappening = false;
			?>
			<div class="col-md-4 pb-3 pl-3 pr-3 mb-5">
				<div class="card panel shadow-sm {{ $resource->status . ($resource->hasNews ? ' hasnews ' . ($resource->isHappening ? $resource->hasNews : '') : '') . ($resource->data ? ' has-services' : '') }}">
					<div class="card-header p-3">
						<div class="row">
							<div class="card-title col-sm-9 col-md-9">{{ $resource->name }}</div>
							<div class="card-status col-sm-3 col-md-3 text-right">
								@if ($resource->hasNews)
									<span class="tip <?php echo $resource->hasNews == 'outage' ? 'danger' : 'info'; ?>" title="{{ trans('status::status.has announcements') }}">
										<i class="fa fa-<?php echo $resource->hasNews == 'outage' ? 'exclamation-circle' : 'wrench'; ?>" aria-hidden="true"></i><span class="sr-only">{{ trans('status::status.has announcements') }}</span>
									</span>
									<!-- <span class="badge badge-<?php echo $resource->hasNews == 'outage' ? 'danger' : 'info'; ?> tip" title="{{ trans('status::status.has announcements') }}"><?php echo $resource->hasNews == 'outage' ? 'outage' : 'maintenance'; ?></span> -->
								@endif

								@if ($resource->status == 'warning')
									<span class="warning tip" title="{{ trans('status::status.state.warning') }}">
										<i id="{{ $resource->id }}_icon" class="fa fa-exclamation-triangle" aria-hidden="true"></i><span class="sr-only">{{ trans('status::status.state.warning') }}</span>
									</span>
								@elseif ($resource->status == 'down')
									<span class="danger tip" title="{{ trans('status::status.state.down') }}">
										<i id="{{ $resource->id }}_icon" class="fa fa-exclamation-circle" aria-hidden="true"></i><span class="sr-only">{{ trans('status::status.state.down') }}</span>
									</span>
								@elseif ($resource->status == 'maintenance')
									<span class="maint tip" title="{{ trans('status::status.state.maintenance') }}">
										<i id="{{ $resource->id }}_icon" class="fa fa-wrench" aria-hidden="true"></i><span class="sr-only">{{ trans('status::status.state.maintenance') }}</span>
									</span>
								@elseif ($resource->status == 'offline')
									<span class="offline tip" title="{{ trans('status::status.state.offline') }}">
										<i id="{{ $resource->id }}_icon" class="fa fa-ellipsis-h" aria-hidden="true"></i><span class="sr-only">{{ trans('status::status.state.offline') }}</span>
									</span>
								@else
									<span class="success tip" title="{{ trans('status::status.state.operational') }}">
										<i id="{{ $resource->id }}_icon" class="fa fa-check" aria-hidden="true"></i><span class="sr-only">{{ trans('status::status.state.operational') }}</span>
									</span>
								@endif
							</div>
						</div>
						<div class="text-muted"><small>Last Update: {{ $resource->statusUpdate->format('M d, h:i a') }}</small></div>
					</div>
					@if (auth()->user() && auth()->user()->can('manage resources'))
						<div class="card-body">
							<div class="input-group">
								<span class="input-group-addon">
									<span class="input-group-text"><label for="status-{{ $resource->id }}">{{ trans('status::status.status') }}</label></span>
								</span>
								<select class="form-control resource-status" name="status" id="status-{{ $resource->id }}" data-id="{{ $resource->id }}" data-api="{{ route('api.resources.update', ['id' => $resource->id]) }}">
									<option value=""<?php if (!$resource->status) { echo ' selected="selected"'; } ?> data-status="success tip" data-class="fa fa-check">{{ trans('status::status.option.automatic') }}</option>
									<option value="success"<?php if ($resource->status == 'success') { echo ' selected="selected"'; } ?> data-status="success tip" data-class="fa fa-check">{{ trans('status::status.option.operational') }}</option>
									<option value="warning"<?php if ($resource->status == 'warning') { echo ' selected="selected"'; } ?> data-status="warning tip" data-class="fa fa-exclamation-triangle">{{ trans('status::status.option.warning') }}</option>
									<option value="danger"<?php if ($resource->status == 'danger') { echo ' selected="selected"'; } ?> data-status="danger tip" data-class="fa fa-exclamation-circle">{{ trans('status::status.option.down') }}</option>
									<option value="maintenance"<?php if ($resource->status == 'maint') { echo ' selected="selected"'; } ?> data-status="maint tip" data-class="fa fa-wrench">{{ trans('status::status.option.maintenance') }}</option>
									<option value="offline"<?php if ($resource->status == 'offline') { echo ' selected="selected"'; } ?> data-status="offline tip" data-class="fa fa-ellipsis-h">{{ trans('status::status.option.offline') }}</option>
								</select>
							</div>
						</div>
					@endif
					
						<?php
						/*if (!($resource->hasNews == 'maintenance' && $resource->isHappening))
						{
							foreach ($resource->data as $section => $items)
							{
								if ($section == 'name' || $section == 'status')// || $section == 'queues' || $section == 'nodes' || $section == 'front-ends')
								{
									continue;
								}

								$status = 'success';
								foreach ($items as $item)
								{
									if (strtolower($item['value']) == 'ok' || stristr($item['value'], ' ok '))
									{
										//$status = 'ok';
									}
									elseif (stristr($item['value'], 'offline') || stristr($item['value'], 'unresponsive'))
									{
										$status = 'danger';
										break;
									}
									elseif (stristr($item['value'], 'deactivated'))
									{
										$status = 'warning';
										//break;
									}

									if (stristr($item['label'], 'offline') && (int)$item['value'])
									{
										$status = 'warning';
										//break;
									}
								}
								?>
								<li class="list-group-item section-<?php echo e($section); ?>" id="section-<?php echo $k . '-' . e($section); ?>">
									<div class="card-text">
										<div class="row">
											<div class="col-sm-10 col-md-10 section-header">
												<a href="#section-<?php echo $k . '-' . e($section); ?>">
													<?php echo e(ucfirst($section)); ?>
												</a>
											</div>
											<div class="col-sm-2 col-md-2 text-right">
												<?php if ($status == 'warning') { ?>
													<span class="warning tip" title="One or more services may be experiencing issues"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i><span class="sr-only">One or more services may be experiencing issues</span></span>
												<?php } elseif ($status == 'danger') { ?>
													<span class="danger tip" title="One or more services are offline"><i class="fa fa-exclamation-circle" aria-hidden="true"></i><span class="sr-only">One or more services are offline</span></span>
												<?php } else { ?>
													<span class="success tip" title="All services operational"><i class="fa fa-check" aria-hidden="true"></i><span class="sr-only">All services operational</span></span>
												<?php } ?>
											</div>
										</div>
									</div>
									<ul class="list-unstyled pl-0 mt-4 section-details" id="section-<?php echo $k . '-' . e($section); ?>-details">
										<?php
										$displayed = array();
										foreach ($items as $item)
										{
											if (in_array($item['label'], $displayed))
											{
												continue;
											}
											$displayed[] = $item['label'];
											?>
											<li class="mt-2 d-flex align-items-center justify-content-between">
												<span class="name text-secondary text-nowrap text-truncate"><?php echo e(ucfirst($item['label'])); ?></span>
												<span class="value text-nowrap status-operational">
													<?php if (strtolower($item['label']) == 'running' || stristr($item['label'], 'passed')) { ?>
														<span class="success tip" title="<?php echo e($item['value']); ?>"><?php echo e($item['value']); ?></span>
													<?php } elseif (stristr($item['label'], 'offline')) { ?>
														<span class="warning tip" title="<?php echo e($item['value']); ?>"><?php echo e($item['value']); ?></span>
													<?php } else { ?>
														<?php if (strtolower($item['value']) == 'ok' || stristr($item['value'], ' ok ')) { ?>
															<span class="success tip" title="Operational">
																<i class="fa fa-check" aria-hidden="true"></i>
																<span class="sr-only">Operational</span>
															</span>
														<?php } elseif (stristr($item['value'], 'offline')) { ?>
															<span class="danger tip" title="<?php echo e($item['value']); ?>">
																<i class="fa fa-exclamation-circle" aria-hidden="true"></i>
																<span class="sr-only"><?php echo e($item['value']); ?></span>
															</span>
														<?php } elseif (stristr($item['value'], 'unresponsive')) { ?>
															<span class="danger tip" title="<?php echo e($item['value']); ?>">
																<i class="fa fa-exclamation-circle" aria-hidden="true"></i>
																<span class="sr-only"><?php echo e($item['value']); ?></span>
															</span>
														<?php } elseif (stristr($item['value'], 'deactivated')) { ?>
															<span class="warning tip" title="<?php echo e($item['value']); ?>">
																<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
																<span class="sr-only"><?php echo e($item['value']); ?></span>
															</span>
														<?php } elseif (stristr($item['value'], 'undefined')) { ?>
															<span class="undefined tip" title="<?php echo e($item['value']); ?>">
																<i class="fa fa-question-circle" aria-hidden="true"></i>
																<span class="sr-only"><?php echo e($item['value']); ?></span>
															</span>
														<?php } else { ?>
															<?php echo e($item['value']); ?>
														<?php } ?>
													<?php } ?>
												</span>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
						}*/

						if (count($resource->news) && $resource->isHappening)
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
									<ul class="list-unstyled pl-0 mt-4" id="section-{{ $k }}-news-details">
										@if ($typ == 'outage')
											<li><!-- class="mt-2 d-flex align-items-center justify-content-between">-->
												<i class="fa fa-link" aria-hidden="true"></i> <a href="#news{{ $ns->id }}">{!! $ns->headline !!}</a>
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
									<i class="fa fa-fw fa-clock-o" aria-hidden="true"></i>
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
										<span class="badge badge-warning"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Updated {{ $lastupdate->datetimecreated->format('h:m') }} {{ $lastupdate->datetimecreated->format('M d, Y') }}</span>
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
										echo '<br /><i class="fa fa-fw fa-tags" aria-hidden="true"></i> ' .  implode(', ', $resourceArray);
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
						<a target="_blank" class="btn " href="/news/rss/<?php echo urlencode($type->name); ?>" title="RSS Feed for <?php echo urlencode($type->name); ?>"><i class="fa fa-rss-square" aria-hidden="true"></i><span class="sr-only">RSS Feed</span></a>
						@if ($type->calendar)
							<a target="_blank" class="btn  calendar calendar-subscribe" href="webcal://{{ request()->getHttpHost() }}/news/calendar/<?php echo urlencode(strtolower($type->name)); ?>" title="Subscribe to calendar"><!--
								--><i class="fa fa-fw fa-calendar" aria-hidden="true"></i><span class="sr-only">Subscribe</span><!--
							--></a>
							<a target="_blank" class="btn  calendar calendar-download" href="/news/calendar/<?php echo urlencode(strtolower($type->name)); ?>" title="Download calendar"><!--
								--><i class="fa fa-fw fa-download" aria-hidden="true"></i><span class="sr-only">Download</span><!--
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
									<h4>
										{{ $news->headline }}
										<!-- <span class="badge badge-{{ $typ == 'outage' ? 'danger' : 'info' }}">{{ $typ }}</span> -->
									</h4>
								</div>
								<div class="card-body news-body">
									<i class="fa fa-fw fa-clock-o" aria-hidden="true"></i> <time datetime="{{ $news->datetimenews->format('Y-m-d h:i:s') }}">{{ $news->formatDate($news->datetimenews, $news->datetimenewsend) }}</time>

									@if ($news->isToday())
										@if ($news->isHappening())
											<span class="badge badge-success">Happening now</span>
										@else
											<span class="badge badge-info">Today</span>
										@endif
									@elseif ($news->isTomorrow())
										<span class="badge">Tomorrow</span>
									@endif

									@if ($news->location)
										<br /><i class="fa fa-fw fa-map-marker" aria-hidden="true"></i> {{ $news->location }}
									@endif
									@if ($news->url)
										<br /><i class="fa fa-fw fa-link" aria-hidden="true"></i> <a href="{{ $news->url }}">{{ $news->url }}</a>
									@endif

									<?php
									if (count($news->resources) > 0):
										$resourceArray = array();
										foreach ($news->resources as $resource):
											$resourceArray[] = '<a href="/news/' . strtolower($resource->resource->name) . '/">' . $resource->resource->name . '</a>';
										endforeach;
										echo '<p><i class="fa fa-fw fa-tags" aria-hidden="true"></i> ' .  implode(', ', $resourceArray) . '</p>';
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
@stop