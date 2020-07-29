@component('mail::message')
Hello {$student->name},

Your request for access to ITaP Research Computing resources under the following research groups has been **denied**.

* {$group}

@endcomponent