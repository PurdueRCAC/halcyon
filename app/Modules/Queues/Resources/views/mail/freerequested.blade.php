@component('mail::message')
Hello {{ $user->name }},

The following people have been requested access for the following {{ config('app.name') }} resources.

@foreach ($requests as $userid => $data)
---

@php
$uuser = App\Modules\Users\Models\User::find($userid);
@endphp
{{ $uuser ? $uuser->name : $userid }} ({{ $uuser ? $uuser->username : trans('global.unknown') }}):

@foreach ($data as $userqueue)
@if ($userqueue->queue)
* {{ $userqueue->queue->resource->name }}: '{{ $userqueue->queue->name }}' queue
@if ($userqueue->request && $userqueue->request->comment)
    * Comment: {{ $userqueue->request->comment }}
@endif
@endif
@endforeach

@endforeach

---

These resources are available to anyone on campus with the approval of a faculty or staff member. These persons have listed you as their advisor or supervisor. Please approve or deny these requests on the [Queue Management website]({{ route('site.users.account') }}). Change the checked boxes to modify the request as you desire before pressing the green "approve" button. Once the request is approved the requestor will be notified via email. You may completely deny the request by pressing the red "deny" button.
@endcomponent