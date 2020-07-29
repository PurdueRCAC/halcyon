@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/css/news.css') }}" />
@stop

@section('scripts')
<script src="{{ asset('modules/news/js/admin.js') }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('news::news.module name'),
		route('admin.news.index')
	);
if ($template)
{
	app('pathway')->append(trans('news::news.templates'));
}
else
{
	app('pathway')->append(trans('news::news.articles'));
}
@endphp

@section('toolbar')
	@if (auth()->user()->can('create news'))
		{!! Toolbar::addNew(route('admin.news.create')) !!}
	@endif

	@if (auth()->user()->can('delete news'))
		{!! Toolbar::deleteList('', route('admin.news.delete')) !!}
	@endif

	@if (auth()->user()->can('admin news'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('news')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('news.name') !!}{{ $template ? ': ' . trans('news::news.templates') : '' }}
@stop

@section('content')

@component('news::admin.submenu')
	@if (request()->segment(3) == 'templates')
		templates
	@else
		articles
	@endif
@endcomponent

<form action="{{ route('admin.news.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
				<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />

				<button class="btn btn-secondary" type="submit">{{ trans('search.submit') }}</button>
			</div>
			<div class="col col-md-8 filter-select text-right">
				<label class="sr-only" for="filter_state">{{ trans('news::news.state') }}</label>
				<select name="state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('news::news.state_all') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('news::news.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('news::news.unpublished') }}</option>
				</select>

				@if (!$template)
				<label class="sr-only" for="filter-access">{{ trans('news::news.access level') }}</label>
				<select name="access" id="filter-access" class="form-control filter filter-submit">
					<option value="*">{{ trans('news::news.select access') }}</option>
					<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
						<option value="<?php echo $access->id; ?>"<?php if ($filters['access'] == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
					<?php endforeach; ?>
				</select>
				@endif

				<label class="sr-only" for="filter-type">{{ trans('news::news.type') }}</label>
				<select name="type" id="filter-type" class="form-control filter filter-submit">
					<option value="0">{{ trans('news::news.select type') }}</option>
					<?php foreach ($types as $type): ?>
						<option value="<?php echo $type->id; ?>"<?php if ($filters['type'] == $type->id) { echo ' selected="selected"'; } ?>>{{ $type->name }}</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</fieldset>

	<table class="table table-hover adminlist">
		<thead>
			<tr>
				<th>
					<?php echo App\Halcyon\Html\Builder\Grid::checkall(); ?>
				</th>
				<th scope="col" class="priority-5">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.id'), 'id', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.headline'), 'headline', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.state'), 'state', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="priority-4">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.type'), 'newstypeid', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" colspan="2" class="priority-4">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.publish window'), 'datetimenews', $filters['order_dir'], $filters['order']); ?>
				</th>
				@if (!$template)
					<th scope="col" class="priority-4 text-right">{{ trans('news::news.updates') }}</th>
				@endif
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit news'))
						<a href="{{ route('admin.news.edit', ['id' => $row->id]) }}">
							{{ Illuminate\Support\Str::limit($row->headline, 70) }}
						</a>
					@else
						<span>
							{{ Illuminate\Support\Str::limit($row->headline, 70) }}
						</span>
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit.state news'))
						@if ($row->published)
							<a class="btn btn-sm state published" href="{{ route('admin.news.unpublish', ['id' => $row->id]) }}" title="{{ trans('news::news.unpublish') }}">
								{{ trans('global.published') }}
							</a>
						@else
							<a class="btn btn-sm state unpublished" href="{{ route('admin.news.publish', ['id' => $row->id]) }}" title="{{ trans('news::news.publish') }}">
								{{ trans('global.unpublished') }}
							</a>
						@endif
					@else
						@if ($row->published)
							<span class="badge state published">
								{{ trans('global.published') }}
							</span>
						@else
							<span class="badge state unpublished">
								{{ trans('global.unpublished') }}
							</span>
						@endif
					@endif
				</td>
				<td class="priority-4">
					{{ $row->type->name }}
				</td>
				<td class="priority-4 text-nowrap"><!--
					@if ($row->getOriginal('datetimecreated') && $row->getOriginal('datetimecreated') != '0000-00-00 00:00:00')
						<time datetime="{{ Carbon\Carbon::parse($row->datetimecreated)->format('Y-m-d\TH:i:s\Z') }}">
							@if ($row->getOriginal('datetimecreated') > Carbon\Carbon::now()->toDateTimeString())
								{{ $row->datetimecreated->diffForHumans() }}
							@else
								{{ $row->datetimecreated }}
							@endif
						</time>
					@else
						<span class="never">{{ trans('global.unknown') }}</span>
					@endif
-->
					@if ($row->getOriginal('datetimenews') && $row->getOriginal('datetimenews') != '0000-00-00 00:00:00')
						<time datetime="{{ $row->datetimenews->format('Y-m-d\TH:i:s\Z') }}">
							{{ $row->datetimenews->format('F j, Y g:ia') }}
						</time>
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="priority-4 text-nowrap">
					@if ($row->getOriginal('datetimenews') && $row->getOriginal('datetimenews') != '0000-00-00 00:00:00')
						@if ($row->getOriginal('datetimenewsend') && $row->getOriginal('datetimenewsend') != '0000-00-00 00:00:00' && $row->getOriginal('datetimenewsend') != '-0001-11-30 00:00:00')
							<time datetime="{{ $row->datetimenewsend->format('Y-m-d\TH:i:s\Z') }}">
								{{ $row->datetimenewsend->format('F j, Y g:ia') }}
							</time>
						@else
							<span class="never">{{ trans('global.never') }}</span>
						@endif
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				@if (!$template)
					<td class="priority-4 text-right">
						<a href="{{ route('admin.news.updates', ['article' => $row->id]) }}">
							{{ $row->updates_count }}
						</a>
					</td>
				@endif
			</tr>
		@endforeach
		</tbody>
	</table>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="order" value="{{ $filters['order'] }}" />
	<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

	@csrf
</form>

@stop