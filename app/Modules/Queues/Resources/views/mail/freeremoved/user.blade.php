@component('mail::message')
Hello {{ $user->name }},

You that you have been **removed** from the following queues.

@foreach ($queueusers as $queueuser)
* {{ $queueuser->subresource->resource->name }}: '{{ $queueuser->queue->name }}' queue
@endforeach

@if (count($existing))
Please note you **still have access** to the following queues and resources.

@foreach ($existing as $queueuser)
* {{ $queueuser->subresource->resource->name }}: '{{ $queueuser->queue->name }}' queue
@endforeach
@endif

@if (count($roleremovals))
Accounts on the following {{ config('app.name') }} resources will be removed during overnight processing.

@foreach ($roleremovals as $resource)
* {{ $resource->name }}
@endforeach

Instructions on retrieving data from your account will be sent when the account(s) are removed.
@endif

@endcomponent