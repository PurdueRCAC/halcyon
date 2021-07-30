/*
USAGE:

    $.growl(title, msg);

OPTIONS:

    animate: Animate the slide in/out of the message
	autoRemove: Automatically temove the message after a period of time
*/

(function($) {
	$.growl = function(type, message, animate, autoRemove) {
		notify(type, message, animate, autoRemove);
	}
	$.growl.version = "1.0.2";

	function create(rebuild) {
		var instance = document.getElementById('growlDock');

		if (!instance || rebuild) {
			instance = $(jQuery.growl.settings.dockTemplate)
				.attr('id', 'growlDock')
				.addClass('growl')
				.addClass(jQuery.growl.settings.position);
			$('body').append(instance);
		} else {
			instance = $(instance);
		}

		return instance;
	};

	function r(text, expr, val) {
		while (expr.test(text)) {
			text = text.replace(expr, val);
		}
		return text;
	};

	function notify(type, message, animate, autoRemove) {
		var container = create();

		/*
		<!-- Example of the DOM we're creating for a notification -->
		<div class="notification">
			<div class="pad">
				<div class="message">
					Message here
				</div>
				<div class="close">
					<div class="close-btn">
					</div>
				</div>
			</div>
			<div class="progressContainer">
				<div class="progress"></div>
			</div>
		</div>
		*/
		var node = $('<div/>')
			.addClass('notification')
			.addClass('alert')
			.addClass('alert-' + type);

		var pad = $('<div/>')
			.addClass('pad');

		var msg = $('<div/>')
			.addClass('message')
			.html(message);

		var close = $('<div/>')
			.addClass('close')
			.css('cursor', 'pointer');

		var closeBtn = $('<div/>')
			.addClass('close-btn');

		// add close-notification click functionality
		close.off('click').on('click', function(e) {
			// animate when closing; then remove the DOM element entirely
			var n = $(this).parent().parent();
			n.animate({left: '-=50px', opacity: "0"}, "fast", function() { n.remove(); });
		});

		var pC = $('<div/>')
			.addClass('progressContainer');

		var p = $('<div/>')
			.addClass('progress');

		if (typeof animate == 'undefined') {
			animate = jQuery.growl.settings.autoRemove;
		}

		if (typeof autoRemove == 'undefined') {
			autoRemove = jQuery.growl.settings.autoRemove;
		}
		
		//if animation is turned on
		if (animate === true) {
			node.addClass("n-animate-in");
		}

		if (autoRemove === true) {
			if (animate === true) {
				//add the animation class to the notification...
				node.addClass("n-animate");
				//...and the progress bar
				p.addClass('progress-animate');
				//ensure the node removes itself after the animation finishes
				node.bind('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', function(e) { $(this).remove(); });
			} else {
				//...and the progress bar
				p.addClass('progress-animate');
				//ensure the node removes itself after the progress-bar animation finishes
				node.bind('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', function(e) { $(this).remove(); });
			}
		}

		pad.append(msg);
		pad.append(close.append(closeBtn));
		
		pC.append(p);

		node.append(pad);
		node.append(pC);

		container.append(node);
	};

	// default settings
	$.growl.settings = {
		dockTemplate: '<div></div>',
		position: 'bottomright',
		animate: true,
		autoRemove: true,
		noticeElement: function(el) {
			$.growl.settings.noticeTemplate = $(el);
		}
	};
})(jQuery);

jQuery(document).ready(function($){
	$('html').removeClass('no-js');

	$('.hamburger').on('click', function (e){
		e.preventDefault();

		var btn = $(this),
			mode = $('body').hasClass('menu-open') ? 'closed' : 'open';

		$('body').toggleClass('menu-open');

		$.ajax({
			url: btn.attr('data-api'),
			type: 'put',
			data: {
				facets: {
					'theme.admin.menu': mode
				}
			},
			dataType: 'json',
			async: false,
			error: function (xhr, ajaxOptions, thrownError) {
				console.log(xhr);
			}
		});
	});

	// Mobile device fix
	$('#toolbar ul').on('click', function(e){
		$(this).toggleClass('active');
	});

	$('.main-navigation li.node>a').on('click', function(e){
		$(this).parent().toggleClass('active');
	});

	$('#mode').on('click', function(e){
		e.preventDefault();

		var btn = $('#mode'),
			mode = btn.attr('data-mode');

		$.ajax({
			url: btn.attr('data-api'),
			type: 'put',
			data: {
				facets: {
					'theme.admin.mode': mode
				}
			},
			dataType: 'json',
			async: false,
			success: function (response) {
				$('html').attr('data-mode', mode);

				btn.attr('data-mode', mode == 'dark' ? 'light' : 'dark');
			},
			error: function (xhr, ajaxOptions, thrownError) {
				console.log(xhr);
			}
		});
	});

	// Display system messages in Growl-like way
	$(document).on("renderMessages", function() {
		var msg = $('#system-messages');
		if (msg.length && msg.html().replace(/\s+/, '') != '') {
			msg.find('.alert').each(function(i, el) {
				var type = '';
				type = $(el).hasClass('alert-warning') ? 'warning' : type;
				type = $(el).hasClass('alert-danger') ? 'danger' : type;
				type = $(el).hasClass('alert-info') ? 'info' : type;
				type = $(el).hasClass('alert-success') ? 'success' : type;

				$.growl(type, $(el).html());
			});
			msg.empty();
		}
	});

	$(document).trigger('renderMessages');
});
