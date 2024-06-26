@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/widgets/js/admin.js') }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('widgets::widgets.module name'),
		route('admin.widgets.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);

if (auth()->user()->can('edit widgets')):
	Toolbar::save(route('admin.widgets.store'));
endif;

Toolbar::spacer();
Toolbar::cancel(route('admin.widgets.cancel', ['id' => $row->id]));
@endphp

@section('toolbar')
	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('widgets::widgets.module name') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.widgets.store') }}" method="post" name="adminForm" id="item-form" class="editform">
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
		<div class="col col-xs-12 col-sm-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<?php echo $form->getLabel('title'); ?>
					<?php echo $form->getInput('title'); ?>
				</div>

				<div class="form-group">
					<?php
					$field = $form->getField('position');
					echo $field->label;
					echo $field->input;
					?>
					@if ($field->description)
						<span class="form-text text-muted">{{ trans($field->description) }}</span>
					@endif
				</div>

				<div class="row">
					<div class="col col-xs-12 col-sm-6">
						<div class="form-group">
							<?php echo $form->getLabel('published'); ?>
							<?php echo $form->getInput('published'); ?>
						</div>
					</div>
					<div class="col col-xs-12 col-sm-6">
						<div class="form-group">
							<?php echo $form->getLabel('access'); ?>
							<?php echo $form->getInput('access'); ?>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col col-xs-12 col-sm-6">
						<div class="form-group">
							<?php
							$field = $form->getField('publish_up');
							echo $field->label;
							echo $field->input;
							?>
							@if ($field->description)
								<span class="form-text text-muted">{{ trans($field->description) }}</span>
							@endif
						</div>
					</div>
					<div class="col col-xs-12 col-sm-6">
						<div class="form-group">
							<?php
							$field = $form->getField('publish_down');
							echo $field->label;
							echo $field->input;
							?>
							@if ($field->description)
								<span class="form-text text-muted">{{ trans($field->description) }}</span>
							@endif
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php
					$field = $form->getField('note');
					echo $field->label;
					echo $field->input;
					?>
					@if ($field->description)
						<span class="form-text text-muted">{{ trans($field->description) }}</span>
					@endif
				</div>

				<input type="hidden" name="id" value="{{ $row->id }}" />
				<input type="hidden" name="fields[widget]" value="{{ $row->widget }}" />
			</fieldset>

			<?php if (empty($row->widget) || strtolower($row->widget) == 'custom') : ?>
				<fieldset class="adminform">
					<legend>{{ trans('widgets::widgets.custom content') }}</legend>

					<div class="form-group">
						<?php echo $form->getLabel('content'); ?>
						<?php echo $form->getInput('content'); ?>
					</div>
				</fieldset>
			<?php endif; ?>

			<?php if ($row->client_id == 0) :?>
				<?php $assignment = $row->menuAssignment(); ?>
				<fieldset class="adminform">
					<legend>{{ trans('widgets::widgets.menu assignment') }}</legend>

					<p class="form-text text-muted">{{ trans('widgets::widgets.menu description') }}</p>

					<div class="form-group">
						<label for="menu_assignment" class="form-label">{{ trans('widgets::widgets.widget assignment') }}</label>
						<select class="form-control" name="menu[assignment]" id="menu_assignment">
							<?php echo App\Halcyon\Html\Builder\Select::options(App\Modules\Widgets\Helpers\Admin::getAssignmentOptions($row->client_id), 'value', 'text', $assignment, true);?>
						</select>
					</div>

					<div id="menu_assignment-dependent">
						<div class="form-group">
							<label id="field_menuselect-lbl" for="field_menuselect" class="form-label">{{ trans('widgets::widgets.menu selection') }}</label>

							<div id="menu-assignment">
								<?php
								$menuTypes = App\Modules\Menus\Helpers\Menus::getMenuLinks(null, 0, 0, [1]);

								foreach ($menuTypes as $type):
									$count = count($type->links);

									$chkbox_class = 'chk-menulink-' . $type->id;
									?>
									<details class="card mb-1" open>
										<summary class="card-header" data-ref="{{ $type->menutype }}-details">
											<!--<a href="#{{ $type->menutype }}-details">-->
												{{ $type->title ? $type->title : $type->menutype }}
											<!--</a>-->
										</summary>
										<div id="{{ $type->menutype }}-details" class="card-body">
											<?php
											$count = count($type->links);
											$i     = 0;
											if ($count):
												?>
												<div class="btn-group mb-3" role="group" aria-label="Selection options">
													<button class="btn-assignments btn btn-sm btn-outline-secondary btn-selectinvert" data-name=".{{ $chkbox_class }}">
														{{ trans('widgets::widgets.invert selection') }}
													</button>

													<button class="btn-assignments btn btn-sm btn-outline-secondary btn-selectnone" data-name=".{{ $chkbox_class }}">
														{{ trans('widgets::widgets.select none') }}
													</button>

													<button class="btn-assignments btn btn-sm btn-outline-secondary btn-selectall" data-name=".{{ $chkbox_class }}">
														{{ trans('widgets::widgets.select all') }}
													</button>
												</div>
												<ul class="menu-links">
													<?php
													foreach ($type->links as $link):
														if (trim($assignment) == '-'):
															$checked = '';
														elseif ($assignment == 0):
															$checked = ' checked="checked"';
														elseif ($assignment < 0):
															$checked = in_array(-$link->value, $row->menuAssigned()) ? ' checked="checked"' : '';
														elseif ($assignment > 0) :
															$checked = in_array($link->value, $row->menuAssigned()) ? ' checked="checked"' : '';
														endif;
														?>
														<li class="menu-link">
															<div class="form-check">
																<input type="checkbox" class="form-check-input chkbox <?php echo $chkbox_class; ?>" name="menu[assigned][]" value="<?php echo (int) $link->value;?>" id="link<?php echo (int) $link->value;?>"<?php echo $checked;?>/>
																<label class="form-check-label" for="link<?php echo (int) $link->value;?>"><?php echo $link->text; ?></label>
															</div>
														</li>
														<?php if ($count > 20 && ++$i == ceil($count/2)): ?>
													</ul>
													<ul class="menu-links">
														<?php endif; ?>
													<?php endforeach; ?>
												</ul>
											<?php else: ?>
												<div class="text-muted"><span class="fa fa-exclamation-triangle" aria-hidden="true"></span> Menu has no active items.</div>
											<?php endif; ?>
										</div><!-- / #{{ $type->menutype }}-details -->
									</details>
								<?php endforeach; ?>
							</div>
						</div><!-- / #menu-assignment -->
					</div>
				</fieldset>
			<?php endif; ?>
		</div>
		<div class="col col-xs-12 col-sm-5">
			<?php
			$fieldSets = $form->getFieldsets('params');
			$i = 0;

			if (count($fieldSets) > 0):
				foreach ($fieldSets as $name => $fieldSet):
					$i++;
					$label = !empty($fieldSet->label) ? $fieldSet->label : 'widgets::widgets.' . $name . ' fieldset';
					?>
					<details class="card" id="{{ $name }}-options"<?php if ($i == 1) { echo ' open'; } ?>>
						<summary class="card-header">
							{{ trans($label) }}
						</summary>
						<fieldset class="card-body">
							@if (isset($fieldSet->description) && trim($fieldSet->description))
								<p>{{ trans($fieldSet->description) }}</p>
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
					</details>
					<?php
				endforeach;
			else:
				?>
				<div class="card">
					<div class="card-body">
						<div class="text-center m-4">
							<div class="display-4 text-muted"><span class="fa fa-sliders" aria-hidden="true"></span></div>
							<p>{{ trans('widgets::widgets.no options') }}</p>
						</div>
					</div>
				</div>
				<?php
			endif;
			?>
		</div>
	</div>

	@csrf
</form>
@stop
