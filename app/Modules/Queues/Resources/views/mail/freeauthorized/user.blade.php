@component('mail::message')
Hello {{ $user->name }},

You have been granted access to {{ config('app.name') }} resources.

@if (count($roles))
---

You have been granted **access** to the following job submission queues, Unix groups, and other resources.

@foreach ($roles as $resource)
* {{ $resource->name }}
@endforeach

These accounts will be created during overnight processing. Accounts are generally ready for use the morning of the next day ({{ Carbon\Carbon::now()->modify('+1 day')->format('F jS') }}) if requested by midnight. You will receive another notification with information about logging in and getting started once your account is ready for use.
@endif

---
You have been granted **access** to the following resources.

@foreach ($queueusers as $queueuser)
* {{ $queueuser->queue->resource->name }}: '{{ $queueuser->queue->name }}' queue
@endforeach
@endcomponent
