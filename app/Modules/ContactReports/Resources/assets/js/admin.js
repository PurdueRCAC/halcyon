/* global $ */ // jquery.js
/* global Halcyon */ // core.js
/* global Chart */ // vendor/chartjs/Chart.min.js

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

	var group = $(".form-groups");
	if (group.length) {
		group.tagsInput({
			placeholder: '',
			importPattern: /([^:]+):(.+)/i,
			limit: 1,
			'autocomplete': {
				source: autocompleteList(group.attr('data-uri')),
				dataName: 'groups',
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
				//maxLength: 1
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

	var tags = $(".form-tags");
	if (tags.length) {
		tags.each(function (i, tag) {
			$(tag).tagsInput({
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
			});
		});
	}

	$('.basic-multiple').select2({
		placeholder: $(this).data('placeholder')
	});

	$('.searchable-select').select2();

	var rselects = $(".searchable-select-multi");
	if (rselects.length) {
		$(".searchable-select-multi").select2({
			multiple: true
		})
		.on('select2:select', function () {
			if (this.classList.contains('filter-submit')) {
				$(this).closest('form').submit();
			}
		})
		.on('select2:unselect', function () {
			if (this.classList.contains('filter-submit')) {
				var frm = $(this).closest('form');
				if (!$(this).val() || !$(this).val().length) {
					frm.attr('action', frm.attr('action') + '?resource=');
				}
				frm.submit();
			}
		});
	}

	$('.comment-add').on('click', function (e) {
		e.preventDefault();

		var btn = $(this);
		var container = $(btn.data('parent'));
		var comment = $('#' + container.attr('id') + '_comment');

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
						.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
						.data('api', response.api);

					template.find('a').each(function (i, el) {
						$(el).attr('href', $(el).attr('href').replace(/\{id\}/g, response.id));
					});
					template.find('textarea').each(function (i, el) {
						$(el).attr('id', $(el).attr('id').replace(/\{id\}/g, response.id));
						//$(el).val(response.comment);
					});
					template.find('label').each(function (i, el) {
						$(el).attr('for', $(el).attr('for').replace(/\{id\}/g, response.id));
					});
					template.find('button').each(function (i, el) {
						$(el).attr('data-parent', $(el).attr('data-parent').replace(/\{id\}/g, response.id));
					});

					template.find('p').each(function (i, el) {
						$(el).html(
							$(el).html()
								.replace(/\{who\}/g, response.username)
								.replace(/\{when\}/g, response.datetimecreated)
						);
					});

					template.find('div').each(function (i, el) {
						if ($(el).attr('id')) {
							$(el).attr('id', $(el).attr('id').replace(/\{id\}/g, response.id));
							if ($(el).attr('id').match(/_text$/)) {
								$(el).html(response.formattedcomment);
							}
						}
					});

					var content = template
						.html()
						.replace(/\{id\}/g, response.id);

					template.html(content).insertBefore(li);

					$('#comment_' + response.id + '_comment').val(response.comment);
				}

				comment.val('');
			},
			error: function (xhr) { //xhr, ajaxOptions, thrownError
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
					success: function () {
						Halcyon.message('success', 'Item removed');
						field.remove();
					},
					error: function (xhr) {
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
				error: function (xhr) {
					//console.log(xhr);
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		});

	$('.comments-show').on('click', function (e) {
		e.preventDefault();
		$($(this).attr('href')).toggleClass('hide');
	});

	var charts = new Array;
	$('.pie-chart').each(function (i, el) {
		const ctx = el.getContext('2d');
		const chart = new Chart(ctx, {
			type: 'doughnut',
			data: {
				labels: JSON.parse($(el).attr('data-labels')),
				datasets: [
					{
						data: JSON.parse($(el).attr('data-values')),
						backgroundColor: [
							'rgb(255, 99, 132)',
							'rgb(54, 162, 235)',
							'rgb(255, 205, 86)',
							'rgb(201, 203, 207)',
							'rgba(75, 192, 192)',
							'rgba(255, 159, 64)',
							'rgba(153, 102, 255)'
						]
					}
				]
			},
			options: {
				animation: {
					duration: 0
				}/*,
				legend: {
					display: false
				}*/
			}
		});
		charts.push(chart);
	});
});
