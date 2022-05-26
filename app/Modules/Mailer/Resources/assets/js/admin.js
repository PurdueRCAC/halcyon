
/* global $ */ // jquery.js
/* global Halcyon */ // core.js

/**
 * Get and return array of objects
 *
 * @param   {string}  url
 * @return  {array}
 */
var autocompleteList = function (url) {
	return function (request, response) {
		return $.getJSON(url.replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
			response($.map(data.data, function (el) {
				if (typeof (el.id) == 'undefined' && typeof (el.username) != 'undefined') {
					el.id = el.username;
				}
				if (typeof (el.username) != 'undefined') {
					el.name += ' (' + el.username + ')';
				}
				if (typeof (el.slug) != 'undefined') {
					el.id = el.slug;
				}
				//var regEx = new RegExp("(" + request.term + ")(?!([^<]+)?>)", "gi");
				//el.name = el.name.replace(regEx, '<span class="highlight">$1</span>');
				return {
					label: el.name,
					name: el.name,
					id: el.id,
				};
			}));
		});
	};
};

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	var newsuser = $(".form-users");
	if (newsuser.length) {
		newsuser.tagsInput({
			placeholder: '',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteList(newsuser.attr('data-uri')),
				dataName: 'users',
				height: 150,
				delay: 100,
				minLength: 1,
				open: function () { //e, ui
					var acData = $(this).data('ui-autocomplete');

					acData
						.menu
						.element
						.find('.ui-menu-item-wrapper')
						.each(function () {
							var me = $(this);
							var regex = new RegExp('(' + acData.term + ')', "gi");
							me.html(me.text().replace(regex, '<b>$1</b>'));
						});
				}
			},
			'onAddTag': function (input) {
				if (input.classList.contains('filter-submit')) {
					$(input).closest('form').submit();
				}
			},
			'onRemoveTag': function (input) {
				if (input.classList.contains('filter-submit')) {
					$(input).closest('form').submit();
				}
			}
		});
	}

	var tags = document.querySelectorAll(".form-tags");
	if (tags.length) {
		tags.forEach(function (tag) {
			new TomSelect(tag, {
				plugins: {
					remove_button: {
						title: 'Remove this tag',
					}
				},
				valueField: 'slug',
				labelField: 'slug',
				searchField: ['name'],
				persist: false,
				create: true,
				load: function (query, callback) {
					var url = tag.getAttribute('data-uri').replace('%s', encodeURIComponent(query));

					fetch(url)
						.then(response => response.json())
						.then(json => {
							callback(json.data);
						}).catch(() => {
							callback();
						});
				},
				onDelete: function () { //values
					tag.closest('form').submit();
					//return confirm(values.length > 1 ? 'Are you sure you want to remove these ' + values.length + ' items?' : 'Are you sure you want to remove "' + values[0] + '"?');
				}
			});
			/*$(tag).tagsInput({
				placeholder: tag.getAttribute('placeholder'),
				importPattern: /([^:]+):(.+)/i,
				'autocomplete': {
					source: autocompleteList(tag.getAttribute('data-uri')),
					dataName: 'tags',
					height: 150,
					delay: 100,
					minLength: 1
				},
				'onAddTag': function (input) {
					if (input.classList.contains('filter-submit')) {
						$(input).closest('form').submit();
					}
				},
				'onRemoveTag': function (input) {
					if (input.classList.contains('filter-submit')) {
						$(input).closest('form').submit();
					}
				}
			});*/
		});
	}

	var templates = document.getElementById('field-template');
	if (templates) {
		templates.addEventListener('change', function () {
			if (this.options[this.selectedIndex].value) {
				var selected = document.getElementById(this.options[this.selectedIndex].value);

				document.getElementById('field-subject').value = this.options[this.selectedIndex].innerHTML;

				document.getElementById('field-body').value = selected.value;
				document.getElementById('field-body').dispatchEvent(new Event('refreshEditor', { bubbles: true }));
			}
		});
	}

	var confirmed = 0;
	var parent = document.getElementById('field-roles');
	if (parent) {
		parent.querySelectorAll('input').forEach(function (el) {
			el.addEventListener('change', function () {
				if (this.checked) {
					if (!confirmed) {
						parent.querySelectorAll('.alert').forEach(function (al) {
							al.classList.remove('d-none');
						});
					}

					confirmed++;
				} else if (!this.checked) {
					confirmed--;
					if (confirmed == 0) {
						parent.querySelectorAll('.alert').forEach(function (al) {
							al.classList.add('d-none');
						});
					}
				}
			});
		});
	}
});
