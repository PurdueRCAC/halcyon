@component('mail::message')

**{{ trans('widget.contactform::contactform.name') }}**
> {{ $name }}

**{{ trans('widget.contactform::contactform.email') }}**
> {{ $email }}

{{ $body }}

@endcomponent