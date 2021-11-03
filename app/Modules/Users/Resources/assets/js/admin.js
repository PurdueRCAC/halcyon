/* global $ */ // jquery.js

$(document).ready(function () {
	var searchusers = $('#filter_search');
	if (searchusers.length) {
		searchusers.each(function (i, el) {
			$(el).select2({
				ajax: {
					url: $(el).data('api'),
					dataType: 'json',
					maximumSelectionLength: 1,
					data: function (params) {
						var query = {
							search: params.term,
							order: 'name',
							order_dir: 'asc'
						}

						return query;
					},
					processResults: function (data) {
						for (var i = 0; i < data.data.length; i++) {
							if (data.data[i].id) {
								data.data[i].text = data.data[i].name + ' (' + data.data[i].username + ')';
							} else {
								data.data[i].text = data.data[i].name + ' (' + data.data[i].username + ')';
								data.data[i].id = data.data[i].username;
							}
						}

						return {
							results: data.data
						};
					}
				},
				templateResult: function (state) {
					if (isNaN(state.id) && typeof state.name != 'undefined') {
						return $('<span>' + state.text + ' <span class="text-warning ml-1"><span class="fa fa-exclamation-triangle" aria-hidden="true"></span> No local account</span></span>');
					}
					return state.text;
				}
			});
		});
		searchusers.on('select2:select', function (e) {
			var data = e.params.data;
			window.location = $(this).data('url') + "?search=" + data.id;
		});
		searchusers.on('select2:unselect', function () {
			window.location = $(this).data('url') + "?search=";
		});
	}
});
