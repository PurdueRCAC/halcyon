@component('mail::message')
Hello {{ $user->name }},

Access to one or more your resources (resources, queues, and Unix groups) has been **<span style="color:red;">removed</span>** for the following people.

@foreach ($removals as $userid => $data)
---

{{ $data['user']->name }} ({{ $data['user']->email }})

@foreach ($data['queueusers'] as $queueuser)
@if ($queueuser->unixgroupid)
* Unix group: '{{ $queueuser->unixgroup->longname }}' {{ $queueuser->removedBy() ? '- _removed by ' . $queueuser->removedBy()->name . '_' : '' }}
@else
* {{ $queueuser->queue->resource->name }}: '{{ $queueuser->queue->name }}' queue {{ $queueuser->removedBy() ? '- _removed by ' . $queueuser->removedBy()->name . '_' : '' }}
@endif
@endforeach
@endforeach

---

Check the [Group History]({{ route('site.users.account.section', ['section' => 'history']) }}) for all access changes.

If any of these are incorrect you can make changes at any time on the [Queue Management website]({{ route('site.users.account') }}). Changes to your queue's authorized user list are effective within a few minutes, however, account removals (if appropriate) are completed during overnight processing. If a person's access to all {{ config('app.name') }} resources is removed they will be able to access their home directory files and long-term storage files for as long as they have an active account. Information regarding the access of these files will be sent to these persons once accounts are removed.

@endcomponent