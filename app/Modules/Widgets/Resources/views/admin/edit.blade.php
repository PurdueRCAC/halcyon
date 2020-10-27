@extends('layouts.master')

@section('scripts')
<script>
	function validate(){
		var value = $('#menu_assignment').val(),
			list  = $('#menu-assignment');

		if (value == '-' || value == '0') {
			$('.btn-assignments').each(function(i, el) {
				$(el).prop('disabled', true);
			});
			list.find('input').each(function(i, el){
				$(el).prop('disabled', true);
				if (value == '-'){
					$(el).prop('checked', false);
				} else {
					$(el).prop('checked', true);
				}
			});
		} else {
			$('.btn-assignments').each(function(i, el) {
				$(el).prop('disabled', false);
			});
			list.find('input').each(function(i, el){
				$(el).prop('disabled', false);
			});
		}
	}

/*$.widget( "custom.combobox", {
	_create: function() {
		this.wrapper = $( "<span>" )
			.addClass( "input-group input-combobox" )
			.insertAfter( this.element );

		this.element.hide();
		this._createAutocomplete();
		this._createShowAllButton();
	},

	_createAutocomplete: function() {
		var selected = this.element.children( ":selected" ),
			value = selected.val() ? selected.text() : "";

		this.input = $( "<input>" )
			.appendTo( this.wrapper )
			.val( value )
			.attr( "title", "" )
			//.addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
			.addClass( "form-control" )
			.autocomplete({
				delay: 0,
				minLength: 0,
				source: $.proxy( this, "_source" )
			})
			.tooltip({
				classes: {
					"ui-tooltip": "ui-state-highlight"
				}
			});

		this._on( this.input, {
			autocompleteselect: function( event, ui ) {
				ui.item.option.selected = true;
				this._trigger( "select", event, {
					item: ui.item.option
				});
			},

			autocompletechange: "_removeIfInvalid"
		});
	},

	_createShowAllButton: function() {
		var input = this.input,
			wasOpen = false;

		var wrap = $('<span>')
			.addClass( "input-group-append" )
			.appendTo( this.wrapper );

		$( "<a>" )
			.attr( "tabIndex", -1 )
			.attr( "title", "Show All Items" )
			.tooltip()
			.appendTo( wrap )
			.button({
				icons: {
					primary: "ui-icon-triangle-1-s"
				},
				text: false
			})
			.removeClass( "ui-corner-all" )
			.addClass( "custom-combobox-toggle input-group-text" )
			.on( "mousedown", function() {
				wasOpen = input.autocomplete( "widget" ).is( ":visible" );
			})
			.on( "click", function() {
				input.trigger( "focus" );

				// Close if already visible
				if ( wasOpen ) {
					return;
				}

				// Pass empty string as value to search for, displaying all results
				input.autocomplete( "search", "" );
			});
	},

	_source: function( request, response ) {
		var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
		response( this.element.children( "option" ).map(function() {
			var text = $( this ).text();
			if ( this.value && ( !request.term || matcher.test(text) ) )
				return {
					label: text,
					value: text,
					option: this
				};
		}) );
	},

	_removeIfInvalid: function( event, ui ) {

		// Selected an item, nothing to do
		if ( ui.item ) {
			return;
		}

		// Search for a match (case-insensitive)
		var value = this.input.val(),
			valueLowerCase = value.toLowerCase(),
			valid = false;
		this.element.children( "option" ).each(function() {
			if ( $( this ).text().toLowerCase() === valueLowerCase ) {
				this.selected = valid = true;
				return false;
			}
		});

		// Found a match, nothing to do
		if ( valid ) {
			return;
		}

		// Remove invalid value
		/*this.input
			.val( "" )
			.attr( "title", value + " didn't match any item" )
			.tooltip( "open" );
		this.element.val( "" );
		this._delay(function() {
			this.input.tooltip( "close" ).attr( "title", "" );
		}, 2500 );
		this.input.autocomplete( "instance" ).term = "";
	},

	_destroy: function() {
		this.wrapper.remove();
		this.element.show();
	}
});*/

$( document ).ready(function() {
	if ($('#item-form').length) {
		validate();
		$('select').on('change', function(e){
			validate();
		});
	}

	//$("#fields_position").combobox();

	var nativedatalist = !!('list' in document.createElement('input')) && !!(document.createElement('datalist') && window.HTMLDataListElement);

	if (!nativedatalist) {
		$('input[list]').each(function () {
			var availableTags = $('#' + $(this).attr("list")).find('option').map(function () {
				return this.value;
			}).get();

			$(this).autocomplete({ source: availableTags });
		});
	}

	if ($('#widgetorder').length) {
		data = $('#widgetorder');

		if (data.length) {
			modorders = JSON.parse(data.html());

			var html = '\n	<select class="form-control" id="' + modorders.name.replace('[', '-').replace(']', '') + '" name="' + modorders.name + '" id="' + modorders.id + '"' + modorders.attr + '>';
			var i = 0,
				key = modorders.originalPos,
				orig_key = modorders.originalPos,
				orig_val = modorders.originalOrder;
			for (x in modorders.orders) {
				if (modorders.orders[x][0] == key) {
					var selected = '';
					if ((orig_key == key && orig_val == modorders.orders[x][1])
					 || (i == 0 && orig_key != key)) {
						selected = 'selected="selected"';
					}
					html += '\n		<option value="' + modorders.orders[x][1] + '" ' + selected + '>' + modorders.orders[x][2] + '</option>';
				}
				i++;
			}
			html += '\n	</select>';

			$('#widgetorder').after(html);
		}
	}

	$('#menu_assignment-dependent').hide();
	if ($('#menu_assignment').val() != '0'
	 && $('#menu_assignment').val() != '-')
	{
		$('#menu_assignment-dependent').show();
	}

	$('#menu_assignment').on('change', function(){
		if ($(this).val() != '0'
		 && $(this).val() != '-')
		{
			$('#menu_assignment-dependent').show();
		}
		else
		{
			$('#menu_assignment-dependent').hide();
		}
	});
});
</script>
@stop

@php
app('pathway')
	->append(
		trans('widgets::widgets.module name'),
		route('admin.widgets.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit widgets'))
		{!! Toolbar::save(route('admin.widgets.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.widgets.cancel', ['id' => $row->id]));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('widgets.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.widgets.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="row">
		<div class="col col-xs-12 col-sm-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<?php echo $form->getLabel('title'); ?>
					<?php echo $form->getInput('title'); ?>
				</div>

				<div class="form-group">
					<?php echo $form->getLabel('showtitle'); ?>
					<?php echo $form->getInput('showtitle'); ?>
				</div>

				<div class="form-group">
					<?php echo $form->getLabel('position'); ?>
					<?php echo $form->getInput('position'); ?>
				</div>

				<div class="form-group">
					<?php echo $form->getLabel('ordering'); ?>
					<?php echo $form->getInput('ordering'); ?>
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
							<?php echo $form->getLabel('publish_up'); ?>
							<?php echo $form->getInput('publish_up'); ?>
						</div>
					</div>
					<div class="col col-xs-12 col-sm-6">
						<div class="form-group">
							<?php echo $form->getLabel('publish_down'); ?>
							<?php echo $form->getInput('publish_down'); ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $form->getLabel('language'); ?>
					<?php echo $form->getInput('language'); ?>
				</div>

				<div class="form-group">
					<?php echo $form->getLabel('note'); ?>
					<?php echo $form->getInput('note'); ?>
				</div>

				<input type="hidden" name="id" value="{{ $row->id }}" />
				<input type="hidden" name="fields[widget]" value="{{ $row->widget }}" />
			</fieldset>

			<?php if (empty($row->widget) || $row->widget == 'custom' || $row->widget == 'mod_custom') : ?>
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

					<div class="form-group">
						<label for="menu_assignment">{{ trans('widgets::widgets.widget assignment') }}</label>
					<!-- <fieldset id="jform_menus" class="radio"> -->
						<select class="form-control" name="menu[assignment]" id="menu_assignment">
							<?php echo App\Halcyon\Html\Builder\Select::options(App\Modules\Widgets\Helpers\Admin::getAssignmentOptions($row->client_id), 'value', 'text', $assignment, true);?>
						</select>
					<!-- </fieldset> -->
					</div>

					<div id="menu_assignment-dependent">
						<div class="form-group">
							<label id="field_menuselect-lbl" for="field_menuselect">{{ trans('widgets::widgets.menu selection') }}</label>

							<!--
							<button class="btn-assignments btn btn-default" onclick="$('.chkbox').each(function(i, el) { el.checked = !el.checked; });">
								<?php echo trans('global.select toggle'); ?>
							</button>

							<button class="btn-assignments btn btn-default" onclick="$('.chkbox').each(function(i, el) { el.checked = false; });">
								<?php echo trans('global.select none'); ?>
							</button>

							<button class="btn-assignments btn btn-default" onclick="$('.chkbox').each(function(i, el) { el.checked = true; });">
								<?php echo trans('global.select all'); ?>
							</button>
							-->

						<div id="menu-assignment" class="accordian">
							<?php $menuTypes = App\Modules\Menus\Helpers\Menus::getMenuLinks(); ?>
							<?php /*
							<div class="tabs">
								<ul class="nav tav-tabs">
									<?php foreach ($menuTypes as &$type) : ?>
										<li class="nav-item">
											<a class="nav-link" href="#<?php echo $type->menutype; ?>-details">
												<?php echo $type->title ? $type->title : $type->menutype; ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>*/ ?>

								<?php foreach ($menuTypes as &$type) : ?>
									<h3 data-ref="{{ $type->menutype }}-details">
										<a href="#{{ $type->menutype }}-details">
											{{ $type->title ? $type->title : $type->menutype }}
										</a>
									</h3>
									<div id="{{ $type->menutype }}-details">
										<?php
										//echo Html::tabs('panel', $type->title ? $type->title : $type->menutype, $type->menutype.'-details');

										$chkbox_class = 'chk-menulink-' . $type->id; ?>

										<!--
										<button class="btn-assignments btn btn-secondary" onclick="$('.<?php echo $chkbox_class; ?>').each(function(i, el) { el.checked = !el.checked; });">
											<?php echo trans('global.SELECTION_INVERT'); ?>
										</button>

										<button class="btn-assignments btn btn-warning" onclick="$('.<?php echo $chkbox_class; ?>').each(function(i, el) { el.checked = false; });">
											<?php echo trans('global.SELECTION_NONE'); ?>
										</button>

										<button class="btn-assignments btn btn-success" onclick="$('.<?php echo $chkbox_class; ?>').each(function(i, el) { el.checked = true; });">
											<?php echo trans('global.SELECTION_ALL'); ?>
										</button>

										<div class="clr"></div>
										-->

										<?php
										$count = count($type->links);
										$i     = 0;
										if ($count):
										?>
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
										<?php endif; ?>
									</div><!-- / #{{ $type->menutype }}-details -->
								<?php endforeach; ?>
							<!--</div> / .tabs -->
						</div>
						</div><!-- / #menu-assignment -->
					</div>
				</fieldset>
			<?php endif; ?>
		</div>
		<div class="col col-xs-12 col-sm-5">
			@sliders('start', 'widget-sliders')
			<?php
			$fieldSets = $form->getFieldsets('params');

			foreach ($fieldSets as $name => $fieldSet) :
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'widgets::widgets.' . $name . ' fieldset';
				echo app('html.builder')->sliders('panel', trans($label), $name . '-options');
					if (isset($fieldSet->description) && trim($fieldSet->description)) :
						echo '<p class="tip">' . trans($fieldSet->description) . '</p>';
					endif;
					?>
				<fieldset class="panelform">
					<?php $hidden_fields = ''; ?>

					<?php foreach ($form->getFieldset($name) as $field) : ?>
						<?php if (!$field->hidden) : ?>
							<div class="form-group">
								<?php echo $field->label; ?><br />
								<?php echo $field->input; ?>
								@if ($field->description)
									<span class="form-text text-muted">{{ trans($field->description) }}</span>
								@endif
							</div>
						<?php else : $hidden_fields .= $field->input; ?>
						<?php endif; ?>
					<?php endforeach; ?>

					<?php echo $hidden_fields; ?>
				</fieldset>
			<?php endforeach; ?>
			@sliders('end')
		</div>
	</div>

	@csrf
</form>
@stop