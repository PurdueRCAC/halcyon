/* global $ */ // jquery.js

/**
 * Build vars for news preview
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSPreviewVars() {
	var preview_vars = {};
	var type = document.querySelector("#field-newstypeid option:checked");

	/* Grab the variables we need and populate the preview variables. */
	if (document.getElementById("field-datetimenews").value != "") {
		preview_vars["startDate"] = document.getElementById("field-datetimenews").value;
	}

	if (document.getElementById("field-datetimenewsend").value != "") {
		preview_vars["endDate"] = document.getElementById("field-datetimenewsend").value;
	}

	if (type.getAttribute('data-tagresources') == 1) {
		preview_vars["resources"] = [];

		var resources = Array.prototype.slice.call(document.querySelectorAll('#field-resources option:checked'), 0).map(function (v) {
			return v.innerHTML;
		});

		$.each(resources, function (i, el) {
			preview_vars['resources'][i] = { "resourcename": el };
		});
	}
	preview_vars['update'] = "0";

	if (type.getAttribute('data-location') == 1) {
		if (document.getElementById("field-location").value != "") {
			preview_vars["location"] = document.getElementById("field-location").value;
		}
	}

	return preview_vars;
}

/**
 * Preview news entry
 *
 * @param   {object}  btn
 * @return  {void}
 */
function NEWSPreview(btn) {
	if (typeof (edit) == 'undefined') {
		edit = false;
	}

	var text = document.getElementById("field-body").value;

	if (text == "") {
		return;
	}

	var post = {
		'id': btn.data('id'),
		'body': text
	};

	post['vars'] = NEWSPreviewVars();
	post['news'] = btn.data('api');

	$.ajax({
		url: btn.data('api'),
		type: 'post',
		data: post,
		dataType: 'json',
		async: false,
		success: function (response) {
			console.log(response);
			document.getElementById("preview").innerHTML = response['formattedbody'];
			$('#preview').dialog({ modal: true, width: '691px' });
			$('#preview').dialog('open');
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(xhr);
			Halcyon.message('danger', xhr.responseJSON.message);
		}
	});
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	var autocompleteUsers = function (url) {
		return function (request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
				response($.map(data.data, function (el) {
					return {
						label: el.name + ' (' + el.username + ')',
						name: el.name,
						id: el.id,
					};
				}));
			});
		};
	};

	var autocompleteResources = function (url) {
		return function (request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
				response($.map(data.data, function (el) {
					return {
						label: el.name,
						name: el.name,
						id: el.id,
					};
				}));
			});
		};
	};

	var newsuser = $(".form-users");
	if (newsuser.length) {
		newsuser.tagsInput({
			placeholder: 'Select user...',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteUsers(newsuser.attr('data-uri')),
				dataName: 'data',
				height: 150,
				delay: 100,
				minLength: 1
			}
		});
	}

	var newsresource = $(".form-resources");
	if (newsresource.length) {
		newsresource.tagsInput({
			placeholder: 'Select resource...',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteResources(newsresource.attr('data-uri')),
				dataName: 'data',
				height: 150,
				delay: 100,
				minLength: 1
			}
		});
	}

	$('.basic-multiple').select2({
		placeholder: $(this).data('placeholder')
	});

	$('#field-newstypeid').on('change', function () {
		var selected = $($(this).children('option:selected'));

		$('.type-option').addClass('d-none');

		if (selected.data('tagresources')) {
			$('.type-tagresources').removeClass('d-none');
		}

		if (selected.data('tagusers')) {
			$('.type-tagusers').removeClass('d-none');
		}

		if (selected.data('location')) {
			$('.type-location').removeClass('d-none');
		}

		if (selected.data('url')) {
			$('.type-url').removeClass('d-none');
		}
	});

	$('.preview').on('click', function (e) {
		e.preventDefault();

		NEWSPreview($(this));
	});
});
