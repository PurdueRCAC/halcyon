/**
 * @package    halcyon
 * @copyright  Copyright 2019 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// Only define the Halcyon namespace if not defined.
if (typeof(Halcyon) === 'undefined') {
	var Halcyon = {};
}

Halcyon.editors = {};
// An object to hold each editor instance on page
Halcyon.editors.instances = {};

/**
 * Generic submit form
 *
 * @param   string  task
 * @param   mixed   form
 * @return  void
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
 * Default function. Usually would be overriden by the component
 *
 * @param   string  pressbutton
 * @return  void
 */
Halcyon.submitbutton = function(pressbutton) {
	Halcyon.submitform(pressbutton);
}

/**
 * Custom behavior for JavaScript I18N
 *
 * Allows you to call Halcyon.Lang.trans() to get a translated JavaScript string pushed in with Lang::script() in Halcyon.
 *
 * @return  mixed
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
 * @param   string  n
 * @return  void
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
 * @param   string   text
 * @return  boolean
 */
Halcyon.isEmail = function(text) {
	var regex = new RegExp("^[\\w-_\.]*[\\w-_\.]\@[\\w]\.+[\\w]+[\\w]$");
	return regex.test(text);
};

/**
 * Toggles the check state of a group of boxes
 *
 * Checkboxes must have an id attribute in the form cb0, cb1...
 *
 * @param   mixed   checkbox  The number of box to 'check', for a checkbox element
 * @param   string  stub      An alternative field name
 * @return  bool
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

Halcyon.enableDisableBtn = function(num) {
	var toolbarbuttons = document.getElementsByClassName('toolbar-list');
	for (i = 0; i < toolbarbuttons.length; i++)
	{
		if (!num) {
			Halcyon.addClass(toolbarbuttons[i], 'disabled');
		} else {
			Halcyon.removeClass(toolbarbuttons[i], 'disabled');
		}
	}
}

/**
 * Render messages send via JSON
 *
 * @param   object  messages  JavaScript object containing the messages to render
 * @return  void
 */
Halcyon.message = function(type, message) {
	//Halcyon.removeMessages();
	var container = $('#system-messages');

	var div = $('<div>')
				.addClass('alert')
				.addClass('alert-' + type)
				.attr('role', 'alert')
				.text(message);

	/*$.each(messages, function (type, item) {
		var dt = $('<dt>')
					.addClass(type)
					.html(type)
					.appendTo(dl);

		var dd = $('<dd>')
					.addClass(type)
					.addClass('message');
		var list = $('<ul>');

		$.each(item, function (index, item, object) {
			var li = $('<li>')
						.html(item)
						.appendTo(list);
		});
		list.appendTo(dd);
		dd.appendTo(dl);
	});*/
	div.appendTo(container);

	$(document).trigger('renderMessages');
};


/**
 * Remove messages
 *
 * @return  void
 */
Halcyon.removeMessages = function() {
	var children = $('#system-messages > *');
	children.remove();
}

/**
 * Evaluate if any items in a list of checkboxes are checked
 *
 * @param   isitchecked
 * @param   form
 * @return  void
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
 * @param   string  mypage
 * @param   string  myname
 * @param   string  w
 * @param   string  h
 * @param   string  scroll
 * @return  void
 */
Halcyon.popupWindow = function(mypage, myname, w, h, scroll) {
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	var winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl
			+ ',scrollbars=' + scroll + ',resizable'

	var win = window.open(mypage, myname, winprops)
	win.window.focus();
}

/**
 * Set the table sort and direction on a form and submit it
 *
 * @param   string  order
 * @param   string  dir
 * @param   string  task
 * @param   mixed   form
 * @return  void
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
 * @param   string  id
 * @param   string  task
 * @return  bool
 */
Halcyon.listItemTask = function(id, task) {
	var f = document.adminForm;
	var cb = f[id];
	if (cb) {
		// Uncheck all other checkboxes
		for (var i = 0; true; i++) {
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
 * @param   integer  n
 * @param   string   task
 * @return  mixed
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
 * Check if an element has the specified class name
 *
 * @param   el         The element to test
 * @param   className  The class to test for
 * @return  bool
 */
Halcyon.hasClass = function(el, className) {
	return el.classList ? el.classList.contains(className) : new RegExp('\\b'+ className+'\\b').test(el.className);
}

/**
 * Add a class to an element
 *
 * @param   el         The element to add the class to
 * @param   className  The class to add
 * @return  bool
 */
Halcyon.addClass = function(el, className) {
	if (el.classList) {
		el.classList.add(className);
	} else if (!Halcyon.hasClass(el, className)) {
		el.className += ' ' + className;
	}
}

/**
 * Remove a class from an element
 *
 * @param   el         The element to remove the class from
 * @param   className  The class to remove
 * @return  bool
 */
Halcyon.removeClass = function(el, className) {
	if (el.classList) {
		el.classList.remove(className);
	} else {
		el.className = el.className.replace(new RegExp('\\b'+ className+'\\b', 'g'), '');
	}
}

/**
 * Handle toolbar actions
 *
 * @param   event
 * @return  void
 */
Halcyon.toolbarAction = function(event) {
	var el = this;

	if (Halcyon.hasClass(el, 'toolbar-submit')) {
		event.preventDefault();

		if (Halcyon.hasClass(el, 'toolbar-list') && document.adminForm.boxchecked.value == 0) {
			alert(el.getAttribute('data-message'));
		} else {
			if (el.getAttribute('data-action')) {
				Halcyon.submitform(el.getAttribute('data-action'));
			} else if (el.getAttribute('href')) {
				Halcyon.submitform(el.getAttribute('href'));
			} else {
				console.log('Error: no task found.');
			}
		}
	}

	if (Halcyon.hasClass(el, 'toolbar-popup')) {
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

	if (Halcyon.hasClass(el, 'toolbar-confirm')) {
		event.preventDefault();

		if (Halcyon.hasClass(el, 'toolbar-list') && document.adminForm.boxchecked.value == 0) {
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
 * @param   event
 * @return  void
 */
Halcyon.filterSubmit = function(event)
{
	this.form.submit();
}

/**
 * Clear filters in a form and submit
 *
 * @param   event
 * @return  void
 */
Halcyon.filterClear = function(event)
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
 * @param   event
 * @return  void
 */
Halcyon.gridCheckboxToggle = function(event)
{
	if (Halcyon.hasClass(this, 'toggle-all')) {
		Halcyon.checkAll(this);
	} else {
		Halcyon.isChecked(this.checked);
		var c = this.closest('tr');
		if (this.checked) {
			Halcyon.addClass(c, 'checked');
		} else {
			Halcyon.removeClass(c, 'checked');
		}
	}

	var checkboxes = document.querySelectorAll('.checkbox-toggle:checked');
	Halcyon.enableDisableBtn(checkboxes.length);
}

/**
 * Grid ordering
 *
 * @param   event
 * @return  void
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
 * @param   event
 * @return  void
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
 * @return  void
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
		limits[i].addEventListener('change', function(event){
			Halcyon.submitform();
		});
	}
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function() {
	var i;

	// Add event listeners to toolbar buttons
	var toolbarbuttons = document.getElementsByClassName('toolbar-btn');
	for (i = 0; i < toolbarbuttons.length; i++)
	{
		if (Halcyon.hasClass(toolbarbuttons[i], 'toolbar-list')) {
			//toolbarbuttons[i].setAttribute('disabled', true);
			Halcyon.addClass(toolbarbuttons[i], 'disabled');
		}
		toolbarbuttons[i].addEventListener('click', Halcyon.toolbarAction);
	}

	// Add event listener for checkbox toggles
	var checkboxes = document.getElementsByClassName('checkbox-toggle');
	for (i = 0; i < checkboxes.length; i++)
	{
		checkboxes[i].addEventListener('click', Halcyon.gridCheckboxToggle);
	}

	// Add event listener for filters
	var filters = document.getElementsByClassName('filter-submit');
	for (i = 0; i < filters.length; i++)
	{
		filters[i].addEventListener('change', Halcyon.filterSubmit);
	}

	// Add event listener for clearing filters
	var clearfilters = document.getElementsByClassName('filter-clear');
	for (i = 0; i < clearfilters.length; i++)
	{
		clearfilters[i].addEventListener('click', Halcyon.filterClear);
	}

	// Add event listener for table sorting
	var clearfilters = document.getElementsByClassName('grid-order');
	for (i = 0; i < clearfilters.length; i++)
	{
		clearfilters[i].addEventListener('click', Halcyon.gridOrder);
	}

	// Add event listener for saving table sorting
	var ordering = document.getElementsByClassName('grid-order-save');
	for (i = 0; i < ordering.length; i++)
	{
		ordering[i].addEventListener('click', Halcyon.gridOrderSave);
	}

	// Add event listener for action items
	var actions = document.getElementsByClassName('grid-action');
	for (i = 0; i < actions.length; i++)
	{
		actions[i].addEventListener('click', Halcyon.gridAction);
	}

	/*
	$('input[maxlength]').on('keyup', function(e){
		$(this)
			.parent()
			.data($(this).length + ' / ' + $(this).attr('maxlength'));

		$(this).after($('<span></span>').text($(this).length + ' / ' + $(this).attr('maxlength')));
	});*/

	// Attach pagination events
	//Halcyon.paginate();
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
			'Authorization': 'Bearer ' + $('meta[name="api-token"]').attr('content'),
		}
	});

	$('.accordian').accordion({heightStyle: "content"});

	$('input.date').datepicker({
		duration: '',
		constrainInput: false,
		dateFormat: 'yy-mm-dd'
	});
	$('input.datetime').datetimepicker({
		duration: '',
		showTime: true,
		constrainInput: false,
		stepMinutes: 1,
		stepHours: 1,
		altTimeField: '',
		time24h: true,
		dateFormat: 'yy-mm-dd',
		timeFormat: 'HH:mm:00'
	});

	$('.dropdown-toggle').on('click', function(event){
		event.preventDefault();
		event.stopPropagation();

		var isActive = $(this).parent().find('.dropdown-menu').hasClass('show');

		$('.dropdown-menu').removeClass('show');

		if (isActive) {
			return;
		}

		if (this.disabled || $(this).hasClass('disabled')) {
			return;
		}

		$(this).parent().find('.dropdown-menu').toggleClass('show');
	});

	$('[data-tip]').tooltip({
		items: "[data-tip]",
		show: false,
		content: function() {
			return $(this).data('tip');
		},
		create: function(event, ui) {
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
	});

	/*$('.has-tip').tooltip({
		//track: true,
		show: false,
		content: function() {
			return $(this).attr('title');
		},
		create: function(event, ui) {
			var tip = $(this),
				tipText = tip.attr('title');

			if (tipText && tipText.indexOf('::') != -1) {
				var parts = tipText.split('::');
				tip.attr('title', '<div class="tip-title">' + parts[0] + '</div><div class="tip-text">' + parts[1] + '</div>');
			} else {
				tip.attr('title', '<div class="tip-text">' + tipText + '</div>');
			}
		},
		tooltipClass: 'tool-tip'
	});*/
	$('<div id="panel"></div>').appendTo('body');

	$('.btn-settings').on('click', function(e){
		e.preventDefault();

		$.get($(this).attr('href'), function(response){
			var panel = $('#panel');
			panel
				.html(response)
				.show("slide", { direction: "right" }, 500);

			panel.find('.btn-cancel').on('click', function(e){
				e.preventDefault();
				$('#panel').hide("slide", { direction: "right" }, 500);
			});

			panel.find('.btn-success').on('click', function(e){
				e.preventDefault();
				$.ajax({
					url: btn.getAttribute('data-api'),
					type: 'put',
					data: post,
					dataType: 'json',
					async: false,
					success: function(data) {
						Halcyon.message('success', 'Settings updated.');
						$('#panel').hide("slide", { direction: "right" }, 500);
					},
					error: function(xhr, reason, thrownError) {
						if (xhr.responseJSON) {
							Halcyon.message('danger', xhr.responseJSON.message);
						} else {
							Halcyon.message('danger', 'Failed to reset permissions.');
						}
						console.log(xhr.responseText);
					}
				});
			});

			$('#permissions-rules').accordion({
				heightStyle: 'content',
				collapsible: true,
				active: false
			});
			$('#permissions-rules .stop-propagation').on('click', function(e) {
				e.stopPropagation();
			});
		});
	});

	/*
	$('.input-datetime input').datetimepicker({
		duration: '',
		showTime: true,
		constrainInput: false,
		stepMinutes: 1,
		stepHours: 1,
		altTimeField: '',
		time24h: true,
		dateFormat: 'yy-mm-dd',
		timeFormat: 'HH:mm:00'
	});*/
});
