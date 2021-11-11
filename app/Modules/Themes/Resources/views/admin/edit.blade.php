@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('themes::themes.module name'),
		route('admin.themes.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@php
		if (auth()->user()->can('edit themes')):
			Toolbar::save(route('admin.themes.store'));
		endif;

		Toolbar::spacer();
		Toolbar::cancel(route('admin.themes.cancel'));
	@endphp

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('themes::themes.module name') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.themes.store') }}" method="post" name="adminForm" id="item-form" class="editform">
	<div class="row">
		<div class="col col-xs-12 col-sm-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				@if (auth()->user()->can('edit themes'))
					<div class="form-group">
						<label for="field-name">{{ trans('themes::themes.title') }}: <span class="required">{{ trans('global.required') }}</span></label>
						<input type="text" name="fields[name]" id="field-name" class="form-control required" required maxlength="250" value="{{ $row->name }}" />
					</div>
				@endif

				@if (auth()->user()->can('edit.state themes'))
					<div class="form-group">
						<fieldset id="fields_home" class="radio inputbox">
							<legend id="fields_home-lbl">{{ trans('themes::themes.home') }}:</legend>
							<ul>
								<li>
									<div class="form-check">
										<input class="form-check-input" type="radio" id="fields_enabled0" name="fields[enabled]" value="0" <?php if ($row->enabled == 0) { echo ' checked="checked"'; } ?> />
										<label class="form-check-label" for="fields_enabled0">{{ trans('global.no') }}</label>
									</div>
								</li>
								<li>
									<div class="form-check">
										<input class="form-check-input" type="radio" id="fields_enabled1" name="fields[enabled]" value="1" <?php if ($row->enabled == 1) { echo ' checked="checked"'; } ?> />
										<label class="form-check-label" for="fields_enabled1">{{ trans('global.yes') }}</label>
									</div>
								</li>
							</ul>
						</fieldset>
					</div>
				@endif
			</fieldset>
		</div>
		<div class="col span5 col-xs-12 col-sm-5">
			<?php
			$fieldSets = $form->getFieldsets('params');

			if (count($fieldSets)):
				?>
				<div class="accordion" id="parameters">
					<?php
					$i = 0;

					foreach ($fieldSets as $name => $fieldSet):
						$i++;
						$label = !empty($fieldSet->label) ? $fieldSet->label : 'widgets::widgets.' . $name . ' fieldset';
						?>
						<div class="card">
							<div class="card-header" id="{{ $name }}-heading">
								<h3 class="my-0 py-0">
									<a href="#{{ $name }}-options" class="btn btn-link btn-block text-left" data-toggle="collapse" data-target="#{{ $name }}-options" aria-expanded="true" aria-controls="{{ $name }}-options">
										<span class="fa fa-chevron-right" aria-hidden="true"></span>
										{{ trans($label) }}
									</a>
								</h3>
							</div>
							<div id="{{ $name }}-options" class="collapse{{ ($i == 1 ? ' show' : '') }}" aria-labelledby="{{ $name }}-heading" data-parent="#parameters">
								<fieldset class="card-body mb-0">
									@if (isset($fieldSet->description) && trim($fieldSet->description))
										<p class="tip">{{ trans($fieldSet->description) }}</p>
									@endif

									<?php
									$hidden_fields = '';

									foreach ($form->getFieldset($name) as $field):
										if (!$field->hidden):
											?>
											<div class="form-group">
												<?php echo $field->label; ?><br />
												<?php echo $field->input; ?>
												@if ($field->description)
													<span class="form-text text-muted">{{ trans($field->description) }}</span>
												@endif
											</div>
											<?php
										else:
											$hidden_fields .= $field->input;
										endif;
									endforeach;

									echo $hidden_fields;
									?>
								</fieldset>
							</div>
						</div>
						<?php
					endforeach;
					?>
				</div>
				<?php
			else:
				?>
				<p class="alert alert-info">{{ trans('themes::themes.no options') }}</p>
				<?php
			endif;
			?>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop
