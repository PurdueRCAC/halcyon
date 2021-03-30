@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ Module::asset('core:vendor/chartjs/Chart.css') . '?v=' . filemtime(public_path() . '/modules/core/vendor/chartjs/Chart.css') }}" />
<style>
.sparkline { 
  display: inline-block;
  height: 1em;
  background-color: rgba(255, 255, 255, 0.05);
  margin: 0;
  transition: all .5s ease;
}

.sparkline .index { 
	position: relative;
  float: left;
  width: 2px;
  height: 1em;
}

.sparkline .index .count { 
  display: block; 
  position: absolute; 
  bottom: 0; 
  left: 0; 
  width: 100%; 
  height: 0; 
  background: #AAA;
  font: 0/0 a;
  text-shadow: none;
  color: transparent;
}
.sparkline-chart {

}
</style>
@endpush

@push('scripts')
<script src="{{ Module::asset('core:vendor/chartjs/Chart.min.js') . '?v=' . filemtime(public_path() . '/modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<script src="{{ Module::asset('pages:js/pages.js') . '?v=' . filemtime(public_path() . '/modules/pages/js/pages.js') }}"></script>
<script>
jQuery(document).ready(function ($) {
	var el = $('.sparkline-chart');
	el.each(function(i, el){
		const ctx = el.getContext('2d');
		const chart = new Chart(ctx, {
		type: 'line',
		data: {
			labels: JSON.parse($(el).attr('data-labels')), //['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
			datasets: [
				{
					fill: false,
					data: JSON.parse($(el).attr('data-values'))//[435, 321, 532, 801, 1231, 1098, 732, 321, 451, 482, 513, 397]
				}
			]
		},
		options: {
			responsive: false,
			animation: {
				duration: 0
			},
			legend: {
				display: false
			},
			elements: {
				line: {
					borderColor: '#fff',
					borderWidth: 1
				},
				point: {
					radius: 0
				}
			},
			scales: {
				yAxes: [
					{
						display: false
					}
				],
				xAxes: [
					{
						display: false
					}
				]
			}
		}
		});
	});
});
</script>
@endpush

@php
app('pathway')
	->append(
		trans('pages::pages.module name'),
		route('admin.pages.index')
	);
/*		@if (auth()->user()->can('manage pages'))
			{!!
				Toolbar::checkin('admin.pages.checkin');
				Toolbar::spacer();
			!!}
		@endif*/
@endphp

@section('toolbar')
	@if ($filters['state'] == 'trashed')
		@if (auth()->user()->can('edit.state pages'))
			{!!
				Toolbar::publishList(route('admin.pages.restore'), 'Restore');
				Toolbar::custom(route('admin.pages.restore'), 'refresh', 'refresh', 'Restore', false);
				Toolbar::spacer();
			!!}
		@endif
		@if (auth()->user()->can('delete pages'))
			{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.pages.delete')) !!}
		@endif
	@else
		@if (auth()->user()->can('edit.state pages'))
			{!!
				Toolbar::publishList(route('admin.pages.publish'));
				Toolbar::unpublishList(route('admin.pages.unpublish'));
				Toolbar::spacer();
			!!}
		@endif
		@if (auth()->user()->can('delete pages'))
			{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.pages.delete')) !!}
		@endif
		@if (auth()->user()->can('create pages'))
			{!! Toolbar::addNew(route('admin.pages.create')) !!}
		@endif
		@if (auth()->user()->can('admin pages'))
			{!!
				Toolbar::spacer();
				Toolbar::preferences('pages')
			!!}
		@endif
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('pages.name') !!}
@stop

@section('content')
<form action="{{ route('admin.pages.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-8 filter-select text-right">
				<label class="sr-only" for="filter_state">{{ trans('pages::pages.state') }}</label>
				<select name="state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('pages::pages.state_all') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter-access">{{ trans('pages::pages.access level') }}</label>
				<select name="access" id="filter-access" class="form-control filter filter-submit">
					<option value="">{{ trans('pages::pages.access select') }}</option>
					@foreach (App\Halcyon\Access\Viewlevel::all() as $access)
						<option value="{{ $access->id }}"<?php if ($filters['access'] == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<thead>
			<tr>
				@if (auth()->user()->can('delete pages'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('pages::pages.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('pages::pages.title'), 'title', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('pages::pages.path'), 'path', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('pages::pages.state'), 'state', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('pages::pages.access'), 'access', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('pages::pages.updated'), 'updated_at', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					Visits
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr<?php if ($row->trashed()) { echo ' class="trashed"'; } ?>>
				@if (auth()->user()->can('delete pages'))
					<td>
						@if ($row->parent_id != 0)
							{!! Html::grid('id', $i, $row->id) !!}
						@endif
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					<?php echo str_repeat('<span class="gi">|&mdash;</span>', $row->level); ?>
					@if ($row->trashed())
						<span class="glyph icon-trash text-danger" aria-hidden="true"></span>
					@endif
					@if (auth()->user()->can('edit pages'))
						<a href="{{ route('admin.pages.edit', ['id' => $row->id]) }}">
							{{ $row->title }}
						</a>
					@else
						{{ $row->title }}
					@endif
				</td>
				<td>
					<a href="{{ route('admin.pages.edit', ['id' => $row->id]) }}">
						/{{ ltrim($row->path, '/') }}
					</a>
				</td>
				<td>
					@if ($row->isRoot())
						<span class="badge badge-success">
							{{ trans('pages::pages.published') }}
						</span>
					@else
						@if ($row->trashed())
							@if (auth()->user()->can('edit pages'))
								<a class="badge badge-secondary state trashed" href="{{ route('admin.pages.restore', ['id' => $row->id]) }}" data-tip="{{ trans('pages::pages.set state to', ['state' => trans('global.published')]) }}">
							@endif
								{{ trans('pages::pages.trashed') }}
							@if (auth()->user()->can('edit pages'))
								</a>
							@endif
						@elseif ($row->state == 1)
							@if (auth()->user()->can('edit pages'))
								<a class="badge badge-success" href="{{ route('admin.pages.unpublish', ['id' => $row->id]) }}" data-tip="{{ trans('pages::pages.set state to', ['state' => trans('global.unpublished')]) }}">
							@endif
								{{ trans('pages::pages.published') }}
							@if (auth()->user()->can('edit pages'))
								</a>
							@endif
						@else
							@if (auth()->user()->can('edit pages'))
								<a class="badge badge-secondary" href="{{ route('admin.pages.publish', ['id' => $row->id]) }}" data-tip="{{ trans('pages::pages.set state to', ['state' => trans('global.published')]) }}">
							@endif
								{{ trans('pages::pages.unpublished') }}
							@if (auth()->user()->can('edit pages'))
								</a>
							@endif
						@endif
					@endif
				</td>
				<td>
					<span class="badge access {{ str_replace(' ', '', strtolower($row->viewlevel->title)) }}">{{ $row->viewlevel->title }}</span>
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->updated_at)
							<time datetime="{{ $row->updated_at->format('Y-m-d\TH:i:s\Z') }}">
								@if ($row->updated_at->toDateTimeString() > Carbon\Carbon::now()->toDateTimeString())
									{{ $row->updated_at->diffForHumans() }}
								@else
									{{ $row->updated_at->format('Y-m-d') }}
								@endif
							</time>
						@elseif ($row->created_at)
							<time datetime="{{ Carbon\Carbon::parse($row->created_at)->format('Y-m-d\TH:i:s\Z') }}">
								@if ($row->created_at->toDateTimeString() > Carbon\Carbon::now()->toDateTimeString())
									{{ $row->created_at->diffForHumans() }}
								@else
									{{ $row->created_at->format('Y-m-d') }}
								@endif
							</time>
						@else
							<span class="never">{{ trans('global.unknown') }}</span>
						@endif
					</span>
				</td>
				<td>
					<?php
					$now = Carbon\Carbon::now();
					$visits = array();
					for ($d = 7; $d >= 0; $d--)
					{
						$yesterday = Carbon\Carbon::now()->modify('- ' . $d . ' days');
						$tomorrow  = Carbon\Carbon::now()->modify(($d ? '- ' . ($d - 1) : '+ 1') . ' days');

						$visits[$yesterday->format('Y-m-d')] = $row->logs()
							->where('datetime', '>', $yesterday->format('Y-m-d') . ' 00:00:00')
							->where('datetime', '<', $tomorrow->format('Y-m-d') . ' 00:00:00')
							->count();
					}
					//array_reverse($visits);
					//$visits = [435, 321, 532, 801, 1231, 1098, 732, 321, 451, 482, 513, 397];
					?>
					<canvas id="sparkline{{ $row->id }}" class="sparkline-chart" width="100" height="25" data-labels="{{ json_encode(array_keys($visits)) }}" data-values="{{ json_encode(array_values($visits)) }}">
						@foreach ($visits as $day => $val)
							{{ $day }}: $val<br />
						@endforeach
					<!-- <span class="sparkline">
							<span class="index"><span class="count" style="height: 27%;">60</span></span>
							<span class="index"><span class="count" style="height: 97%;">220</span></span>
							<span class="index"><span class="count" style="height: 62%;">140</span></span>
							<span class="index"><span class="count" style="height: 35%;">80</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 0%;">0</span></span>
							<span class="index"><span class="count" style="height: 5%;">5</span></span>
						</span> -->
					</canvas>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop