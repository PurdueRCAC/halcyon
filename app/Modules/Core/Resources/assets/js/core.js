/* global $ */ // jquery.js
/* global jQuery */ // jquery.js

// Only define the Halcyon namespace if not defined.
if (typeof(Halcyon) === 'undefined') {
	var Halcyon = {
		config: {}
	};
}

/* exported config */
function config(key, def) {
	if (typeof (def) === 'undefined') {
		def = null;
	}
	var result = key.split('.').reduce(function (obj, i) {
		return obj[i];
	}, Halcyon.config);
	if (typeof (result) === 'undefined') {
		result = def;
	}
	return result;
}

Halcyon.editors = {};
// An object to hold each editor instance on page
Halcyon.editors.instances = {};

/**
 * Generic submit form
 *
 * @param   {string}  task
 * @param   {mixed}   form
 * @return  {void}
 */
Halcyon.submitform = function(task, form) {
	if (typeof(form) === 'undefined') {
		form = document.getElementById('adminForm');

		if (!form) {
			form = document.adminForm;
		}
	} else {
		if (form instanceof jQuery) {
			form = form[0];
		}
	}

	if (typeof(task) !== 'undefined' && '' !== task) {
		//form.task.value = task;
		form.action = task;
	}

	var event;
	if (document.createEvent) {
		event = document.createEvent('HTMLEvents');
		event.initEvent('submit', true, true);
	} else if (document.createEventObject) { // IE < 9
		event = document.createEventObject();
		event.eventType = 'submit';
	}

	// Submit the form.
	if (typeof form.onsubmit == 'function') {
		form.onsubmit();
	}
	else if (typeof form.dispatchEvent == "function") {
		form.dispatchEvent(event);
	}
	else if (typeof form.fireEvent == "function") {
		form.fireEvent(event);
	}

	form.submit();
};

/**
 * Default function. Usually would be overriden by the module
 *
 * @param   {string}  task
 * @return  {void}
 */
Halcyon.submitbutton = function(task) {
	//Halcyon.submitform(task);
	var frm = document.getElementById('adminForm');

	if (frm) {
		return Halcyon.submitform(task, frm);
	}

	$(document).trigger('editorSave');

	frm = document.getElementById('item-form');
	var invalid = false;

	if (frm) {
		if (task == 'cancel' || task.match(/cancel$/)) {
			Halcyon.submitform(task, frm);
		}

		var elms = frm.querySelectorAll('input[required]');
		elms.forEach(function (el) {
			if (!el.value || !el.validity.valid) {
				el.classList.add('is-invalid');
				invalid = true;
			} else {
				el.classList.remove('is-invalid');
			}
		});
		elms = frm.querySelectorAll('select[required]');
		elms.forEach(function (el) {
			if (!el.value || el.value <= 0) {
				el.classList.add('is-invalid');
				invalid = true;
			} else {
				el.classList.remove('is-invalid');
			}
		});
		elms = frm.querySelectorAll('textarea[required]');
		elms.forEach(function (el) {
			if (!el.value || !el.validity.valid) {
				el.classList.add('is-invalid');
				invalid = true;
			} else {
				el.classList.remove('is-invalid');
			}
		});

		if (!invalid) {
			Halcyon.submitform(task, frm);
		}
	}
}

/**
 * Custom behavior for JavaScript I18N
 *
 * Allows you to call Halcyon.Lang.trans() to get a translated JavaScript string pushed in with Lang::script() in Halcyon.
 *
 * @return  {mixed}
 */
Halcyon.Lang = {
	strings: {},
	trans: function(key, def) {
		return typeof this.strings[key.toUpperCase()] !== 'undefined' ? this.strings[key.toUpperCase()] : def;
	},
	load: function(object) {
		for (var key in object) {
			this.strings[key.toUpperCase()] = object[key];
		}
		return this;
	}
};

/**
 * Method to replace all request tokens on the page with a new one.
 *
 * @param   {string}  n
 * @return  {void}
 */
Halcyon.replaceTokens = function(n) {
	var els = document.getElementsByTagName('input');
	for (var i = 0; i < els.length; i++) {
		if ((els[i].type == 'hidden') && (els[i].name.length == 32) && els[i].value == '1') {
			els[i].name = n;
		}
	}
};

/**
 * Verifies if the string is in a valid email format
 *
 * @param   {string}   text
 * @return  {bool}
 */
Halcyon.isEmail = function(text) {
	var regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	//var regex = new RegExp("^[\\w-_.]*[\\w-_.]@[\\w]\.+[\\w]+[\\w]$");
	return regex.test(text);
};

/**
 * Toggles the check state of a group of boxes
 *
 * Checkboxes must have an id attribute in the form cb0, cb1...
 *
 * @param   {mixed}   checkbox  The number of box to 'check', for a checkbox element
 * @param   {string}  stub      An alternative field name
 * @return  {bool}
 */
Halcyon.checkAll = function(checkbox, stub) {
	if (!stub) {
		stub = 'cb';
	}
	if (checkbox.form) {
		var c = 0;
		for (var i = 0, n = checkbox.form.elements.length; i < n; i++) {
			var e = checkbox.form.elements[i];
			if (e.type == checkbox.type) {
				if ((stub && e.id.indexOf(stub) == 0) || !stub) {
					e.checked = checkbox.checked;
					c += (e.checked == true ? 1 : 0);
				}
			}
		}
		Halcyon.enableDisableBtn(c);
		if (checkbox.form.boxchecked) {
			checkbox.form.boxchecked.value = c;
		}
		return true;
	}
	return false;
}

/**
 * Toggle button disabled state
 *
 * @param   {number}  num
 * @return  {void}
 */
Halcyon.enableDisableBtn = function(num) {
	document.querySelectorAll('.toolbar-btn').forEach(function (toolbarbutton) {
		if (toolbarbutton.classList.contains('toolbar-list')) {
			if (!num) {
				toolbarbutton.classList.add('disabled');
			} else {
				toolbarbutton.classList.remove('disabled');
			}
		} else {
			if (num) {
				toolbarbutton.classList.add('disabled');
			} else {
				toolbarbutton.classList.remove('disabled');
			}
		}
	});
}

/**
 * Render messages send via JSON
 *
 * @param   {object}  messages  JavaScript object containing the messages to render
 * @return  {void}
 */
Halcyon.message = function(type, message) {
	var container = document.getElementById('system-messages');

	if (typeof message === 'object') {
		var lines = Object.values(message);
		message = lines.join('<br />');
	}

	var div = document.createElement('div');
	div.classList.add('alert');
	div.classList.add('alert-' + type);
	div.setAttribute('role', 'alert');
	div.innerHTML = message;

	container.appendChild(div);

	document.dispatchEvent(new Event('renderMessages'));
};


/**
 * Remove messages
 *
 * @return  {void}
 */
Halcyon.removeMessages = function() {
	document.querySelectorAll('#system-messages > *').forEach(function(el) {
		el.remove();
	});
}

/**
 * Evaluate if any items in a list of checkboxes are checked
 *
 * @param   {bool} isitchecked
 * @param   {object} form
 * @return  {void}
 */
Halcyon.isChecked = function(isitchecked, form) {
	if (typeof(form) === 'undefined') {
		form = document.getElementById('adminForm');

		if (!form) {
			form = document.adminForm;
		}
	}

	if (isitchecked == true) {
		form.boxchecked.value++;
	} else {
		form.boxchecked.value--;
	}
}

/**
 * Pops up a new window in the middle of the screen
 *
 * @param   {string}  mypage
 * @param   {string}  myname
 * @param   {string}  w
 * @param   {string}  h
 * @param   {string}  scroll
 * @return  {void}
 */
Halcyon.popupWindow = function(mypage, myname, w, h, scroll) {
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	var winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable';

	var win = window.open(mypage, myname, winprops);
	win.window.focus();
}

/**
 * Set the table sort and direction on a form and submit it
 *
 * @param   {string}  order
 * @param   {string}  dir
 * @param   {string}  task
 * @param   {mixed}   form
 * @return  {void}
 */
Halcyon.tableOrdering = function(order, dir, task, form) {
	if (typeof(form) === 'undefined') {
		form = document.getElementById('adminForm');

		if (!form) {
			form = document.adminForm;
		}
	}

	if (typeof(form.filter_order) != 'undefined') {
		form.filter_order.value = order;
		form.filter_order_dir.value = dir;
	} else if (typeof(form.order) != 'undefined') {
		form.order.value = order;
		form.order_dir.value = dir;
	}

	Halcyon.submitform(form.action, form);
}

/**
 * Check the checkbox for this item and submit the form with the task
 *
 * @param   {string}  id
 * @param   {string}  task
 * @return  {bool}
 */
Halcyon.listItemTask = function(id, task) {
	var f = document.adminForm;
	var cb = f[id];
	if (cb) {
		// Uncheck all other checkboxes
		var total = document.querySelectorAll('input[type=checkbox]').length;
		for (var i = 0; i < total; i++) {
			var cbx = f['cb'+i];
			if (!cbx) {
				break;
			}
			cbx.checked = false;
		}
		// Check this checkbox
		cb.checked = true;
		f.boxchecked.value = 1;

		// Submit the form
		var form = document.getElementById('adminForm');
		Halcyon.submitform(task, form);
	}
	return false;
}

/**
 * Check if an element has the specified class name
 *
 * @param   {integer}  n
 * @param   {string}   task
 * @return  {mixed}
 */
Halcyon.saveOrder = function(n, task) {
	if (!task) {
		task = 'saveorder';
	}

	for (var j = 0; j <= n; j++) {
		var box = document.adminForm['cb'+j];
		if (box) {
			if (box.checked == false) {
				box.checked = true;
			}
		} else {
			alert("You cannot change the order of items, as an item in the list is `Checked Out`");
			return;
		}
	}

	return Halcyon.submitform(task);
}

/**
 * Handle toolbar actions
 *
 * @param   {object} event
 * @return  {void}
 */
Halcyon.toolbarAction = function(event) {
	var el = this;

	if (el.classList.contains('toolbar-submit')) {
		event.preventDefault();

		if (el.classList.contains('toolbar-list') && document.adminForm.boxchecked.value == 0) {
			alert(el.getAttribute('data-message'));
		} else {
			if (el.getAttribute('data-action')) {
				Halcyon.submitbutton(el.getAttribute('data-action'));
			} else if (el.getAttribute('href')) {
				Halcyon.submitbutton(el.getAttribute('href'));
			} else {
				console.log('Error: no task found.');
			}
		}
	}

	if (el.classList.contains('toolbar-popup')) {
		event.preventDefault();

		var width  = (el.getAttribute('data-width') ? el.getAttribute('data-width') : 700),
			height = (el.getAttribute('data-height') ? el.getAttribute('data-height') : 500),
			scroll = 1;

		Halcyon.popupWindow(
			el.getAttribute('href'),
			el.getAttribute('data-message'),
			width,
			height,
			scroll
		);
	}

	if (el.classList.contains('toolbar-confirm')) {
		event.preventDefault();

		if (el.classList.contains('toolbar-list') && document.adminForm.boxchecked.value == 0) {
			alert(el.getAttribute('data-message'));
		} else {
			if (confirm(el.getAttribute('data-confirm'))) {
				if (el.getAttribute('data-action')) {
					Halcyon.submitbutton(el.getAttribute('data-action'));
				} else if (el.getAttribute('href')) {
					Halcyon.submitbutton(el.getAttribute('href'));
				} else {
					console.log('Error: no task found.');
				}
			}
		}
	}
}

/**
 * Submit a form
 *
 * @return  {void}
 */
Halcyon.filterSubmit = function()
{
	this.form.submit();
}

/**
 * Clear filters in a form and submit
 *
 * @param   {object} event
 * @return  {void}
 */
Halcyon.filterClear = function()
{
	var k,
		filters = this.form.getElementsByClassName('filter');

	for (k = 0; k < filters.length; k++)
	{
		if (filters[k].tagName.toLowerCase() == 'select') {
			filters[k].selectedIndex = 0;
		}
		if (filters[k].tagName.toLowerCase() == 'input') {
			filters[k].value = '';
		}
	}

	this.form.submit();
}

/**
 * Toggle check-all checkbox
 *
 * @return  {void}
 */
Halcyon.gridCheckboxToggle = function()
{
	if (this.classList.contains('toggle-all')) {
		Halcyon.checkAll(this);
	} else {
		Halcyon.isChecked(this.checked);
		var c = this.closest('tr');
		if (this.checked) {
			c.classList.add('checked');
		} else {
			c.classList.remove('checked');
		}
	}

	var checkboxes = document.querySelectorAll('.checkbox-toggle:checked');
	Halcyon.enableDisableBtn(checkboxes.length);
}

/**
 * Grid ordering
 *
 * @param   {object} event
 * @return  {void}
 */
Halcyon.gridOrder = function(event)
{
	event.preventDefault();

	Halcyon.tableOrdering(
		this.getAttribute('data-order'),
		this.getAttribute('data-direction'),
		this.getAttribute('data-action')
	);

	return false;
}

Halcyon.gridOrderSave = function(event)
{
	event.preventDefault();

	var rows = this.getAttribute('data-rows'),
		task = this.getAttribute('data-action');

	if (rows && task) {
		Halcyon.saveOrder(rows, task);
	}

	return false;
}

/**
 * Grid actions
 *
 * @param   {object} event
 * @return  {void}
 */
Halcyon.gridAction = function(event)
{
	event.preventDefault();

	var id = this.getAttribute('data-id'),
		task = this.getAttribute('data-action');

	if (id && task) {
		return Halcyon.listItemTask(id, task);
	}

	return false;
}

/**
 * Attach pagination events
 *
 * @return  {void}
 */
Halcyon.paginate = function()
{
	var i,
		pages = document.querySelectorAll('.pagination a');
	for (i = 0; i < pages.length; i++)
	{
		pages[i].addEventListener('click', function(event){
			event.preventDefault();
			document.adminForm[this.getAttribute('data-prefix') + 'limitstart'].value = parseInt(this.getAttribute('data-start'));
			Halcyon.submitform();
		});
	}
	var limits = document.querySelectorAll('.pagination select');
	for (i = 0; i < limits.length; i++)
	{
		limits[i].addEventListener('change', function(){
			Halcyon.submitform();
		});
	}
}

/* exported ROOT_URL */
var ROOT_URL = '/api/';

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function() {
	ROOT_URL = document.querySelector('meta[name="base-url"]').getAttribute('content') + '/api/';

	// Add event listeners to toolbar buttons
	document.querySelectorAll('.toolbar-btn').forEach(function (toolbarbutton) {
		if (toolbarbutton.classList.contains('toolbar-list')) {
			//toolbarbutton.setAttribute('disabled', true);
			toolbarbutton.classList.add('disabled');
		}
		toolbarbutton.addEventListener('click', Halcyon.toolbarAction);
	});

	// Add event listener for checkbox toggles
	document.querySelectorAll('.checkbox-toggle').forEach(function (checkbox) {
		checkbox.addEventListener('click', Halcyon.gridCheckboxToggle);
	});

	// Add event listener for filters
	document.querySelectorAll('.filter-submit').forEach(function (filter) {
		filter.addEventListener('change', Halcyon.filterSubmit);
	});

	// Add event listener for clearing filters
	document.querySelectorAll('.filter-clear').forEach(function (clearfilter) {
		clearfilter.addEventListener('click', Halcyon.filterClear);
	});

	// Add event listener for table sorting
	document.querySelectorAll('.grid-order').forEach(function (ordering) {
		ordering.addEventListener('click', Halcyon.gridOrder);
	});

	// Add event listener for saving table sorting
	document.querySelectorAll('.grid-order-save').forEach(function (orderingsave) {
		orderingsave.addEventListener('click', Halcyon.gridOrderSave);
	});

	// Add event listener for action items
	document.querySelectorAll('.grid-action').forEach(function (action) {
		action.addEventListener('click', Halcyon.gridAction);
	});

	// Attach pagination events
	//Halcyon.paginate();
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
			'Authorization': 'Bearer ' + $('meta[name="api-token"]').attr('content'),
		}
	});

	$('input.date').datepicker({
		duration: '',
		constrainInput: false,
		dateFormat: 'yy-mm-dd',
		onClose: function () {
			if (this.classList.contains('filter-submit')) {
				this.form.submit();
			}
		}
	}).keyup(function (e) {
		if (e.keyCode == 8 || e.keyCode == 46) {
			$(this).val('').datepicker("refresh");
		}
	});

	$('input.datetime').datetimepicker({
		duration: '',
		constrainInput: false,
		dateFormat: 'yy-mm-dd',
		controlType: 'select',
		oneLine: true,
		timeFormat: 'HH:mm:00',
		onClose: function () {
			if (this.classList.contains('filter-submit')) {
				this.form.submit();
			}
		}
	}).keyup(function (e) {
		if (e.keyCode == 8 || e.keyCode == 46) {
			$(this).val('').datetimepicker("refresh");
		}
	});

	document.querySelectorAll('.dropdown-toggle').forEach(function (el) {
		el.addEventListener('click', function(event) {
			event.preventDefault();
			event.stopPropagation();

			var isActive = this.parentNode.querySelector('.dropdown-menu').classList.contains('show');

			document.querySelectorAll('.dropdown-toggle').forEach(function (dm) {
				dm.classList.remove('show');
			});

			if (isActive) {
				return;
			}

			if (this.disabled || this.classList.contains('disabled')) {
				return;
			}

			this.parentNode.querySelector('.dropdown-menu').classList.toggle('show');
		});
	});

	/*$('[data-tip]').tooltip({
		items: "[data-tip]",
		show: false,
		content: function() {
			return $(this).data('tip');
		},
		create: function () { //event, ui
			var tip = $(this),
				tipText = tip.data('tip');

			if (tipText && tipText.indexOf('::') != -1) {
				var parts = tipText.split('::');
				tip.data('tip', '<div class="tip-title">' + parts[0] + '</div><div class="tip-text">' + parts[1] + '</div>');
			} else {
				tip.data('tip', '<div class="tip-text">' + tipText + '</div>');
			}
		},
		tooltipClass: 'tool-tip'
	});*/

	document.querySelectorAll('.btn-settings').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			let overlay = document.createElement('div');
			overlay.classList.add('ui-widget-overlay');
			overlay.classList.add('ui-front');
			overlay.style.zIndex = 100;

			document.querySelector('body').appendChild(overlay);

			let panel = document.createElement('div');
			panel.id = 'panel';
			panel.style.zIndex = 101;

			let spinner = document.createElement('div');
			spinner.classList.add('spinner-border');
			spinner.classList.add('mx-auto');
			spinner.setAttribute('role', 'status');

			panel.appendChild(spinner);

			document.querySelector('body').appendChild(panel);

			$(panel).show("slide", { direction: "right" }, 500);

			fetch(this.getAttribute('href'), {
				method: 'GET',
				headers: {
					//'Content-Type': 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				}
			})
			.then(function (response) {
				if (response.ok) {
					return response.text();
				}
				return response.json().then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				});
			})
			.then(function (result) {
				panel.innerHTML = result;

				panel.querySelectorAll('.btn-cancel').forEach(function(item) {
					item.addEventListener('click', function (e) {
						e.preventDefault();
						$(panel).hide("slide", { direction: "right" }, 500);
						document.querySelector('.ui-widget-overlay').remove();
					});
				});

				panel.querySelector('.btn-save').addEventListener('click', function (e) {
					e.preventDefault();

					var frm = this.closest('form');
					const formData = new FormData(frm);

					fetch(this.getAttribute('href'), {
						method: 'POST',
						headers: {
							//'Content-Type': 'application/json',
							'X-Requested-With': 'XMLHttpRequest',
							'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
							'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
						},
						body: formData//JSON.stringify(serializeFormData(formData))//frm.serialize())
					})
					.then(function (response) {
						if (response.ok) {
							$(panel).hide("slide", { direction: "right" }, 500);
							document.querySelector('.ui-widget-overlay').remove();
							return;
						}
						return response.json().then(function (data) {
							var msg = data.message;
							if (typeof msg === 'object') {
								msg = Object.values(msg).join('<br />');
							}
							throw msg;
						});
					})
					.catch(function (err) {
						Halcyon.message('danger', err);
					});
				});
			})
			.catch(function (err) {
				Halcyon.message('danger', err);
			});
		});
	});

	document.querySelectorAll('[maxlength]').forEach(function (el) {
		if (el.getAttribute('data-counter') && el.getAttribute('data-counter') == 'false') {
			return;
		}
		var container = document.createElement('span');
		container.classList.add('char-counter-wrap');

		var counter = document.createElement('span');
		counter.classList.add('char-counter');

		if (el.getAttribute('id') != '') {
			counter.setAttribute('id', el.getAttribute('id') + '-counter');
		}

		if (el.parentNode.classList.contains('input-group')) {
			el.parentNode.parentNode.insertBefore(container, el.parentNode);
			container.appendChild(counter);
			container.appendChild(el.parentNode);
		} else {
			el.parentNode.insertBefore(container, el);
			container.appendChild(counter);
			container.appendChild(el);
		}
		counter.innerHTML = el.value.length + ' / ' + el.getAttribute('maxlength');

		el.addEventListener('focus', function () {
			var container = this.closest('.char-counter-wrap');
			if (container) {
				container.classList.add('char-counter-focus');
			}
		});
		el.addEventListener('blur', function () {
			var container = this.closest('.char-counter-wrap');
			if (container) {
				container.classList.remove('char-counter-focus');
			}
		});
		el.addEventListener('keyup', function () {
			var chars = this.value.length;
			var counter = document.getElementById(this.getAttribute('id') + '-counter');
			if (counter) {
				counter.innerHTML = chars + ' / ' + this.getAttribute('maxlength');
			}
		});
	});
});
