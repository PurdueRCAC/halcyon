/* global $ */ // jquery.js
/* global Handlebars */ // handlebars.js
/* global Halcyon */ // core.js

/**
 * Send an email
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSSendMail(btn) {
	// Get text and updates
	$.getJSON(btn.data('article'), function (data) {
		// Gather some  variables from DOM
		var resources = [], resourcelist = [], x;
		if (data['resources'].length > 0) {
			resources = data['resources'];
			for (x = 0; x < data['resources'].length; x++) {
				resourcelist.push(data['resources'][x]['name']);
			}
		}

		var source = $('#mailpreview-template').html(),
			template = Handlebars.compile(source),
			context = {
				"subject": data.headline,
				"formatteddate": data.formatteddate,
				"formattedbody": data.formattedbody,
				"local": data.location,
				"uri": data.uri,
				"updates": data.updates.length ? data.updates : null,
				"resourcelist": resourcelist.join(', '),
				"resources": data.resources.length ? data.resources : null
			},
			html = template(context);

		$('#mailpreview').html(html);

		var to = $('#mail-to');
		to.val('');
		to.tagsInput({
			placeholder: 'Select user...',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteUsers(to.attr('data-uri')),
				dataName: 'users',
				height: 150,
				delay: 100,
				minLength: 1
			}
		});
		to.clearTags();

		for (x = 0; x < data.associations.length; x++) {
			if ($('.tagsinput').length) {
				if (!to.tagExist(data.associations[x]['id'])) {
					to.addTag({
						'id': data.associations[x]['associd'],
						'label': data.associations[x]['name']
					});
				}
			}
		}

		$('#mailpreview').dialog({
			modal: true,
			width: '691px',
			buttons: {
				"Cancel": function () {
					$(this).dialog("close");
				},
				"Send mail": function () {
					$(this).dialog("close");

					var post = {
						'mail': 1//,
						//'lastedit': LASTEDIT[news]
					};

					var resources = [];
					$('.preview-resource').each(function (i, el) {
						if ($(el).is(':checked')) {
							resources.push($(el).val());
						}
					});

					var usersto = document.querySelector("input[name=to]"),
						associations = [];

					if (usersto) {
						var usersdata = usersto.value.split(','),
							i;
						for (i = 0; i < usersdata.length; i++) {
							if (usersdata[i] != "") {
								associations.push(usersdata[i]);
							}
						}
					}

					//if ($('.preview-resource').length != resources.length) {
					post.resources = resources;
					post.associations = associations;
					//}

					$.ajax({
						url: btn.data('api'),
						type: 'put',
						data: post,
						dataType: 'json',
						async: false,
						success: function () {
							//document.getElementById("datetimemail_" + data.id).innerHTML = response.datetimemail;
							Halcyon.message('success', btn.data('success'));
						},
						error: function (xhr) {
							Halcyon.message('danger', xhr.responseJSON.message);
						}
					});
				}
			}
		});
		if ($(".ui-dialog-buttonpane").find("div").length == 1) {
			$(".ui-dialog-buttonpane").prepend('<div style="float:left;padding-top:1em;padding-left:18em">Send this email message?</div>');
		}
		$('#mailpreview').dialog('open');
	});
}

/**
 * Write an email
 *
 * @param   {string}  news
 * @return  {void}
 */
/*function NEWSWriteMail(news) {
	$.getJSON(ROOT_URL + "news/" + news, function (data) {
		$('#mail-subject').val(data.headline);

		var body = '**Date:** ' + data.formatteddate.replace(/(<([^>]+)>)/ig, '').replace(/&nbsp;/g, ' ').replace('&#8211;', '-') + "\n";

		if (data.location) {
			body += '**Location:** ' + data.location + "\n";
		}
		if (data.url) {
			body += '**URL:** ' + data.url + "\n";
		}

		//var name = $( ".login").find( "a" ).first().text();

		$('#mail-body').val(body + "\n\n");

		var to = $('#mail-to');
		to.val('');
		to.tagsInput({
			placeholder: 'Select user...',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteUsers(to.attr('data-uri')),
				dataName: 'users',
				height: 150,
				delay: 100,
				minLength: 1
			}
		});
		to.clearTags();

		var x;
		for (x = 0; x < data.associations.length; x++) {
			if ($('.tagsinput').length) {
				if (!to.tagExist(data.associations[x]['id'])) {
					to.addTag({
						'id': data.associations[x]['associd'],
						'label': data.associations[x]['assocname']
					});
				}
			}
		}

		$('#mailwrite').dialog({
			modal: true,
			width: '691px',
			buttons: {
				"Cancel": function () {
					$(this).dialog("close");
				},
				"Send mail": function () {
					var usersdata = document.getElementById("mail-to").value.split(',');
					var associations = [],
						i;
					for (i = 0; i < usersdata.length; i++) {
						if (usersdata[i] != "") {
							associations.push(usersdata[i]);
						}
					}

					$(this).dialog("close");
					var post = JSON.stringify({
						'mail': 1,
						'lastedit': LASTEDIT[news],
						'headline': $('#mail-subject').val(),
						'news': $('#mail-body').val(),
						'associations': associations
					});
					WSPostURL(ROOT_URL + "news/" + news, post, NEWSSentMail);
				}
			}
		});
		if ($(".ui-dialog-buttonpane").find("div").length == 1) {
			$(".ui-dialog-buttonpane").prepend('<div style="float:left;padding-top:1em;padding-left:18em">Send this email message?</div>');
		}
		$('#mailwrite').dialog('open');
	});
}*/

/**
 * Callback after an email has been sent
 *
 * @param   {object}  xml
 * @param   {string}  news
 * @return  {void}
 */
/*function NEWSSentMail(xml) {
	if (xml.status >= 400) {
		var results = JSON.parse(xml.responseText);

		Halcyon.message('danger', results.message);
	}
}*/

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
		preview_vars["startdate"] = document.getElementById("field-datetimenews").value;
	}

	if (document.getElementById("field-datetimenewsend").value != "") {
		preview_vars["enddate"] = document.getElementById("field-datetimenewsend").value;
	}

	if (type.getAttribute('data-tagresources') == 1) {
		preview_vars["resources"] = [];

		var resources = Array.prototype.slice.call(document.querySelectorAll('#field-resources option:checked'), 0).map(function (v) {
			return v.innerHTML;
		});

		$.each(resources, function (i, el) {
			preview_vars['resources'][i] = el;
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
	/*if (typeof (edit) == 'undefined') {
		edit = false;
	}*/

	var text = document.getElementById("fields-body").value;

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
			document.getElementById("preview").innerHTML = response['formattedbody'];
			$('#preview').dialog({ modal: true, width: '691px' });
			$('#preview').dialog('open');
		},
		error: function (xhr) {
			Halcyon.message('danger', xhr.responseJSON.message);
		}
	});
}

/**
 * Preview news text
 *
 * @param   {string}  example
 * @return  {void}
 */
function PreviewExample(example) {
	var example_vars = {};
	example_vars["startDate"] = new Date();
	var d = new Date();
	d.setDate(d.getDate() + 1);
	example_vars["endDate"] = d;
	example_vars["resources"] = ["Anvil", "Bell"];//[{"resourcename": "Carter"}, {"resourcename": "Conte"}];
	example_vars["location"] = "Envision Center";

	var post = {
		'body': document.getElementById('help1' + example + 'input').value,
		'vars': example_vars
	};

	$.ajax({
		url: document.getElementById('markdown-help').getAttribute('data-api'),
		type: 'post',
		data: post,
		dataType: 'json',
		async: false,
		success: function (response) {
			document.getElementById('help1' + example + 'output').innerHTML = response['formattedbody'];
		},
		error: function (xhr) {
			Halcyon.message('danger', xhr.responseJSON.message);
		}
	});
}

function autocompleteUsers(url) {
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
}
/*
function autocompleteResources(url) {
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
}*/

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
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

	// Dialogs
	if ($('.samplebox').length) {
		$('.samplebox').on('keyup', function () {
			PreviewExample($(this).data('sample'));
		});

		$('#markdown-help').tabs();

		//Load the formatting guide example variables into the text box.
		//PreviewExample('h');
	}

	//$('#markdown').tabs();

	/*var newsresource = $(".form-resources");
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
	}*/
	$('#field-template').on('change', function () {
		if ($(this).is(':checked')) {
			$('.template-hide').addClass('hide');
		} else {
			$('.template-hide').removeClass('hide');
		}
	});

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

	$('.news-mail').on('click', function (e) {
		e.preventDefault();
		NEWSSendMail($(this));
	});

	$('#template_select').on('change', function () {
		var template = this.options[this.selectedIndex].value;

		if (template == "0") {
			return;
		}

		var overwrite = false;
		if ($("#field-headline").val() != "") {
			overwrite = true;
		}
		if ($("#field-body").val() != "") {
			overwrite = true;
		}

		if (overwrite) {
			if (!confirm("Are you sure you wish to overwrite text with this template? Any work will be lost.")) {
				this.selectedIndex = 0;
				return;
			}
		}

		$.ajax({
			url: template,
			type: 'get',
			dataType: 'json',
			async: false,
			success: function (response) {
				$("#field-headline").val(response.headline.replace(/&#039;/g, "'").replace(/&quot;/g, '"'));
				$("#field-body").val(response.body.replace(/&#039;/g, "'").replace(/&quot;/g, '"'));
				$('#field-location').val(response.location);
				$('#field-url').val(response.url);

				var resources = [], x;
				for (x = 0; x < response.resources.length; x++) {
					resources.push(response.resources[x]['resourceid']);
				}

				$('#field-resources')
					.val(resources)
					.trigger('change');

				$('#field-newstypeid')
					.val(response.newstypeid)
					.trigger('change');
			},
			error: function (xhr) {
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	if ($("#copy-article").length) {
		var cdialog = $("#copy-article").dialog({
			autoOpen: false,
			height: 250,
			width: 500,
			modal: true
		});

		$('#toolbar-copy>.btn-copy').removeClass('toolbar-submit')
			.off('click')
			.on('click', function (e) {
				e.preventDefault();
				cdialog.dialog("open");
			});

		$("#copy-article").find('.btn').on('click', function (e) {
			e.preventDefault();
			$(this).closest('form').append($('#adminForm').find('input:checked')).submit();
		});
	}
});
