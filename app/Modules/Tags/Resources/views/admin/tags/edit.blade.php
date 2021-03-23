@extends('layouts.master')

@push('scripts')
<script>
jQuery(document).ready(function ($) {
	/*var alias = $('#field-slug');
	if (alias.length && !alias.val()) {
		$('#field-tag').on('keyup', function (e){
			var val = $(this).val();

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');

			alias.val(val);
		});
	}*/
	$('.sluggable').on('keyup', function (e){
		if ($(this).attr('data-rel')) {
			var alias = $($(this).attr('data-rel'));

			//if (alias.length && !alias.val()) {
				var val = $(this).val();

				val = val.toLowerCase()
					.replace(/\s+/g, '_')
					.replace(/[^a-z0-9_]+/g, '');

				alias.val(val);
			//}
		}
	});

	$('.alias-add').on('click', function(e){
		e.preventDefault();

		var name = $($(this).attr('href'));
		var btn = $(this);

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'post',
			data: {
				'parent_id' : btn.data('id'),
				'name' : name.val()
			},
			dataType: 'json',
			async: false,
			success: function(response) {
				Halcyon.message('success', 'Item added');

				var c = name.closest('table');
				var li = c.find('tr.hidden');

				if (typeof(li) !== 'undefined') {
					var template = $(li)
						.clone()
						.removeClass('hidden');

					template
						.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
						.data('id', response.id);

					template.find('a').each(function(i, el){
						$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.id));
					});

					var content = template
						.html()
						.replace(/\{id\}/g, response.id)
						.replace(/\{name\}/g, response.name)
						.replace(/\{slug\}/g, response.slug);

					template.html(content).insertBefore(li);
				}

				name.val('');
			},
			error: function(xhr, ajaxOptions, thrownError) {
				//console.log(xhr);
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	$('#main').on('click', '.remove-alias', function(e){
		e.preventDefault();

		var result = confirm($(this).data('confirm'));

		if (result) {
			var field = $($(this).attr('href'));

			// delete relationship
			$.ajax({
				url: $(this).data('api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function(data) {
					Halcyon.message('success', 'Item removed');
					field.remove();
				},
				error: function(xhr, ajaxOptions, thrownError) {
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		}
	});
});
</script>
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
{!! config('tags.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.tags.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="row">
		<div class="col-md-7">
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
			</fieldset>

		@if ($row->id)
			<fieldset class="adminform">
				<legend>{{ trans('tags::tags.alias') }}</legend>

					<table class="table table-hover">
						<caption class="sr-only">{{ trans('tags::tags.alias') }}</caption>
						<thead>
							<tr>
								<th scope="col">{{ trans('tags::tags.id') }}</th>
								<th scope="col">{{ trans('tags::tags.name') }}</th>
								<th scope="col">{{ trans('tags::tags.slug') }}</th>
								<th scope="col" class="text-right"></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($row->aliases as $i => $u)
								<tr id="alias-{{ $u->id }}" data-id="{{ $u->id }}">
									<td>{{ $u->id }}</td>
									<td>{{ $u->name }}</td>
									<td>{{ $u->slug }}</td>
									<td class="text-right">
										<a href="#alias-{{ $u->id }}" class="btn btn-secondary btn-danger remove-alias"
											data-api="{{ route('api.tags.delete', ['id' => $u->id]) }}"
											data-confirm="{{ trans('tags::tags.confirm delete') }}">
											<span class="icon-trash glyph">{{ trans('global.trash') }}</span>
										</a>
									</td>
								</tr>
							@endforeach
							<tr class="hidden" id="alias-{id}" data-id="{id}">
								<td>{id}</td>
								<td>{name}</td>
								<td>{slug}</td>
								<td class="text-right">
									<a href="#alias-{id}" class="btn btn-secondary btn-danger remove-alias"
										data-api="{{ route('api.tags.create') }}/{id}"
										data-confirm="{{ trans('tags::tags.confirm delete') }}">
										<span class="icon-trash glyph">{{ trans('global.trash') }}</span>
									</a>
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td></td>
								<td colspan="2">
									<input type="text" name="name" id="name" class="form-control input-alias" placeholder="{{ trans('tags::tags.name') }}" />
								</td>
								<td class="text-right">
									<a href="#name" class="btn btn-secondary btn-success alias-add"
										data-id="{{ $row->id }}"
										data-api="{{ route('api.tags.create') }}">
										<span class="icon-plus glyph">{{ trans('global.add') }}</span>
									</a>
								</td>
							</tr>
						</tfoot>
					</table>
			</fieldset>
		@endif
		</div>
		<div class="col-md-5">
			@include('history::admin.history')
		</div>
	</div>

	@csrf
</form>
@stop