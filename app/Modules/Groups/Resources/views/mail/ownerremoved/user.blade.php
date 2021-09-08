@component('mail::message')
Hello {{ $user->name }},

You have been removed as a manager of {{ config('app.name') }} resources. You are no longer able to manage queues or unix groups for {{ $group->name }}.

You no longer have access to the {{ config('app.name') }} Group Management web application. Any accounts granted will be removed unless you have explicit authorization for specific queues or unix groups.
@endcomponent