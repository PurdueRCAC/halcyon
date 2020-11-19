
(function() {
	CKEDITOR.plugins.add('halcyonhighlight', {
		init: function( editor ) {
			var $      = (typeof(jq) !== "undefined" ? jq : jQuery),
				mode   = '', 
				plugin = this,
				ready  = false;

			var path = this.path;

			editor.on('instanceReady', function(event){
				ready = true;

				// highlight
				plugin.highlight(editor);

				// add css for mark elements
				if (editor.mode != 'source')
				{
					this.document.appendStyleSheet(path + 'plugin.css');
				}
			});

			editor.on('mode', function(event) {
				if (ready)
				{
					plugin.highlight(editor);

					// add css for mark elements
					if (editor.mode != 'source')
					{
						this.document.appendStyleSheet(path + 'plugin.css');
					}
				}
			});

			editor.on('blur', function(event) {
				if (ready)
				{
					plugin.highlight(editor);

					// add css for mark elements
					if (editor.mode != 'source')
					{
						this.document.appendStyleSheet(path + 'plugin.css');
					}
				}
			});

			var form = $(editor.element.$.form);
			form.submit(function(event) {
				var data  = editor.getData();

				// remove old mark tags
				data = data.replace(/<mark class="control">/g, '');
				data = data.replace(/<\/mark>/g, '');
				data = data.replace(/<mark class="variable">/g, '');
				data = data.replace(/<\/mark>/g, '');
				data = data.replace(/<mark class="widget">/g, '');
				data = data.replace(/<\/mark>/g, '');

				// set new data
				editor.setData(data);
			});
		},

		highlight: function( editor )
		{
			// get current data
			var data = editor.getData();

			// remove old mark tags
			data = data.replace(/<mark class="control">/g, '');
			data = data.replace(/<\/mark>/g, '');
			data = data.replace(/<mark class="variable">/g, '');
			data = data.replace(/<\/mark>/g, '');
			data = data.replace(/<mark class="widget">/g, '');
			data = data.replace(/<\/mark>/g, '');

			// add new mark tags
			if (editor.mode == 'wysiwyg')
			{
				data = data.replace(/(\{::(if.*?|else|elseif.*?|\/)\})/g, '<mark class="control">$1</mark>');
				data = data.replace(/(\$\{.*?\})/g, '<mark class="variable">$1</mark>');
				data = data.replace(/(@widget\([^)]*\))/g, '<mark class="widget">$1</mark>');
			}

			// set new data
			editor.setData(data);
		}
	});
})();
