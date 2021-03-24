@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/knowledge/js/admin.js?v=' . filemtime(public_path() . '/modules/knowledge/js/admin.js')) }}"></script>
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
{!! config('knowledge.name') !!}: {{ trans('knowledge::knowledge.snippets') }}
@stop

@section('content')

@component('knowledge::admin.submenu')
	snippets
@endcomponent

<form action="{{ route('admin.knowledge.snippets') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-3">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col-md-9 text-right">
				<label class="sr-only" for="filter_parent">{{ trans('knowledge::knowledge.parent') }}:</label>
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

	<div class="card mb-4">
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
				<th scope="col" class="priority-2">
					{!! Html::grid('sort', trans('knowledge::knowledge.last update'), 'updated_at', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{{ trans('knowledge::knowledge.ordering') }}
				</th>
				<th scope="col" class="text-right">
					{{ trans('knowledge::knowledge.used') }}
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
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"><span class="sr-only">{{ trans('global.admin.record id', ['id' => $row->id]) }}</span></label></span>
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					{!! str_repeat('<span class="gi">|&mdash;</span>', $row->level - 1) !!}
					@if (auth()->user()->can('edit knowledge'))
						<a href="{{ route('admin.knowledge.snippets.edit', ['id' => $row->id]) }}">
							{{ Illuminate\Support\Str::limit($row->title, 70) }}
						</a>
					@else
						<span>
							{{ Illuminate\Support\Str::limit($row->title, 70) }}
						</span>
					@endif
				</td>
				<td>
					{{ $row->path }}
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
				<td class="text-center">
					<?php $orderkey = array_search($row->id, $ordering[$row->parent_id]); ?>
					<?php if (auth()->user()->can('edit knowledge')): ?>
						<span class="glyph">{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, isset($ordering[$row->parent_id][$orderkey - 1]), route('admin.knowledge.snippets.orderup', ['id' => $row->id])) !!}</span>
						<span class="glyph">{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), isset($ordering[$row->parent_id][$orderkey + 1]), route('admin.knowledge.snippets.orderdown', ['id' => $row->id])) !!}</span>
						<?php $originalOrders[] = $orderkey + 1; ?>
					<?php else : ?>
						<?php echo $orderkey + 1;?>
					<?php endif; ?>
				</td>
				<td class="text-right">
					{{ $row->used }}
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
<?php /*
<form action="{{ route('admin.knowledge.snippets.attach') }}" method="post" name="adminForm" id="adminForm" class="form-inline">
	<div class="row">
		<div class="col-md-12">
			<div class="form-group">
				<label for="field-parent_id">{{ trans('knowledge::knowledge.parent') }}:</label>
				<select name="parent_id" id="field-parent_id" class="form-control searchable-select">
					<option value="1">{{ trans('global.none') }}</option>
					<?php foreach ($tree as $pa): ?>
						<option value="{{ $pa->id }}" data-path="/{{ $pa->path }}"><?php echo str_repeat('|&mdash; ', $pa->level) . e(Illuminate\Support\Str::limit($pa->title, 70)); ?> (/{{ $pa->path }})</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="form-group">
				<label for="field-parent_id">{{ trans('knowledge::knowledge.parent') }}:</label>
				<select name="page_id" id="field-parent_id" class="form-control searchable-select">
					<!--<option value="0">{{ trans('global.none') }}</option>-->
					<?php foreach ($pages as $pa): ?>
						<option value="{{ $pa->id }}" data-path="/{{ $pa->path }}">{{ $pa->alias }}: ({{ $pa->id }}) <?php echo str_repeat('|&mdash; ', $pa->level) . e(Illuminate\Support\Str::limit($pa->title, 70)); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>

	<input type="submit" class="btn btn-success" value="{{ trans('global.save') }}" />

	@csrf
</form>*/ ?>
@stop