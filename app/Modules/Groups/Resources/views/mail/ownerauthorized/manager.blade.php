@component('mail::message')
Hello {{ $user->name }},

The following people have been granted manager privileges on {{ config('app.name') }} queues and resources that you manage for {{ $group->name }}.

@foreach ($people as $person)
* {{ $person->name }} ({{ $person->email }})
@endforeach

If any of these are incorrect you can make changes at any time on the [Queue Management website]({{ route('site.users.account') }}). These persons now have access to the {{ config('app.name') }} Group and Queue Management web application. They are able to grant and remove access to any of your queues, grant manager privileges, use the Usage Reporting tool, and use any of the other functions of the web application. They will also be granted accounts on all the queues and resources you manage.

Please note that you and the other managers of this group will receive email notifications when access to a queue is granted or removed by this person.
@endcomponent