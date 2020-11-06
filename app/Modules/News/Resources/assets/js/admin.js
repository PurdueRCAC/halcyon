/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

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
});
