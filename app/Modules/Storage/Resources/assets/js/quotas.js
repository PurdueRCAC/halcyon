/* global $ */ // jquery.js

$(document).ready(function () {
	$('.storagealert-confirm-delete').on('click', function (e) {
		e.preventDefault();

		if (confirm($(this).data('confirm'))) {
			$.ajax({
				url: $(this).data('api'),
				type: 'DELETE',
				success: function () {
					location.reload(true);
				},
				error: function () {
					alert("An error occurred. Please reload the page and try again");
				}
			});
		}
	});

	if ($('#create-newalert').length) {
		$('#newalert').dialog({
			modal: true,
			width: '400px',
			autoOpen: false,
			buttons: {
				OK: {
					text: 'Create Alert',
					'class': 'btn btn-success',
					autofocus: true,
					click: function () {

						var val = $('input:radio[name=newalert]:checked').val();

						if (typeof (val) == 'undefined') {
							return;
						}

						var postdata = {
							value: $("#newalertvalue").val(),
							storagedirquotanotificationtypeid: val,
							userid: $('#HIDDEN_user').val(),
							storagedirid: $('[name=newalertstorage]:selected').val()
						};

						//$('#newalert_error').addClass('hide').text('');

						$.ajax({
							url: $('#newalert').data('api'),
							type: 'POST',
							data: postdata,
							success: function () {
								//$(this).dialog('close');
								location.reload(true);
							},
							error: function (result) {
								var response = result.responseJSON;
								var msg = 'An error occurred. Please reload the page and try again.';
								if (response.message) {
									msg = response.message;
									if (typeof msg === 'object') {
										var errors = Object.values(msg);
										msg = errors.join('<br />');
									}
								}
								$('#newalert_error').removeClass('hide').html(msg);
							}
						});
					}
				},
				Cancel: {
					text: 'Cancel',
					'class': 'btn btn-link',
					click: function () {
						$(this).dialog('close');
					}
				}
			}
		});

		$('#create-newalert').on('click', function (e) {
			e.preventDefault();

			$('#newalert_error').addClass('hide').text('');
			$('#newalert').dialog('open');
		});

		$("input[name='newalert']").on('change', function () {
			$("#newalertvalue").val($(this).data('value'));
			$("#newalertvalueunit").html($(this).data('unit'));
		});
	}

	if ($('#create-newreport').length) {
		$('#newreport').dialog({
			modal: true,
			width: '400px',
			autoOpen: false,
			buttons: {
				OK: {
					text: 'Create Report',
					'class': 'btn btn-success',
					click: function () {
						var postdata = {};
						postdata['storagedirquotanotificationtypeid'] = '1';
						postdata['userid'] = $('#HIDDEN_user').val();
						postdata['timeperiodid'] = $('#newreportperiod').val();
						postdata['periods'] = $('#newreportnumperiods').val();
						postdata['value'] = '0';
						postdata['storagedirid'] = $('[name=newreportstorage]:selected').val();
						postdata['datetimelastnotify'] = $('#newreportdate').val();

						//$('#newreport_error').addClass('hide').text('');

						$.ajax({
							url: $('#newreport').data('api'),
							type: 'POST',
							data: postdata,
							success: function () {
								$(this).dialog('close');
								location.reload(true);
							},
							error: function (result) {
								var response = result.responseJSON;
								var msg = 'An error occurred. Please reload the page and try again.';
								if (response.message) {
									msg = response.message;
									if (typeof msg === 'object') {
										var errors = Object.values(msg);
										msg = errors.join('<br />');
									}
								}
								$('#newreport_error').removeClass('hide').text(msg);
							}
						});
					}
				},
				Cancel: {
					text: 'Cancel',
					'class': 'btn btn-link',
					click: function () {
						$(this).dialog('close');
					}
				}
			}
		});

		$('#create-newreport').on('click', function (e) {
			e.preventDefault();

			$('#newreport').dialog('open');
			$('#newreport_error').addClass('hide').text('');
		});
	}

	// Details dialogs
	$('.dialog-storagealert').dialog({
		autoOpen: false,
		modal: true,
		width: '450px'
	});

	$('.storagealert-edit').on('click', function (e) {
		e.preventDefault();

		if ($($(this).attr('href')).length) {
			$($(this).attr('href') + '_not_error').addClass('hide').text('');
			$($(this).attr('href')).dialog('open');
		}
	});

	$('.storagealert-edit-save').on('click', function (e) {
		e.preventDefault();

		var btn = $(this);

		$.ajax({
			url: btn.data('api'),
			type: 'PUT',
			data: {
				'value': ($('#value_' + btn.data('id')).length ? $('#value_' + btn.data('id')).val() : 0),
				'enabled': ($('#enabled_' + btn.data('id') + ':checked').length ? 1 : 0),
				'periods': ($('#periods_' + btn.data('id')).length ? $('#periods_' + btn.data('id')).val() : 0),
				'timeperiodid': ($('#timeperiod_' + btn.data('id')).length ? $('#timeperiod_' + btn.data('id')).val() : 0)
			},
			success: function () {
				location.reload(true);
			},
			error: function (result) {
				var response = result.responseJSON;
				var msg = 'An error occurred. Please reload the page and try again.';
				if (response.message) {
					msg = response.message;
					if (typeof msg === 'object') {
						var errors = Object.values(msg);
						msg = errors.join('<br />');
					}
				}
				$('#' + btn.data('id') + '_not_error').removeClass('hide').text(msg);
			}
		});
	});

	// Quota checks
	$('.updatequota').on('click', function () {
		var btn = $(this),
			did = btn.data('id');

		$('#' + did + '_dialog_error').addClass('hide').html('');

		btn.addClass('processing');
		btn.find('.fa').addClass('hide');
		btn.find('.spinner-border').removeClass('hide');

		$.ajax({
			url: btn.data('api'),
			type: 'GET',
			success: function (data) {
				if (typeof (data) === 'string') {
					data = JSON.parse(data);
				}

				$.ajax({
					url: btn.data('api'),
					type: 'PUT',
					data: { 'quotaupdate': '1' },
					success: function () {

						var oldtime = data['latestusage'] ? data['latestusage']['datetimerecorded'] : 0;
						var currtime = data['latestusage'] ? data['latestusage']['datetimerecorded'] : 0;
						var checkcount = 0;

						function check() {
							setTimeout(function () {
								$.get(btn.data('api'), function (data) {
									if (typeof (data) === 'string') {
										data = JSON.parse(data);
									}

									currtime = data['latestusage'] ? data['latestusage']['datetimerecorded'] : 0;
								});

								if (currtime != oldtime) {
									location.reload(true);
								}

								checkcount++;

								if (checkcount < 45 && currtime == oldtime) {
									check();
								}

								if (checkcount >= 45) {
									alert("Quota checking system is busy or filesystem is unavailable at the moment. Quota refresh has been scheduled so check back on this page later.");
									location.reload(true);
								}
							}, 5000);
						}

						check();
					},
					error: function (result) {
						var response = result.responseJSON;
						var msg = 'An error occurred. Please reload the page and try again.';
						if (response.message) {
							msg = response.message;
							if (typeof msg === 'object') {
								var errors = Object.values(msg);
								msg = errors.join('<br />');
							}
						}

						btn.find('.fa')
							.removeClass('fa-undo')
							.addClass('fa-exclamation-triangle')
							.addClass('text-danger')
							.removeClass('hide');
						btn.find('.spinner-border').addClass('hide');
						btn.attr('title', msg);
					}
				});
			},
			error: function (result) {
				var response = result.responseJSON;
				var msg = 'An error occurred. Please reload the page and try again.';
				if (response.message) {
					msg = response.message;
					if (typeof msg === 'object') {
						var errors = Object.values(msg);
						msg = errors.join('<br />');
					}
				}

				btn.find('.fa')
					.removeClass('fa-undo')
					.addClass('fa-exclamation-triangle')
					.addClass('text-danger')
					.removeClass('hide');
				btn.find('.spinner-border').addClass('hide');
				btn.attr('title', msg);
			}
		});
	});
});
