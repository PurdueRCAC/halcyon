@extends('layouts.master')

@section('scripts')
<script src="{{ asset('modules/knowledge/js/admin.js') }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('knowledge::knowledge.knowledge base'),
		route('admin.knowledge.index')
	)
	->append(
		trans('knowledge::knowledge.pages'),
		route('admin.knowledge.index')
	);

	if (auth()->user()->can('create knowledge')):
		Toolbar::addNew(route('admin.knowledge.create'));
	endif;

	if (auth()->user()->can('delete knowledge')):
		Toolbar::deleteList(trans('knowledge::knowledge.verify delete'), route('admin.knowledge.delete'));
	endif;

	if (auth()->user()->can('admin knowledge')):
		Toolbar::spacer();
		Toolbar::preferences('knowledge');
	endif;
@endphp

@section('toolbar')
	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('knowledge.name') !!}
@stop

@section('content')

@component('knowledge::admin.submenu')
	pages
@endcomponent

<form action="{{ route('admin.knowledge.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col-md-8 text-right">
				<label class="sr-only" for="filter_state">{{ trans('knowledge::knowledge.state') }}</label>
				<select name="state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('knowledge::knowledge.state_all') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter-access">{{ trans('knowledge::knowledge.access level') }}</label>
				<select name="access" id="filter-access" class="form-control filter filter-submit">
					<option value="">{{ trans('knowledge::knowledge.access select') }}</option>
					<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
						<option value="<?php echo $access->id; ?>"<?php if ($filters['access'] == $access->id) { echo ' selected="selected"'; } ?>><?php echo e($access->title); ?></option>
					<?php endforeach; ?>
				</select>

				<label class="sr-only" for="filter_parent">{{ trans('knowledge::knowledge.parent') }}:</label>
				<select name="parent" id="filter_parent" class="form-control filter filter-submit">
					<option value="0">{{ trans('knowledge::knowledge.all pages') }}</option>
					<?php foreach ($tree as $page): ?>
						<?php $selected = ($page->assoc_id == $filters['parent'] ? ' selected="selected"' : ''); ?>
						<option value="{{ $page->assoc_id }}"<?php echo $selected; ?>><?php echo str_repeat('|&mdash; ', $page->level) . e(Illuminate\Support\Str::limit($page->title, 70)); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{!! config('knowledge.name') !!}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('knowledge::knowledge.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('knowledge::knowledge.title'), 'title', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{{ trans('path') }}
				</th>
				<th scope="col">
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('knowledge::knowledge.state'), 'state', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('knowledge::knowledge.access'), 'access', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{!! Html::grid('sort', trans('knowledge::knowledge.last update'), 'updated_at', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit knowledge'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					{!! str_repeat('<span class="gi">|&mdash;</span>', $row->level) !!}
					@if (auth()->user()->can('edit knowledge'))
						<a href="{{ route('admin.knowledge.edit', ['id' => $row->id]) }}">
							{{ Illuminate\Support\Str::limit($row->title, 70) }}
						</a>
					@else
						<span>
							{{ Illuminate\Support\Str::limit($row->title, 70) }}
						</span>
					@endif
				</td>
				<td>
					/{{ trim($row->path, '/') }}
				</td>
				<td>
					@if ($row->snippet)
						<span class="icon-repeat"></span>
					@endif
				</td>
				<td>
					@if ($row->trashed())
						@if (auth()->user()->can('edit knowledge'))
							<a class="btn btn-secondary state trashed" href="{{ route('admin.knowledge.restore', ['id' => $row->id]) }}" title="{{ trans('knowledge::knowledge.set state to', ['state' => trans('global.published')]) }}">
						@endif
							{{ trans('knowledge::knowledge.trashed') }}
						@if (auth()->user()->can('edit knowledge'))
							</a>
						@endif
					@elseif ($row->state == 1)
						@if (auth()->user()->can('edit knowledge'))
							<a class="btn btn-secondary state published" href="{{ route('admin.knowledge.unpublish', ['id' => $row->id]) }}" title="{{ trans('knowledge::knowledge.set state to', ['state' => trans('global.unpublished')]) }}">
						@endif
							{{ trans('knowledge::knowledge.published') }}
						@if (auth()->user()->can('edit knowledge'))
							</a>
						@endif
					@else
						@if (auth()->user()->can('edit knowledge'))
							<a class="btn btn-secondary state unpublished" href="{{ route('admin.knowledge.publish', ['id' => $row->id]) }}" title="{{ trans('knowledge::knowledge.set state to', ['state' => trans('global.published')]) }}">
						@endif
							{{ trans('knowledge::knowledge.unpublished') }}
						@if (auth()->user()->can('edit knowledge'))
							</a>
						@endif
					@endif
				</td>
				<td>
					<span class="access {{ str_replace(' ', '', strtolower($row->viewlevel->title)) }}">{{ $row->viewlevel->title }}</span>
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->getOriginal('updated_at') && $row->getOriginal('updated_at') != '0000-00-00 00:00:00')
							<time datetime="{{ Carbon\Carbon::parse($row->updated_at)->format('Y-m-d\TH:i:s\Z') }}">{{ $row->updated_at }}</time>
						@else
							@if ($row->getOriginal('created_at') && $row->getOriginal('created_at') != '0000-00-00 00:00:00')
								<time datetime="{{ Carbon\Carbon::parse($row->created_at)->format('Y-m-d\TH:i:s\Z') }}">
									@if ($row->getOriginal('created_at') > Carbon\Carbon::now()->toDateTimeString())
										{{ $row->created_at->diffForHumans() }}
									@else
										{{ $row->created_at }}
									@endif
								</time>
							@else
								<span class="never">{{ trans('global.unknown') }}</span>
							@endif
						@endif
					</span>
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