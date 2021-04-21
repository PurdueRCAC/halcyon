/**
 * @package    halcyon
 * @copyright  Copyright 2019 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */
/*
Halcyon.submitbutton = function(task) {
	var frm = document.getElementById('item-form');

	if (frm) {
		$(document).trigger('editorSave');

		if (task == 'cancel' || document.formvalidator.isValid(frm)) {
			Halcyon.submitform(task, frm);
		} else {
			alert(frm.getAttribute('data-invalid-msg'));
		}
	}
}

function filterRecords() {
	$.ajax({
		url: my_url,
		type: 'get',
		dataType: 'json',
		async: false,
		success: function(data) {
			if (data !== null) {
				result = parseDirectoryListing(data);
			}
		},
		error: function() {
			console.log('error loading history');
		}
	});
}
*/
/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function() {
	var autocompleteUsers = function(url) {
		return function(request, response) {
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

	var newsuser = $(".form-users");
	if (newsuser.length) {
		newsuser.tagsInput({
			placeholder: '',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteUsers(newsuser.attr('data-uri')),
				dataName: 'users',
				height: 150,
				delay: 100,
				minLength: 1,
				open: function (e, ui) {
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
			}/*,
			'onAddTag': function(input, value) {
				NEWSSearch();
			},
			'onRemoveTag': function(input, value) {
				NEWSSearch();
			}*/
		});
	}

	$('.basic-multiple').select2({
		placeholder: $(this).data('placeholder')
	});

	$('.searchable-select').select2();

	$('.comment-add').on('click', function (e) {
		e.preventDefault();

		var btn = $(this);
		var container = $(btn.data('parent'));
		var comment = $('#' + container.attr('id') + '_comment');
		var post = {
			'contactreportid': $('#id').val(),
			'comment': comment.val()
		};console.log(post);
		// create new relationship
		$.ajax({
			url: container.data('api'),
			type: 'post',
			data: {
				'contactreportid': $('#field-id').val(),
				'comment': comment.val()
			},
			dataType: 'json',
			async: false,
			success: function (response) {
				Halcyon.message('success', 'Item added');

				var c = container.parent();
				var li = c.find('li.d-none');

				if (typeof (li) !== 'undefined') {
					var template = $(li)
						.clone()
						.removeClass('d-none');

					template
						.attr('id', template.attr('id').replace(/\{id\}/g, response.data.id))
						.data('api', response.data.api);

					template.find('a').each(function (i, el) {
						$(el).attr('href', $(el).attr('href').replace(/\{id\}/g, response.data.id));
					});
					template.find('textarea').each(function (i, el) {
						$(el).attr('id', $(el).attr('id').replace(/\{id\}/g, response.data.id));
						//$(el).val(response.data.comment);
					});
					template.find('label').each(function (i, el) {
						$(el).attr('for', $(el).attr('for').replace(/\{id\}/g, response.data.id));
					});
					template.find('button').each(function (i, el) {
						$(el).attr('data-parent', $(el).attr('data-parent').replace(/\{id\}/g, response.data.id));
					});

					template.find('p').each(function (i, el) {
						$(el).html(
							$(el).html()
								.replace(/\{who\}/g, response.data.username)
								.replace(/\{when\}/g, response.data.datetimecreated)
						);
					});

					template.find('div').each(function (i, el) {
						if ($(el).attr('id')) {
							$(el).attr('id', $(el).attr('id').replace(/\{id\}/g, response.data.id));
							if ($(el).attr('id').match(/_text$/)) {
								$(el).html(response.data.formattedcomment);
							}
						}
					});

					var content = template
						.html()
						.replace(/\{id\}/g, response.data.id);

					template.html(content).insertBefore(li);

					$('#comment_' + response.data.id + '_comment').val(response.data.comment);
				}

				comment.val('');
			},
			error: function (xhr, ajaxOptions, thrownError) {
				//console.log(xhr);
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	$('#main')
		.on('click', '.comment-edit,.comment-cancel', function (e) {
			e.preventDefault();

			$(this).closest('li').toggleClass('is-editing');
			//$('#' + container.attr('id') + '_text').toggleClass('d-none');
			//$('#' + container.attr('id') + '_edit').toggleClass('d-none');
			//$(this).toggleClass('d-none');
		})
		.on('click', '.comment-delete', function (e) {
			e.preventDefault();

			var result = confirm($(this).data('confirm'));

			if (result) {
				var field = $($(this).attr('href'));

				$.ajax({
					url: field.data('api'),
					type: 'delete',
					dataType: 'json',
					async: false,
					success: function (data) {
						Halcyon.message('success', 'Item removed');
						field.remove();
					},
					error: function (xhr, ajaxOptions, thrownError) {
						Halcyon.message('danger', xhr.responseJSON.message);
					}
				});
			}
		})
		.on('click', '.comment-save', function (e) {
			e.preventDefault();

			//var btn = $(this);
			var container = $(this).closest('li');
			var comment = $('#' + container.attr('id') + '_comment');

			$.ajax({
				url: container.data('api'),
				type: 'put',
				data: {
					'comment': comment.val()
				},
				dataType: 'json',
				async: false,
				success: function (response) {
					Halcyon.message('success', 'Item saved');
					container.toggleClass('is-editing');
					$('#comment_' + response.data.id + '_text').html(response.data.formattedcomment);
				},
				error: function (xhr, ajaxOptions, thrownError) {
					//console.log(xhr);
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		});
});