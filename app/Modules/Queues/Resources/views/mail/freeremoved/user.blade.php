@component('mail::message')
Hello {{ $user->name }},

You that you have been **<span style="color:red;">removed</span>** from the following queues.

@foreach ($removedqueues as $queueuser)
* {{ $queueuser->queue->resource->name }}: '{{ $queueuser->queue->name }}' queue
@endforeach

@if (count($keptqueues))
Please note you **still have access** to the following queues and resources.

@foreach ($keptqueues as $queueuser)
* {{ $queueuser->queue->resource->name }}: '{{ $queueuser->queue->name }}' queue
@endforeach
@endif

@if (count($removedroles))
Accounts on the following {{ config('app.name') }} resources will be removed during overnight processing.

@foreach ($removedroles as $resource)
* {{ $resource->name }}
@endforeach

Instructions on retrieving data from your account will be sent when the account(s) are removed.
@endif

@endcomponent