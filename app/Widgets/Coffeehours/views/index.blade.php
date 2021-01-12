<?php
/**
 * News widget layout
 */
?>
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

<?php
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
	<div id="coffee{{ $event->id }}" class="dialog dialog-event" title="{{ $event->headline }}">
		<p class="newsattend">
			@if ($event->url)
				@if (auth()->user() && in_array(config()->get('module.news.ignore_role', 4), auth()->user()->getAuthorisedRoles()))
					{{ $reserved ? trans('widget.coffeehours::coffeehours.reserved by', ['name' => $reserved]) : trans('widget.coffeehours::coffeehours.not reserved') }}
				@else
					@if ($reserved)
						This time is reserved
					@else
						@if (auth()->user())
							@if (!$attending)
								<a class="btn-attend btn btn-primary" href="{{ route('page', ['uri' => 'coffee', 'attend' => 1]) }}" data-newsid="{{ $event->id }}" data-assoc="{{ auth()->user()->id }}">Reserve this time</a>
							@else
								You reserved this time.<br />
								<a class="btn-notattend btn btn-danger" href="{{ route('page', ['uri' => 'coffee', 'attend' => 0]) }}" data-id="{{ $attending }}">Cancel</a>
							@endif
						@else
							<a class="btn btn-primary" href="/login?loginrefer=<?php echo urlencode(route('page', ['uri' => 'coffee', 'attend' => 1])); ?>" data-newsid="{{ $event->id }}" data-assoc="0">Reserve this time</a>
						@endif
					@endif
				@endif
			@else
				@if (auth()->user())
					@if (!$attending)
						<a class="btn-attend btn btn-primary" href="{{ route('page', ['uri' => 'coffee', 'attend' => 1]) }}" data-newsid="{{ $event->id }}" data-assoc="{{ auth()->user()->id }}">I'm interested in attending</a>
					@else
						You expressed interest in attemding.<br />
						<a class="btn-notattend btn btn-danger" href="{{ route('page', ['uri' => 'coffee', 'attend' => 0]) }}" data-id="{{ $attending }}">Cancel</a>
					@endif
				@else
					<a class="btn btn-primary" href="/login?loginrefer=<?php echo urlencode(route('page', ['uri' => 'coffee', 'attend' => 1])); ?>" data-newsid="{{ $event->id }}" data-assoc="0">I'm interested in attending</a>
				@endif
			@endif
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
					$resourceArray[] = '<a href="' . route('site.news.type', ['name' => strtolower($resource->resource->name)]) . '">' . $resource->resource->name . '</a>';
				}
				echo '<br /><i class="fa fa-fw fa-tags" aria-hidden="true"></i> ' .  implode(', ', $resourceArray);
			}

			if (auth()->user() && auth()->user()->can('manage news') && !empty($event->associations))
			{
				$users = array();
				foreach ($event->associations as $assoc)
				{
					if ($assoc->associated)
					{
						$users[] = $assoc->associated->name;
					}
				}
				if (!empty($users))
				{
					echo '<br /><i class="fa fa-fw fa-user" aria-hidden="true"></i> ' . implode(', ', $users);
				}
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
					<a target="_blank" class="calendar calendar-download" href="<?php echo route('site.news.calendar', ['name' => $event->id]); ?>" title="Download event"><!--
						-->Download<!--
					--></a>
					<?php
				}
			}
			?>
		</p>
		{!! $event->body !!}
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
