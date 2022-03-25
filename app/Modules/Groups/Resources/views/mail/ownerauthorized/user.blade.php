@component('mail::message')
Hello {{ $user->name }},

You have been granted manager privileges on {{ config('app.name') }} resources. You are now able to manage the following for {{ $group->name }}.

**Queues:**

@foreach ($group->queues as $queue)
* {{ $queue->resource ? $queue->resource->name : trans('global.unknown') . ' (' . $queue->subresourceid . ')' }}: '{{ $queue->name }}'
@endforeach

**Unix groups:**

@foreach ($group->unixGroups as $unixgroup)
* {{ $unixgroup->longname }}
@endforeach

You have access to the {{ config('app.name') }} Group Management web application. From this web site you are be able to grant access to people on the above queues and unix groups. You also have access to the Usage Reporting tool where you may view usage on these queues by people in the group. Access will be granted to all of the above queues, unix groups, and resources. New accounts on systems are generally ready by the next day, if you do not already have an account there. This web application can be accessed at the following address.

[{{ route('site.users.account') }}]({{ route('site.users.account') }})

Please note that you and the other managers of this group will receive email notifications when access to a queue is granted or removed.
@endcomponent