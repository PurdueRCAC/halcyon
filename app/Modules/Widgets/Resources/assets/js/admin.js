/* global $ */ // jquery.js

function validate() {
	var value = $('#menu_assignment').val(),
		list = $('#menu-assignment');

	if (value == '-' || value == '0') {
		$('.btn-assignments').each(function (i, el) {
			$(el).prop('disabled', true);
		});
		list.find('input').each(function (i, el) {
			$(el).prop('disabled', true);
			if (value == '-') {
				$(el).prop('checked', false);
			} else {
				$(el).prop('checked', true);
			}
		});
	} else {
		$('.btn-assignments').each(function (i, el) {
			$(el).prop('disabled', false);
		});
		list.find('input').each(function (i, el) {
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

$(document).ready(function () {
	if ($('#item-form').length) {
		validate();
		$('select').on('change', function () {
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
		var data = $('#widgetorder');

		if (data.length) {
			var modorders = JSON.parse(data.html());

			var html = '\n	<select class="form-control" id="' + modorders.name.replace('[', '-').replace(']', '') + '" name="' + modorders.name + '" id="' + modorders.id + '"' + modorders.attr + '>';
			var i = 0,
				key = modorders.originalPos,
				orig_key = modorders.originalPos,
				orig_val = modorders.originalOrder,
				x = 0;
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
		&& $('#menu_assignment').val() != '-') {
		$('#menu_assignment-dependent').show();
	}

	$('#menu_assignment').on('change', function () {
		if ($(this).val() != '0'
			&& $(this).val() != '-') {
			$('#menu_assignment-dependent').show();
		}
		else {
			$('#menu_assignment-dependent').hide();
		}
	});

	$('.btn-selectinvert').on('click', function (e) {
		e.preventDefault();
		$($(this).data('name')).each(function (i, el) {
			el.checked = !el.checked;
		});
	});
	$('.btn-selectnone').on('click', function (e) {
		e.preventDefault();
		$($(this).data('name')).each(function (i, el) {
			el.checked = false;
		});
	});
	$('.btn-selectall').on('click', function (e) {
		e.preventDefault();
		$($(this).data('name')).each(function (i, el) {
			el.checked = true;
		});
	});

	var dialog = $("#new-widget").dialog({
		autoOpen: false,
		height: 400,
		width: 500,
		modal: true
	});

	$('#toolbar-plus').on('click', function (e) {
		e.preventDefault();

		dialog.dialog("open");
	});
});
