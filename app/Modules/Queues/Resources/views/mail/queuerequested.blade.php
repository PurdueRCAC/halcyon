@component('mail::message')
Hello {$manager->name},

The following people have been requested access for the following ITaP Research Computing resources and queues that you manage.

@foreach ($user_activity as $user_id => $userqueues)
---

{{ $userqueues[0]->user->name }} ({{ $userqueues[0]->user->email }}):

@foreach ($userqueues as $userqueue)
* {{ $$userqueue->queue->resource->name }}: '{{ $userqueue->queue->name }}' queue
@if ($userqueue->request->comment)
    * Comment: {{ $userqueue->request->comment }}
@endif
@endforeach

@endforeach

---

You may approve or deny these requests on the [Queue Management website](https://www.rcac.purdue.edu/account/user). You will be presented with a menu to approve or deny requests upon logging in. Once the request is approved the requestor will be notified via email. No changes to the person's access will be made if a request is denied.
@endcomponent