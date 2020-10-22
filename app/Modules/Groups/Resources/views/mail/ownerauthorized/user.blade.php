@component('mail::message')
Hello {{ $user->name }},

You have been granted manager privileges on {{ config('app.name') }} resources. You are now able to manage the following queues for {{ $group->name }}.

@foreach ($group->queues as $queue)
* {{ $queue->resource ? $queue->resource->name : trans('global.unknown') . ' (' . $queue->subresourceid . ')' }}: '{{ $queue->name }}'
@endforeach

You have access to the {{ config('app.name') }} Group and Queue Management web application. From this web site you are be able to grant access to people on the above queues. You also have access to the Usage Reporting tool where you may view usage on these queues by people in the group. Access will be granted to all of the above queues and resources. New accounts on systems are generally ready by the next day, if you do not already have an account there. This web application can be accessed at the following address.

{{ route('site.users.account') }}

Please note that you and the other managers of this group will receive email notifications when access to a queue is granted or removed.
@endcomponent