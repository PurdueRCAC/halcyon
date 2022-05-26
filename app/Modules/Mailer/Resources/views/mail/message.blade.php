@component('mail::message', ($alert ? ['alert' => $alert] : []))
{!! $body !!}
@endcomponent