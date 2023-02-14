/* global TomSelect */ // vendor/tom-select/js/tom-select.complete.min.js

/**
 * Create new group
 *
 * @return  {void}
 */
function CreateNewGroup() {
	var input = document.getElementById("new_group_input"),
		name = input.value;

	if (!name) {
		document.getElementById('new_group_action').innerHTML = 'Please enter a group name';
		return;
	}

	var post = JSON.stringify({
		'name': name,
		'userid': input.getAttribute('data-userid')
	});

	fetch(input.getAttribute('data-api'), {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
		},
		body: post
	})
	.then(function (response) {
		document.getElementById(document.getElementById('new_group_btn').getAttribute('data-indicator')).classList.add('hide');

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
		var err = document.getElementById('new_group_action');
		err.classList.remove('hide');
		err.innerHTML = error;

		document.getElementById('new_group_btn').disabled = false;
	});
}

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
function CreateNewGroupVal(num, btn, all) {
	var group = btn.data('group');
	//var base = btn.data('value');

	if (typeof (all) == 'undefined') {
		all = true;
	}

	// The callback only accepts one argument, so we
	// need to compact this
	//var args = [num, group];
	var post = {
		'longname': BASEGROUPS[num],
		'groupid': group
	};

	fetch(input.getAttribute('data-api'), {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
		},
		body: JSON.stringify(post)
	})
	.then(function (response) {
		if (response.ok) {
			num++;
			if (all && num < BASEGROUPS.length) {
				setTimeout(function () {
					CreateNewGroupVal(num, btn, all);
				}, 5000);
			} else {
				window.location.reload(true);
			}
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
		btn.querySelector('.spinner-border').classList.add('d-none');
		alert(error);
	});
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	//---------
	// Departments and Fields of Science

	if (typeof TomSelect !== 'undefined') {
		var sselects = document.querySelectorAll(".searchable-select");
		if (sselects.length) {
			sselects.forEach(function (input) {
				new TomSelect(input);
			});
		}
	}

	document.querySelectorAll('.edit-categories').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var container = document.getElementById(this.getAttribute('href').replace('#', ''));
			container.querySelectorAll('.edit-show').forEach(function (it) {
				it.classList.remove('hide');
			});
			container.querySelectorAll('.edit-hide').forEach(function (it) {
				it.classList.add('hide');
			});
		});
	});
	document.querySelectorAll('.cancel-categories').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var container = document.getElementById(this.getAttribute('href').replace('#', ''));
			container.querySelectorAll('.edit-hide').forEach(function (it) {
				it.classList.remove('hide');
			});
			container.querySelectorAll('.edit-show').forEach(function (it) {
				it.classList.add('hide');
			});
		});
	});

	document.querySelectorAll('.add-category').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var select = document.getElementById(this.getAttribute('href').replace('#', ''));
			var btn = this;

			// create new relationship
			fetch(btn.getAttribute('data-api'), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				},
				body: JSON.stringify({
					'groupid': btn.getAttribute('data-group'),
					[select.getAttribute('data-category')]: select.value
				})
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
			.then(function (result) {
				var c = select.closest('ul');
				var li = c.querySelector('li.hide');

				if (li) {
					var template = li.cloneNode(true);

					template.classList.remove('hide');
					template.id = template.id.replace(/\{id\}/g, result.id);
					template.setAttribute('data-id', result.id);

					template.querySelectorAll('a').forEach(function (a) {
						a.setAttribute('data-api', a.getAttribute('data-api').replace(/\{id\}/g, result.id));
					});

					var content = template.innerHTML;
					content = content.replace(/\{id\}/g, result.id);
					content = content.replace(/\{name\}/g, select.options[select.selectedIndex].innerHTML);
					
					template.innerHTML = content;

					c.insertBefore(template, li);
				}

				select.value = 0;
			})
			.catch(function (error) {
				var tr = btn.closest('tr');
				if (tr) {
					var td = tr.querySelector('td');
					if (td) {
						var span = document.createElement("div");
						span.classList.add('text-warning');
						span.append(document.createTextNode(error));
						td.append(span);
					}
				}
				btn.classList.add('hide');
			});
		});
	});

	document.querySelector('body').addEventListener('click', function(e) {
		if (!e.target.parentNode.matches('.remove-category')) {
			return;
		}
		e.preventDefault();

		var btn = e.target.parentNode;

		var result = confirm(btn.getAttribute('data-confirm'));

		if (result) {
			var field = document.getElementById(btn.getAttribute('href').replace('#', ''));

			// delete relationship
			fetch(btn.getAttribute('data-api'), {
				method: 'DELETE',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				}
			})
			.then(function (response) {
				if (response.ok) {
					field.remove();
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

	//---------
	// Unix Groups

	document.querySelectorAll('.input-unixgroup').forEach(function (el) {
		el.addEventListener('keyup', function () {
			var val = this.value;

			val = val.toLowerCase()
				.replace(/\s+/g, '-')
				.replace(/[^a-z0-9-]+/g, '');

			this.value = val;
		});
	});

	document.querySelectorAll('.create-default-unix-groups').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			this.setAttribute('data-loading', true);

			CreateNewGroupVal(0, this, parseInt(this.getAttribute('data-all-groups')));
		});
	});

	var longname = document.getElementById('longname');
	if (longname) {
		longname.addEventListener('change', function () {
			this.classList.remove('is-invalid');
			this.classList.remove('is-valid');

			if (this.value) {
				if (this.validity.valid) {
					this.classList.add('is-valid');
				} else {
					this.classList.add('is-invalid');
				}
			}
		});
	}

	document.querySelectorAll('.add-unixgroup').forEach(function(el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var name = document.getElementById(this.getAttribute('href').replace('#', ''));
			var btn = this;

			name.classList.remove('is-invalid');
			name.classList.remove('is-valid');

			if (name.value && name.validity.valid) {
				name.classList.add('is-valid');
			} else {
				name.classList.add('is-invalid');
				return false;
			}

			// create new relationship
			fetch(btn.getAttribute('data-api'), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				},
				body: JSON.stringify({
					'groupid': btn.getAttribute('data-group'),
					'longname': name.value
				})
			})
			.then(function (response) {
				if (response.ok) {
					window.location.reload(true);
					return; // response.json();
				}
				return response.json().then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				});
			})
			/*.then(function (result) {
				var c = document.querySelector(btn.getAttribute('data-container'));
				var li = c.querySelector('tr.hidden');

				if (li) {
					var template = li.cloneNode(true);
					template.classList.remove('hidden');

					template.id = template.id.replace(/\{id\}/g, result.id);
					template.setAttribute('data-id', result.id);

					template.querySelectorAll('a').forEach(function (a) {
						a.setAttribute('data-api', a.getAttribute('data-api').replace(/\{id\}/g, result.id));
					});

					var content = template.innerHTML;
					content = content.replace(/\{id\}/g, result.id);
					content = content.replace(/\{longname\}/g, result.longname)
					content = content.replace(/\{shortname\}/g, result.shortname);

					template.innerHTML = content
					
					li.parentNode.insertBefore(template, li);

					var uused = document.getElementById('unix-used');
					var total = parseInt(uused.innerHTML);
					total = total + 1;
					uused.innerHTML = total;

					if (total >= 26) {
						btn.classList.add('disabled');
						btn.disabled = true;
					}
				}

				name.classList.remove('is-valid');
				name.value = '';
			})*/
			.catch(function (error) {
				name.classList.add('is-invalid');

				var err = document.querySelector(btn.getAttribute('data-error'));
				if (err) {
					err.classList.remove('hide');
					err.innerHTML = error;
				}
			});
		});
	});

	document.querySelector('body').addEventListener('click', function (e) {
		if (!e.target.parentNode.matches('.remove-unixgroup')) {
			return;
		}
		e.preventDefault();

		var btn = e.target.parentNode;
		var result = confirm(btn.getAttribute('data-confirm'));

		if (result) {
			// delete relationship
			fetch(btn.getAttribute('data-api') + '?groupid=' + btn.getAttribute('data-value'), {
				method: 'DELETE',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				}
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
				var tr = btn.closest('tr');
				if (tr) {
					var td = tr.querySelector('td');
					if (td) {
						var span = document.createElement("div");
						span.classList.add('text-warning');
						span.append(document.createTextNode(error));
						td.append(span);
					}
				}
				btn.classList.add('hide');
			});
		}
	});

	//---------
	// New group

	var newgroup = document.getElementById('new_group_btn');
	if (newgroup) {
		newgroup.addEventListener('click', function (e) {
			e.preventDefault();
			document.getElementById(this.getAttribute('data-indicator').replace('#', '')).classList.remove('hide');
			this.disabled = true;
			CreateNewGroup();
		});
	}

	var newgroupi = document.getElementById('new_group_input');
	if (newgroupi) {
		newgroupi.addEventListener('keyup', function (e) {
			if (e.keyCode == 13) {
				CreateNewGroup();
			}
		});
	}
});
