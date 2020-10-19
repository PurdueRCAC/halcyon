@component('mail::message')
Hello {{ $user->name }},

Your request for access to {{ config('app.name') }} resources under the following research groups has been **denied**.

@foreach ($queueusers as $queueuser)
* {{ $queueuser->group->name }}
@endforeach

@endcomponent