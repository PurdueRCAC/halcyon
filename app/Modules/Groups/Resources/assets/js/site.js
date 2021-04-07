/* global $ */ // jquery.js
/* global ROOT_URL */ // common.js
/* global WSGetURL */ // common.js
/* global WSPostURL */ // common.js
/* global WSDeleteURL */ // common.js
/* global SetError */ // common.js
/* global PrepareText */ // common.js
/* global HighlightMatches */ // text.js

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
	var base = btn.data('value'),
		group = btn.data('group');

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
	console.log(post);
	$.ajax({
		url: btn.data('api'),
		type: 'post',
		data: {
			'longname': BASEGROUPS[num],
			'groupid': group
		},
		dataType: 'json',
		async: false,
		success: function (response) {
			num++;
			if (all && num < BASEGROUPS.length) {
				setTimeout(function () {
					CreateNewGroupVal(num, btn, all);
				}, 5000);
			} else {
				//Halcyon.message('success', 'Item added');
				window.location.reload(true);
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			//console.log(xhr);
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

	/*$('#main').on('change', '.membertype', function () {
		$.ajax({
			url: $(this).data('api'),
			type: 'put',
			data: { membertype: $(this).val() },
			dataType: 'json',
			async: false,
			success: function (data) {
				Halcyon.message('success', 'Member type updated!');
			},
			error: function (xhr, ajaxOptions, thrownError) {
				Halcyon.message('danger', 'Failed to update member type.');
			}
		});
	});*/

	$('.input-unixgroup').on('keyup', function (e) {
		var val = $(this).val();

		val = val.toLowerCase()
			.replace(/\s+/g, '-')
			.replace(/[^a-z0-9\-]+/g, '');

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
				var li = c.find('li.hidden');

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
						.replace(/\{name\}/g, select.find('option:selected').text());

					template.html(content).insertBefore(li);
				}

				select.val(0);

				//SetAction('Item added', null);
			},
			error: function (xhr, ajaxOptions, thrownError) {
				//console.log(xhr);
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
				success: function (data) {
					field.remove();
					//SetAction('Item removed', null);
				},
				error: function (xhr, ajaxOptions, thrownError) {
					SetError(xhr.responseJSON.message);
				}
			});
		}
	});

	$('.add-unixgroup').on('click', function (e) {
		e.preventDefault();

		var name = $($(this).attr('href'));
		var btn = $(this);

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

				//SetAction('Item added', null);
			},
			error: function (xhr, ajaxOptions, thrownError) {
				//console.log(xhr);
				$('.help').dalog('close');
				alert(xhr.responseJSON.message);
				//SetError(xhr.responseJSON.message);
			}
		});
	});

	$('body').on('click', '.remove-unixgroup', function (e) {
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
				success: function (data) {
					field.remove();
					//SetAction('Item removed');
				},
				error: function (xhr, ajaxOptions, thrownError) {
					alert(xhr.responseJSON.message);
					//SetError(xhr.responseJSON.message);
				}
			});
		}
	});
});