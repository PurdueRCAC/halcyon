@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/vendor/fullcalendar/core/main.min.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/vendor/fullcalendar/daygrid/main.min.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/vendor/fullcalendar/timegrid/main.min.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/css/news.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/news/vendor/fullcalendar/core/main.min.js') }}"></script>
<script src="{{ asset('modules/news/vendor/fullcalendar/interaction/main.min.js') }}"></script>
<script src="{{ asset('modules/news/vendor/fullcalendar/daygrid/main.min.js') }}"></script>
<script src="{{ asset('modules/news/vendor/fullcalendar/timegrid/main.min.js') }}"></script>
<script src="{{ asset('modules/news/js/site.js') }}"></script>
@endpush

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	Education menu here?
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">

	<h2 class="newsheader">
		<a class="icn tip" href="{{ route('site.news.rss', ['name' => $type->name]) }}" title="{{ trans('news::news.rss feed') }}">
			<i class="fa fa-rss-square" aria-hidden="true"></i> {{ trans('news::news.rss feed') }}
		</a>
		{{ $type->name }}
	</h2>

	<?php
	$day = date('w');
	$week_start = (new DateTime)->modify('-' . $day . ' days');
	$week_end   = (new DateTime)->modify('+' . 30 . ' days');

	$start = $week_start->format('Y-m-d') . ' 00:00:00';
	$stop  = $week_end->format('Y-m-d') . ' 00:00:00';

	$rows = $type->articles()
		->where('published', '=', 1)
		->where('template', '=', 0)
		->where(function($where) use ($start, $stop)
		{
			$where->where('datetimenews', '<=', $start)
				->orWhere('datetimenews', '<=', $stop)
				->orWhere('datetimenewsend', '=', '0000-00-00 00:00:00');
		})
		->where(function($where) use ($start, $stop)
		{
			$where->where('datetimenewsend', '>=', $start)
				->orWhere('datetimenewsend', '>=', $stop)
				->orWhere('datetimenewsend', '=', '0000-00-00 00:00:00');
		})
		->where(function($where) use ($start, $stop)
		{
			$where->where('datetimenews', '<=', $start)
				->orWhere('datetimenews', '<=', $stop)
				->orWhere('datetimenewsend', '!=', '0000-00-00 00:00:00');
		})
		->where(function($where) use ($start, $stop)
		{
			$where->where('datetimenews', '>=', $start)
				->orWhere('datetimenews', '>=', $stop)
				->orWhere('datetimenewsend', '=', '0000-00-00 00:00:00');
		})
		->orderBy('datetimenews', 'asc')
		->limit(100)
		->get();

	$events = array();

	foreach ($rows as $event)
	{
		$slot = new stdClass;
		$slot->title = (isset($event->location) && $event->location) ? $event->location : $event->headline;
		$slot->start = (new DateTime($event->datetimenews))->format('Y-m-d\TH:i:s');
		$slot->end   = (new DateTime($event->datetimenewsend))->format('Y-m-d\TH:i:s');
		$slot->id    = $event->id;

		$attending = false;
		$reserved  = false;
		foreach ($event->associations as $assoc)
		{
			if (auth()->user() && $assoc->associd == auth()->user()->id)
			{
				$attending = $assoc->id;
			}
			elseif ($event->url && $assoc->assoctype == 'user')
			{
				$u = App\Modules\Users\Models\User::find($assoc->associd);

				if ($u && !in_array(config()->get('module.news.ignore_role', 4), $u->getAuthorisedRoles()))
				{
					$reserved = $u->name;
				}
			}
		}

		if ($event->url)
		{
			$slot->backgroundColor = '#0e7e12';
			$slot->borderColor = '#0e7e12';

			if ($reserved)
			{
				$slot->backgroundColor = '#757575';
				$slot->borderColor = '#757575';
			}
		}

		$events[] = $slot;
		?>
		<div id="coffee<?php echo $event->id; ?>" class="dialog dialog-event" title="<?php echo $event->headline; ?>">
			<p class="newsattend">
				<?php if ($event['url']) { ?>
					<?php if (auth()->user() && in_array(config()->get('module.news.ignore_role', 4), $u->getAuthorisedRoles())) { ?>
						<?php echo $reserved ? 'Reserved by ' . $reserved : 'Not reserved'; ?>
					<?php } else { ?>
						<?php if ($reserved) { ?>
							This time is reserved
						<?php } else { ?>
							<?php if (auth()->user()) { ?>
								<?php if (!$attending) { ?>
									<a class="btn-attend btn btn-primary" href="/coffee?attend=1" data-newsid="<?php echo $event->id; ?>" data-assoc="<?php echo auth()->user()->id; ?>">Reserve this time</a>
								<?php } else { ?>
									You reserved this time.<br />
									<a class="btn-notattend btn btn-danger" href="/coffee?attend=0" data-id="<?php echo $attending; ?>">Cancel</a>
								<?php } ?>
							<?php } else { ?>
								<a class="btn btn-primary" href="/login?loginrefer=<?php echo urlencode('/coffee?attend=1'); ?>" data-newsid="<?php echo $event->id; ?>" data-assoc="0">Reserve this time</a>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				<?php } else { ?>
					<?php if (auth()->user()) { ?>
						<?php if (!$attending) { ?>
							<a class="btn-attend btn btn-primary" href="/coffee?attend=1" data-newsid="<?php echo $event->id; ?>" data-assoc="<?php echo auth()->user()->id; ?>">I'm interested in attending</a>
						<?php } else { ?>
							You expressed interest in attemding.<br />
							<a class="btn-notattend btn btn-danger" href="/coffee?attend=0" data-id="<?php echo $attending; ?>">Cancel</a>
						<?php } ?>
					<?php } else { ?>
						<a class="btn btn-primary" href="/login?loginrefer=<?php echo urlencode('/coffee?attend=1'); ?>" data-newsid="<?php echo $event->id; ?>" data-assoc="0">I'm interested in attending</a>
					<?php } ?>
				<?php } ?>
			</p>
			<p class="newsheader">
				<i class="fa fa-fw fa-clock-o" aria-hidden="true"></i> {!! $event->formatDate($event->datetimenews, $event->datetimenewsend) !!}
				<?php
				$news_start = new DateTime($event->datetimenews);
				$news_end = new DateTime($event->datetimenewsend);

				$now = new DateTime();

				if ($now->format('Y-m-d') == $news_start->format('Y-m-d'))
				{
					if ($event->datetimenewsend
					 && $event->datetimenewsend != '0000-00-00 00:00:00'
					 && $now->format('Y-m-d h:i:s') > $event->datetimenews
					 && $now->format('Y-m-d h:i:s') < $news_end)
					{
						echo ' <span class="badge badge-success">' . trans('news::news.happening now') . '</span>';
					}
					else
					{
						echo ' <span class="badge badge-info">' . trans('news::news.today') . '</span>';
					}
				}
				elseif ($now->modify('+1 day')->format('Y-m-d') == $news_start->format('Y-m-d'))
				{
					echo ' <span class="badge">' . trans('news::news.tomorrow') . '</span>';
				}

				if ($event->location != '')
				{
					echo '<br /><i class="fa fa-fw fa-map-marker" aria-hidden="true"></i> ' . $event->location;
				}

				if ($event->url)
				{
					echo '<br /><i class="fa fa-fw fa-link" aria-hidden="true"></i> <a href="' . $event->url . '">' . $event->url . '</a>';
				}

				$resourceArray;
				if (count($event->resources) > 0)
				{
					$resourceArray = array();
					foreach ($event->resources as $resource)
					{
						$resourceArray[] = '<a href="/news/' . strtolower($resource->resource->name) . '/">' . $resource->resource->name . '</a>';
					}
					echo '<br /><i class="fa fa-fw fa-tags" aria-hidden="true"></i> ' .  implode(', ', $resourceArray);
				}

				if (auth()->user() && auth()->user()->can('manage news') && !empty($event->associations))
				{
					$users = array();
					foreach ($event->associations as $assoc)
					{
						if ($assoc->assoctype == 'user')
						{
							$user = App\Modules\Users\Models\User::find($assoc->associd);
							if ($user)
							{
								$users[] = $user->name;
							}
						}
					}
					echo '<br /><i class="fa fa-fw fa-user" aria-hidden="true"></i> ' . implode(', ', $users);
				}

				if (!$event->template
				 && $event->datetimenewsend != '0000-00-00 00:00:00'
				 && $event->datetimenewsend > $now->format('Y-m-d h:i:s'))
				{
					if ($type->calendar)
					{
						?>
						<br />
						<i class="fa fa-fw fa-calendar" aria-hidden="true"></i>
						<a target="_blank" class="calendar calendar-subscribe" href="webcal://<?php echo request()->getHttpHost(); ?>/news/calendar/<?php echo $event->id; ?>" title="Subscribe to event"><!--
							-->Subscribe<!--
						--></a>
						&nbsp;|&nbsp;
						<i class="fa fa-fw fa-download" aria-hidden="true"></i>
						<a target="_blank" class="calendar calendar-download" href="/news/calendar/<?php echo $event->id; ?>" title="Download event"><!--
							-->Download<!--
						--></a>
						<?php
					}
				}
				?>
			</p>
			<?php echo $event->body; ?>
		</div>
		<?php
	}
	?>
	<hr />
	<div id="calendar">
	</div>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var calendarEl = document.getElementById('calendar');

			var calendar = new FullCalendar.Calendar(calendarEl, {
				plugins: ['interaction', 'dayGrid', 'timeGrid'],
				defaultView: 'timeGridWeek',
				defaultDate: '<?php echo $week_start->format('Y-m-d'); ?>',
				nowIndicator: true,
				allDaySlot: false,
				minTime: "06:00:00",
				maxTime: "19:00:00",
				weekends: false,
				businessHours: {
					// days of week. an array of zero-based day of week integers (0=Sunday)
					daysOfWeek: [ 1, 2, 3, 4, 5 ], // Monday - Thursday
					startTime: '07:00', // a start time (10am in this example)
					endTime: '17:00', // an end time (6pm in this example)
				},
				eventClick: function(info) {
					//$('.dialog-event').dialog({ autoOpen: false, modal: true, width: '600px' });
					$('#coffee' + info.event.id).dialog('open');
				},
				events: <?php echo json_encode($events); ?>
			});

			calendar.render();
			$('.fallback').hide();
			$('.dialog-event').dialog({ autoOpen: false, modal: true, width: 600 });
		});
	</script>
</div>

@stop