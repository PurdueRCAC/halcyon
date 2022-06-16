@component('mail::message')
{{ $name }},

{{ trans('widget.contactform::contactform.confirmation to', ['app' => config('app.name')]) }}

---

{{ $body }}

@endcomponent