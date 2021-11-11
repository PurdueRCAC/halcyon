/* global $ */ // jquery.js

function validate() {
	var value = document.getElementById('menu_assignment').value,
		list = document.getElementById('menu-assignment');

	if (value == '-' || value == '0') {
		document.querySelectorAll('.btn-assignments').forEach(function (el) {
			el.disabled = true;
		});
		list.querySelectorAll('input').forEach(function (el) {
			el.disabled = true;
			if (value == '-') {
				el.checked = false;
			} else {
				el.checked = true;
			}
		});
	} else {
		document.querySelectorAll('.btn-assignments').forEach(function (el) {
			el.disabled = false;
		});
		list.querySelectorAll('input').forEach(function (el) {
			el.disabled = false;
		});
	}
}

document.addEventListener('DOMContentLoaded', function () {
	if (document.getElementById('item-form')) {
		validate();
		document.querySelectorAll('select').forEach(function(select){
			select.addEventListener('change', function () {
				validate();
			});
		});
	}

	var nativedatalist = !!('list' in document.createElement('input')) && !!(document.createElement('datalist') && window.HTMLDataListElement);

	if (!nativedatalist) {
		document.querySelectorAll('input[list]').forEach(function (el) {
			var availableTags = $('#' + el.getAttribute("list")).find('option').map(function () {
				return this.value;
			}).get();

			$(this).autocomplete({ source: availableTags });
		});
	}

	var data = document.getElementById('widgetorder');
	if (data) {
		var modorders = JSON.parse(data.innerHTML);

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

		data.insertAdjacentHTML('afterend', html);
	}

	var menuassign = document.getElementById('menu_assignment');
	if (menuassign) {
		var menuad = document.getElementById('menu_assignment-dependent');

		if (menuassign.value == '0' || menuassign.value == '-') {
			menuad.classList.add('d-none');
		}

		document.getElementById('menu_assignment').addEventListener('change', function () {
			if (this.value != '0' && this.value != '-') {
				menuad.classList.remove('d-none');
			} else {
				menuad.classList.add('d-none');
			}
		});
	}

	document.querySelectorAll('.btn-selectinvert').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			document.querySelectorAll(this.getAttribute('data-name')).forEach(function (el) {
				el.checked = !el.checked;
			});
		});
	});
	document.querySelectorAll('.btn-selectnone').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			document.querySelectorAll(this.getAttribute('data-name')).forEach(function (el) {
				el.checked = true;
			});
		});
	});
	document.querySelectorAll('.btn-selectall').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			document.querySelectorAll(this.getAttribute('data-name')).forEach(function (el) {
				el.checked = true;
			});
		});
	});

	var btnnew = document.getElementById('toolbar-plus');
	if (btnnew) {
		btnnew.setAttribute('data-toggle', 'modal');
		btnnew.setAttribute('data-target', '#new-widget');

		btnnew.addEventListener('click', function (e) {
			e.preventDefault();
		});
	}
});
