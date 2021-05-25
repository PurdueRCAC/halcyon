@component('mail::message')
Hello {{ $user->name }},

The following people have been **removed** as a manager of {{ config('app.name') }} queues and resources that you manage for {{ $group->name }}.

@foreach ($people as $person)
* {{ $person->user->name }} ({{ $person->user->username }}){{ $person->user->actor ? ' - _removed by ' . $person->user->actor->name . '_' : '' }}
@endforeach

If any of these are incorrect you can make changes at any time on the [Queue Management website]({{ route('site.users.account') }}). These persons no longer have access to the {{ config('app.name') }} Group and Queue Management web application. They will not be able to grant or remove access to any of your queues, grant manager privileges, use the Usage Reporting tool, or use any of the other functions of the web application. Any accounts granted to them will be removed unless they have explicit authorization for specific queues.
@endcomponent