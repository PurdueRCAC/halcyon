@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/tags/admin.js') }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('tags::tags.module name'),
		route('admin.tags.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit tags'))
		{!! Toolbar::save(route('admin.tags.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.tags.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('tags::tags.module name') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.tags.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="row">
		<div class="col-md-6">
			<fieldset class="adminform">
				<legend><span>{{ trans('global.details') }}</span></legend>

				<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

				<div class="form-group">
					<label for="field-name">{{ trans('tags::tags.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" data-rel="#field-slug" class="form-control sluggable required" required maxlength="250" value="{{ $row->name }}" />
				</div>

				<div class="form-group">
					<label for="field-slug">{{ trans('tags::tags.slug') }}:</label>
					<input type="text" name="fields[slug]" id="field-slug" class="form-control" placeholder="{{ trans('tags::tags.slug placeholder') }}" pattern="[^a-zA-Z0-9]+" maxlength="250" value="{{ $row->slug }}" />
					<span class="hint form-text text-muted">{{ trans('tags::tags.slug hint') }}</span>
				</div>

				<div class="form-group">
					<label for="field-domain">{{ trans('tags::tags.domain') }}:</label>
					<input type="text" name="fields[domain]" id="field-domain" class="form-control" pattern="[^a-zA-Z0-9]+" maxlength="250" value="{{ $row->domain }}" />
					<span class="hint form-text text-muted">{{ trans('tags::tags.domain hint') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col-md-6">
			@if ($row->id)
				<fieldset class="adminform">
					<legend>{{ trans('tags::tags.alias') }}</legend>

					<table class="table table-hover">
						<caption class="sr-only visually-hidden">{{ trans('tags::tags.alias') }}</caption>
						<thead>
							<tr>
								<th scope="col">{{ trans('tags::tags.id') }}</th>
								<th scope="col">{{ trans('tags::tags.name') }}</th>
								<th scope="col">{{ trans('tags::tags.slug') }}</th>
								<th scope="col" class="text-right text-end"></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($row->aliases as $i => $u)
								<tr id="alias-{{ $u->id }}" data-id="{{ $u->id }}">
									<td>{{ $u->id }}</td>
									<td>{{ $u->name }}</td>
									<td>{{ $u->slug }}</td>
									<td class="text-right text-end">
										<a href="#alias-{{ $u->id }}" class="btn text-danger remove-alias"
											data-api="{{ route('api.tags.delete', ['id' => $u->id]) }}"
											data-confirm="{{ trans('global.confirm delete') }}"
											data-success="{{ trans('tags::tags.item removed') }}">
											<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('global.button.trash') }}</span>
										</a>
									</td>
								</tr>
							@endforeach
							<tr class="hidden" id="alias-{id}" data-id="{id}">
								<td>{id}</td>
								<td>{name}</td>
								<td>{slug}</td>
								<td class="text-right text-end">
									<a href="#alias-{id}" class="btn text-danger remove-alias"
										data-api="{{ route('api.tags.create') }}/{id}"
										data-confirm="{{ trans('global.confirm delete') }}"
										data-success="{{ trans('tags::tags.item removed') }}">
										<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('global.button.trash') }}</span>
									</a>
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td></td>
								<td colspan="2">
									<input type="text" name="name" id="name" class="form-control input-alias" placeholder="{{ trans('tags::tags.alias placeholder') }}" />
								</td>
								<td class="text-right text-end">
									<a href="#name" class="btn btn-success alias-add"
										data-id="{{ $row->id }}"
										data-api="{{ route('api.tags.create') }}"
										data-success="{{ trans('tags::tags.item added') }}">
										<span class="fa fa-plus" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('global.button.add') }}</span>
									</a>
								</td>
							</tr>
						</tfoot>
					</table>
					<p class="form-text text-muted">{{ trans('tags::tags.alias desc') }}</p>
				</fieldset>
			@endif
		</div>
	</div>

	@csrf
</form>
@stop
