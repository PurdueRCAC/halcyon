@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ timestamped_asset('modules/core/vendor/chartjs/Chart.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/pages/js/pages.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('pages::pages.module name'),
		route('admin.pages.index')
	);
	/*if (auth()->user()->can('manage pages'))
		Toolbar::checkin('admin.pages.checkin');
		Toolbar::spacer();
	endif;*/
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
{{ trans('pages::pages.module name') }}
@stop

@section('content')
<form action="{{ route('admin.pages.index') }}" method="get" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<label class="form-label sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
				<span class="input-group">
					<input type="text" name="search" id="filter_search" enterkeyhint="search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
					<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
				</span>
			</div>
			<div class="col col-sm-12 col-md-4">
			</div>
			<div class="col filter-select text-right text-end">
				<label class="form-label sr-only visually-hidden" for="filter_state">{{ trans('pages::pages.state') }}</label>
				<select name="state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('pages::pages.state_all') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>
			</div>
			<div class="col filter-select text-right text-end">
				<label class="form-label sr-only visually-hidden" for="filter-access">{{ trans('pages::pages.access level') }}</label>
				<select name="access" id="filter-access" class="form-control filter filter-submit">
					<option value="0">{{ trans('pages::pages.access select') }}</option>
					@php
					$levels = auth()->user() ? auth()->user()->getAuthorisedViewLevels() : array(1);
					@endphp
					@foreach (App\Halcyon\Access\Viewlevel::all() as $access)
						@php
						if (!in_array($access->id, $levels)):
							continue;
						endif;
						@endphp
						<option value="{{ $access->id }}"<?php if ($filters['access'] == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
	</fieldset>

	@if (count($rows))
		<div class="card mb-4">
			<div class="table-responsive">
				<table class="table table-hover adminlist">
					<thead>
						<tr>
							@if (auth()->user()->can('delete pages'))
								<th>
									{!! Html::grid('checkall') !!}
								</th>
							@endif
							<th scope="col" class="priority-6">
								{!! Html::grid('sort', trans('pages::pages.id'), 'id', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('pages::pages.title'), 'title', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col" class="priority-4">
								{!! Html::grid('sort', trans('pages::pages.path'), 'path', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col" class="priority-3">
								{!! Html::grid('sort', trans('pages::pages.state'), 'state', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col" class="priority-3">
								{!! Html::grid('sort', trans('pages::pages.access'), 'access', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col" class="priority-6" colspan="2">
								{!! Html::grid('sort', trans('pages::pages.updated'), 'updated_at', $filters['order_dir'], $filters['order']) !!}
							</th>
							<?php /*<th scope="col" class="priority-6">
								7 Day Trend
							</th>*/ ?>
						</tr>
					</thead>
					<tbody>
					<?php
					$canDelete = auth()->user()->can('delete pages');
					$canEditState = auth()->user()->can('edit.state pages');
					$canEdit = auth()->user()->can('edit pages');
					?>
					@foreach ($rows as $i => $row)
						<tr<?php if ($row->trashed()) { echo ' class="trashed"'; } ?>>
							@if ($canDelete)
								<td>
									@if ($row->parent_id != 0)
										{!! Html::grid('id', $i, $row->id) !!}
									@endif
								</td>
							@endif
							<td class="priority-6">
								{{ $row->id }}
							</td>
							<td>
								<?php echo str_repeat('<span class="gi">|&mdash;</span>', $row->level); ?>
								@if ($row->trashed())
									<span class="fa fa-trash text-danger" aria-hidden="true"></span>
								@endif
								@if ($canEdit)
									<a href="{{ route('admin.pages.edit', ['id' => $row->id]) }}">
										{!! App\Halcyon\Utility\Str::highlight(e($row->title), $filters['search']) !!}
									</a>
								@else
									{!! App\Halcyon\Utility\Str::highlight(e($row->title), $filters['search']) !!}
								@endif
							</td>
							<td class="priority-4">
								<a href="{{ route('admin.pages.edit', ['id' => $row->id]) }}">
									/{!! App\Halcyon\Utility\Str::highlight(e(ltrim($row->path, '/')), $filters['search']) !!}
								</a>
							</td>
							<td class="priority-3">
								@if ($row->isRoot())
									<span class="badge badge-success">
										{{ trans('pages::pages.published') }}
									</span>
								@else
									@if ($row->trashed())
										@if ($canEditState)
											<a class="badge badge-secondary state trashed" href="{{ route('admin.pages.restore', ['id' => $row->id]) }}" data-tip="{{ trans('pages::pages.set state to', ['state' => trans('global.published')]) }}">
										@endif
											{{ trans('pages::pages.trashed') }}
										@if ($canEditState)
											</a>
										@endif
									@elseif ($row->state == 1)
										@if ($canEditState)
											<a class="badge badge-success" href="{{ route('admin.pages.unpublish', ['id' => $row->id]) }}" data-tip="{{ trans('pages::pages.set state to', ['state' => trans('global.unpublished')]) }}">
										@endif
											{{ trans('pages::pages.published') }}
										@if ($canEditState)
											</a>
										@endif
									@else
										@if ($canEditState)
											<a class="badge badge-secondary" href="{{ route('admin.pages.publish', ['id' => $row->id]) }}" data-tip="{{ trans('pages::pages.set state to', ['state' => trans('global.published')]) }}">
										@endif
											{{ trans('pages::pages.unpublished') }}
										@if ($canEditState)
											</a>
										@endif
									@endif
								@endif
							</td>
							<td class="priority-3">
								<span class="badge access {{ str_replace(' ', '', strtolower($row->viewlevel->title)) }}">{{ $row->viewlevel->title }}</span>
							</td>
							<td class="priority-6">
								@if ($row->updated_at)
									<time datetime="{{ $row->updated_at->toDateTimeLocalString() }}">
										{{ $row->updated_at->diffForHumans() }}
									</time>
								@elseif ($row->created_at)
									<time datetime="{{ $row->created_at->toDateTimeLocalString() }}">
										{{ $row->created_at->diffForHumans() }}
									</time>
								@else
									<span class="text-muted">{{ trans('global.unknown') }}</span>
								@endif
							</td>
							<td class="priority-6">
								<a href="{{ route('admin.pages.history', ['id' => $row->id]) }}" data-href="#history{{ $row->id }}">
									<span class="fa fa-clock-o" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('pages::pages.change history') }}</span>
								</a>
							</td>
							<?php /*<td class="priority-6">
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
								?>
								<canvas id="sparkline{{ $row->id }}" class="sparkline-chart" width="100" height="25" data-labels="{{ json_encode(array_keys($visits)) }}" data-values="{{ json_encode(array_values($visits)) }}">
									@foreach ($visits as $day => $val)
										{{ $day }}: $val<br />
									@endforeach
								</canvas>
							</td>*/ ?>
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