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
	var regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	//var regex = new RegExp("^[\\w-_.]*[\\w-_.]@[\\w]\.+[\\w]+[\\w]$");
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
	var toolbarbuttons = document.getElementsByClassName('toolbar-btn');
	for (var i = 0; i < toolbarbuttons.length; i++) {
		if (Halcyon.hasClass(toolbarbuttons[i], 'toolbar-list')) {
			if (!num) {
				Halcyon.addClass(toolbarbuttons[i], 'disabled');
			} else {
				Halcyon.removeClass(toolbarbuttons[i], 'disabled');
			}
		} else {
			if (num) {
				Halcyon.addClass(toolbarbuttons[i], 'disabled');
			} else {
				Halcyon.removeClass(toolbarbuttons[i], 'disabled');
			}
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
 * @return  void
 */
Halcyon.removeMessages = function() {
	document.querySelectorAll('#system-messages > *').forEach(function(el) {
		el.remove();
	});
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
	var winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable';

	var win = window.open(mypage, myname, winprops);
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
	if (!el || !className) {
		return false;
	}
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
	if (!el) {
		return;
	}
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
	if (!el) {
		return;
	}
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
				Halcyon.submitbutton(el.getAttribute('data-action'));
			} else if (el.getAttribute('href')) {
				Halcyon.submitbutton(el.getAttribute('href'));
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
Halcyon.filterSubmit = function()
{
	this.form.submit();
}

/**
 * Clear filters in a form and submit
 *
 * @param   event
 * @return  void
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
 * @param   event
 * @return  void
 */
Halcyon.gridCheckboxToggle = function()
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
		limits[i].addEventListener('change', function(){
			Halcyon.submitform();
		});
	}
}

/* exported ROOT_URL */
var ROOT_URL = '/api/';

// this function returns a HttpRequest object
function GetXmlHttpObject() {
	var xmlHttp = null;

	try {
		// Firefox, Opera 8.0+, Safari
		xmlHttp = new XMLHttpRequest();
	}
	catch (e) {
		/* eslint-disable */
		//Internet Explorer
		try {
			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e) {
			xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		/* eslint-enable */
	}

	return xmlHttp;
}

/**
 * Legacy AJAX
 */
/* exported WSGetURL */
function WSGetURL(id, result_function, arg1) {
	if (id.substring(0, 4) != 'http') {
		if (id.substring(0, 1) != '/') {
			id = '/' + id;
		}
		id = 'https://' + window.location.hostname + id;
	}
	var url = id;
	var xml = GetXmlHttpObject();
	xml.onreadystatechange = function () {
		if (xml.readyState == 4 || xml.readyState == "complete") {
			// Authentication is stale, kick the page around
			if (xml.status == 408) {
				window.location.reload(true);
			}
			if (result_function) {
				if (typeof (arg1) != 'undefined') {
					result_function(xml, arg1);
				} else {
					result_function(xml);
				}
			}
		}
	}
	xml.open('GET', url, true);
	xml.setRequestHeader('Authorization', 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'));
	xml.send(null);
}

/* exported WSPostURL */
function WSPostURL(id, json, result_function, arg1) {
	if (id.substring(0, 4) != 'http') {
		if (id.substring(0, 1) != '/') {
			id = '/' + id;
		}
		id = 'https://' + window.location.hostname + id;
	}
	var url = id;
	var xml = GetXmlHttpObject();
	xml.onreadystatechange = function () {
		if (xml.readyState == 4 || xml.readyState == "complete") {
			// Authentication is stale, kick the page around
			if (xml.status == 408) {
				window.location.reload(true);
			}
			if (typeof (arg1) != 'undefined') {
				result_function(xml, arg1);
			}
			else {
				result_function(xml);
			}
		}
	}
	xml.open("POST", url, true);
	xml.setRequestHeader("Content-type", "application/json");
	xml.setRequestHeader('Authorization', 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'));
	xml.send(json);
}

/* exported WSPutURL */
function WSPutURL(id, json, result_function, arg1) {
	if (id.substring(0, 4) != 'http') {
		if (id.substring(0, 1) != '/') {
			id = '/' + id;
		}
		id = 'https://' + window.location.hostname + id;
	}
	var url = id;
	var xml = GetXmlHttpObject();
	xml.onreadystatechange = function () {
		if (xml.readyState == 4 || xml.readyState == "complete") {
			// Authentication is stale, kick the page around
			if (xml.status == 408) {
				window.location.reload(true);
			}
			if (typeof (arg1) != 'undefined') {
				result_function(xml, arg1);
			}
			else {
				result_function(xml);
			}
		}
	}
	xml.open("PUT", url, true);
	xml.setRequestHeader("Content-type", "application/json");
	xml.setRequestHeader('Authorization', 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'));
	xml.send(json);
}

/* exported WSDeleteURL */
function WSDeleteURL(id, result_function, arg1) {
	if (id.substring(0, 4) != 'http') {
		if (id.substring(0, 1) != '/') {
			id = '/' + id;
		}
		id = 'https://' + window.location.hostname + id;
	}
	var url = id;
	var xml = GetXmlHttpObject();
	xml.onreadystatechange = function () {
		if (xml.readyState == 4 || xml.readyState == "complete") {
			// Authentication is stale, kick the page around
			if (xml.status == 408) {
				window.location.reload(true);
			}
			if (typeof (arg1) != 'undefined') {
				result_function(xml, arg1);
			} else {
				result_function(xml);
			}
		}
	}
	xml.open("DELETE", url, true);
	xml.setRequestHeader('Authorization', 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'));
	xml.send(null);
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function() {
	var i;

	ROOT_URL = $('meta[name="base-url"]').attr('content') + '/api/';

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
	var ordering = document.getElementsByClassName('grid-order');
	for (i = 0; i < ordering.length; i++)
	{
		ordering[i].addEventListener('click', Halcyon.gridOrder);
	}

	// Add event listener for saving table sorting
	var orderingsave = document.getElementsByClassName('grid-order-save');
	for (i = 0; i < orderingsave.length; i++)
	{
		orderingsave[i].addEventListener('click', Halcyon.gridOrderSave);
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

	$('.tabs').tabs();

	$('.dialog-help').dialog({
		autoOpen: false,
		modal: true,
		width: 600
	});

	$('.help-dialog').on('click', function (e) {
		e.preventDefault();

		if ($($(this).attr('href')).length) {
			$($(this).attr('href')).dialog('open');
		}
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

	$('.btn-settings').on('click', function(e){
		e.preventDefault();

		$('<div class="ui-widget-overlay ui-front" style="z-index: 100;"></div>').appendTo('body');
		$('<div id="panel" style="z-index: 101;"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>').appendTo('body');
		var panel = $('#panel');
		panel
			.show("slide", { direction: "right" }, 500);

		$.get($(this).attr('href'), function(response){
			panel
				.html(response);

			panel.find('.btn-cancel').on('click', function(e){
				e.preventDefault();
				$('#panel').hide("slide", { direction: "right" }, 500);
				$('.ui-widget-overlay').remove();
			});

			panel.find('.btn-save').on('click', function(e){
				e.preventDefault();

				var frm = $(this).closest('form');

				$.ajax({
					url: $(this).attr('href'),
					type: 'post',
					data: frm.serialize(),
					//dataType: 'json',
					async: false,
					success: function() {
						$('#panel').hide("slide", { direction: "right" }, 500);
						$('.ui-widget-overlay').remove();
					},
					error: function (xhr) { //xhr, reason, thrownError
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
			$('.tabs').tabs();
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

	$('[maxlength]').each(function (i, el) {
		if (el.getAttribute('data-counter') && el.getAttribute('data-counter') == 'false') {
			return;
		}
		var container = $('<span class="char-counter-wrap"></span>');
		var counter = $('<span class="char-counter"></span>');
		var input = $(el);

		if (input.attr('id') != '') {
			counter.attr('id', input.attr('id') + '-counter');
		}

		if (input.parent().hasClass('input-group')) {
			input.parent().wrap(container);
			counter.insertBefore(input.parent());
		} else {
			input.wrap(container);
			counter.insertBefore(input);
		}
		counter.text(input.val().length + ' / ' + input.attr('maxlength'));

		input
			.on('focus', function () {
				var container = $(this).closest('.char-counter-wrap');
				if (container.length) {
					container.addClass('char-counter-focus');
				}
			})
			.on('blur', function () {
				var container = $(this).closest('.char-counter-wrap');
				if (container.length) {
					container.removeClass('char-counter-focus');
				}
			})
			.on('keyup', function () {
				var chars = $(this).val().length;
				var counter = $('#' + $(this).attr('id') + '-counter');
				if (counter.length) {
					counter.text(chars + ' / ' + $(this).attr('maxlength'));
				}
			});
	});
});
