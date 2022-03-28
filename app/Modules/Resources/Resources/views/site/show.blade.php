@extends('layouts.master')

@if ($resource->metadesc || $resource->metakey)
@section('meta')
	@if ($resource->metadesc)
		<meta name="description" content="{{ $resource->metadesc }}" />
	@endif
	@if ($resource->metakey)
		<meta name="keywords" content="{{ $resource->metakey }}" />
	@endif
@stop
@endif

@if ($resource->metadata)
	@foreach ($resource->metadata->all() as $k => $v)
		@if ($v)
			@if ($v == '__comment__')
				@push('meta')
		{!! $k !!}
@endpush
			@else
				@push('meta')
		{!! $v !!}
@endpush
			@endif
		@endif
	@endforeach
@endif

@section('title'){{ $resource->type->name }}: {{ $resource->name }}@stop

@php
$content = '';
@endphp

@section('content')
<div class="row">
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

	<ul class="nav flex-column">
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
	<ul class="nav flex-column">
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
		@if ($resource->hasFacet('gateway') || $resource->hasFacet('desktop') || $resource->hasFacet('notebook') || $resource->hasFacet('rstudio'))
			<div class="launch">
				@if ($gateway = $resource->getFacet('gateway'))
					<div class="panel">
						Gateway
						<a class="btn btn-launch" href="{{ $gateway->value }}" title="Launch OnDemand Gateway" target="_blank" rel="noopener">Launch</a>
					</div>
				@endif

				@if ($desktop = $resource->getFacet('desktop'))
					<div class="panel">
						Remote Desktop
						<a class="btn btn-launch" href="{{ $desktop->value }}" title="Launch Remote Desktop" target="_blank" rel="noopener">Launch</a>
					</div>
				@endif

				@if ($notebook = $resource->getFacet('notebook'))
					<div class="panel">
						Jupyter Hub
						<a class="btn btn-launch" href="{{ $notebook->value }}" title="Launch Jupyter Hub" target="_blank" rel="noopener">Launch</a>
					</div>
				@endif

				@if ($rstudio = $resource->getFacet('rstudio'))
					<div class="panel">
						Rstudio
						<a class="btn btn-launch" href="{{ $rstudio->value }}" title="Launch Rstudio" target="_blank" rel="noopener">Launch</a>
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
					<td>{{ $resource->datetimecreated ? $resource->datetimecreated->format('Y-m-d') : trans('global.unknown') }}</td>
				</tr>
				<tr>
					<th scope="row">Retired</th>
					<td>{{ $resource->datetimeremoved ? $resource->datetimeremoved->format('Y-m-d') : trans('global.unknown') }}</td>
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
</div>
@stop