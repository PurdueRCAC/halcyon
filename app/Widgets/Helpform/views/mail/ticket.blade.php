@component('mail::message')
{{ $resource }} Early User program application:

---

**Name**
> {{ $data['name'] }}

**Email**
> {{ $data['email'] }}

@include('widget.earlyuserform::mail.submitted', ['data' => $data])

@endcomponent