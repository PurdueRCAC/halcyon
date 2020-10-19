@component('mail::message')
Hello {{ $user->name }},

Your request for access to {{ config('app.name') }} resources under the following research groups has been **denied**.

@foreach ($denied as $groupqueue)
* {{ $groupqueue->group->name }}
@endforeach

If you have any questions about this process please contact rcac-help@purdue.edu.

@endcomponent