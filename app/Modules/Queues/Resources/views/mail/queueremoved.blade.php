@component('mail::message')
Hello {$student->name},

You have been **removed** from the following queues and Unix groups.

* {$group}

@endcomponent