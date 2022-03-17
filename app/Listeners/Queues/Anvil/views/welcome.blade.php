@php
$resources = array();
$frontends = array();
foreach ($activity as $resourceid => $data):
	$resources[] = $data->resource->name;
	$frontends[] = $data->resource->rolename . '.' . request()->getHttpHost();
endforeach;
@endphp

@component('mail::xsede')
Hello {{ $user->name }},

Welcome to your XSEDE allocation on Anvil! Your allocation details are as follows:

@foreach ($activity as $resourceid => $data)
<table>
	<caption>{{ $data->resource->name }}</caption>
	<tbody>
		<tr>
			<th scope="row"><strong>User guide</strong></th>
			<td><a href="{{ route('site.knowledge.page', ['uri' => ($data->resource->listname ? $data->resource->listname : $data->resource->rolename)]) }}">{{ route('site.knowledge.page', ['uri' => ($data->resource->listname ? $data->resource->listname : $data->resource->rolename)]) }}</a></td>
		</tr>
@php
$account = null;
if (count($data->queues)):
	foreach ($data->queues as $queue):
		if ($queue->cluster == 'gpu'):
			$account = substr($queue->name, 0, -4);
		else:
			$account = $queue->name;
		endif;
	endforeach;
endif;
@endphp
@if ($account)
		<tr>
			<th scope="row"><strong>Account</strong></th>
			<td>
				{{ $account }}
			</td>
		</tr>
@endif
@foreach ($data->queues as $i => $queue)
		<tr>
			<th scope="row">{!! ($i == 0 ? '<strong>Allocation</strong>' : '') !!}</th>
			<td>
				{{ ($queue->cluster == 'gpu' ? 'GPU' : 'CPU') }} - {{ $queue->serviceunits ? number_format($queue->serviceunits) . ' SUs ' : '' }}
			</td>
		</tr>
@endforeach
	</tbody>
</table>
@endforeach

Users may log in to Anvil through a variety of mechanisms listed [in the user guide](https://www.rcac.purdue.edu/knowledge/anvil/access/login), all of which require XSEDE credentials. If you are the PI on the allocation, you may add other users (postdoc, graduate student, etc.) to your allocation via the XSEDE portal. Once a user is added to your allocation, this request is typically processed overnight and they should have access within a day.

Documentation for Anvil can be found in the [user guide](https://www.rcac.purdue.edu/anvil#docs). All jobs submitted to Anvil will need to use the allocation ID above as described in the [examples](https://www.rcac.purdue.edu/knowledge/anvil/run/examples). Please note that jobs will be charged service units (SUs) depending on the queue the job is submitted to. Review the documentation on [accounting](https://www.rcac.purdue.edu/knowledge/anvil/run/accounting) for details. You may always see how many SUs your allocation has left via the `mybalance` command. Other helpful commands can be found in our [tips](https://www.rcac.purdue.edu/knowledge/anvil/policies/tips).

<div class="alert alert-info">
<h3>Need Help?</h3>

<p>Please use the XSEDE <a href="https://portal.xsede.org/help-desk">ticket system</a> to report issues or if you have any queries regarding system software.</p>
</div>
@endcomponent