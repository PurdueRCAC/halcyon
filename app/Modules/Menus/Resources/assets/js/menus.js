
jQuery(document).ready(function ($) {
	var alias = $('#field-alias');
	if (alias.length && !alias.val()) {
		$('#field-title,#field-alias').on('keyup', function (e){
			var val = $(this).val();

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');

			alias.val(val);
		});
	}

	var alias = $('#field-menutype');
	if (alias.length && !alias.val()) {
		$('#field-title,#field-menutype').on('keyup', function (e) {
			var val = $(this).val();

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');

			alias.val(val);
		});
	}

	$('.menutype-dependant').hide();
	//$('.menu-page').fadeIn();
	$('[name="fields[type]"]')
		.on('change', function () {
			$('.menutype-dependant').hide();
			$('.menutype-' + $(this).val()).show();

			/*if ($(this).val() == 'separator') {
				if (!$('#fields_title').val()) {
					$('#fields_title').val('[ separator ]');
				}
			}*/
		})
		.each(function (i, el) {
			if ($(el).prop('checked')) {
				$('.menutype-' + $(el).val()).show();
			}
		});

	$('#fields_page_id').on('change', function (e) {
		if ($('#fields_title').val() == '') {
			$('#fields_title').val($(this).children("option:selected").text().replace(/\|\— /g, ''));
		}
	});

	var data = $('#menutypes');
	if (data.length) {
		menus = JSON.parse(data.html());

		$('#' + data.data('field')).on('change', function (e) {
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
});
