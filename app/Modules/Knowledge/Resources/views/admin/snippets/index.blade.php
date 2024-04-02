@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/knowledge/js/admin.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('knowledge::knowledge.knowledge base'),
		route('admin.knowledge.index')
	)
	->append(
		trans('knowledge::knowledge.snippets'),
		route('admin.knowledge.snippets')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit.state menus'))
		{!! Toolbar::unpublishList(route('admin.knowledge.snippets.unpublish')) !!}
		{!! Toolbar::spacer() !!}
	@endif

	@if (auth()->user()->can('delete knowledge'))
		{!! Toolbar::deleteList('', route('admin.knowledge.snippets.delete')) !!}
	@endif

	@if (auth()->user()->can('create knowledge'))
		{!! Toolbar::addNew(route('admin.knowledge.snippets.create')) !!}
	@endif

	@if (auth()->user()->can('admin knowledge'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('knowledge')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('knowledge::knowledge.knowledge base') }}: {{ trans('knowledge::knowledge.snippets') }}
@stop

@section('content')

@component('knowledge::admin.submenu')
	snippets
@endcomponent

<form action="{{ route('admin.knowledge.snippets') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-3">
				<div class="form-group">
					<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" name="search" enterkeyhint="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
			</div>
			<div class="col-md-9 text-right">
				<label class="sr-only visually-hidden" for="filter_parent">{{ trans('knowledge::knowledge.parent') }}</label>
				<select name="parent" id="filter_parent" class="form-control filter filter-submit">
					<option value="0">{{ trans('knowledge::knowledge.all snippets') }}</option>
					<?php foreach ($tree as $page): ?>
						<?php $selected = ($page->assoc_id == $filters['parent'] ? ' selected="selected"' : ''); ?>
						<option value="{{ $page->assoc_id }}"<?php echo $selected; ?>><?php echo str_repeat('|&mdash; ', $page->level) . e(Illuminate\Support\Str::limit($page->title, 70)); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
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
					{{ trans('knowledge::knowledge.path') }}
				</th>
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('knowledge::knowledge.last update'), 'updated_at', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('knowledge::knowledge.ordering'), 'lft', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2 text-right">
					{{ trans('knowledge::knowledge.used') }}
				</th>
				<th scope="col">
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
					@php
					$title = Illuminate\Support\Str::limit($row->title, 70);
					$title = App\Halcyon\Utility\Str::highlight(e($title), $filters['search']);
					@endphp
					{!! str_repeat('<span class="gi">|&mdash;</span>', $row->level) !!}
					@if (auth()->user()->can('edit knowledge'))
						<a href="{{ route('admin.knowledge.snippets.edit', ['id' => $row->id]) }}">
							@if ($row->isSeparator())
								<span class="unknown">{{ trans('knowledge::knowledge.type separator') }}</span>
							@endif
							{!! $title !!}
						</a>
					@else
						<span>
							@if ($row->isSeparator())
								<span class="unknown">{{ trans('knowledge::knowledge.type separator') }}</span>
							@endif
							{!! $title !!}
						</span>
					@endif
				</td>
				<td>
					@if ($row->isSeparator())
						<span class="unknown">&mdash;</span>
					@else
						/{!! App\Halcyon\Utility\Str::highlight(e(trim($row->path, '/')), $filters['search']) !!}
					@endif
				</td>
				<td class="priority-6">
					<span class="datetime">
						@if ($row->updated_at)
							<time datetime="{{ Carbon\Carbon::parse($row->updated_at)->toDateTimeLocalString() }}">{{ $row->updated_at->format('Y-m-d') }}</time>
						@else
							@if ($row->created_at)
								<time datetime="{{ Carbon\Carbon::parse($row->created_at)->toDateTimeLocalString() }}">
									@if ($row->getOriginal('created_at') > Carbon\Carbon::now()->toDateTimeString())
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
				<td class="priority-5 text-centr">
					@if ($row->level > 1)
						{!! str_repeat('<span class="gi">|&mdash;</span>', $row->level - 1) !!}
						<?php $orderkey = array_search($row->id, $ordering[$row->parent_id]); ?>
						<?php if (auth()->user()->can('edit knowledge')): ?>
							{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, isset($ordering[$row->parent_id][$orderkey - 1]), route('admin.knowledge.snippets.orderup', ['id' => $row->id])) !!}
							{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), isset($ordering[$row->parent_id][$orderkey + 1]), route('admin.knowledge.snippets.orderdown', ['id' => $row->id])) !!}
							<?php $originalOrders[] = $orderkey + 1; ?>
						<?php else : ?>
							<?php echo $orderkey + 1; ?>
						<?php endif; ?>
					@endif
				</td>
				<td class="priority-2 text-right">
					{{ $row->used }}
				</td>
				<td>
					<a href="{{ route('admin.knowledge.snippets.copy', ['id' => $row->id]) }}" data-hint="{{ trans('knowledge::knowledge.copy') }}">
						<span class="fa fa-copy" aria-hidden="true"></span>
						<span class="sr-only visually-hidden">{{ trans('knowledge::knowledge.copy') }}</span>
					</a>
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
</form>
@stop
