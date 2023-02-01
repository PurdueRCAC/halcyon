/* global $ */ // jquery.js
/* global Handlebars */ // handlebars.js
/* global Halcyon */ // core.js

var headers = {
	'Content-Type': 'application/json'
};

/**
 * Send an email
 *
 * @param   {object}  btn
 * @return  {void}
 */
function NEWSSendMail(btn) {
	// Get text and updates
	fetch(btn.getAttribute('data-article'), {
		method: 'GET',
		headers: headers
	})
	.then(function (response) {
		if (response.ok) {
			return response.json();
		}
		return response.json().then(function (data) {
			var msg = data.message;
			if (typeof msg === 'object') {
				msg = Object.values(msg).join('<br />');
			}
			throw msg;
		});
	})
	.then(function (data) {
		// Gather some  variables from DOM
		var resources = [],
			resourcelist = [],
			x;
		if (data['resources'].length > 0) {
			resources = data['resources'];
			for (x = 0; x < data['resources'].length; x++) {
				resourcelist.push(data['resources'][x]['name']);
			}
		}

		var source = document.getElementById('mailpreview-template').innerHTML,
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

		document.getElementById('mailpreview').innerHTML = html;

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

		document.getElementById('mailsend').addEventListener('click', function (e) {
			e.preventDefault();

			var post = {
				'mail': 1//,
				//'lastedit': LASTEDIT[news]
			};

			var resources = [];
			document.querySelectorAll('.preview-resource').forEach(function (el) {
				if (el.checked) {
					resources.push(el.value);
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

			post.resources = resources;
			post.associations = associations;

			post = JSON.stringify(post);

			fetch(btn.getAttribute('data-api'), {
				method: 'PUT',
				headers: headers,
				body: post
			})
			.then(function (response) {
				if (response.ok) {
					Halcyon.message('success', btn.data('success'));
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
		alert(err);
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

		resources.forEach(function(el){
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
	var text = document.getElementById("fields-body").value;

	if (text == "") {
		return;
	}

	var post = {
		'id': btn.getAttribute('data-id'),
		'body': text
	};

	post['vars'] = NEWSPreviewVars();
	post['news'] = btn.getAttribute('data-api');

	fetch(btn.getAttribute('data-api'), {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
		},
		body: JSON.stringify(post)
	})
	.then(function (response) {
		if (response.ok) {
			return response.json();
		}
		return response.json().then(function (data) {
			var msg = data.message;
			if (typeof msg === 'object') {
				msg = Object.values(msg).join('<br />');
			}
			throw msg;
		});
	})
	.then(function(json) {
		document.getElementById("preview").innerHTML = json['formattedbody'];
	})
	.catch(function (error) {
		Halcyon.message('danger', error);
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

	fetch(document.getElementById('markdown-help').getAttribute('data-api'), {
		method: 'POST',
		headers: headers,
		body: JSON.stringify(post)
	})
	.then(function (response) {
		if (response.ok) {
			return response.json();
		}
		return response.json().then(function (data) {
			var msg = data.message;
			if (typeof msg === 'object') {
				msg = Object.values(msg).join('<br />');
			}
			throw msg;
		});
	})
	.then(function (results) {
		document.getElementById('help1' + example + 'output').innerHTML = results['formattedbody'];
	})
	.catch(function (err) {
		Halcyon.message('danger', xhr.responseJSON.message);
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
	headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
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

	var template = document.getElementById('field-template');
	if (template) {
		template.addEventListener('change', function () {
			if (this.checked) {
				document.querySelectorAll('.template-hide').forEach(function(el) {
					el.classList.add('hide');
				});
			} else {
				document.querySelectorAll('.template-hide').forEach(function (el) {
					el.classList.remove('hide');
				});
			}
		});
	}

	document.querySelectorAll('.basic-multiple').forEach(function(el) {
		$(el).select2({
			placeholder: el.getAttribute('data-placeholder')
		});
	});

	var newstype = document.getElementById('field-newstypeid');
	if (newstype) {
		newstype.addEventListener('change', function () {
			var selected = this.options[this.selectedIndex];

			document.querySelectorAll('.type-option').forEach(function (el) {
				el.classList.add('d-none');
			});

			if (selected.getAttribute('data-tagresources') == '1') {
				document.querySelectorAll('.type-tagresources').forEach(function(el) {
					el.classList.remove('d-none');
				});
			}

			if (selected.getAttribute('data-tagusers') == '1') {
				document.querySelectorAll('.type-tagusers').forEach(function (el) {
					el.classList.remove('d-none');
				});
			}

			if (selected.getAttribute('data-location') == '1') {
				document.querySelectorAll('.type-location').forEach(function (el) {
					el.classList.remove('d-none');
				});
			}

			if (selected.getAttribute('data-url') == '1') {
				document.querySelectorAll('.type-url').forEach(function (el) {
					el.classList.remove('d-none');
				});
			}
		});
	}

	document.querySelectorAll('.preview').forEach(function(el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			NEWSPreview(this);
		});
	});

	document.querySelectorAll('.news-mail').forEach(function(el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			NEWSSendMail(this);
		});
	});

	var templatesel = document.getElementById('template_select');
	if (templatesel) {
		templatesel.addEventListener('change', function () {
			var template = this.options[this.selectedIndex].value;

			if (template == "0") {
				return;
			}

			var overwrite = false;
			if (document.getElementById("field-headline").value != "") {
				overwrite = true;
			}
			if (document.getElementById("field-body").value != "") {
				overwrite = true;
			}

			if (overwrite) {
				if (!confirm("Are you sure you wish to overwrite text with this template? Any work will be lost.")) {
					this.selectedIndex = 0;
					return;
				}
			}

			fetch(template, {
				method: 'GET',
				headers: headers
			})
			.then(function (response) {
				if (response.ok) {
					return response.json();
				}

				return response.json().then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				});
			})
			.then(function (response) {
				document.getElementById("field-headline").value = response.headline.replace(/&#039;/g, "'").replace(/&quot;/g, '"');
				var body = document.getElementById("field-body");
				body.value = response.body.replace(/&#039;/g, "'").replace(/&quot;/g, '"');
				body.dispatchEvent(new Event('refreshEditor'));
				document.getElementById('field-location').value = response.location;
				document.getElementById('field-url').value = response.url;

				var resources = [], x;
				for (x = 0; x < response.resources.length; x++) {
					resources.push(response.resources[x]['resourceid']);
				}

				var res = document.getElementById('field-resources');
				res.value = resources;
				res.dispatchEvent(new Event('change'));

				var newstype = document.getElementById('field-newstypeid');
				newstype.value = response.newstypeid;
				newstype.dispatchEvent(new Event('change'));
			})
			.catch(function (error) {
				Halcyon.message('danger', error);
			});
		});
	}

	var copy = document.getElementById('copy-article');
	if (copy) {
		copy.querySelector('.btn').addEventListener('click', function (e) {
			e.preventDefault();
			var frm = this.closest('form');
			document.getElementById('adminForm').querySelectorAll('input:checked').forEach(function(input) {
				frm.appendChild(input);
			})
			frm.submit();
		});

		document.querySelectorAll('.btn-copy').forEach(function(btncopy){
			btncopy.setAttribute('data-toggle', 'modal');
			btncopy.setAttribute('href', '#copy-article');
			btncopy.replaceWith(btncopy.cloneNode(true));
			btncopy.addEventListener('click', function (e) {
				e.preventDefault();
				document.getElementById('adminForm').addEventListener('submit', function(e){
					e.preventDefault();
					return false;
				});
			});
		});
	}
});
