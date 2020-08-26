@component('mail::message')
Hello {{ $user->name }},

Access to your ITaP Research Computing resources (resources, queues, and Unix groups) has been removed for the following people.

@foreach ($removals as $userid => $data)
---

{{ $data['user']->name }} ({{ $data['user']->email }})

@foreach ($data['queueusers'] as $queueuser)
* {{ $queueuser->subresource->resource->name }}: '{{ $queueuser->queue->name }}' queue - _removed by {{ $queueuser->log->user }}_
@endforeach
@endforeach

---

Check the [Group History](https://www.rcac.purdue.edu/account/history/) to see who made this change.

If any of these are incorrect you can make changes at any time on the [Queue Management website](http://www.rcac.purdue.edu/account). Changes to your queue's authorized user list are effective within a few minutes, however, account removals (if appropriate) are completed during overnight processing. If a person's access to all ITaP Research Computing resources is removed they will be able to access their home directory files and Fortress files for as long as they have a current Purdue Career Account. Information regarding the access of these files will be sent to these persons once accounts are removed.

@endcomponent