@extends('layouts.master')

@php
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
<form action="{{ route('admin.tags.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.VALIDATION_FORM_FAILED') }}">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend><span>{{ trans('global.details') }}</span></legend>

				<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

				<div class="form-group" data-hint="{{ trans('tags::tags.name hint') }}">
					<label for="field-name">{{ trans('tags::tags.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" size="30" maxlength="250" value="{{ $row->name }}" />
					<span class="hint form-text">{{ trans('tags::tags.name hint') }}</span>
				</div>

				<div class="form-group">
					<label for="field-slug">{{ trans('tags::tags.slug') }}:</label>
					<input type="text" name="fields[slug]" id="field-slug" class="form-control" placeholder="{{ trans('tags::tags.slug placeholder') }}" maxlength="250" value="{{ $row->slug }}" />
				</div>

				<div class="form-group">
					<label for="field-description">{{ trans('tags::tags.description') }}:</label>
					<textarea name="fields[description]" id="field-description" class="form-control minimal" rows="4" cols="50">{{ $row->description }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<div class="data-wrap">
				<?php if ($row->id) { ?>
					<h4>{{ trans('tags::tags.history') }}</h4>
					<ul class="entry-log" id="entry-log">
						<?php
						foreach ($row->history()->limit(100)->get() as $log)
						{
							?>
							<li data-id="<?php echo $log->id; ?>">
								<span class="entry-log-data">
									<time><?php echo $log->created_at; ?></time>
									<?php echo $log->user->name; ?>
									<?php echo $log->action; ?>
								</span>
							</li>
							<?php
						}
						?>
					</ul>
				<?php } ?>
			</div>
		</div>
	</div>

	@csrf
</form>
@stop