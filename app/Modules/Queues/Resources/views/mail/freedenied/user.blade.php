@component('mail::message')
Hello {{ $user->name }},

Your request for access to {{ config('app.name') }} resources under the following research groups has been **<span style="color:red;">denied</span>**.

@foreach ($denials as $groupqueue)
* {{ $groupqueue->group->name }}
@endforeach

@endcomponent