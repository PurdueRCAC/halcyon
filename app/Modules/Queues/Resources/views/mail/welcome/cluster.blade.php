@php
$resources = array();
$frontends = array();
foreach ($activity as $resourceid => $data):
	$resources[] = $data->resource->name;
	$frontends[] = $data->resource->rolename . '.' . request()->getHttpHost();
endforeach;
@endphp

@component('mail::message')
Hello {{ $user->name }},

Your account on {{ implode(', ', $resources) }} has been created and are ready for use. Details about using these and other {{ config('app.name') }} resources are included below.

---

You can access the clusters through the front-ends with your institution credentials using [SSH]({{ route('site.knowledge.index') }}) or [Thinlinc]({{ route('site.knowledge.index') }}). You've been granted access to the following:

@php
$partner = false;
$scratch = false;
$standby = false;
@endphp
@foreach ($activity as $resourceid => $data)
<table>
	<caption>{{ $data->resource->name }}</caption>
	<tbody>
		<tr>
			<th scope="row"><strong>User guide</strong></th>
			<td><a href="{{ route('site.knowledge.page', ['uri' => ($data->resource->listname ? $data->resource->listname : $data->resource->rolename)]) }}">{{ route('site.knowledge.page', ['uri' => ($data->resource->listname ? $data->resource->listname : $data->resource->rolename)]) }}</a></td>
		</tr>
		<tr>
			<th scope="row"><strong>Front-end</strong></th>
			<td>{{ $data->resource->rolename }}.{{ str_replace('www.', '', request()->getHttpHost()) }}</td>
		</tr>
		<tr>
			<th scope="row"><strong>Home directory</strong></th>
			<td>{!! $data->resource->params->get('home') == 'shared' ? 'shared' : '<strong>specific</strong> to ' . $data->resource->name !!}, 25GB</td>
		</tr>
@if ($data->storage)
@php
	$scratch = true;
@endphp
		<tr>
			<th scope="row"><strong>Scratch space</strong></th>
			<td>{{ $data->storage->formattedDefaultquotaspace }} space; {{ number_format($data->storage->defaultquotafile) }} files</td>
		</tr>
@endif
@foreach ($data->queues as $i => $queue)
		<tr>
			<th scope="row">{!! ($i == 0 ? '<strong>Queues</strong>' : '') !!}</th>
			<td>{{ $queue->name }} - {{ $queue->totalcores ? $queue->totalcores . ' cores, ' : '' }}{{ $queue->humanWalltime }}</td>
		</tr>
@endforeach
@foreach ($data->standbys as $sb)
@php
if (preg_match("/^partner/", $sb->name)):
	$partner = true;
else:
	$standby = true;
endif;
@endphp
		<tr>
			<th></th>
			<td>{{ $sb->name }} - {{ $sb->humanWalltime }}</td>
		</tr>
@endforeach
	</tbody>
</table>
@endforeach

@if ($partner)
### Partner Queue

One of the above resources provides partners and their researchers who have purchased shared access to the cluster through a shared 'partner' queue. If your research group has purchased dedicated access, there will also be a queue named after that partner or research group on this resource.
@endif

@if ($standby)
### Standby Queue

You also have access to the "standby" queue. This queue utilizes idle cores from other queues. You can use this queue to run jobs of up to 4 hours. Wait times in standby will vary wildly (minutes to days) depending on cluster utilization and how many nodes your jobs request.
@endif

@if ($scratch)
### Scratch Space

Scratch space is available for storing large input and output data during computations. This space offers both a much larger quota and better performance than your home directory.

<p class="alert alert-warning">There is no backup service for scratch directories and files not accessed or modified in the <a href="{{ route('page', ['uri' => 'policies/scratchpurge']) }}">last 60 days will be removed</a>. Files in scratch directories are not recoverable if they are purged or accidentally deleted.</p>
@endif

### Archival Space

Long-term archival space is also offered via the Fortress HPSS Archival system. Fortress stores files on a tape library and uses a tape robot to retrieve and store these files upon request. Further information on using Fortress can be found in the [user guide]({{ route('site.knowledge.page', ['uri' => 'fortress']) }}).

----

Please also review the [acceptable use]({{ route('page', ['uri' => 'policies/resourceuse']) }}), [data]({{ route('page', ['uri' => 'policies/dataaccess']) }}), [scratch purge policies]({{ route('page', ['uri' => 'policies/scratchpurge']) }}), and [other policies]({{ route('page', ['uri' => 'policies']) }}).

<div class="alert alert-info">
<h3>Need Help?</h3>

<p>Informal, one-on-one help is available from {{ config('app.name') }} staff at <a href="{{ route('page', ['uri' => 'coffee']) }}">Coffee Break Consultations</a>. Check the schedule for available times.</p>
</div>
@endcomponent