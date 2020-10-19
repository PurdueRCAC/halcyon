@component('mail::message')
Hello {{ $user->name }},

Access to your ITaP Research Computing resources has been **removed** for the following people, resources and queues.

@foreach ($removals as $userid => $data)
---

{{ $data['user']->name }} ({{ $data['user']->email }})

@foreach ($data['queueusers'] as $queueuser)
* {{ $queueuser->subresource->resource->name }}: '{{ $queueuser->queue->name }}' queue - _removed by {{ $queueuser->log->user->name }}_
@endforeach
@endforeach

---

If any of these are incorrect you can make changes at any time on the [Queue Management website]({{ route('site.users.account') }}). Account removals are completed during overnight processing. If a person's access to all {{ config('app.name') }} resources is removed they will be able to access their home directory files and Fortress files for as long as they have a current Purdue Career Account.

@endcomponent