@component('mail::message')

Scheduling has been <span style="color:green;">restarted</span> on:

@foreach ($started as $resource)
* {{ $resource->name }}
@endforeach

@if (!count($stopped))
Scheduling on all queues has <span style="color:green;">resumed</span>.
@else
Scheduling on these clusters remains **<span style="color:red;">STOPPED</span>**:

@foreach ($stopped as $resource)
* {{ $resource->name }}
@endforeach
@endif

See current scheduling status at:

http://www.rcac.purdue.edu/admin/scheduling/
@endcomponent