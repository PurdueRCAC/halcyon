@component('mail::message')
Hello {{ $user->name }},

Requests for access to {{ config('app.name') }} resources has been **<span style="color:red;">denied</span>** for the following people, resources and queues.

@foreach ($denials as $userid => $data)
---

{{ $data['user']->name }} ({{ $data['user']->email }})

@foreach ($data['queueusers'] as $groupqueue)
* {{ $groupqueue->queue()->withTrashed()->first()->resource()->withTrashed()->first()->name }}: '{{ $groupqueue->queue()->withTrashed()->first()->name }}' queue - _denied by {{ $groupqueue->log ? $groupqueue->log->user->name : trans('global.unknown') }}_
@endforeach
@endforeach

---

If any of these are incorrect you can make changes at any time on the [Queue Management website]({{ route('site.users.account') }}). No changes have been made to existing access. These persons will receive a similar email notification.

@endcomponent