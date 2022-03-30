/* global $ */ // jquery.js
/* global Halcyon */ // core.js

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {

	var elms = document.querySelectorAll('input[required]');

	for (var i = 0; i < elms.length; i++) {
		elms[i].addEventListener('change', function () {
			if (this.classList.contains('is-invalid')) {
				this.classList.remove('is-invalid');
			}
		});
		elms[i].addEventListener('blur', function () {
			if (!this.value || !this.validity.valid) {
				this.classList.add('is-invalid');
			}
		});
	}

	document.querySelectorAll('.form-groups').forEach(function (group, i) {
		group = $(group);

		var cl = group.clone()
			.attr('type', 'hidden')
			.val(group.val().replace(/([^:]+):/, ''));
		group
			.attr('name', 'groupid' + i)
			.attr('id', group.attr('id') + i)
			.val(group.val().replace(/(:\d+)$/, ''))
			.after(cl);

		group.autocomplete({
			minLength: 2,
			source: function (request, response) {
				return $.getJSON(group.attr('data-uri').replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
					response($.map(data.data, function (el) {
						return {
							label: el.name,
							name: el.name,
							id: el.id,
						};
					}));
				});
			},
			select: function (event, ui) {
				event.preventDefault();
				// Set selection
				group.val(ui.item.label); // display the selected text
				cl.val(ui.item.id); // save selected id to input
				return false;
			}
		});
	});

	var name = document.getElementById('field-name');
	if (name) {
		name.addEventListener('keyup', function () {
			this.value = this.value.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9_-]+/g, '');
		});
	}

	var queueclass = document.getElementById('field-queueclass');
	if (queueclass) {
		queueclass.addEventListener('change', function () {
			if (this.value == 'debug') {
				document.getElementById('field-reservation').checked = true;
			}
		});
	}

	var subresourceid = document.getElementById('field-subresourceid');
	if (subresourceid) {
		subresourceid.addEventListener('change', function () {
			var opt = this.options[this.selectedIndex];

			document.getElementById("SPAN_nodecores").innerHTML = opt.getAttribute('data-nodecores');
			document.getElementById("SPAN_nodemem").innerHTML = opt.getAttribute('data-nodemem');

			document.getElementById('field-nodememmin').value = opt.getAttribute('data-nodemem');
			document.getElementById('field-nodememmax').value = opt.getAttribute('data-nodemem');

			document.getElementById('field-nodecoresmin').value = opt.getAttribute('data-nodecores');
			document.getElementById('field-nodecoresmax').value = opt.getAttribute('data-nodecores');

			document.getElementById("field-cluster").value = opt.getAttribute('data-cluster');
		});
	}

	// Clone the select to preserve all the optgroups
	var select = document.getElementById("field-subresourceid");
	if (select) {
		var sclone = select.cloneNode(true);
		sclone.setAttribute('id', select.getAttribute('id') + '-clone');
		sclone.classList.add('d-none');
		sclone.innerHTML = select.innerHTML;
		sclone.name = '_' + sclone.name;
		select.parentNode.insertBefore(sclone, select);

		var schedulerid = document.getElementById('field-schedulerid');
		if (schedulerid) {
			schedulerid.addEventListener('change', function () {
				if (this.selectedIndex == 0) {
					return;
				}

				// Clear some values
				document.getElementById("SPAN_nodecores").innerHTML = '-';
				document.getElementById("SPAN_nodemem").innerHTML = '-';

				var sched = this,
					opt = sched.options[sched.selectedIndex];

				// Start processing
				sched.parentNode.classList.add('loading');

				// Set max wall time
				document.getElementById("field-maxwalltime").value = parseInt(opt.getAttribute('data-defaultmaxwalltime')) / 60 / 60;

				// Set policy
				var policies = document.getElementById("field-schedulerpolicyid");

				for (var x = 0; x < policies.options.length; x++) {
					if (policies.options[x].value == opt.getAttribute('data-schedulerpolicyid')) {
						policies.options[x].selected = "true";
					} else {
						policies.options[x].selected = "";
					}
				}

				// Get the optgroup for the selected resource
				select.querySelectorAll("optgroup").forEach(function (optgroup) {
					optgroup.remove();
				});
				var optg = sclone.querySelector('optgroup[data-resourceid="' + opt.getAttribute('data-resourceid') + '"]');
				var node = optg.cloneNode();
				node.innerHTML = optg.innerHTML;
				select.appendChild(node);

				// Finished processing
				sched.parentNode.classList.remove('loading');
			});
		}
	}

	document.querySelectorAll('.dialog-btn').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			$($(this).attr('href')).on('shown.bs.modal', function () {
				document.querySelectorAll(".form-group-queues").forEach(function (el) {
					$(el).select2({})
						.on('select2:select', function (e) {
							e.preventDefault();

							var group = this;
							var queue = document.getElementById(group.getAttribute('data-update'));

							if (group.value == 0) {
								queue.value = 0;
								queue.parentNode.classList.add('d-none');
								return;
							} else {
								queue.parentNode.classList.remove('d-none');
							}

							fetch(group.getAttribute('data-queue-api') + '?' + new URLSearchParams({
								'group': group.value,
								'subresource': document.getElementById('field-subresourceid').value
							}), {
								method: 'GET',
								headers: {
									'Content-Type': 'application/json',
									'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
								}
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
									if (data.data.length > 0) {
										queue.disabled = false;
										queue.options.length = 0;

										opt = document.createElement('option');
										opt.value = 0;
										opt.innerHTML = '(Select Queue)';
										queue.append(opt);

										var x, opt;
										for (x in data.data) {
											opt = document.createElement("option");
											opt.innerHTML = data.data[x]['name'] + ' (' + data.data[x]['subresource']['name'] + ')';
											opt.value = data.data[x]['id'];

											queue.append(opt);
										}
									}
								})
								.catch(function (error) {
									console.log(error);
									Halcyon.message('danger', error);
								});

							return false;
						});
				});
			});
		});
	});

	document.querySelectorAll('.nodes').forEach(function (el) {
		el.addEventListener('change', function () {
			var nodecores = this.getAttribute('data-nodes');

			var cores = document.getElementById(this.getAttribute('data-cores-field'));
			var nodes = this.value.replace(/(^\s+|\s+$)/g, '');

			if (nodes.match(RegExp("^[-]?[0-9]+(.[0-9]{1,2})?$"))) {
				cores.value = (nodes * nodecores);
			} else {
				cores.value = '';
			}
		});
	});

	document.querySelectorAll('.cores').forEach(function (el) {
		el.addEventListener('change', function () {
			var nodecores = this.getAttribute('data-cores');

			if (nodecores == 0) {
				return;
			}

			var cores = this.value.replace(/(^\s+|\s+$)/g, '');
			var nodes = document.getElementById(this.getAttribute('data-nodes-field'));

			if (cores.match(RegExp("^[-]?[0-9]+$"))) {
				nodes.value = (cores / nodecores);
			} else {
				nodes.value = '';
			}
		});
	});

	document.querySelectorAll('.queue-dialog-submit').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this,
				frm = this.closest('form'),
				invalid = false;

			if (frm) {
				frm.querySelectorAll('input[required]').forEach(function (el) {
					if (!el.value || !el.validity.valid) {
						el.classList.add('is-invalid');
						invalid = true;
					} else {
						el.classList.remove('is-invalid');
					}
				});

				frm.querySelectorAll('select[required]').forEach(function (el) {
					if (!el.value || el.value <= 0) {
						el.classList.add('is-invalid');
						invalid = true;
					} else {
						el.classList.remove('is-invalid');
					}
				});

				frm.querySelectorAll('textarea[required]').forEach(function (el) {
					if (!el.value || !el.validity.valid) {
						el.classList.add('is-invalid');
						invalid = true;
					} else {
						el.classList.remove('is-invalid');
					}
				});

				if (invalid) {
					return;
				}
			}

			btn.classList.add('loading');

			var data = Object.fromEntries(new FormData(frm).entries());

			fetch(frm.getAttribute('data-api'), {
				method: (btn.getAttribute('data-action') == 'update' ? 'PUT' : 'POST'),
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				},
				body: JSON.stringify(data)
			})
				.then(function (response) {
					if (response.ok) {
						btn.classList.remove('loading');
						Halcyon.message('success', btn.getAttribute('data-success'));
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
					btn.classList.remove('loading');

					Halcyon.message('danger', error);
				});
		});
	});

	document.querySelectorAll('.delete').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;

			if (confirm(btn.getAttribute('data-confirm'))) {
				fetch(btn.getAttribute('data-api'), {
					method: 'DELETE',
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					}
				})
					.then(function (response) {
						if (response.ok) {
							Halcyon.message('success', btn.getAttribute('data-success'));
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
						Halcyon.message('danger', error);
					});
			}
		});
	});

	var aclusersenabled = document.getElementById('aclusersenabled');
	if (aclusersenabled) {
		aclusersenabled.addEventListener('change', function () {
			document.getElementById('field-aclgroups').parentNode.classList.toggle('hide');
		});
	}
});
