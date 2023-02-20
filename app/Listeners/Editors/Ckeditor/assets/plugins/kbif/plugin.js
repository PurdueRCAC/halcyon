/**
 * Plugin to highlight IF statement syntax in Knowledge pages
 */
(function() {
	CKEDITOR.plugins.add('kbif', {
		init: function (editor) {
			var plugin = this,
				ready  = false;

			var path = this.path;

			editor.on('instanceReady', function () {
				ready = true;

				// highlight
				plugin.highlight(editor);

				// add css for mark elements
				if (editor.mode != 'source') {
					this.document.appendStyleSheet(path + 'plugin.css');
				}
			});

			editor.on('mode', function () {
				if (ready) {
					plugin.highlight(editor);

					// add css for mark elements
					if (editor.mode != 'source')
					{
						this.document.appendStyleSheet(path + 'plugin.css');
					}
				}
			});

			editor.on('blur', function() {
				if (ready) {
					plugin.highlight(editor);

					// add css for mark elements
					if (editor.mode != 'source') {
						this.document.appendStyleSheet(path + 'plugin.css');
					}
				}
			});
			
			editor.element.$.form.addEventListener('submit', function () {
				var data = editor.getData();

				// remove old mark tags
				data = data.replace(/<mark class="kbif if">/g, '');
				data = data.replace(/<mark class="kbif elseif">/g, '');
				data = data.replace(/<mark class="kbif else">/g, '');
				data = data.replace(/<mark class="kbif endif">/g, '');
				data = data.replace(/<\/mark>/g, '');

				// set new data
				editor.setData(data);
			});
		},

		highlight: function (editor) {
			// get current data
			var data  = editor.getData();

			// remove old mark tags
			data = data.replace(/<mark class="kbif if">/g, '');
			data = data.replace(/<mark class="kbif elseif">/g, '');
			data = data.replace(/<mark class="kbif else">/g, '');
			data = data.replace(/<mark class="kbif endif">/g, '');
			data = data.replace(/<\/mark>/g, '');

			// add new mark tags
			if (editor.mode == 'wysiwyg') {
				data = data.replace(/(\{::if\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\})/g, '<mark class="kbif if">$1</mark>');
				data = data.replace(/(\{::elseif\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\})/g, '<mark class="kbif elseif">$1</mark>');
				data = data.replace(/(\{::else\})/g, '<mark class="kbif else">$1</mark>');
				data = data.replace(/(\{::\/\})/g, '<mark class="kbif endif">$1</mark>');
			}

			// set new data
			editor.setData(data);
		}
	});
})();
