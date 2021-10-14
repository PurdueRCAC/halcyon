/* global $ */ // jquery.js
/* global SetError */ // common.js

/**
 * Unix base groups
 *
 * @const
 * @type  {array}
 */
var BASEGROUPS = Array('', 'data', 'apps');

/**
 * Create UNIX group
 *
 * @param   {integer}  num    index for BASEGROUPS array
 * @param   {string}   group
 * @return  {void}
 */
function CreateNewGroupVal(num, btn, all) {
	var group = btn.data('group');
	//var base = btn.data('value');

	if (typeof (all) == 'undefined') {
		all = true;
	}

	// The callback only accepts one argument, so we
	// need to compact this
	//var args = [num, group];
	var post = {
		'longname': BASEGROUPS[num],
		'groupid': group
	};

	$.ajax({
		url: btn.data('api'),
		type: 'post',
		data: post,
		dataType: 'json',
		async: false,
		success: function () {
			num++;
			if (all && num < BASEGROUPS.length) {
				setTimeout(function () {
					CreateNewGroupVal(num, btn, all);
				}, 5000);
			} else {
				window.location.reload(true);
			}
		},
		error: function (xhr) {
			btn.find('.spinner-border').addClass('d-none');
			alert(xhr.responseJSON.message);
		}
	});
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	if ($.fn.select2) {
		$('.searchable-select').select2();
	}

	$('.input-unixgroup').on('keyup', function () {
		var val = $(this).val();

		val = val.toLowerCase()
			.replace(/\s+/g, '-')
			.replace(/[^a-z0-9-]+/g, '');

		$(this).val(val);
	});

	$('.create-default-unix-groups').on('click', function (e) {
		e.preventDefault();

		$(this).attr('data-loading', true);

		CreateNewGroupVal(0, $(this), parseInt($(this).data('all-groups')));
	});

	$('.edit-categories').on('click', function (e) {
		e.preventDefault();

		var container = $($(this).attr('href'));
		container.find('.edit-show').removeClass('hide');
		container.find('.edit-hide').addClass('hide');
	});
	$('.cancel-categories').on('click', function (e) {
		e.preventDefault();

		var container = $($(this).attr('href'));
		container.find('.edit-hide').removeClass('hide');
		container.find('.edit-show').addClass('hide');
	});

	$('.add-category').on('click', function (e) {
		e.preventDefault();

		var select = $($(this).attr('href'));
		var btn = $(this);

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'post',
			data: {
				'groupid': btn.data('group'),
				[select.data('category')]: select.val()
			},
			dataType: 'json',
			async: false,
			success: function (response) {
				var c = select.closest('ul');
				var li = c.find('li.hide');

				if (typeof (li) !== 'undefined') {
					var template = $(li)
						.clone()
						.removeClass('hide');

					template
						.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
						.data('id', response.id);

					template.find('a').each(function (i, el) {
						$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.id));
					});

					var content = template
						.html()
						.replace(/\{id\}/g, response.id)
						.replace(/\{name\}/g, select.find('option:selected').text());

					template.html(content).insertBefore(li);
				}

				select.val(0);
			},
			error: function (xhr) {
				SetError(xhr.responseJSON.message);
			}
		});
	});

	$('body').on('click', '.remove-category', function (e) {
		e.preventDefault();

		var result = confirm($(this).data('confirm'));

		if (result) {
			var field = $($(this).attr('href'));

			// delete relationship
			$.ajax({
				url: $(this).data('api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function () {
					field.remove();
				},
				error: function (xhr) {
					SetError(xhr.responseJSON.message);
				}
			});
		}
	});

	$('#longname').on('change', function () {
		this.classList.remove('is-invalid');
		this.classList.remove('is-valid');

		if (this.value) {
			if (this.validity.valid) {
				this.classList.add('is-valid');
			} else {
				this.classList.add('is-invalid');
			}
		}
	});

	$('.add-unixgroup').on('click', function (e) {
		e.preventDefault();

		var name = $($(this).attr('href'));
		var btn = $(this);

		name.removeClass('is-invalid').removeClass('is-valid');

		if (name.val() && name[0].validity.valid) {
			name.addClass('is-valid');
		} else {
			name.addClass('is-invalid');
			return false;
		}

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'post',
			data: {
				'groupid': btn.data('group'),
				'longname': name.val()
			},
			dataType: 'json',
			async: false,
			success: function (response) {
				var c = $(btn.data('container'));
				var li = c.find('tr.hidden');

				if (typeof (li) !== 'undefined') {
					var template = $(li)
						.clone()
						.removeClass('hidden');

					template
						.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
						.data('id', response.id);

					template.find('a').each(function (i, el) {
						$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.id));
					});

					var content = template
						.html()
						.replace(/\{id\}/g, response.id)
						.replace(/\{longname\}/g, response.longname)
						.replace(/\{shortname\}/g, response.shortname);

					template.html(content).insertBefore(li);
					$('.dialog-help').dialog('close');
				}

				name.val('');
			},
			error: function (xhr) {
				name.addClass('is-invalid');

				$(btn.attr('data-error')).removeClass('hide').html(xhr.responseJSON.message);
			}
		});
	});

	$('body').on('click', '.remove-unixgroup', function (e) {
		e.preventDefault();

		var result = confirm($(this).data('confirm'));

		if (result) {
			var obj = {
				'groupid': $(this).data('value')
			};

			// delete relationship
			$.ajax({
				url: $(this).data('api'),
				type: 'delete',
				data: obj,
				dataType: 'json',
				async: false,
				success: function () {
					window.location.reload(true);
				},
				error: function () { //xhr, ajaxOptions, thrownError
					var notice = $("#deletegroup_" + obj['groupid']);

					if (notice.length) {
						notice
							.removeClass('hide')
							.text("An error occured while deleting group.");
					}
				}
			});
		}
	});

	$('[maxlength]').each(function (i, el) {
		var container = $('<span class="char-counter-wrap"></span>');
		var counter = $('<span class="char-counter"></span>');
		var input = $(el);

		if (input.attr('id') != '') {
			counter.attr('id', input.attr('id') + '-counter');
		}

		if (input.parent().hasClass('input-group')) {
			input.parent().wrap(container);
			counter.insertAfter(input.parent());
		} else {
			input.wrap(container);
			counter.insertAfter(input);
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
