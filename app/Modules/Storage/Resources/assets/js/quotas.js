
var headers = {
	'Content-Type': 'application/json'
};
var oldtime = 0;
var currtime = 0;
var checkcount = 0;

/**
 * Check for usage info
 *
 * @param   {string}  api
 * @return  {void}
 */
function check(api) {
	setTimeout(function () {
		fetch(api, {
			method: 'GET',
			headers: headers
		})
		.then(function (response) {
			if (response.ok) {
				return response.json();
			}
			return response.json().then(function (data) {
				var msg = data.message;
				if (typeof msg === 'object') {
					msg = Object.values(msg).join('<br />');
				}
				throw msg;
			});
		})
		.then(function (dat) {
			currtime = dat['latestusage'] ? dat['latestusage']['datetimerecorded'] : 0;
		})
		.catch(function (error) {
			alert(error);
		});

		if (currtime != oldtime) {
			location.reload(true);
		}

		checkcount++;

		if (checkcount < 45 && currtime == oldtime) {
			check(api);
		}

		if (checkcount >= 45) {
			alert("Quota checking system is busy or filesystem is unavailable at the moment. Quota refresh has been scheduled so check back on this page later.");
			window.location.reload(true);
		}
	}, 5000);
}

document.addEventListener('DOMContentLoaded', function () {

	headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	// Ask for confirmation when someone deletes something
	document.querySelectorAll('.storagealert-confirm-delete').forEach(function(el) {
		el.addEventListener('click', function(e) {
			e.preventDefault();

			if (confirm(this.getAttribute('data-confirm'))) {
				fetch(this.getAttribute('data-api'), {
					method: 'DELETE',
					headers: headers
				})
				.then(function (response) {
					if (response.ok) {
						location.reload(true);
						return;
					}
					return response.json().then(function (data) {
						var msg = data.message;
						if (typeof msg === 'object') {
							msg = Object.values(msg).join('<br />');
						}
						throw msg;
					});
				})
				.catch(function (error) {
					alert(error);
				});
			}
		});
	});

	// Create a new alert button
	let createalert = document.getElementById('create-newalert');

	if (createalert) {
		// Save new alert form button
		let newalertsave = document.getElementById('newalert-save');

		newalertsave.addEventListener('click', function(e) {
			e.preventDefault();

			var type = document.querySelector('input[name=newalert]:checked');

			if (!type) {
				return;
			}

			var postdata = {
				value: document.getElementById('newalertvalue').value,
				storagedirquotanotificationtypeid: type.value,
				userid: document.getElementById('HIDDEN_user').value,
				storagedirid: document.querySelector('[name=newalertstorage]').value
			};

			fetch(this.getAttribute('data-api'), {
				method: 'POST',
				headers: headers,
				body: JSON.stringify(postdata)
			})
				.then(function (response) {
					if (response.ok) {
						window.location.reload(true);
						return;
					}
					return response.json().then(function (data) {
						var msg = data.message;
						if (typeof msg === 'object') {
							msg = Object.values(msg).join("\n");
						}
						throw msg;
					});
				})
				.catch(function (error) {
					alert(error);
				});
		});

		// Clear out any previous errors
		createalert.addEventListener('click', function (e) {
			e.preventDefault();

			let err = document.getElementById('newalert_error');
			err.classList.add('hide');
			err.innerHTML = '';
		});

		document.querySelectorAll('input[name="newalert"]').forEach(function(input) {
			input.addEventListener('change', function () {
				document.getElementById('newalertvalue').value = this.getAttribute('data-value');
				document.getElementById('newalertvalueunit').innerHTML = this.getAttribute('data-unit');
			});
		});
	}

	// Create new report button
	let createreport = document.getElementById('create-newreport');

	if (createreport) {
		// Save new report form button
		let newreportsave = document.getElementById('newreport-save');

		newreportsave.addEventListener('click', function (e) {
			e.preventDefault();

			var postdata = {};
			postdata['storagedirquotanotificationtypeid'] = '1';
			postdata['userid'] = document.getElementById('HIDDEN_user').value;
			postdata['timeperiodid'] = document.getElementById('newreportperiod').value;
			postdata['periods'] = document.getElementById('newreportnumperiods').value;
			postdata['value'] = 0;
			postdata['storagedirid'] = document.querySelector('[name=newreportstorage]').value;
			postdata['datetimelastnotify'] = document.getElementById('newreportdate').value;

			fetch(this.getAttribute('data-api'), {
				method: 'POST',
				headers: headers,
				body: JSON.stringify(postdata)
			})
			.then(function (response) {
				if (response.ok) {
					window.location.reload(true);
					return;
				}
				return response.json().then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				});
			})
			.catch(function (error) {
				alert(error);
			});
		});

		createreport.addEventListener('click', function (e) {
			e.preventDefault();

			let err = document.getElementById('newreport_error');
			err.classList.add('hide');
			err.innerHTML = '';
		});
	}

	// Edit buttons
	document.querySelectorAll('.storagealert-edit').forEach(function(el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			let tgt = document.querySelector(this.getAttribute('href'));

			if (tgt) {
				let err = document.querySelector(this.getAttribute('href') + '_not_error');
				err.classList.add('hide');
				err.innerHTML = '';
			}
		});
	});

	// Save edit form buttons
	document.querySelectorAll('.storagealert-edit-save').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;
			var id = btn.getAttribute('data-id');

			fetch(btn.getAttribute('data-api'), {
				method: 'PUT',
				headers: headers,
				body: JSON.stringify({
					'value': (document.getElementById('value_' + id) ? document.getElementById('value_' + id).value : 0),
					'enabled': (document.getElementById('enabled_' + id).checked ? 1 : 0),
					'periods': (document.getElementById('periods_' + id) ? document.getElementById('periods_' + id).value : 0),
					'timeperiodid': (document.getElementById('timeperiod_' + id) ? document.getElementById('timeperiod_' + id).value : 0)
				})
			})
			.then(function (response) {
				if (response.ok) {
					window.location.reload(true);
					return;
				}
				return response.json().then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				});
			})
			.catch(function (error) {
				let err = document.querySelector(btn.getAttribute('data-id') + '_not_error');
				err.classList.remove('hide');
				err.innerHTML = error;
			});
		});
	});

	// Quota checks
	document.querySelectorAll('.updatequota').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;
				//did = btn.getAttribute('data-id');

			// Hide any previous errors
			//let err = document.getElementById(did + '_dialog_error');
			//err.classList.add('hide');
			//err.innerHTML = '';

			// Show working indicator
			btn.classList.add('processing');
			btn.querySelectorAll('.fa').forEach(function (el) {
				el.classList.add('hide');
			});
			btn.querySelectorAll('.spinner-border').forEach(function (el) {
				el.classList.remove('hide');
			});

			fetch(btn.getAttribute('data-api'), {
				method: 'GET',
				headers: headers
			})
			.then(function (response) {
				if (response.ok) {
					return response.json();
				}
				return response.json().then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				});
			})
			.then(function (data) {
				fetch(btn.getAttribute('data-api'), {
					method: 'PUT',
					headers: headers,
					body: JSON.stringify({
						'quotaupdate': '1'
					})
				})
				.then(function (response) {
					if (response.ok) {
						oldtime = data['latestusage'] ? data['latestusage']['datetimerecorded'] : 0;
						currtime = data['latestusage'] ? data['latestusage']['datetimerecorded'] : 0;
						checkcount = 0;

						check(btn.getAttribute('data-api'));

						return;
					}
					return response.json().then(function (data) {
						var msg = data.message;
						if (typeof msg === 'object') {
							msg = Object.values(msg).join('<br />');
						}
						throw msg;
					});
				})
				.catch(function (error) {
					btn.setAttribute('title', error);
					btn.classList.remove('processing');
					btn.querySelectorAll('.fa').forEach(function (el) {
						el.classList.remove('fa-undo');
						el.classList.add('fa-exclamation-triangle');
						el.classList.add('text-danger');
						el.classList.remove('hide');
					});
					btn.querySelectorAll('.spinner-border').forEach(function (el) {
						el.classList.add('hide');
					});
				});
			})
			.catch(function (error) {
				btn.setAttribute('title', error);
				btn.classList.remove('processing');
				btn.querySelectorAll('.fa').forEach(function (el) {
					el.classList.remove('fa-undo');
					el.classList.add('fa-exclamation-triangle');
					el.classList.add('text-danger');
					el.classList.remove('hide');
				});
				btn.querySelectorAll('.spinner-border').forEach(function (el) {
					el.classList.add('hide');
				});
			});
		});
	});
});
