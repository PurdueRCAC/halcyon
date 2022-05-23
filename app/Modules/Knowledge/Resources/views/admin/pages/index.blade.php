@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/knowledge/js/admin.js?v=' . filemtime(public_path() . '/modules/knowledge/js/admin.js')) }}"></script>
@endpush

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

	if (auth()->user()->can('edit.state menus')):
		Toolbar::publishList(route('admin.knowledge.publish'));
		Toolbar::unpublishList(route('admin.knowledge.unpublish'));
		Toolbar::spacer();
	endif;
	if (auth()->user()->can('delete knowledge')):
		Toolbar::deleteList(trans('knowledge::knowledge.verify delete'), route('admin.knowledge.delete'));
	endif;

	if (auth()->user()->can('create knowledge')):
		Toolbar::addNew(route('admin.knowledge.create'));
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
				<label class="sr-only" for="filter_level">{{ trans('knowledge::knowledge.depth') }}</label>
				<select name="level" id="filter_level" class="form-control filter filter-submit">
					<option value="0"<?php if ($filters['level'] == '0'): echo ' selected="selected"'; endif;?>>{{ trans('knowledge::knowledge.all levels') }}</option>
					<option value="1"<?php if ($filters['level'] == 1): echo ' selected="selected"'; endif;?>>1</option>
					<option value="2"<?php if ($filters['level'] == 2): echo ' selected="selected"'; endif;?>>2</option>
					<option value="3"<?php if ($filters['level'] == 3): echo ' selected="selected"'; endif;?>>3</option>
					<option value="4"<?php if ($filters['level'] == 4): echo ' selected="selected"'; endif;?>>4</option>
					<option value="5"<?php if ($filters['level'] == 5): echo ' selected="selected"'; endif;?>>5</option>
					<option value="6"<?php if ($filters['level'] == 6): echo ' selected="selected"'; endif;?>>6</option>
					<option value="7"<?php if ($filters['level'] == 7): echo ' selected="selected"'; endif;?>>7</option>
					<option value="8"<?php if ($filters['level'] == 8): echo ' selected="selected"'; endif;?>>8</option>
					<option value="9"<?php if ($filters['level'] == 9): echo ' selected="selected"'; endif;?>>9</option>
					<option value="10"<?php if ($filters['level'] == 10): echo ' selected="selected"'; endif;?>>10</option>
				</select>

				<label class="sr-only" for="filter_state">{{ trans('knowledge::knowledge.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('knowledge::knowledge.all states') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="archived"<?php if ($filters['state'] == 'archived'): echo ' selected="selected"'; endif;?>>&nbsp;|_&nbsp;{{ trans('knowledge::knowledge.archived') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter-access">{{ trans('knowledge::knowledge.access level') }}</label>
				<select name="access" id="filter-access" class="form-control filter filter-submit">
					<option value="">{{ trans('knowledge::knowledge.all access levels') }}</option>
					@foreach (App\Halcyon\Access\Viewlevel::all() as $access)
						<option value="<?php echo $access->id; ?>"<?php if ($filters['access'] == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
					@endforeach
				</select>

				<label class="sr-only" for="filter_parent">{{ trans('knowledge::knowledge.parent') }}</label>
				<select name="parent" id="filter_parent" class="form-control filter filter-submit searchable-select">
					<option value="0">{{ trans('knowledge::knowledge.all pages') }}</option>
					@foreach ($tree as $page)
						<?php $selected = ($page->assoc_id == $filters['parent'] ? ' selected="selected"' : ''); ?>
						<option value="{{ $page->assoc_id }}"<?php echo $selected; ?>><?php echo str_repeat('|&mdash; ', $page->level) . e(Illuminate\Support\Str::limit($page->title, 70)); ?></option>
					@endforeach
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{!! config('knowledge.name') !!}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete knowledge'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
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
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('knowledge::knowledge.last update'), 'updated_at', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('knowledge::knowledge.ordering'), 'lft', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$originalOrders = array();
		$parent_id = 0;
		?>
		@foreach ($rows as $i => $row)
			<?php $orderkey = array_search($row->id, $ordering[$row->parent_id]); ?>
			<tr>
				@if (auth()->user()->can('delete knowledge'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					{!! str_repeat('<span class="gi">|&mdash;</span>', $row->level) !!}
					@if (auth()->user()->can('edit knowledge'))
						<a href="{{ route('admin.knowledge.edit', ['id' => $row->id]) }}">
							@if ($row->isSeparator())
								<span class="unknown">{{ trans('knowledge::knowledge.type separator') }}</span>
							@endif
							{{ Illuminate\Support\Str::limit($row->title, 70) }}
						</a>
					@else
						<span>
							@if ($row->isSeparator())
								<span class="unknown">{{ trans('knowledge::knowledge.type separator') }}</span>
							@endif
							{{ Illuminate\Support\Str::limit($row->title, 70) }}
						</span>
					@endif
				</td>
				<td>
					@if ($row->isSeparator())
						<span class="unknown">&mdash;</span>
					@else
						/{{ trim($row->path, '/') }}
					@endif
				</td>
				<td>
					@if ($row->snippet)
						<span class="icon-repeat" data-tip="{{ trans('knowledge::knowledge.snippet') }}"><span class="sr-only">{{ trans('knowledge::knowledge.snippet') }}</span></span>
					@endif
				</td>
				<td class="priority-4">
					@if ($row->trashed())
						@if (auth()->user()->can('edit knowledge'))
							<a class="badge badge-secondary btn-state trashed" href="{{ route('admin.knowledge.restore', ['id' => $row->id]) }}" title="{{ trans('knowledge::knowledge.set state to', ['state' => trans('global.published')]) }}">
						@endif
							{{ trans('knowledge::knowledge.trashed') }}
						@if (auth()->user()->can('edit knowledge'))
							</a>
						@endif
					@elseif ($row->isPublished())
						@if (auth()->user()->can('edit knowledge'))
							<a class="badge badge-success btn-state" href="{{ route('admin.knowledge.unpublish', ['id' => $row->id]) }}" title="{{ trans('knowledge::knowledge.set state to', ['state' => trans('global.unpublished')]) }}">
						@endif
							{{ trans('knowledge::knowledge.published') }}
						@if (auth()->user()->can('edit knowledge'))
							</a>
						@endif
					@elseif ($row->isArchived())
						@if (auth()->user()->can('edit knowledge'))
							<a class="badge badge-warning btn-state" href="{{ route('admin.knowledge.publish', ['id' => $row->id]) }}" title="{{ trans('knowledge::knowledge.set state to', ['state' => trans('global.published')]) }}">
						@endif
							{{ trans('knowledge::knowledge.archived') }}
						@if (auth()->user()->can('edit knowledge'))
							</a>
						@endif
					@else
						@if (auth()->user()->can('edit knowledge'))
							<a class="badge badge-secondary btn-state" href="{{ route('admin.knowledge.publish', ['id' => $row->id]) }}" title="{{ trans('knowledge::knowledge.set state to', ['state' => trans('global.published')]) }}">
						@endif
							{{ trans('knowledge::knowledge.unpublished') }}
						@if (auth()->user()->can('edit knowledge'))
							</a>
						@endif
					@endif
				</td>
				<td class="priority-4">
					<span class="badge access {{ str_replace(' ', '', strtolower($row->viewlevel ? $row->viewlevel->title : '')) }}">{{ $row->viewlevel ? $row->viewlevel->title : trans('global.unknown') }}</span>
				</td>
				<td class="priority-6">
					<span class="datetime">
						@if ($row->updated_at)
							<time datetime="{{ Carbon\Carbon::parse($row->updated_at)->toDateTimeLocalString() }}">
								@if ($row->updated_at->timestamp > Carbon\Carbon::now()->timestamp)
									{{ $row->updated_at->diffForHumans() }}
								@else
									{{ $row->updated_at->format('Y-m-d') }}
								@endif
							</time>
						@else
							@if ($row->created_at)
								<time datetime="{{ Carbon\Carbon::parse($row->created_at)->toDateTimeLocalString() }}">
									@if ($row->created_at->timestamp > Carbon\Carbon::now()->timestamp)
										{{ $row->created_at->diffForHumans() }}
									@else
										{{ $row->created_at->format('Y-m-d') }}
									@endif
								</time>
							@else
								<span class="never">{{ trans('global.unknown') }}</span>
							@endif
						@endif
					</span>
				</td>
				<td class="priority-5 text-cente">
					{!! str_repeat('<span class="gi">|&mdash;</span>', $row->level) !!}
					<?php $orderkey = array_search($row->id, $ordering[$row->parent_id]); ?>
					<?php if (auth()->user()->can('edit knowledge')): ?>
						<span class="glyph">{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, isset($ordering[$row->parent_id][$orderkey - 1]), route('admin.knowledge.orderup', ['id' => $row->id])) !!}</span>
						<span class="glyph">{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), isset($ordering[$row->parent_id][$orderkey + 1]), route('admin.knowledge.orderdown', ['id' => $row->id])) !!}</span>
						<?php $originalOrders[] = $orderkey + 1; ?>
					<?php else : ?>
						<?php echo $orderkey + 1;?>
					<?php endif; ?>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
		</div>
	</div>

	{{ $rows->render() }}
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif

	<input type="hidden" name="boxchecked" value="0" />

	<div id="new-page" class="hide" title="{{ trans('knowledge::knowledge.choose type') }}">
		<h2 class="modal-title sr-only">{{ trans('knowledge::knowledge.choose type') }}</h2>

		<div class="row">
			<div class="col-md-4">
				<a href="{{ route('admin.knowledge.create') }}" class="form-group form-block">
					<span class="icon-edit" aria-hidden="true"></span>
					{{ trans('knowledge::knowledge.new page') }}
				</a>
			</div>
			<div class="col-md-4">
				<a href="{{ route('admin.knowledge.select') }}" class="form-group form-block">
					<span class="icon-repeat" aria-hidden="true"></span>
					{{ trans('knowledge::knowledge.snippet') }}
				</a>
			</div>
			<div class="col-md-4">
				<a href="{{ route('admin.knowledge.create', ['type' => 'separator']) }}" class="form-group form-block">
					<span class="icon-minus" aria-hidden="true"></span>
					{{ trans('knowledge::knowledge.separator') }}
				</a>
			</div>
		</div>
	</div>
	@csrf
</form>
@stop
