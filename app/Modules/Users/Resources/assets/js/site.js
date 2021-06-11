jQuery(document).ready(function ($) {
	$('.property-edit').on('click', function (e) {
		e.preventDefault();

		$('.edit-hide').addClass('hide');
		$('.edit-show').removeClass('hide');
	});

	$('.property-cancel').on('click', function (e) {
		e.preventDefault();

		$('.edit-show').addClass('hide');
		$('.edit-hide').removeClass('hide');
	});

	$('.property-save').on('click', function (e) {
		e.preventDefault();

		var btn = $(this);

		$.ajax({
			url: btn.attr('data-api'),
			type: 'put',
			data: {
				loginShell: $('#INPUT_loginshell').val()
			},
			dataType: 'json',
			async: false,
			success: function (data) {
				window.location.reload(true);
			},
			error: function (xhr, reason, thrownError) {
				$('#loginshell_error').removeClass('hide');
				if (xhr.responseJSON) {
					$('#loginshell_error').text(xhr.responseJSON.message);
				} else {
					$('#loginshell_error').text('Failed to update login shell.');
				}
				console.log(xhr.responseText);
			}
		});
	});
});
