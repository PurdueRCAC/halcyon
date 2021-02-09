/**
 * @package    Issues tracker
 */

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function() {
	$('.issue-todo').on('change', function (e) {
		var that = $(this);

		if (that.is(':checked')) {
			$.ajax({
				url: that.data('api'),
				type: 'post',
				data: {
					'report': that.data('name'),
					'issuetodoid': that.data('id')
				},
				dataType: 'json',
				async: false,
				success: function (data) {
					$(that.closest('li')).addClass('hide'); //fadeOut();
					$(that.closest('li')).addClass('complete');
					$(that.closest('li')).removeClass('incomplete');
					that.data('issue', data.id);
					$('#checklist_status').trigger('change');
				},
				error: function (xhr, ajaxOptions, thrownError) {
					var img = $(that.closest('li')).find('.fa')[0];
					img.className = "fa fa-exclamation-triangle";
					img.parentNode.title = "Unable to save changes, reload the page and try again.";

					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		} else if (that.data('issue')) {
			$.ajax({
				url: that.data('api') + '/' + that.data('issue'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function (data) {
					$(that.closest('li')).removeClass('hide'); //fadeOut();
					$(that.closest('li')).removeClass('complete');
					$(that.closest('li')).addClass('incomplete');
					that.data('issue', '');
					$('#checklist_status').trigger('change');
				},
				error: function (xhr, ajaxOptions, thrownError) {
					var img = $(that.closest('li')).find('.fa')[0];
					img.className = "fa fa-exclamation-triangle";
					img.parentNode.title = "Unable to save changes, reload the page and try again.";

					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		}
	});

	$('#checklist_status').on('change', function(){
		var val = $(this).val();
		if (val == 'all') {
			$('.checklist>li').removeClass('hide');
		} else {
			$('.checklist>li').addClass('hide');
			$('.' + val).removeClass('hide');
		}
	});

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
			'issueid': $('#id').val(),
			'comment': comment.val()
		};
		// create new relationship
		$.ajax({
			url: container.data('api'),
			type: 'post',
			data: {
				'issueid': $('#field-id').val(),
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