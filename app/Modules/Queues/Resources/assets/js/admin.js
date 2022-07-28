/* global TomSelect */ // vendor/tom-select/js/tom-select.complete.min.js
/* global Halcyon */ // core.js
/* global Chart */ // vendor/chartjs/Chart.min.js

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	var headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

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

	var sselects = document.querySelectorAll('.searchable-select');
	if (sselects.length) {
		sselects.forEach(function (select) {
			new TomSelect(select, { plugins: ['dropdown_input'] });
		});
	}

	var name = document.getElementById('field-name');
	if (name) {
		name.addEventListener('keyup', function () {
			// Strip out some unwanted characters
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

			document.getElementById('field-clusterlabel').innerHTML = opt.getAttribute('data-clusterlabel');

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

	// When selecting a group to sell or loan from, update its list of queues
	document.querySelectorAll(".form-group-queues").forEach(function (group) {
		var sel = new TomSelect(group);
		sel.on('item_add', function (value) {
			var queue = document.getElementById(group.getAttribute('data-update'));

			// No group. This means we're selling hardware (populating the base group
			// that all resources will be sold or loaned out from). We do this so we
			// can keep a better accounting of available resources and act accordingly.
			if (value == 0) {
				queue.value = 0;
				queue.parentNode.classList.add('d-none');
				return;
			} else {
				queue.parentNode.classList.remove('d-none');
			}

			fetch(group.getAttribute('data-queue-api') + '?' + new URLSearchParams({
				'group': value, //group.value,
				'subresource': document.getElementById('field-subresourceid').value
			}), {
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
					Halcyon.message('danger', error);
				});
		});
	});

	// Update the "cores" field based on the cores-per-node value
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

	// Update the "nodes" field based on the cores-per-node value
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

	// Create or update a loan/purchase
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
				headers: headers,
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

	// Delete a loan/purchase
	document.querySelectorAll('.delete').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;

			if (confirm(btn.getAttribute('data-confirm'))) {
				fetch(btn.getAttribute('data-api'), {
					method: 'DELETE',
					headers: headers
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

	//
	// Stats
	//
	document.querySelectorAll('.items-toggle').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			document.getElementById(this.getAttribute('href').replace('#', '')).classList.toggle('collapse');
		});
	});

	var charts = new Array;
	document.querySelectorAll('.sparkline-chart').forEach(function (el) {
		const ctx = el.getContext('2d');
		const chart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: JSON.parse(el.getAttribute('data-labels')),
				datasets: [
					{
						fill: true,
						data: JSON.parse(el.getAttribute('data-values'))
					}
				]
			},
			options: {
				//responsive: false,
				bezierCurve: false,
				animation: {
					duration: 0
				},
				legend: {
					display: false
				},
				elements: {
					line: {
						borderColor: 'rgb(54, 162, 235)', //'#0091EB',
						backgroundColor: 'rgb(54, 162, 235)',
						borderWidth: 1,
						tension: 0
					},
					point: {
						borderColor: 'rgb(54, 162, 235)'//'#0091EB'
					}
				},
				scales: {
					/*yAxes: [
						{
							display: false
						}
					],*/
					xAxes: [
						{
							display: false
						}
					]
				}
			}
		});
		charts.push(chart);
	});

	document.querySelectorAll('.pie-chart').forEach(function (el) {
		const ctx = el.getContext('2d');
		const pchart = new Chart(ctx, {
			type: 'doughnut',
			data: {
				labels: JSON.parse(el.getAttribute('data-labels')),
				datasets: [
					{
						data: JSON.parse(el.getAttribute('data-values')),
						backgroundColor: [
							'rgb(255, 99, 132)', // red
							'rgb(54, 162, 235)', // blue
							'rgb(255, 205, 86)', // yellow
							'rgb(201, 203, 207)', // grey
							'rgb(75, 192, 192)', // blue green
							'rgb(255, 159, 64)', // orange
							'rgb(153, 102, 255)', // purple
							'rgb(43, 11, 63)',
							'rgb(87, 22, 126)',
							'rgb(155, 49, 146)',
							'rgb(234, 95, 137)',
							'rgb(247, 183, 163)',
							'rgb(255, 241, 201)'
						],
						borderColor: (document.querySelector('html').getAttribute('data-mode') == 'dark' ? "rgba(0, 0, 0, 0.6)" : "#fff")
					}
				]
			},
			options: {
				animation: {
					duration: 0
				}/*,
				legend: {
					display: false
				}*/
			}
		});
		charts.push(pchart);
	});
});
