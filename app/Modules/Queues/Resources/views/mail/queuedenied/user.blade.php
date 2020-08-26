@component('mail::message')
Hello {{ $user->name }},

Your request for access to ITaP Research Computing resources under the following research groups has been **denied**.

@foreach ($queueusers as $queueuser)
* {{ $queueuser->group->name }}
@endforeach

@endcomponent