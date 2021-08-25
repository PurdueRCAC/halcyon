

jQuery(document).ready(function ($) {
	// Feedback
	$('.btn-feedback').on('click', function (e) {
		e.preventDefault();

		$('#feedback-state').removeClass('hide');
		var lbl = $('#feedback-label'),
			val = $(this).data('feedback-text');

		$('#feedback-type').val($(this).data('feedback-type'));

		lbl.text($('#feedback-text').data(val + '-label'));
		$('#feedback-response').text($('#feedback-response').data(val + '-label'));

		$('#question-state').addClass('hide');
	});

	$('#submit-feedback').on('click', function (e) {
		e.preventDefault();

		// Honeypot was filled
		if ($('#feedback-hpt').val()) {
			return;
		}

		$('#feedback-state').addClass('hide');

		var frm = $($(this).closest('form'));

		$.ajax({
			url: frm.data('api'),
			type: 'post',
			data: frm.serialize(),
			dataType: 'json',
			async: false,
			success: function (response) {
				$('#rating-done').removeClass('hide');
			},
			error: function (xhr, ajaxOptions, thrownError) {
				$('#rating-error').removeClass('hide');
			}
		});
	});

	//----

	$('[data-max-length]').on('keyup', function () {
		var chars = $(this).val().length,
			max = parseInt($(this).data('max-length')),
			ctr = $(this).parent().find('.char-count');

		if (chars) {
			ctr.removeClass('hide');
		} else {
			ctr.addClass('hide');
		}
		ctr.text(max - chars);

		if (chars >= max) {
			var trimmed = $(this).val().substring(0, max);
			$(this).val(trimmed);
		}
	});

	var alias = $('#field-alias');
	if (alias.length) {
		$('#field-title').on('keyup', function (e) {
			var val = $(this).val();

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');

			alias.val(val);
		});
	}

	$('#field-parent_id')
		.on('change', function () {
			$('#parent-path').html($(this).children("option:selected").data('path'));
		});

	// Page editing
	$('#content')
		// Add confirm dialog to delete links
		.on('click', 'a.delete', function (e) {
			var res = confirm($(this).attr('data-confirm'));
			if (!res) {
				e.preventDefault();
			}
			return res;
		})
		.on('click', 'a.edit,a.cancel', function (e) {
			e.preventDefault();

			var id = $(this).attr('data-id');

			$('#page-form' + id).toggleClass('hide');
			$('#page-content' + id).toggleClass('hide');
		});

	$('#pageform').on('submit', function (e) {
		e.preventDefault();

		var frm = $(this),
			invalid = false;

		var elms = frm.find('input[required]');
		elms.each(function (i, el) {
			if (!el.value || !el.validity.valid) {
				el.classList.add('is-invalid');
				invalid = true;
			} else {
				el.classList.remove('is-invalid');
			}
		});
		var elms = frm.find('select[required]');
		elms.each(function (i, el) {
			if (!el.value || el.value <= 0) {
				el.classList.add('is-invalid');
				invalid = true;
			} else {
				el.classList.remove('is-invalid');
			}
		});
		var elms = frm.find('textarea[required]');
		elms.each(function (i, el) {
			if (!el.value || !el.validity.valid) {
				el.classList.add('is-invalid');
				invalid = true;
			} else {
				el.classList.remove('is-invalid');
			}
		});

		if (invalid) {
			return false;
		}

		var btn = $('#save-page');
		btn.addClass('processing');
		/*var post = frm.serializeArray().reduce(function(obj, item) {
			obj[item.name] = item.value;
			return obj;
		}, {});*/

		//const frm = document.querySelector('form');
		//const data = Object.fromEntries(new FormData(frm).entries());

		var post = {},
			k,
			fields = frm.serializeArray();
		for (var i = 0; i < fields.length; i++) {
			if (fields[i].name.substring(0, 6) == 'params') {
				if (typeof (post['params']) === 'undefined') {
					post['params'] = {};
				}
				k = fields[i].name.substring(7);

				post['params'][k.substring(0, k.length - 1)] = fields[i].value;
			} else {
				post[fields[i].name] = fields[i].value;
			}
		}

		$.ajax({
			url: frm.data('api'),
			type: (post['id'] ? 'put' : 'post'),
			data: post,
			dataType: 'json',
			async: false,
			success: function (response) {
				if (response.url) {
					window.location.href = response.url;
				} else {
					window.location.reload();
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				btn.removeClass('processing');
				frm.prepend('<div class="alert alert-danger">' + xhr.responseJSON.message + '</div>');
			}
		});
	});

	//----

	var dialog = $("#new-page").dialog({
		autoOpen: false,
		height: 250,
		width: 500,
		modal: true
	});

	$('#add-page').on('click', function (e) {
		e.preventDefault();

		dialog.dialog("open");
	});

	//----

	$('.snippet-checkbox').on('change', function (e) {
		if ($(this).is(':checked')) {
			$('tr[data-parent=' + $(this).data('id') + ']')
				.find('.snippet-checkbox')
				.each(function (i, el) {
					$(el).prop('checked', true).trigger('change');
				});
		} else {
			$('tr[data-parent=' + $(this).data('id') + ']')
				.find('.snippet-checkbox')
				.each(function (i, el) {
					$(el).prop('checked', false).trigger('change');
				});
		}
	});
	$('.toggle-tree').on('click', function (e) {
		e.preventDefault();

		$(this).closest('tr').toggleClass('open');

		$('tr[data-parent=' + $(this).data('id') + ']')
			.toggleClass('d-none');
	});
});
