@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('listeners::listeners.module name'),
		route('admin.listeners.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit listeners'))
		{!! Toolbar::save(route('admin.listeners.store')) !!}
	@endif
	{!! Toolbar::cancel(route('admin.listeners.cancel')) !!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('listeners.name') !!}: {{ $row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.listeners.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="row">
		<div class="col col-xs-12 col-sm-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

				<div class="row">
					<div class="col col-xs-12 col-sm-6">
						<div class="form-group">
							<?php echo $form->getLabel('folder'); ?>
							<?php echo $form->getInput('folder'); ?>
						</div>
					</div>
					<div class="col col-xs-12 col-sm-6">
						<div class="form-group">
							<?php echo $form->getLabel('element'); ?>
							<?php echo $form->getInput('element'); ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $form->getLabel('name'); ?>
					<?php echo $form->getInput('name'); ?>
				</div>

				<div class="form-group">
					<label>{{ trans('listeners::listeners.description') }}</label>
					<p>
					{!! trans(strtolower('listener.' . $row->folder . '.' . $row->element . '::' . $row->element . '.listener desc')) !!}
					</p>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="row">
					<div class="col col-xs-12 col-sm-6">
						<div class="form-group">
							<?php echo $form->getLabel('enabled'); ?>
							<?php echo $form->getInput('enabled'); ?>
						</div>
					</div>
					<div class="col col-xs-12 col-sm-6">
						<div class="form-group">
							<?php echo $form->getLabel('access'); ?>
							<?php echo $form->getInput('access'); ?>
						</div>
					</div>
				</div>

				<?php /*<div class="form-group">
					<?php echo $form->getLabel('ordering'); ?><br />
					<?php echo $form->getInput('ordering'); ?>
				</div>

				@if ($row->extension_id)
					<div class="form-group">
						<?php echo $form->getLabel('id'); ?><br />
						<?php echo $form->getInput('id'); ?>
					</div>
				@endif */ ?>
			</fieldset>
		</div>
		<div class="col col-xs-12 col-sm-5">
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
				<p class="alert alert-info">{{ trans('listeners::listeners.no params') }}</p>
				<?php
			endif;
			?>
		</div>
	</div>

	@csrf
</form>
@stop
