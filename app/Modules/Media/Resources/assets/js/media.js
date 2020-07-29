/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

String.prototype.nohtml = function () {
	return this + (this.indexOf('?') == -1 ? '?' : '&') + 'no_html=1';
};

var _DEBUG = 0;

if (typeof('Dropzone') !== undefined) {
	Dropzone.autoDiscover = false;
}

function bindContextModals()
{
	/*$('.media-opt-path,.media-opt-info').fancybox({
		type: 'ajax',
		width: 700,
		height: 'auto',
		autoSize: false,
		fitToView: false,
		titleShow: false,
		beforeLoad: function() {
			if (_DEBUG) {
				window.console && console.log('Calling: ' + $(this).attr('href'));
			}
			$(this).attr('href', $(this).attr('href'));
		}
	});*/

	// Initialize dialogs
	$('.dialog').dialog({
		autoOpen: false,
		modal: true,
		width: 700//,
		//effect:"slide", direction:"right"
	});
	// Initialize context menus
	$('.media-opt-info,.media-opt-path,.media-opt-rename').on('click', function(e){
		e.preventDefault();

		if ($($(this).attr('href')).length) {
			$('.media-item').removeClass('ui-activated');
			$($(this).attr('href')).dialog('open');
		}
	});
}

jQuery(document).ready(function($){
	var contents = $('#media-items'),
		layout = $('#layout'),
		folder = $('#folder');

	_DEBUG = 1; //$('#system-debug').length;

	if (!contents.length) {
		return;
	}

	var isModal = (contents.attr('data-tmpl') == 'component');

	$('.media-upload').on('click', function(e){
		e.preventDefault();

		if ($($(this).attr('href')).length) {
			$('.media-item').removeClass('ui-activated');
			$($(this).attr('href')).dialog('open');
		}
	});

	var views = $('.media-files-view');
	$('.media-files-view').on('click', function(e){
		e.preventDefault();

		views.removeClass('active');
		$('.media-files').removeClass('active');

		$(this).addClass('active');

		var view = $(this).attr('data-view');
		$('#media-' + view).addClass('active');

		layout.val(view);

		/*$.post(layout.data('api'), { 'lauout': view }, function(response){
			console.log(response);
		});*/
	});

	//$('.media-folder-new').on('click', function(e){
	$('#toolbar-folder-new a').off('click').on('click', function(e){
		e.preventDefault();

		var title = prompt($(this).attr('data-prompt'));
		if (title) {
			//var href = $(this).attr('href');
			var href = $(this).data('api');
			if (_DEBUG) {
				window.console && console.log('Creating folder: ' + href);
				console.log({'path': folder.val(), 'name': title})
			}

			$.post(href, {'path': folder.val(), 'name': title}, function(response){
				if (_DEBUG) {
					window.console && console.log(response);
				}

				$.get(contents.attr('data-list') + '?layout=' + layout.val() + '&folder=' + folder.val(), function(data){
					if (_DEBUG) {
						window.console && console.log(data);
					}
					contents.html(data);

					bindContextModals();
				});
			});
		}
	});

	$('.media-breadcrumbs-block').on('click', '.media-breadcrumbs', function(e){
		e.preventDefault();

		folder.val($(this).attr('data-folder'));

		if (_DEBUG) {
			window.console && console.log('Calling: ' + $(this).attr('href') + '&layout=' + layout.val());
		}

		var trail = $(this).attr('data-folder').split('/'),
			crumbs = '',
			fld = '',
			href = contents.attr('data-list') + '?layout=' + layout.val() + '&folder=';

		for (var i = 0; i < trail.length; i++)
		{
			if (trail[i] == '')
			{
				continue;
			}

			href += '/' + trail[i];
			fld += '/' + trail[i];

			crumbs += '<span class="icon-chevron-right dir-separator">/</span>';
			crumbs += '<a href="' + href + '" data-folder="' + fld + '" class="media-breadcrumbs folder has-next-button" id="path_' + trail[i] + '">' + trail[i] + '</a>';
		}

		$('#media-breadcrumbs').html(crumbs);
		$('.spinner').removeClass('d-none');

		$.get($(this).attr('href') + '?layout=' + layout.val(), function(data){
			contents.html(data);
			$('.spinner').addClass('d-none');
			bindContextModals();
		});
	});

	contents
		.on('click', '.folder-item', function(e){
			e.preventDefault();

			folder.val($(this).attr('data-folder'));

			if (_DEBUG) {
				window.console && console.log('Calling: ' + $(this).attr('href') + '&layout=' + layout.val());
			}

			var trail = $(this).attr('data-folder').split('/'),
				crumbs = '',
				fld = '',
				href = contents.attr('data-list') + '?layout=' + layout.val() + '&folder=';

			for (var i = 0; i < trail.length; i++)
			{
				if (trail[i] == '')
				{
					continue;
				}

				href += '/' + trail[i];
				fld += '/' + trail[i];

				crumbs += '<span class="icon-chevron-right dir-separator">/</span>';
				crumbs += '<a href="' + href + '" data-folder="' + fld + '" class="media-breadcrumbs folder has-next-button" id="path_' + trail[i] + '">' + trail[i] + '</a>';
			}

			$('#media-breadcrumbs').html(crumbs);
			$('.spinner').removeClass('d-none');

			$.get($(this).attr('href') + '&layout=' + layout.val(), function(data){
				contents.html(data);
				$('.spinner').addClass('d-none');
				bindContextModals();
			});
		})
		.on('click', '.doc-item', function(e){
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
					// Update preview area
					//window.parent.document.getElementById(id + '_preview_empty').style.display = 'hidden';
					//window.parent.document.getElementById(id + '_preview_img').style.display = 'block';
					//window.parent.document.getElementById(id + '_preview').src = url;
				}
				//window.parent.$.fancybox.close();
				return false;
			}
		})
		.on('click', '.media-options-btn', function(e){
			e.preventDefault();

			var item = $(this).closest('.media-item');

			if (!item.hasClass('ui-activated')) {
				$('.media-item').removeClass('ui-activated');
			}
			item.toggleClass('ui-activated');
		})
		.on('click', '.media-opt-delete', function(e){
			e.preventDefault();

			var conf = confirm(contents.data('confirm'));
			if (!conf) {
				return;
			}

			var href = $(this).data('api');
			if (_DEBUG) {
				window.console && console.log('Deleting: ' + href);
			}

			$.ajax({
				url: href,
				type: 'DELETE',
				success: function(response){
					if (_DEBUG) {
						window.console && console.log(response);
					}

					$.get(contents.attr('data-list') + '?layout=' + layout.val() + '&folder=' + folder.val(), function(data){
						if (_DEBUG) {
							window.console && console.log(data);
						}
						contents.html(data);

						bindContextModals();
					});
				}
			});
		});

	bindContextModals();

	$('#media-tree')
		.find('a')
		.on('click', function(e){
			e.preventDefault();

			folder.val($(this).attr('data-folder'));

			if (_DEBUG) {
				window.console && console.log('Calling: ' + $(this).attr('href') + '&layout=' + layout.val());
			}

			var trail = $(this).attr('data-folder').split('/'),
				crumbs = '',
				href = contents.attr('data-list') + '?layout=' + layout.val() + '&folder=';

			for (var i = 0; i < trail.length; i++) {
				if (trail[i] == '') {
					continue;
				}

				href += '/'  + trail[i];

				crumbs += '<span class="icon-chevron-right dir-separator">/</span>';
				crumbs += '<a href="' + href + '" class="media-breadcrumbs folder has-next-button" id="path_' + trail[i] + '">' + trail[i] + '</a>';
			}

			$('#media-breadcrumbs').html(crumbs);
			$('.spinner').removeClass('d-none');
			$.get($(this).attr('href') + '&layout=' + layout.val(), function(data){
				contents.html(data);
				$('.spinner').addClass('d-none');
				bindContextModals();
			});
		});

	$('#media-tree').treeview({
		collapsed: true
	});

	$('.dropzone').dropzone({
		queuecomplete: function() {
			$.get(contents.attr('data-list') + '?layout=' + layout.val() + '&folder=' + folder.val(), function(data){
				contents.html(data);

				bindContextModals();
				Dropzone.forElement('.dropzone').removeAllFiles();
				$('.media-upload').close();
			});
		}
	});

	/*var attach = $("#ajax-uploader");
	if (attach.length) {

		attach.fileupload({
			dropZone: attach, //$(attach.data('drop')),
			url: attach.attr('data-action'),
			//dataType: 'json',
			//progressInterval: 500,
			drop: function (e, data) {
				$.each(data.files, function (index, file) {
					alert('Dropped file: ' + file.name);
				});
			},
			change: function (e, data) {
        $.each(data.files, function (index, file) {
            alert('Selected file: ' + file.name);
        });
    },
			add: function(e, data) {
				data.context = $('<p class="file"></p>')
					.append($('<a target="_blank"></a>').text(data.files[0].name))
					.appendTo(document.body);
				//data.submit();
			},
			progress: function(e, data) {
				var progress = parseInt((data.loaded / data.total) * 100, 10);
				data.context.css("background-position-x", 100 - progress + "%");
			},
			done: function(e, data) {
				data.context
					.addClass("done")
					.find("a")
					.prop("href", data.result.files[0].url);
			}
		});

		var running = 0;
		if (_DEBUG) {
			window.console && console.log('Uploading to: ' + attach.attr('data-action') + '?layout=' + layout.val() + '&folder=' + folder.val());
		}

		var uploader = new qq.FileUploader({
			element: attach[0],
			action: attach.attr('data-action'),
			params: {
				layout: function() {
					return layout.val();
				},
				folder: function() {
					return folder.val();
				}
			},
			multiple: true,
			debug: true,
			template: '<div class="qq-uploader">' +
							'<div class="qq-upload-button"><span>' + attach.attr('data-instructions') + '</span></div>' + 
							'<div class="qq-upload-drop-area"><span>' + attach.attr('data-instructions') + '</span></div>' +
							'<ul class="qq-upload-list"></ul>' + 
						'</div>',
			onSubmit: function(id, file) {
				running++;
			},
			onComplete: function(id, file, response) {
				running--;

				if (running == 0) {
					$('ul.qq-upload-list').empty();
				}

				if (_DEBUG) {
					window.console && console.log('Calling: ' + contents.attr('data-list') + '?layout=' + layout.val() + '&folder=' + folder.val());
				}
				$.get(contents.attr('data-list') + '?layout=' + layout.val() + '&folder=' + folder.val(), function(data){
					contents.html(data);

					bindContextModals();
				});
			}
		});
	}*/
});
