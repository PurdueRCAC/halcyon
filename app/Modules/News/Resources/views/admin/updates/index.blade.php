@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/news/js/admin.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('news::news.module name'),
		route('admin.news.index')
	)
	->append(
		'Article # ' . $article->id,
		route('admin.news.index')
	)
	->append(
		trans('news::news.updates')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete news'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.news.updates.delete', ['article' => $article->id])) !!}
	@endif

	@if (auth()->user()->can('create news'))
		{!! Toolbar::addNew(route('admin.news.updates.create', ['article' => $article->id])) !!}
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
{{ trans('news::news.module name') }}
@stop

@section('content')

@component('news::admin.submenu')
	@if (request()->segment(3) == 'templates')
		templates
	@else
		articles
	@endif
@endcomponent

<form action="{{ route('admin.news.updates', ['article' => $article->id]) }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-3 filter-search">
				<div class="form-group">
					<label class="form-label sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<?php /*<div class="col-md-4 filter-select">
				<label class="sr-only visually-hidden" for="filter_state">{{ trans('news::news.state') }}</label>
				<select name="state" class="filter filter-submit form-control">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('news::news.state_all') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('news::news.published') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('news::news.trashed') }}</option>
				</select>
			</div>*/ ?>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only visually-hidden" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
		<div class="card mb-4">
			<div class="table-responsive">
				<table class="table table-hover adminlist">
					<caption>#{{ $article->id }} - {{ $article->headline }}</caption>
					<thead>
						<tr>
							@if (auth()->user()->can('edit news'))
								<th>
									<?php echo App\Halcyon\Html\Builder\Grid::checkall(); ?>
								</th>
							@endif
							<th scope="col">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.id'), 'id', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.body'), 'body', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.created'), 'datetimenews', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.creator'), 'userid', $filters['order_dir'], $filters['order']); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					@foreach ($rows as $i => $row)
						<tr>
							@if (auth()->user()->can('edit news'))
								<td>
									{!! Html::grid('id', $i, $row->id) !!}
								</td>
							@endif
							<td>
								{{ $row->id }}
							</td>
							<td>
								@if (auth()->user()->can('edit news'))
									<a href="{{ route('admin.news.updates.edit', ['article' => $article->id, 'id' => $row->id]) }}">
										{{ Illuminate\Support\Str::limit(strip_tags($row->body), 70) }}
									</a>
								@else
									<span>
										{{ Illuminate\Support\Str::limit(strip_tags($row->body), 70) }}
									</span>
								@endif
							</td>
							<td>
								<span class="datetime">
									@if ($row->datetimecreated)
										<time datetime="{{ $row->datetimecreated->toDateTimeLocalString() }}">{{ $row->datetimecreated }}</time>
									@else
										<span class="never">{{ trans('global.unknown') }}</span>
									@endif
								</span>
							</td>
							<td>
								{{ ($row->creator ? $row->creator->name : trans('global.unknown')) }}
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