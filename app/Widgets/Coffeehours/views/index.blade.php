<?php
/**
 * News widget layout
 */
?>
@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/vendor/fullcalendar/core/main.min.css?v=' . filemtime(public_path() . '/modules/news/vendor/fullcalendar/core/main.min.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/vendor/fullcalendar/daygrid/main.min.css?v=' . filemtime(public_path() . '/modules/news/vendor/fullcalendar/daygrid/main.min.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/vendor/fullcalendar/timegrid/main.min.css?v=' . filemtime(public_path() . '/modules/news/vendor/fullcalendar/timegrid/main.min.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/css/news.css?v=' . filemtime(public_path() . '/modules/news/css/news.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/news/vendor/fullcalendar/core/main.min.js?v=' . filemtime(public_path() . '/modules/news/vendor/fullcalendar/core/main.min.js')) }}"></script>
<script src="{{ asset('modules/news/vendor/fullcalendar/interaction/main.min.js?v=' . filemtime(public_path() . '/modules/news/vendor/fullcalendar/interaction/main.min.js')) }}"></script>
<script src="{{ asset('modules/news/vendor/fullcalendar/daygrid/main.min.js?v=' . filemtime(public_path() . '/modules/news/vendor/fullcalendar/daygrid/main.min.js')) }}"></script>
<script src="{{ asset('modules/news/vendor/fullcalendar/timegrid/main.min.js?v=' . filemtime(public_path() . '/modules/news/vendor/fullcalendar/timegrid/main.min.js')) }}"></script>
<script src="{{ asset('modules/news/js/site.js?v=' . filemtime(public_path() . '/modules/news/js/site.js')) }}"></script>
@endpush

<?php
$ignore = $params->get('ignore_role');

$events = array();
$attend = array();

$now = Carbon\Carbon::now();

foreach ($rows as $event):
	$slot = new stdClass;
	$slot->title = (isset($event->location) && $event->location) ? $event->location : $event->headline;
	$slot->start = $event->datetimenews->format('Y-m-d\TH:i:s');
	$slot->end   = $event->datetimenewsend->format('Y-m-d\TH:i:s');
	$slot->id    = $event->id;

	if (strstr($event->body, '&amp;#039;')):
		$event->body = str_replace('&amp;#039;', "'", $event->body);
	endif;

	$attending = false;
	$reserved  = false;
	$claimed   = false;
	$comment   = null;
	$canAttend = true;
	$userid = 0;

	foreach ($event->associations as $assoc):
		if ($assoc->assoctype == 'staff'):
			$claimed = $assoc;
			continue;
		endif;

		if (auth()->user() && $assoc->associd == auth()->user()->id):
			$attending = $assoc->id;
			$comment = $assoc->comment;
			if (!$event->ended()):
				$event->attending = $assoc->id;
				$attend[] = $event;
			endif;
		elseif ($event->url):
			$u = App\Modules\Users\Models\User::find($assoc->associd);

			if ($u && (!$ignore || !in_array($ignore, $u->getAuthorisedRoles()))):
				$reserved = $u->name;
				$userid = $assoc->associd;
				$comment = $assoc->comment;
			endif;
		endif;
	endforeach;

	if (!$attending && $event->url && isset($attendance[$event->datetimenews->format('Y-m-d')])):
		$canAttend = false;
	endif;

	$now = Carbon\Carbon::now();

	$endregistration = Carbon\Carbon::parse($event->datetimenews);
	if ($end_reg = config('module.news.end_registration')):
		$endregistration = $endregistration->modify($end_reg);
	endif;

	$slot->backgroundColor = '#0e7e12'; // green
	$slot->borderColor = '#0e7e12';

	if ($event->url):
		// Mark as closed registration
		if ($now->getTimestamp() >= $endregistration->getTimestamp()):
			$slot->backgroundColor = '#757575'; // gray
			$slot->borderColor = '#757575';
		endif;

		// Mark as reserved if the event hasn't ended
		if (($reserved || $attending) && $now->getTimestamp() < $event->datetimenewsend->getTimestamp()):
			$slot->backgroundColor = '#0c5460'; // blue
			$slot->borderColor = '#0c5460';
		endif;
	else:
		$slot->backgroundColor = '#7F379A'; // purple
		$slot->borderColor = '#7F379A';

		if ($now->getTimestamp() >= $endregistration->getTimestamp()):
			$slot->backgroundColor = '#757575'; // gray
			$slot->borderColor = '#757575';
		endif;
	endif;

	$events[] = $slot;
	?>
	<section id="coffee{{ $event->id }}" class="dialog dialog-event" title="{{ $event->headline }}" aria-labelledby="coffee{{ $event->id }}-title">
		<h3 id="coffee{{ $event->id }}-title" class="sr-only"><span class="sr-only">Article #{{ $event->id }}:</span> {{ $event->headline }}</h3>

		<ul class="news-meta text-muted">
			<li>
				<span class="fa fa-fw fa-clock-o text-muted" aria-hidden="true"></span> {!! $event->formatDate($event->datetimenews, $event->datetimenewsend) !!}
				@if ($event->isToday())
					@if ($event->isNow())
						<span class="badge badge-success">{{ trans('news::news.happening now') }}</span>
					@else
						<span class="badge badge-info">{{ trans('news::news.today') }}</span>
					@endif
				@elseif ($event->isTomorrow())
					<span class="badge badge-secondary">{{ trans('news::news.tomorrow') }}</span>
				@endif
			</li>

			@if ($event->location)
				<li><span class="fa fa-fw fa-map-marker" aria-hidden="true"></span> {{ $event->location }}</li>
			@endif

			<?php
			$resources = $event->resourceList()->get();
			if (count($resources) > 0):
				$resourceArray = array();
				foreach ($resources as $resource):
					$resourceArray[] = '<a href="' . route('site.news.type', ['name' => strtolower($resource->name)]) . '">' . $resource->name . '</a>';
				endforeach;

				echo '<li><span class="fa fa-fw fa-tags" aria-hidden="true"></span> ' .  implode(', ', $resourceArray) . '</li>';
			endif;

			if (auth()->user() && auth()->user()->can('manage news') && !empty($event->associations)):
				$users = array();
				foreach ($event->associations as $assoc):
					if ($assoc->associated):
						$users[] = $assoc->associated->name . ' (' . $assoc->associated->username . ')';
					endif;
				endforeach;

				if (!empty($users)):
					echo '<li><span class="fa fa-fw fa-user" aria-hidden="true"></span> ' . implode(', ', $users) . '</li>';
				endif;
			endif;

			if (!$event->template
			 && $event->hasEnd()
			 && $event->datetimenewsend > $now->format('Y-m-d h:i:s')):
				if ($type->calendar):
					?>
					<li>
					<span class="fa fa-fw fa-calendar" aria-hidden="true"></span>
					<a target="_blank" class="calendar calendar-subscribe" href="{{ $event->subscribeCalendarLink }}"><!--
						-->Subscribe<span class="sr-only"> to event #{{ $event->id }} at {!! $event->formatDate($event->datetimenews, $event->datetimenewsend) !!}</span><!--
					--></a>
					&nbsp;|&nbsp;
					<span class="fa fa-fw fa-download" aria-hidden="true"></span>
					<a target="_blank" class="calendar calendar-download" href="{{ $event->downloadCalendarLink }}"><!--
						-->Download<span class="sr-only"> event #{{ $event->id }} at {!! $event->formatDate($event->datetimenews, $event->datetimenewsend) !!}</span><!--
					--></a>
					</li>
					<?php
				endif;
			endif;
			?>
		</ul>

		@if ($event->url)
			@if ($attending || (auth()->user() && auth()->user()->can('manage news')))
				<p class="text-center"><a class="btn btn-success" href="{{ $event->visitableUrl }}">{{ trans('widget.coffeehours::coffeehours.join meeting') }}</a></p>
			@endif
		@endif

		<div class="sr-only">{!! $event->toHtml() !!}</div>

		<div class="dialog-footer newsattend">

				@if (auth()->user() && $ignore && in_array($ignore, auth()->user()->getAuthorisedRoles()))
					@if ($reserved)
						<div class="text-success">{{ trans('widget.coffeehours::coffeehours.reserved by', ['name' => $reserved]) }}</div>
					@else
						<div class="text-info">{{ trans('widget.coffeehours::coffeehours.not reserved') }}</div>
					@endif
				@else
					@if ($event->url && $reserved)
						@if (auth()->user() && auth()->user()->can('manage news'))
							<div class="text-success">{{ trans('widget.coffeehours::coffeehours.reserved by', ['name' => $reserved]) }}</div>
							@if ($comment)
								<blockquote>"{{ $comment }}"</blockquote>
							@endif

							@if ($claimed)
								<div class="text-info">{{ trans('widget.coffeehours::coffeehours.claimed by', ['name' => $claimed->associated ? $claimed->associated->name . ' (' . $claimed->associated->username . ')' : trans('global.unknown')]) }}</div>
							@endif

							@if ($event->datetimenews->timestamp <= $now->timestamp)
								<div class="text-center">
									<a href="{{ route('site.contactreports.index', ['from' => 'news:' . $event->id]) . '&add' }}" class="btn btn-primary">New Contact Report</a>
								</div>
							@endif
						@else
							<div class="text-success">This time is reserved.</div>
						@endif
					@elseif ($now->getTimestamp() < $endregistration->getTimestamp())
						@if (auth()->user())
							@if (!$attending && $canAttend)
								<div class="form-group" id="reserve-comment{{ $event->id }}">
									<label for="comment{{ $event->id }}">Please explain your issue to help the consultant prepare for the session: <span class="required">*</span></label>
									<textarea class="form-control" name="comment" id="comment{{ $event->id }}" required rows="2" cols="35"></textarea>
								</div>
								<div class="row">
									<div class="col-md-6 text-right">
										<div class="alert hide" data-success="You reserved this time." data-hide="#reserve-comment{{ $event->id }}" data-error="An error occurred. We were unable to reserve this time."></div>
									</div>
									<div class="col-md-6 text-right">
										<a class="btn-attend btn btn-primary" href="{{ route('page', ['uri' => 'coffee', 'attend' => 1]) }}" data-comment="#comment{{ $event->id }}" data-newsid="{{ $event->id }}" data-assoc="{{ auth()->user()->id }}">{{ $event->url ? 'Reserve this time' : 'I plan to attend' }}</a>
									</div>
								</div>
							@elseif (!$attending && !$canAttend)
								<div class="alert alert-warning">Reservations are limited to one per day. If you need more time, please contact support to schedule a consultation.</div>
							@else
								<div class="row">
									<div class="col-md-6">
										<div class="text-success">{{ $event->url ? 'You reserved this time.' : 'You will be attending.' }}
										</div>
									</div>
									<div class="col-md-6 text-right">
										<a class="btn-notattend btn btn-danger" href="{{ route('page', ['uri' => 'coffee', 'attend' => 0]) }}" data-id="{{ $attending }}">Cancel</a>
									</div>
								</div>
								@if ($comment)
									<blockquote>"{{ $comment }}"</blockquote>
								@endif
							@endif
						@else
							<div class="row">
								<div class="col-md-12 text-right">
									<a href="{{ route('login') }}?return=<?php echo base64_encode(route('page', ['uri' => 'coffee', 'attend' => 1, 'event' => $event->id])); ?>" data-newsid="{{ $event->id }}" data-assoc="0">Login</a> is required to reserve times.
								</div>
							</div>
						@endif
					@else
						<div class="alert alert-warning">Reservations are closed.</div>
					@endif
				@endif
			<?php /*@else
				@if (auth()->user())
					@if (!$attending)
						<div class="row">
							<div class="col-md-12 text-right">
								<a class="btn-attend btn btn-primary" href="{{ route('page', ['uri' => 'coffee', 'attend' => 1, 'event' => $event->id]) }}" data-newsid="{{ $event->id }}" data-assoc="{{ auth()->user()->id }}">I'm interested in attending</a>
							</div>
						</div>
					@else
						<div class="row">
							<div class="col-md-6">
								<div class="text-success">You expressed interest in attending.</div>
							</div>
							<div class="col-md-6 text-right">
								<a class="btn-notattend btn btn-danger" href="{{ route('page', ['uri' => 'coffee', 'attend' => 0, 'event' => $event->id]) }}" data-id="{{ $attending }}">Cancel reservation</a>
							</div>
						</div>
					@endif
				@else
					<div class="row">
						<div class="col-md-12 text-right">
							<a href="/login?return=<?php echo base64_encode(route('page', ['uri' => 'coffee', 'attend' => 1, 'event' => $event->id])); ?>" data-newsid="{{ $event->id }}" data-assoc="0">Login</a> is required to reserve times.
						</div>
					</div>
				@endif*/ ?>
		</div>
		@if (auth()->user() && auth()->user()->can('edit news'))
			<div class="row border-top mt-3 pt-3">
				<div class="col-md-6">
					ID #{{ $event->id }}
				</div>
				<div class="col-md-6 text-right">
					<a class="edit tip" href="{{ route('site.news.manage', ['id' => $event->id]) }}&edit" title="{{ trans('global.edit') }}"><!--
						--><span class="fa fa-fw fa-pencil" aria-hidden="true"></span><!--
						--><span class="sr-only">{{ trans('global.button.edit') }}</span><!--
					--></a>
					<a href="#coffee{{ $event->id }}" class="delete-news tip text-danger ml-3" data-confirm="Are you sure you want to delete this event?" data-api="{{ route('api.news.delete', ['id' => $event->id]) }}" title="{{ trans('global.button.delete') }}"><!--
						--><span class="fa fa-fw fa-trash" aria-hidden="true"></span><!--
						--><span class="sr-only">{{ trans('global.button.delete') }}</span><!--
					--></a>
				</div>
			</div>
		@endif
	</section>
	<?php
endforeach;
?>

@if (count($attend))
	@foreach ($attend as $event)
		<div class="alert alert-success">
			<a class="btn-notattend float-right btn btn-sm btn-danger" href="{{ route('page', ['uri' => 'coffee', 'attend' => 0]) }}" data-id="{{ $event->attending }}" title="Cancel reservation">Cancel</a>

			You have the following time slot reserved.<br />
			{!! $event->formatDate($event->datetimenews, $event->datetimenewsend) !!}

			@if ($event->isToday())
				@if ($event->isNow())
					<span class="badge badge-success">{{ trans('news::news.happening now') }}</span>
				@else
					<span class="badge badge-info">{{ trans('news::news.today') }}</span>
				@endif
			@elseif ($event->isTomorrow())
				<span class="badge badge-secondary">{{ trans('news::news.tomorrow') }}</span>
			@endif
		</div>
	@endforeach
@endif

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
			//initialDate: '<?php echo $week_start->format('Y-m-d'); ?>',
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

		@if (auth()->user() && auth()->user()->can('edit news'))
		document.querySelectorAll('.delete-news').forEach(function (el){
			el.addEventListener('click', function (e) {
				e.preventDefault();

				if (confirm(this.getAttribute('data-confirm'))) {
					fetch(this.getAttribute('data-api'), {
						method: 'DELETE',
						headers: {
							'Content-Type': 'application/json',
							'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
						}
					})
					.then(function (response) {
						if (response.ok) {
							window.location.reload(true);
							return;
						}

						return response.json().then(function (data) {
							var msg = data.message;
							if (typeof msg === 'object') {
								msg = Object.values(msg).join('<br />');
							}
							throw msg;
						});
					})
					.catch(function (error) {
						alert(error);
					});
				}
			});
		});
		@endif
	});
</script>
