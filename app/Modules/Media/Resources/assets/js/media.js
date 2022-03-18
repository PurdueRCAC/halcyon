/* global $ */ // jquery.js
/* global jQuery */ // jquery.js
/* global Dropzone */ // dropzone.js

var _DEBUG = 1;

if (typeof ('Dropzone') !== undefined) {
	Dropzone.autoDiscover = false;
}

function bindContextModals() {
	// Initialize dialogs
	$('.dialog').dialog({
		autoOpen: false,
		modal: true,
		close: function () {
			// We need to do this to avoid "cached" dialogs
			// for folder contents that are reloaded via AJAX
			$(this).dialog('close');
			$(this).dialog('destroy');
			$(this).html('');
		},
		width: 700
	});

	// Initialize context menus
	$('.media-opt-info,.media-opt-path,.media-opt-rename').on('click', function (e) {
		e.preventDefault();

		if ($($(this).attr('href')).length) {
			$('.media-item').removeClass('ui-activated');
			$($(this).attr('href')).dialog('open');
		}
	});
}

jQuery(document).ready(function () {
	var contents = $('#media-items'),
		layout = $('#layout'),
		folder = $('#folder');

	_DEBUG = 1; //$('#system-debug').length;

	if (!contents.length) {
		return;
	}

	var isModal = (contents.attr('data-tmpl') == 'component');

	var views = $('.media-files-view');
	$('.media-files-view').on('click', function (e) {
		e.preventDefault();

		views.removeClass('active');
		$('.media-files').removeClass('active');

		$(this).addClass('active');

		var view = $(this).attr('data-view');
		$('#media-' + view).addClass('active');

		layout.val(view);
	});

	$('#toolbar-folder-new a').off('click').on('click', function (e) {
		e.preventDefault();

		var title = prompt($(this).attr('data-prompt'));
		if (title) {
			//var href = $(this).attr('href');
			var href = $(this).data('api');
			if (_DEBUG) {
				window.console && console.log({ 'path': folder.val(), 'name': title })
			}

			$('.spinner').removeClass('d-none');

			$.post(href, { 'path': folder.val(), 'name': title }, function (response) {
				if (_DEBUG) {
					window.console && console.log(response);
				}

				$.get(contents.attr('data-list') + '?layout=' + layout.val() + '&folder=' + folder.val(), function (data) {
					if (_DEBUG) {
						window.console && console.log(data);
					}
					contents.html(data);

					$('.spinner').addClass('d-none');

					bindContextModals();
				});
			});
		}
	});

	$('.media-breadcrumbs-block').on('click', '.media-breadcrumbs', function (e) {
		e.preventDefault();

		folder.val($(this).attr('data-folder'));

		if (_DEBUG) {
			window.console && console.log('Calling: ' + $(this).attr('href') + '&layout=' + layout.val());
		}

		var fldr = $(this).attr('data-folder');

		breadcrumbs(
			fldr,
			contents.attr('data-list') + '?layout=' + layout.val() + '&folder='
		);

		$('.spinner').removeClass('d-none');

		var dataurl = $(this).attr('href');

		$.get(dataurl, function (data) {
			contents.html(data);
			window.history.pushState({ dataurl: dataurl, folder: fldr, layout: layout.val() }, '', dataurl.replace('/files', ''));
			$('.spinner').addClass('d-none');
			bindContextModals();
		});
	});

	contents
		.on('click', '.folder-item', function (e) {
			e.preventDefault();

			folder.val($(this).attr('data-folder'));

			if (_DEBUG) {
				window.console && console.log('Calling: ' + $(this).attr('href') + '&layout=' + layout.val());
			}

			var fldr = $(this).attr('data-folder');

			breadcrumbs(
				fldr,
				contents.attr('data-list') + '?layout=' + layout.val() + '&folder='
			);

			$('.spinner').removeClass('d-none');

			var url = $(this).attr('href'),
				dataurl = $(this).attr('data-href');

			$.get(dataurl + '&layout=' + layout.val(), function (data) {
				contents.html(data);

				window.history.pushState({ dataurl: dataurl, folder: fldr, layout: layout.val() }, '', url);

				$('.spinner').addClass('d-none');
				bindContextModals();
			});
		})
		.on('click', '.doc-item', function (e) {
			if (isModal) {
				e.preventDefault();

				// Get the image tag field information
				var url = $(this).attr('href');

				if (url == '') {
					return false;
				}

				if ($('#e_name').length) {
					var alt = $(this).attr('title');
					var tag = '<img src="' + url + '" ';

					// Set alt attribute
					if (alt != '') {
						tag += 'alt="' + alt + '" ';
					} else {
						tag += 'alt="" ';
					}

					tag += '/>';

					window.parent.insertEditorText(tag, $('#e_name').val());
				}
				if ($('#fieldid').length) {
					var id = $('#fieldid').val();
					window.parent.document.getElementById(id).value = url;
				}

				return false;
			}
		})
		.on('click', '.media-options-btn', function (e) {
			e.preventDefault();

			var item = $(this).closest('.media-item');

			if (!item.hasClass('ui-activated')) {
				$('.media-item').removeClass('ui-activated');
			}
			item.toggleClass('ui-activated');
		})
		.on('click', '.media-opt-download', function () {
			$('.media-item').removeClass('ui-activated');
		})
		.on('click', '.media-opt-rename', function (e) {
			e.preventDefault();

			var title = prompt($(this).data('prompt'), $(this).data('name'));
			if (title) {
				var href = $(this).data('api');

				var data = {
					'before': $(this).data('path') + '/' + $(this).data('name'),
					'after': $(this).data('path') + '/' + title
				};

				if (_DEBUG) {
					window.console && console.log(data);
				}

				$('.spinner').removeClass('d-none');

				$.ajax({
					url: href,
					type: 'PUT',
					data: data,
					success: function (response) {
						if (_DEBUG) {
							window.console && console.log(response);
						}

						$.get(contents.attr('data-list') + '?layout=' + layout.val() + '&folder=' + folder.val(), function (data) {
							if (_DEBUG) {
								window.console && console.log(data);
							}
							contents.html(data);

							$('.spinner').addClass('d-none');

							bindContextModals();
						});
					}
				});
			}
		})
		.on('click', '.media-opt-move', function (e) {
			e.preventDefault();

			$('.media-item').removeClass('ui-activated');
			$('#media-move').dialog('open');

			var name = $(this).data('name');
			var before = $(this).data('path') + '/' + name;
			var href = $(this).data('api');

			before = '/' + before.replace(/^\.+/gm, '').replace(/^\/+/gm, '');

			$('#mover').on('submit', function (e) {
				e.preventDefault();

				var after = $('#move-destination').val() + '/' + name;

				if (before == after) {
					alert('Cannot move to self.');
				}

				var data = {
					'before': before,
					'after': after
				};

				if (_DEBUG) {
					window.console && console.log(data);
				}

				$('.spinner').removeClass('d-none');

				$.ajax({
					url: href,
					type: 'PUT',
					data: data,
					success: function (response) {
						if (_DEBUG) {
							window.console && console.log(response);
						}

						$.get(contents.attr('data-list') + '?layout=' + layout.val() + '&folder=' + folder.val(), function (data) {
							if (_DEBUG) {
								window.console && console.log(data);
							}
							contents.html(data);

							$('.spinner').addClass('d-none');

							bindContextModals();

							$('#media-move').dialog('close');
						});
					}
				});
			});
		})
		.on('click', '.media-opt-delete', function (e) {
			e.preventDefault();

			var conf = confirm(contents.data('confirm'));
			if (!conf) {
				return;
			}

			var href = $(this).data('api');
			if (_DEBUG) {
				window.console && console.log('Deleting: ' + href);
			}

			$('.spinner').removeClass('d-none');

			$.ajax({
				url: href,
				type: 'DELETE',
				success: function (response) {
					if (_DEBUG) {
						window.console && console.log(response);
					}

					$.get(contents.attr('data-list') + '?layout=' + layout.val() + '&folder=' + folder.val(), function (data) {
						if (_DEBUG) {
							window.console && console.log(data);
						}
						contents.html(data);

						$('.spinner').addClass('d-none');

						bindContextModals();
					});
				}
			});
		});

	bindContextModals();

	function breadcrumbs(path, href) {
		var trail = path.split('/'),
			crumbs = '',
			fld = '';

		for (var i = 0; i < trail.length; i++) {
			if (trail[i] == '') {
				continue;
			}

			href += '/' + trail[i];
			fld += '/' + trail[i];

			crumbs += '<span class="icon-chevron-right dir-separator">/</span>';
			crumbs += '<a href="' + href + '" data-folder="' + fld + '" class="media-breadcrumbs folder has-next-button" id="path_' + trail[i] + '">' + trail[i] + '</a>';
		}

		$('#media-breadcrumbs').html(crumbs);
	}

	$('#media-tree')
		.find('a')
		.on('click', function (e) {
			e.preventDefault();

			folder.val($(this).attr('data-folder'));

			if (_DEBUG) {
				window.console && console.log('Calling: ' + $(this).attr('href') + '&layout=' + layout.val());
			}

			var fldr = $(this).attr('data-folder');

			breadcrumbs(
				fldr,
				contents.attr('data-list') + '?layout=' + layout.val() + '&folder='
			);

			$('.spinner').removeClass('d-none');

			var url = $(this).attr('href'),
				dataurl = $(this).attr('data-href');

			$.get(dataurl + '&layout=' + layout.val(), function (data) {
				contents.html(data);

				window.history.pushState({ dataurl: dataurl, folder: fldr, layout: layout.val() }, '', url);

				$('.spinner').addClass('d-none');
				bindContextModals();
			});
		});

	$('#media-tree').treeview({
		collapsed: true
	});

	$(window).on("popstate", function () {
		if (history.state) {
			$('.spinner').removeClass('d-none');

			$.get(history.state.dataurl + '&layout=' + history.state.layout, function (data) {
				contents.html(data);

				breadcrumbs(
					history.state.folder,
					contents.attr('data-list') + '?layout=' + layout.val() + '&folder='
				);

				$('.spinner').addClass('d-none');
				bindContextModals();
			});
		}
	});

	$('.media-upload').on('click', function (e) {
		e.preventDefault();

		if ($($(this).attr('href')).length) {
			$('.media-item').removeClass('ui-activated');
			$($(this).attr('href')).dialog('open');
		}
	});

	$('.dropzone').dropzone({
		init: function () {
			this.on("sending", function (file, xhr, formData) {
				formData.append("path", folder.val());
			});
		},
		queuecomplete: function () {
			$.get(contents.attr('data-list') + '?layout=' + $('#layout').val() + '&folder=' + $('#folder').val(), function (data) {
				contents.html(data);

				bindContextModals();
				Dropzone.forElement('.dropzone').removeAllFiles();

				$($('.media-upload').attr('href')).dialog('close');
			});
		},
		error: function (errorMessage) {
			alert(errorMessage);
		}
	});
});
