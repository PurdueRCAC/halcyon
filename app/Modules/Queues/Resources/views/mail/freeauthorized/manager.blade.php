@component('mail::message')
Hello {{ $user->name }},

The following people have been granted access for the following {{ config('app.name') }} resources and queues.

@foreach ($authorized as $userid => $data)
---

{{ $data['user']->name }} ({{ $data['user']->email }})

@foreach ($data['queueusers'] as $groupqueue)
* {{ $groupqueue->queue->resource->name }}: '{{ $groupqueue->queue->name }}' queue (account ready {{ $groupqueue->eta }})
@endforeach
@endforeach

---

If any of these are incorrect you can make changes at any time on the [Queue Management website]({{ route('site.users.account') }}). New accounts are generally ready for use the morning of the next day ({{ Carbon\Carbon::now()->modify('+1 day')->format('F jS') }}) if requested by midnight. 

Persons being granted access will receive a similar notification and will also receive a notification once their accounts are ready for use.

@endcomponent
