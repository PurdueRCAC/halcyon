/**
 * Unix base groups
 *
 * @const
 * @type  {array}
 */
var BASEGROUPS = Array('', 'data', 'apps');

/**
 * Create UNIX group
 *
 * @param   {integer}  num    index for BASEGROUPS array
 * @param   {string}   group
 * @return  {void}
 */
function CreateNewGroupVal(num, btn) {
	var base = btn.data('value'),
		group = btn.data('group');

	// The callback only accepts one argument, so we
	// need to compact this
	var args = [num, group];

	$.ajax({
		url: btn.data('api'),
		type: 'post',
		data: {
			'longname': BASEGROUPS[num],
			'groupid': group
		},
		dataType: 'json',
		async: false,
		success: function (response) {
			num++;
			if (num < BASEGROUPS.length) {
				setTimeout(function () {
					CreateNewGroupVal(num, btn);
				}, 5000);
			} else {
				Halcyon.message('success', 'Item added');
				window.location.reload(true);
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			//console.log(xhr);
			btn.find('.spinner-border').addClass('d-none');
			Halcyon.message('danger', xhr.responseJSON.message);
		}
	});
}

var _DEBUG = true;
/**
 * Message of the Day
 */
var motd = {
	/**
	 * Set the MOTD for a group
	 *
	 * @param   {string}  group
	 * @return  {void}
	 */
	set: function (group) {
		var message = document.getElementById("MotdText_" + group);

		if (!group) {
			Halcyon.message('danger', 'No group ID provided.');
			return false;
		}

		var post = {
			'groupid': group,
			'motd': message.value
		};

		_DEBUG ? console.log('post: ' + message.getAttribute('data-api'), post) : null;

		$.ajax({
			url: message.getAttribute('data-api'),
			type: 'post',
			data: post,
			dataType: 'json',
			async: false,
			success: function (data) {
				window.location.reload();
			},
			error: function (xhr, ajaxOptions, thrownError) {
				Halcyon.message('danger', xhr.response);
			}
		});
	},

	/**
	 * Delete the MOTD for a group
	 *
	 * @param   {string}  group
	 * @return  {void}
	 */
	delete: function (group) {
		if (!group) {
			Halcyon.message('danger', 'No group ID provided.');
			return false;
		}

		var btn = document.getElementById("MotdText_delete_" + group);

		_DEBUG ? console.log('delete: ' + btn.getAttribute('data-api')) : null;

		$.ajax({
			url: btn.getAttribute('data-api'),
			type: 'delete',
			async: false,
			success: function (data) {
				window.location.reload();
			},
			error: function (xhr, ajaxOptions, thrownError) {
				Halcyon.message('danger', xhr.response);
			}
		});
	}
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function() {
	if ($.fn.select2) {
		$('.searchable-select').select2();
	}

	$('.motd-delete').on('click', function (e) {
		e.preventDefault();
		motd.delete(this.getAttribute('data-group'));
	});

	$('.motd-set').on('click', function (e) {
		e.preventDefault();
		motd.set(this.getAttribute('data-group'));
	});

	$('#main').on('change', '.membertype', function(){
		$.ajax({
			url: $(this).data('api'),
			type: 'put',
			data: {membertype: $(this).val()},
			dataType: 'json',
			async: false,
			success: function(data) {
				Halcyon.message('success', 'Member type updated!');
			},
			error: function(xhr, ajaxOptions, thrownError) {
				Halcyon.message('danger', 'Failed to update member type.');
			}
		});
	});

	$('.input-unixgroup').on('keyup', function (e){
		var val = $(this).val();

		val = val.toLowerCase()
			.replace(/\s+/g, '-')
			.replace(/[^a-z0-9\-]+/g, '');

		$(this).val(val);
	});

	$('.create-default-unix-groups').on('click', function(e) {
		e.preventDefault();

		$(this).find('.spinner-border').removeClass('d-none');

		CreateNewGroupVal(0, $(this));
	});

	$('.add-category').on('click', function(e){
		e.preventDefault();

		var select = $($(this).attr('href'));
		var btn = $(this);

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'post',
			data: {
				'groupid' : btn.data('group'),
				[select.data('category')] : select.val()
			},
			dataType: 'json',
			async: false,
			success: function(response) {
				Halcyon.message('success', 'Item added');

				var c = select.closest('table');
				var li = c.find('tr.hidden');

				if (typeof(li) !== 'undefined') {
					var template = $(li)
						.clone()
						.removeClass('hidden');

					template
						.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
						.data('id', response.id);

					template.find('a').each(function(i, el){
						$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.id));
					});

					var content = template
						.html()
						.replace(/\{id\}/g, response.id)
						.replace(/\{name\}/g, select.find('option:selected').text());

					template.html(content).insertBefore(li);
				}

				select.val(0);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				//console.log(xhr);
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	$('#main').on('click', '.remove-category', function(e){
		e.preventDefault();

		var result = confirm($(this).data('confirm'));

		if (result) {
			var field = $($(this).attr('href'));

			// delete relationship
			$.ajax({
				url: $(this).data('api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function(data) {
					Halcyon.message('success', 'Item removed');
					field.remove();
				},
				error: function(xhr, ajaxOptions, thrownError) {
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		}
	});

	$('.add-unixgroup').on('click', function(e){
		e.preventDefault();

		var name = $($(this).attr('href'));
		var btn = $(this);

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'post',
			data: {
				'groupid' : btn.data('group'),
				'longname' : name.val()
			},
			dataType: 'json',
			async: false,
			success: function(response) {
				Halcyon.message('success', 'Item added');

				var c = name.closest('table');
				var li = c.find('tr.hidden');

				if (typeof(li) !== 'undefined') {
					var template = $(li)
						.clone()
						.removeClass('hidden');

					template
						.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
						.data('id', response.id);

					template.find('a').each(function(i, el){
						$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.id));
					});

					var content = template
						.html()
						.replace(/\{id\}/g, response.id)
						.replace(/\{longname\}/g, response.longname)
						.replace(/\{shortname\}/g, response.shortname);

					template.html(content).insertBefore(li);
				}

				name.val('');
			},
			error: function(xhr, ajaxOptions, thrownError) {
				//console.log(xhr);
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	$('#main').on('click', '.remove-unixgroup', function(e){
		e.preventDefault();

		var result = confirm($(this).data('confirm'));

		if (result) {
			var field = $($(this).attr('href'));

			// delete relationship
			$.ajax({
				url: $(this).data('api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function(data) {
					Halcyon.message('success', 'Item removed');
					field.remove();
				},
				error: function(xhr, ajaxOptions, thrownError) {
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		}
	});
});