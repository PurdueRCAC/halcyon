/* global $ */ // jquery.js
/* global jQuery */ // jquery.js

function setmenutype(type) {
	window.parent.Halcyon.submitbutton('items.setType', type);
	window.parent.$.dialog.close();
}

jQuery(document).ready(function () {
	var alias = $('#field-menutype');
	if (alias.length && !alias.val()) {
		$('#field-title,#field-menutype').on('keyup', function () {
			var val = $(this).val();

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');

			alias.val(val);
		});
	}

	$('.menutype-dependant').hide();

	$('[name="fields[type]"]')
		.on('change', function () {
			$('.menutype-dependant').hide();
			$('.menutype-' + $(this).val()).show();
		})
		.each(function (i, el) {
			if ($(el).prop('checked')) {
				$('.menutype-' + $(el).val()).show();
			}
		});

	$('#fields_page_id').on('change', function () {
		if ($('#fields_title').val() == '') {
			$('#fields_title').val($(this).children("option:selected").text().replace(/\|— /g, ''));
		}
	});

	var data = $('#menutypes');
	if (data.length) {
		var menus = JSON.parse(data.html());

		$('#' + data.data('field')).on('change', function () {
			var val = $(this).val();

			if (typeof (menus[val]) !== 'undefined') {
				$('#fields_parent_id')
					.find('option')
					.remove()
					.end();

				for (var i = 0; i < menus[val].length; i++) {
					$('#fields_parent_id').append('<option value="' + menus[val][i].value + '">' + menus[val][i].text + '</option>');
				}
			}
		});
		/*var html = '\n	<select name="' + modorders.name + '" id="' + modorders.id + '"' + modorders.attr + '>';
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

		$('#moduleorder').after(html);*/
	}

	var sortableHelper = function (e, ui) {
		ui.children().each(function () {
			$(this).width($(this).width());
		});
		return ui;
	};
	//var corresponding;
	$('.sortable').sortable({
		handle: '.draghandle',
		cursor: 'move',
		helper: sortableHelper,
		containment: 'parent',
		start: function (e, ui) {
			//corresponding = [];
			var height = ui.helper.outerHeight();
			$(this).find('> tr[data-parent=' + $(ui.item).data('id') + ']').each(function (idx, row) {

				height += $(row).outerHeight();
				// corresponding.push(row);
				//row.detach();
				/*var corresponding = $('tr[data-parent=' + $(ui.item).data('id') + ']');
				corresponding.detach();

				corresponding.each(function (idx, row) {
				});*/
				//row.insertAfter($(ui.item));

			});
			ui.placeholder.height(height);
		},
		update: function () { //e, ui
			//var tableHasUnsortableRows = $(this).find('> tbody > tr:not(.sortable)').length;

			$(this).find('> tr').each(function (idx, row) {
				var uniqID = $(row).attr('data-id'),
					correspondingFixedRow = $('tr[data-parent=' + uniqID + ']');
				correspondingFixedRow.detach().insertAfter($(this));
			});
		}/*,
		stop: function (e, ui) {
			corresponding.detach().insertAfter($(ui.item));
		}*/
	}).disableSelection();

	$('.choose_type').on('click', function(e){
		e.preventDefault();

		setmenutype($(this).attr('data-type'));
	});
});
