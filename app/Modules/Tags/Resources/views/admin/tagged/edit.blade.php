@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('tags::tags.module name'),
		route('admin.tags.index')
	)
	->append(
		trans('tags::tags.tagged')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit tags'))
		{!! Toolbar::save(route('admin.tags.tagged.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.tags.tagged.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('tags::tags.module name') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.tags.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend><span>{{ trans('global.details') }}</span></legend>

				<div class="form-group" data-hint="{{ trans('tags::tags.name hint') }}">
					<label for="field-name">{{ trans('tags::tags.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" size="30" maxlength="250" value="{{ $row->name }}" />
					<span class="hint form-text">{{ trans('tags::tags.name hint') }}</span>
				</div>

				<div class="form-group">
					<label for="field-slug">{{ trans('tags::tags.slug') }}:</label>
					<input type="text" disabled="disabled" class="form-control disabled" name="fields[slug]" id="field-slug" placeholder="{{ trans('tags::tags.slug placeholder') }}" maxlength="250" value="{{ $row->slug }}" />
				</div>

				<!-- <div class="form-group" data-hint="{{ trans('tags::tags.namespace hint') }}">
					<label for="field-namespace">{{ trans('tags::tags.namespace') }}:</label>
					<input type="text" name="fields[namespace]" id="field-namespace" maxlength="250" value="{{ $row->namespace }}" />
					<span class="hint form-text">{{ trans('tags::tags.namespace hint') }}</span>
				</div> -->

				<div class="form-group">
					<label for="field-description">{{ trans('tags::tags.description') }}:</label>
					<textarea name="fields[description]" id="field-description" class="minimal" rows="4" cols="50">{{ $row->description }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<table class="meta">
				<caption class="sr-only">Metadata</caption>
				<tbody>
					<tr>
						<th scope="row">{{ trans('tags::tags.id') }}:</th>
						<td>
							<?php if ($row->id): ?>
								{{ $row->id }}
							<?php else: ?>
								{{ trans('global.none') }}
							<?php endif; ?>
							<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
						</td>
					</tr>
					<tr>
						<th scope="row">{{ trans('tags::tags.created') }}:</th>
						<td>
							<?php if ($row->created): ?>
								{{ $row->created }}
							<?php else: ?>
								{{ trans('global.unknown') }}
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	@csrf
</form>
@stop