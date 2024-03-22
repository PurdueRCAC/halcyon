@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('themes::themes.module name'),
		route('admin.themes.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete themes'))
		{!! Toolbar::deleteList('', route('admin.themes.delete')) !!}
	@endif

	@if (auth()->user()->can('admin themes'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('themes')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('themes::themes.module name') }}
@stop

@section('content')
<form action="{{ route('admin.themes.store') }}" method="get" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4">
				<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
				<span class="input-group">
					<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
					<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
				</span>
			</div>
			<div class="col col-md-5">
			</div>
			<div class="col col-md-3">
				<label class="sr-only visually-hidden" for="filter_client_id">{{ trans('themes::themes.type') }}</label>
				<select name="client_id" id="filter_client_id" class="form-control filter filter-submit">
					<option value="*">{{ trans('themes::themes.all') }}</option>
					<option value="0"<?php if ($filters['client_id'] == '0'): echo ' selected="selected"'; endif;?>>{{ trans('themes::themes.site') }}</option>
					<option value="1"<?php if ($filters['client_id'] == '1'): echo ' selected="selected"'; endif;?>>{{ trans('themes::themes.admin') }}</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only visually-hidden" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
		<div class="card mb-4">
			<div class="table-responsive">
				<table class="table table-hover adminlist">
					<caption class="sr-only visually-hidden">{{ trans('themes::themes.themes') }}</caption>
					<thead>
						<tr>
							@if (auth()->user()->can('delete themes'))
								<th>
									{!! Html::grid('checkall') !!}
								</th>
							@endif
							<th scope="col" class="priority-5">
								{!! Html::grid('sort', trans('themes::themes.id'), 'id', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('themes::themes.title'), 'title', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('themes::themes.type'), 'client_id', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col" class="priority-4">
								{!! Html::grid('sort', trans('themes::themes.home'), 'home', $filters['order_dir'], $filters['order']) !!}
							</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$canEdit = auth()->user()->can('edit themes') || auth()->user()->can('edit.state themes');
					$canDelete = auth()->user()->can('delete themes');
					?>
					@foreach ($rows as $i => $row)
						<tr>
							@if ($canDelete)
								<td>
									{!! Html::grid('id', $i, $row->id) !!}
								</td>
							@endif
							<td class="priority-5">
								{{ $row->id }}
							</td>
							<td>
								@if ($canEdit)
									<a href="{{ route('admin.themes.edit', ['id' => $row->id]) }}">
										{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
									</a>
								@else
									{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
								@endif
								@if (!$row->path())
									<p class="text-warning">{{ trans('themes::themes.error.source path not found', ['path' => app_path('Themes/' . $row->element)]) }}</p>
								@endif
							</td>
							<td>
								@if ($canEdit)
									<a href="{{ route('admin.themes.edit', ['id' => $row->id]) }}">
										{{ $row->client_id ? trans('themes::themes.admin') : trans('themes::themes.site') }}
									</a>
								@else
									{{ $row->client_id ? trans('themes::themes.admin') : trans('themes::themes.site') }}
								@endif
							</td>
							<td class="priority-4">
								@if ($row->enabled)
									<span class="badge badge-success">
										{{ trans('global.yes') }}
									</span>
								@else
									<span class="badge badge-secondary">
										{{ trans('global.no') }}
									</span>
								@endif
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
		</div>

		{{ $rows->render() }}
	@else
		<div class="placeholder py-4 mx-auto text-center">
			<div class="placeholder-body p-4">
				<span class="fa fa-ban display-4 text-muted" aria-hidden="true"></span>
				<p>{{ trans('global.no results') }}</p>
			</div>
		</div>
	@endif

	<input type="hidden" name="boxchecked" value="0" />
</form>

@stop