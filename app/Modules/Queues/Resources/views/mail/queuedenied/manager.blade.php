@component('mail::message')
Hello {{ $user->name }},

Requests for access to your {{ config('app.name') }} resources has been denied for the following people, resources and queues that you manage.

@foreach ($denials as $userid => $data)
---

{{ $data['user']->name }} ({{ $data['user']->email }})

@foreach ($data['queueusers'] as $queueuser)
* {{ $queueuser->queue->resource->name }}: '{{ $queueuser->queue->name }}' queue - _denied by {{ $queueuser->log ? $queueuser->log->user->name : trans('global.unknown') }}_
@endforeach
@endforeach

---

If any of these are incorrect you can make changes at any time on the [Queue Management website]({{ route('site.users.account') }}). No changes have been made to existing access. These persons will receive a similar email notification.

@endcomponent