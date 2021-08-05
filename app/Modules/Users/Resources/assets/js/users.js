/*Halcyon.submitbutton = function(task, type = '') {
	var afrm = document.getElementById('adminForm');

	if (afrm) {
		Halcyon.submitform(task, afrm);
		return;
	}

	var frm = document.getElementById('item-form');

	if (frm) {
		$(document).trigger('editorSave');
		if (task == 'cancel' || document.formvalidator.isValid(frm)) {
			Halcyon.submitform(task, frm);
		} else {
			alert(frm.getAttribute('data-invalid-msg'));
		}
	}
}*/

jQuery(document).ready(function($){
	/*$('#btn-batch-submit')
		.on('click', function (e){
			Halcyon.submitbutton('user.batch');
		});

	$('#btn-batch-clear')
		.on('click', function (e){
			e.preventDefault();
			$('#batch-group-id').val('');
		});

	var password = $('#newpass'),
		passrule = $('#passrules');

	if (password.length > 0 && passrule.length > 0) {
		password.on('keyup', function(){
			// Create an ajax call to check the potential password
			$.ajax({
				url: password.attr('data-href'), //"/api/members/checkpass",
				type: "POST",
				data: "password1=" + password.val() + "&" + password.attr('data-values'),
				dataType: "json",
				cache: false,
				success: function(json) {
					if (json.html.length > 0 && password.val() !== '') {
						passrule.html(json.html);
					} else {
						// Probably deleted password, so reset classes
						passrule.find('li').switchClass('error passed', 'empty', 200);
					}
				}
			});
		});
	}

	$('#class_id').on('change', function (e) {
		//e.preventDefault();
		$.getJSON($(this).attr('data-href') + $(this).val(), {}, function (data) {
			$.each(data, function (key, val) {
				var item = $('#field-'+key);
				item.val(val);

				if (e.target.options[e.target.selectedIndex].text == 'custom') {
					item.prop("readonly", false);
				} else {
					item.prop("readonly", true);
				}
			});
		});
	});*/
	$('.btn-apitoken').on('click', function(e){
		e.preventDefault();

		if (confirm('Are you sure you want to regenerate the API token for this user?')) {
			$('#field-api_token').val(token(60)).prop('readonly', false);
		}
	});


	function token(length) {
		var result = '';
		var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		var charactersLength = characters.length;
		for (var i = 0; i < length; i++) {
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
		}
		return result;
	}


	$('#permissions-rules').accordion({
		heightStyle: 'content',
		collapsible: true,
		active: false
	});
	$('#permissions-rules .stop-propagation').on('click', function(e) {
		e.stopPropagation();
	});

	$('.add-facet').on('click', function (e) {
		e.preventDefault();

		var btn = $(this);
		var key = $(btn.attr('href') + '-key'),
			value = $(btn.attr('href') + '-value'),
			access = $(btn.attr('href') + '-access');

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'post',
			data: {
				'user_id': btn.data('userid'),
				'key': key.val(),
				'value': value.val(),
				'access': access.val()
			},
			dataType: 'json',
			async: false,
			success: function (response) {
				Halcyon.message('success', 'Item added');

				var c = btn.closest('table');
				var li = '#facet-template';//c.find('tr.hidden');

				if (typeof (li) !== 'undefined') {
					var template = $(li);
					//.clone()
					//.removeClass('hidden');

					//template
					//	.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
					//	.data('id', response.id);

					template.find('a').each(function (i, el) {
						$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.id));
					});

					var content = template
						.html()
						.replace(/\{i\}/g, c.find('tbody>tr').length + 2)
						.replace(/\{id\}/g, response.id)
						.replace(/\{key\}/g, response.key)
						.replace(/\{value\}/g, response.value)
						.replace(/\{access\}/g, response.access);

					//template.html(content).insertBefore(li);
					//template.html(content);
					$(c.find('tbody')[0]).append(content);
				}

				key.val(''),
					value.val(''),
					access.val(0);
			},
			error: function (xhr, ajaxOptions, thrownError) {
				//console.log(xhr);
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	$('#main').on('click', '.remove-facet', function (e) {
		e.preventDefault();

		var result = confirm($(this).data('confirm'));

		if (result) {
			var field = $($(this).attr('href'));

			$.ajax({
				url: $(this).data('api'),
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
	});
});
