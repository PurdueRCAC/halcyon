@extends('layouts.master')

@section('title'){{ $resource->type->name }}: {{ $resource->name }}@stop

@php
$content = '';
@endphp

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<h2>{{ $resource->name }}</h2>

	@if ($pic = $resource->picture)
		<div class="resource_pic">
			<img src="{{ $pic }}" alt="{{ $resource->name }}">
		</div>
	@endif

	@foreach ($sections as $section)
		@if (!$section['active'] && $section['content'])
			{!! $section['content'] !!}
		@endif
	@endforeach

	<ul class="nav">
		@foreach ($sections as $section)
			<?php
			$active = '';
			if ($section['active'])
			{
				$active = ' active';
				$content = $section['content'];
			}
			?>
			<li class="nav-item{!! $active !!}">
				<a class="nav-link{!! $active !!}" href="{{ $section['route'] }}">{!! $section['name'] !!}</a>
			</li>
		@endforeach
	</ul>

	<h2>{{ $type->name }} Resources</h2>
	<ul class="nav">
		@foreach ($rows as $i => $row)
			@php
			if (!$row->listname)
			{
				continue;
			}
			$active = '';
			if ($row->listname == $resource->listname)
			{
				$active = ' active';
			}
			@endphp
			<li class="nav-item{!! $active !!}">
				<a class="nav-link{!! $active !!}" href="{{ route('site.resources.' . $type->alias . '.show', ['name' => $row->listname]) }}">{{ $row->name }}</a>
				<?php /*if ($active)
					<ul>
						@foreach ($sections as $section)
							<?php
							$act = '';
							if ($section['active'])
							{
								$act = ' class="active"';
								$content = $section['content'];
							}
							?>
							<li{!! $act !!}>
								<a href="{{ $section['route'] }}">{!! $section['name'] !!}</a>
							</li>
						@endforeach
					</ul>
				@endif*/ ?>
			</li>
		@endforeach
		<li class="nav-item"><div class="separator"></div></li>
		<li class="nav-item<?php if ($resource->trashed()) { echo ' active'; } ?>">
			<a class="nav-link<?php if ($resource->trashed()) { echo ' active'; } ?>" href="{{ route('site.resources.' . $type->alias . '.retired') }}">{{ trans('resources::resources.retired') }}</a>
		</li>
	</ul>
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	@if (!$resource->trashed())
		@if ($resource->params->get('gateway') || $resource->params->get('desktop') || $resource->params->get('notebook') || $resource->params->get('rstudio'))
			<div class="launch">
				@if ($gateway = $resource->params->get('gateway'))
					<div class="panel">
						Gateway
						<a class="btn btn-launch" href="{{ $gateway }}" title="Launch OnDemand Gateway" target="_blank" rel="noopener">Launch</a>
					</div>
				@endif

				@if ($desktop = $resource->params->get('desktop'))
					<div class="panel">
						Remote Desktop
						<a class="btn btn-launch" href="{{ $desktop }}" title="Launch Remote Desktop" target="_blank" rel="noopener">Launch</a>
					</div>
				@endif

				@if ($notebook = $resource->params->get('notebook'))
					<div class="panel">
						Jupyter Hub
						<a class="btn btn-launch" href="{{ $notebook }}" title="Launch Jupyter Hub" target="_blank" rel="noopener">Launch</a>
					</div>
				@endif

				@if ($rstudio = $resource->params->get('rstudio'))
					<div class="panel">
						Rstudio
						<a class="btn btn-launch" href="{{ $rstudio }}" title="Launch Rstudio" target="_blank" rel="noopener">Launch</a>
					</div>
				@endif
			</div>
		@endif
	@else
		<div class="alert alert-info">
			This was retired on {{ $resource->datetimeremoved->format('M d, Y') }}
		</div>
	@endif

	@if ($content)
		{!! $content !!}
	@else
		<h2>{{ $resource->name }}</h2>
		<p>{{ $resource->description }}</p>
	@endif

	@if ($resource->trashed())
		<h3>Lifetime Service</h3>

		<table class="table table-bordered">
			<caption class="sr-only">Stats</caption>
			<tbody>
				<tr>
					<th scope="row">Installed</th>
					<td>{{ $resource->datetimecreated->format('Y-m-d') }}</td>
				</tr>
				<tr>
					<th scope="row">Retired</th>
					<td>{{ $resource->datetimeremoved->format('Y-m-d') }}</td>
				</tr>
				<?php /*<tr>
					<th scope="row">Groups</th>
					<td>
						<?php
						$g = (new App\Modules\Groups\Models\Group)->getTable();
						$q = (new App\Modules\Queues\Models\Queue)->getTable();
						$c = (new App\Modules\Resources\Models\Child)->getTable();

						$total = App\Modules\Groups\Models\Group::query()
							->select(Illuminate\Support\Facades\DB::raw('DISTINCT(' . $g . '.id)'))
							->join($q, $q . '.groupid', $g . '.id')
							->join($c, $c . '.subresourceid', $q . '.subresourceid')
							->whereNull($q . '.datetimeremoved')
							->where($c . '.resourceid', '=', $resource->id)
							->count();

						echo $total;
						?>
					</td>
				</tr>*/ ?>
			</tbody>
		</table>
	@endif
</div>
@stop