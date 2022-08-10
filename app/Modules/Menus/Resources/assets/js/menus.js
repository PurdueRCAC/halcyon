/* global TomSelect */ // vendor/tom-select/js/tom-select.complete.min.js

/**
 * Set menu type
 *
 * @param   {string}  type
 * @return  {void}
 */
function setmenutype(type) {
	window.parent.Halcyon.submitbutton('items.setType', type);
	window.parent.$.dialog.close();
}

document.addEventListener('DOMContentLoaded', function () {
	var alias = document.getElementById('field-menutype');
	if (alias && !alias.value) {
		document.getElementById('field-title').addEventListener('keyup', function () {
			alias.value = this.value.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');
		});
		alias.addEventListener('keyup', function () {
			alias.value = this.value.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');
		});
	}

	document.querySelectorAll('.menutype-dependant').forEach(function (el) {
		el.classList.add('d-none');
	});

	document.querySelectorAll('[name="fields[type]"]').forEach(function (item) {
		item.addEventListener('change', function () {
			document.querySelectorAll('.menutype-dependant').forEach(function (el) {
				el.classList.add('d-none');
			});
			document.querySelectorAll('.menutype-' + this.value).forEach(function (el) {
				el.classList.remove('d-none');
			});
		});

		if (item.checked) {
			document.querySelectorAll('.menutype-' + item.value).forEach(function (el) {
				el.classList.remove('d-none');
			});
		}
	});

	var pageid = document.getElementById('fields_page_id');
	if (pageid) {
		if (typeof TomSelect !== 'undefined') {
			var sel = new TomSelect(pageid, { plugins: ['dropdown_input'] });
			sel.on('change', function () {
				if (document.getElementById('fields_title').value == '') {
					document.getElementById('fields_title').value = this.input.selectedOptions[0].innerHTML.replace(/\|— /g, '');
				}
			});
		} else {
			pageid.addEventListener('change', function () {
				if (document.getElementById('fields_title').value == '') {
					document.getElementById('fields_title').value = this.selectedOptions[0].innerHTML.replace(/\|— /g, '');
				}
			});
		}
	}

	var data = document.getElementById('menutypes');
	if (data) {
		var menus = JSON.parse(data.innerHTML);

		document.getElementById(data.getAttribute('data-field')).addEventListener('change', function () {
			var val = this.value;

			if (typeof (menus[val]) !== 'undefined') {
				document.getElementById('fields_parent_id')
					.querySelectorAll('option').forEach(function (opt) {
						opt.remove();
					});

				for (var i = 0; i < menus[val].length; i++) {
					document.getElementById('fields_parent_id').append('<option value="' + menus[val][i].value + '">' + menus[val][i].text + '</option>');
				}
			}
		});
	}

	/*var sortableHelper = function (e, ui) {
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
			var height = ui.helper.outerHeight();
			$(this).find('> tr[data-parent=' + $(ui.item).data('id') + ']').each(function (idx, row) {
				height += $(row).outerHeight();
			});
			ui.placeholder.height(height);
		},
		update: function () { //e, ui
			$(this).find('> tr').each(function (idx, row) {
				var uniqID = $(row).attr('data-id'),
					correspondingFixedRow = $('tr[data-parent=' + uniqID + ']');
				correspondingFixedRow.detach().insertAfter($(this));
			});
		}
	}).disableSelection();*/

	document.querySelectorAll('.choose_type').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			setmenutype(this.getAttribute('data-type'));
		});
	});
});
