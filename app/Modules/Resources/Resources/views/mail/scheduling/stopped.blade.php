@component('mail::message')
Scheduling has been **<span style="color:red;">STOPPED</span>** on:

@foreach ($stopped as $resource)
* {{ $resource->name }}
@endforeach

See current scheduling status at:

{{ route('admin.resources.index') }}
@endcomponent