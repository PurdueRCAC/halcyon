@component('mail::message')
Hello {{ $data['name'] }},

This is a confirmation of your application to the {{ $resource }} Early User program.

---

@include('widget.earlyuserform::mail.submitted', ['data' => $data])

---

If you have any questions about this process please contact {{ $destination }}
@endcomponent