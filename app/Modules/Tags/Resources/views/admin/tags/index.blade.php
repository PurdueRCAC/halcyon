@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('tags::tags.module name'),
		route('admin.tags.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete tags'))
		{!! Toolbar::deleteList('', route('admin.tags.delete')) !!}
	@endif

	@if (auth()->user()->can('create tags'))
		{!! Toolbar::addNew(route('admin.tags.create')) !!}
	@endif

	@if (auth()->user()->can('admin tags'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('tags')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('tags::tags.module name') }}
@stop

@section('content')
<form action="{{ route('admin.tags.index') }}" method="get" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="container-fluid mb-3">
		<div class="row">
			<div class="col-md-3 mb-2 filter-search">
				<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
				<span class="input-group">
					<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
					<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
				</span>
			</div>
			<div class="col">
			</div>
			<div class="col-md-4 col-xs-12 mb-2 filter-select text-right text-end">
				<label class="sr-only visually-hidden" for="filter-domain">{{ trans('pages::pages.state') }}</label>
				<select name="domain" class="form-control filter filter-submit" id="filter-domain">
					<option value=""<?php if ($filters['domain'] == ''): echo ' selected="selected"'; endif;?>>{{ trans('tags::tags.all domains') }}</option>
					@foreach ($domains as $domain)
						<option value="{{ $domain->domain }}"<?php if ($filters['domain'] == $domain->domain): echo ' selected="selected"'; endif;?>>{{ $domain->domain }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button type="submit" class="btn btn-secondary sr-only visually-hidden">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
		<?php
		$weekAgo = Carbon\Carbon::now()->modify('-1 week')->getTimestamp();
		$canDelete = auth()->user()->can('delete tags');
		$canEdit = auth()->user()->can('edit tags');
		?>
		<div class="card mb-4">
			<div class="table-responsive">
				<table class="table table-hover adminlist">
					<caption class="sr-only visually-hidden">{{ trans('tags::tags.tags') }}</caption>
					<thead>
						<tr>
							@if ($canDelete)
								<th>
									{!! Html::grid('checkall') !!}
								</th>
							@endif
							<th scope="col" class="priority-5">
								{!! Html::grid('sort', trans('tags::tags.id'), 'id', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col" class="priority-5">
								{!! Html::grid('sort', trans('tags::tags.domain'), 'domain', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('tags::tags.name'), 'name', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col" class="priority-2">
								{!! Html::grid('sort', trans('tags::tags.slug'), 'slug', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col" class="priority-3 text-right text-end">
								{!! Html::grid('sort', trans('tags::tags.tagged'), 'tagged_count', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col" class="priority-3 text-right text-end">
								{!! Html::grid('sort', trans('tags::tags.aliases'), 'alias_count', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col" class="priority-4 text-center">
								{!! Html::grid('sort', trans('tags::tags.created'), 'created', $filters['order_dir'], $filters['order']) !!}
							</th>
						</tr>
					</thead>
					<tbody>
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
								<td class="priority-5">
									{{ $row->domain }}
								</td>
								<td>
									@if ($canEdit)
										<a href="{{ route('admin.tags.edit', ['id' => $row->id]) }}">
											{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
										</a>
									@else
										{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
									@endif
								</td>
								<td class="priority-2">
									@if ($canEdit)
										<a href="{{ route('admin.tags.edit', ['id' => $row->id]) }}">
											{{ $row->slug }}
										</a>
									@else
										{{ $row->slug }}
									@endif
								</td>
								<td class="priority-3 text-right text-end">
									<a href="{{ route('admin.tags.tagged', ['tag_id' => $row->id]) }}">
										{{ number_format($row->tagged_count) }}
									</a>
								</td>
								<td class="priority-3 text-right text-end">
									{{ number_format($row->alias_count) }}
								</td>
								<td class="priority-4 text-center">
									<span class="datetime">
										@if ($row->created_at)
											<time datetime="{{ $row->created_at->toDateTimeLocalString() }}">
												@if ($row->created_at->getTimestamp() > $weekAgo)
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

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
@stop
