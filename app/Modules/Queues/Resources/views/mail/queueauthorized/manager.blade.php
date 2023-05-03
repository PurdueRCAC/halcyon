@component('mail::message')
Hello {{ $user->name }},

The following people have been granted access for the following {{ config('app.name') }} resources, queues, and Unix groups that you manage.

@php
$itar = 0;
@endphp
@foreach ($authorized as $userid => $data)
---

{{ $data['user']->name }} ({{ $data['user']->email }})

@foreach ($data['queueusers'] as $groupqueue)
@php
if ($groupqueue->unixgroupid):
	$eta = 'within 4 hours';

	foreach ($data['roles'] as $resource):
		if ($resource->name == 'RCACIUSR' || $resource->name == 'exrc'):
			$itar = 1;
		endif;

		// If this is a Fortress-only addition, user won't be able to use it till tomorrow
		if ($resource->name == 'HPSSUSER'):
			$eta = 'tomorrow';
			break;
		endif;
	endforeach;
else:
	$eta = 'now';

	foreach ($data['roles'] as $resource):
		if ($resource->name == 'RCACIUSR' || $resource->name == 'exrc'):
			$itar = 1;
		endif;

		if ($groupqueue->queue->resource->name == $resource->name):
			$eta = 'tomorrow';
			break;
		endif;
	endforeach;
endif;
@endphp
@if ($groupqueue->unixgroupid)
* Unix group: '{{ $groupqueue->unixgroup->longname }}' (membership ready {{ $eta }})
@else
* {{ $groupqueue->queue->resource->name }}: '{{ $groupqueue->queue->name }}' queue (account ready {{ $eta }})
@endif
@endforeach
@endforeach

---

@if ($itar)
If any of these are incorrect you can make changes at any time on the [Queue Management website]({{ route('site.users.account') }}).

**Access to ITAR systems is only possible by public SSH key authentication. These persons will need to send a public SSH key to support if they do not yet have access. Information regarding this was sent to these persons.**

Non-ITAR accounts are generally ready for use the morning of the next day ({{ Carbon\Carbon::now()->modify('+1 day')->format('F jS') }}) if requested by midnight. ITAR accounts are generally ready for use one business day after a public key is received. If a person already had an account on the resources they will be able to access the queue(s) within a few minutes.

Please note that accounts on the Fortress (HPSS) Archival system have also been granted if these persons did not have an account previously (and will be processed overnight as well).

Persons being granted access will receive a similar notification and will also receive a notification once their accounts are ready for use.
@else
Check the [Group History]({{ route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $groupqueue->groupid, 'subsection' => 'history']) }}) to see who made this change.

If any of these are incorrect you can make changes at any time on the [Queue Management website]({{ route('site.users.account') }}). New accounts are generally ready for use the morning of the next day ({{ Carbon\Carbon::now()->modify('+1 day')->format('F jS') }}) if requested by midnight. If a person already had an account on the resources they will be able to access the queue(s) within a few minutes.

Please note that accounts on the Fortress (HPSS) Archival system have also been granted if these persons did not have an account previously (and will be processed overnight as well).

Persons being granted access will receive a similar notification and will also receive a notification once their accounts are ready for use.
@endif

@endcomponent
