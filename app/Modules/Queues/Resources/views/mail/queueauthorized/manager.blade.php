@component('mail::message')
Hello {{ $user->name }},

The following people have been granted access for the following ITaP Research Computing resources, queues, and Unix groups that you manage.

@foreach ($authorized as $userid => $data)
---

{{ $data['user']->name }} ({{ $data['user']->email }})

@foreach ($data['queueusers'] as $groupqueue)
* {{ $groupqueue->queue->resource->name }}: '{{ $groupqueue->queue->name }}' queue (account ready {{ $eta }})
@endforeach
@endforeach

---

@if ($itar)
If any of these are incorrect you can make changes at any time on the [Queue Management website](https://www.rcac.purdue.edu/account).

**Access to ITAR systems is only possible by public SSH key authentication. These persons will need to send a public SSH key to rcac-help@purdue.edu if they do not yet have access. Information regarding this was sent to these persons.**

Non-ITAR accounts are generally ready for use the morning of the next day ($tomorrow) if requested by midnight. ITAR accounts are generally ready for use one business day after a public key is received. If a person already had an account on the resources they will be able to access the queue(s) within a few minutes.

Please note that accounts on the Fortress (HPSS) Archival system have also been granted if these persons did not have an account previously (and will be processed overnight as well).

Persons being granted access will receive a similar notification and will also receive a notification once their accounts are ready for use.
@else
Check the [Group History](https://www.rcac.purdue.edu/account/history/) to see who made this change.

If any of these are incorrect you can make changes at any time on the [Queue Management website](https://www.rcac.purdue.edu/account). New accounts are generally ready for use the morning of the next day ($tomorrow) if requested by midnight. If a person already had an account on the resources they will be able to access the queue(s) within a few minutes.

Please note that accounts on the Fortress (HPSS) Archival system have also been granted if these persons did not have an account previously (and will be processed overnight as well).

Persons being granted access will receive a similar notification and will also receive a notification once their accounts are ready for use.
@endif

@endcomponent


header
newroleheader
newrolerow
newrolefooteritar
newrolefooterdata
newrolefooter
newqueue
newgrouprow
newqueuerowshort
newqueuerow
datainfo
footer