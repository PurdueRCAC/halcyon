/* global $ */ // jquery.js

var headers = {
	'Content-Type': 'application/json'
};

/**
 * Pending items count
 *
 * @var  {number}
 */
var pending = 0;

/**
 * List of pending resources
 *
 * @var  {array}
 */
var pending_resources = [];

/**
 * All state
 *
 * @var  {number}
 */
var all_state = 0;

/**
 * Set status for all queues
 *
 * @param   {array}   subresources
 * @param   {number}  state
 * @param   {string}  tab_id
 * @param   {array}   resources
 * @return  {void}
 */
function SetAllQueueStatus(subresources, state, tab_id, resources) {
	var i;
	pending = 0;
	pending_resources = [];
	all_state = state;
	if (typeof (resources) == 'undefined') {
		resources = [];
	}
	setStatusIndicator(tab_id + "_total_status", 'loading');
	for (i = 0; i < resources.length; i++) {
		setStatusIndicator(resources[i] + "_total_status", 'loading');
		pending_resources[i] = resources[i];
	}
	pending_resources[pending_resources.length] = tab_id;
	for (i = 0; i < subresources.length; i++) {
		pending++;
		SetQueueStatus(subresources[i], state);
	}
}

/**
 * Set status for queue and subresources
 *
 * @param   {string}  queue
 * @param   {array}   subresources
 * @param   {number}  state
 * @param   {string}  tab_id
 * @return  {void}
 */
function SetQueueAndSubresourceStatus(queue, subresource, state, tab_id) {
	SetQueueStatus(queue, state);

	// now change the subresource status image to the appropriate color
	//
	// first, collect all owner queue statuses for each subresource
	var table = document.getElementById('owner_queues_' + tab_id);
	var td;
	//var subresource;
	//var color;
	var total = {};
	var total_active = {};
	var subresources = new Array();
	var regex = new RegExp(queue, "i");

	for (var i = 0; i < table.rows.length; i++) {
		td = table.rows[i].cells[0];
		subresource = td.id;
		for (var x = 0; x < td.children.length; x++) {
			if (isImage(td.children[x])) {
				if (Object.prototype.hasOwnProperty.call(total, subresource)) {
					//if (total.hasOwnProperty(subresource)) {
					total[subresource] += 1;
					if ((td.children[x].id.match(regex) && state == 1) || td.children[x].src.match(/green/)) {
						total_active[subresource] += 1;
					}
				} else {
					total[subresource] = 1;
					subresources.push(subresource);
					if ((td.children[x].id.match(regex) && state == 1) || td.children[x].src.match(/green/)) {
						total_active[subresource] = 1;
					} else {
						total_active[subresource] = 0;
					}
				}
			}
		}
	}

	// now loop through each subresource, check its total vs active and set green, yellow, or red light appropriately
	// also keep track of individual subresource colors so subresource-ALL status can be set
	var total_subresources = subresources.length;
	var active_subresources = 0;
	var yellow_subresources = 0;

	for (var z = 0; z < subresources.length; z++) {
		if (total[subresources[z]] == total_active[subresources[z]]) {
			setStatusIndicator(subresources[z] + "_status", 'enabled');
			active_subresources += 1;
		} else if ((total[subresources[z]] > total_active[subresources[z]]) && (total_active[subresources[z]] > 0)) {
			setStatusIndicator(subresources[z] + "_status", 'error');
			yellow_subresources += 1;
		} else {
			setStatusIndicator(subresources[z] + "_status", 'disabled');
		}
	}

	if (total_subresources == active_subresources) {
		setStatusIndicator(tab_id + "_total_status", 'enabled');
	} else if (active_subresources == 0 && yellow_subresources == 0) {
		setStatusIndicator(tab_id + "_total_status", 'disabled');
	} else {
		setStatusIndicator(tab_id + "_total_status", 'error');
	}
}

/**
 * Is the element an image?
 *
 * @param   {object}  i
 * @return  {bool}
 */
function isImage(i) {
	return i instanceof HTMLImageElement;
}

/**
 * Set status for queue
 *
 * @param   {array}   subresources
 * @param   {number}  state
 * @return  {void}
 */
function SetQueueStatus(subresource, state) {
	if (state == 0 || state == 1) {
		setStatusIndicator(subresource + "_status", 'loading');
	}

	var post;
	if (subresource.match(/queue/)) {
		post = JSON.stringify({ 'started': state });
	} else {
		post = JSON.stringify({ 'queuestatus': state });
	}

	fetch(subresource, {
		method: 'POST',
		headers: headers,
		body: post
	})
		.then(function (response) {
			if (response.ok) {
				var results = response.json().then(function (data) {
					return data;
				});
				if (typeof (results['resource']) != 'undefined') {
					if (window.location.pathname == "/admin/queue/") {
						window.location = "/admin/queue/#" + results['resource'];
						location.reload(true);
					} else {
						pending--;
						setStatusIndicator(subresource + "_status", (results['queuestatus'] == "1" ? 'enabled' : 'disabled'));
					}
				} else {
					setStatusIndicator(subresource + "_status", (results['started'] == "1" ? 'enabled' : 'disabled'));
				}
			} else {
				pending--;
				setStatusIndicator(subresource + "_status", 'error');
			}

			if (pending == 0) {
				for (var i = 0; i < pending_resources.length; i++) {
					setStatusIndicator(pending_resources[i] + "_total_status", (all_state == "1" ? 'enabled' : 'disabled'));
				}
			}
		});
}

/**
 * Set indicator status
 *
 * @param   {string}  id
 * @param   {string}  status
 * @return  {void}
 */
function setStatusIndicator(id, status) {
	var img = document.getElementById("IMG_" + id);

	if (img != null) {
		if (status == 'enabled') {
			img.src = "/modules/queues/images/check.png";
			img.alt = "Enabled";
		} else if (status == 'disabled') {
			img.src = "/modules/queues/images/x.png";
			img.alt = "Disabled";
		} else if (status == 'error') {
			img.src = "/modules/queues/images/error.png";
			img.alt = "Error";
		} else if (status == 'loading') {
			img.src = "/modules/queues/images/loading.gif";
			img.alt = "Processing..."
		}
	}
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	document.querySelectorAll('.set-queue-status').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			SetQueueStatus(
				this.getAttribute('data-resource'),
				parseInt(this.getAttribute('data-status'))
			);
		});
	});

	document.querySelectorAll('.set-queue-all-status').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			var queues = this.getAttribute('data-queues').split(',');
			SetAllQueueStatus(
				queues,
				parseInt(this.getAttribute('data-status')),
				this.getAttribute('data-resource')
			);
		});
	});

	document.querySelectorAll('.set-queues-all-status').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			var queues = this.getAttribute('data-queues').split(',');
			var resources = this.getAttribute('data-resources').split(',');
			SetAllQueueStatus(
				queues,
				parseInt(this.getAttribute('data-status')),
				'all',
				resources
			);
		});
	});

	document.querySelectorAll('.set-queue-subresource-status').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			SetQueueAndSubresourceStatus(
				this.getAttribute('data-queue'),
				this.getAttribute('data-subresource'),
				parseInt(this.getAttribute('data-status')),
				this.getAttribute('data-resource')
			);
		});
	});

	document.querySelectorAll('.delete-queue').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			if (confirm(this.getAttribute('data-confirm'))) {
				fetch(this.getAttribute('data-api'), {
					method: 'DELETE',
					headers: headers
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
					.catch(function (err) {
						alert(err);
					});
			}
		});
	});

	// --- Purchases & Loans

	document.querySelectorAll('.dialog-pl-btn').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			$(this.getAttribute('href')).dialog({
				modal: true,
				width: '550px',
				open: function () {
					//var d = $(this);

					var groups = $(".form-group-queues");
					if (groups.length) {
						$(".form-group-queues")
							.select2({})
							.on('select2:select', function (e) {
								e.preventDefault();

								var group = $(this);

								var queue = $('#' + group.data('update'));
								//var dest_queue = group.attr('data-queueid');

								$.ajax({
									url: group.data('queue-api'),
									type: 'get',
									data: {
										'group': group.val(),
										'subresource': group.attr('data-subresource')
									},
									dataType: 'json',
									async: false,
									success: function (data) {
										if (data.data.length > 0) {
											queue.prop('disabled', false);
											queue.empty();//options.length = 0;

											opt = document.createElement("option");
											opt.value = 0;
											opt.innerHTML = "(Select Queue)";
											queue.append(opt);

											var x, opt;
											for (x in data.data) {
												//if (data.data[x]['name'].match(/^(rcac|workq|debug)/)) {
												//if (data.data[x]['id'] != dest_queue) {
												opt = document.createElement("option");
												opt.innerHTML = data.data[x]['name'] + " (" + data.data[x]['subresource']['name'] + ")";
												opt.value = data.data[x]['id'];

												queue.append(opt);
												//}
												//}
											}
										}
									},
									error: function (xhr) {
										var msg = 'Failed to retrieve queues.';
										if (xhr.responseJSON && xhr.responseJSON.message) {
											msg = xhr.responseJSON.message;
										}
										alert(msg);

										console.log(xhr.responseText);
									}
								});
								return false;
							});
					}
				}
			});
		});
	});

	// Update the "cores" field based on the cores-per-node value
	document.querySelectorAll('.nodes').forEach(function (el) {
		el.addEventListener('change', function () {
			var nodecores = this.getAttribute('data-cores');
			var nodegpus = this.getAttribute('data-gpus');

			var cores = document.getElementById(this.getAttribute('data-cores-field'));
			var gpus = document.getElementById(this.getAttribute('data-gpus-field'));
			var nodes = this.value.replace(/(^\s+|\s+$)/g, '');

			if (nodes.match(RegExp("^[-]?[0-9]+(.[0-9]{1,2})?$"))) {
				cores.value = Math.round(nodes * nodecores);
				if (gpus) {
					gpus.value = Math.round(nodes * nodegpus);
				}
			} else {
				cores.value = '';
				if (gpus) {
					gpus.value = '';
				}
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

	// Update the "nodes" field based on the gpus-per-node value
	document.querySelectorAll('.gpus').forEach(function (el) {
		el.addEventListener('change', function () {
			var nodegpus = this.getAttribute('data-gpus');

			if (nodegpus == 0) {
				return;
			}

			var gpus = this.value.replace(/(^\s+|\s+$)/g, '');
			var nodes = document.getElementById(this.getAttribute('data-nodes-field'));

			var cores = document.getElementById(nodes.getAttribute('data-cores-field'));

			if (gpus.match(RegExp("^[-]?[0-9]+$"))) {
				nodes.value = (gpus / nodegpus);
				cores.value = Math.round(nodes.value * nodes.getAttribute('data-cores'));
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
				var elms = frm.querySelectorAll('input[required]');
				elms.forEach(function (el) {
					if (!el.value || !el.validity.valid) {
						el.classList.add('is-invalid');
						invalid = true;
					} else {
						el.classList.remove('is-invalid');
					}
				});
				elms = frm.querySelectorAll('select[required]');
				elms.forEach(function (el) {
					if (!el.value || el.value <= 0) {
						el.classList.add('is-invalid');
						invalid = true;
					} else {
						el.classList.remove('is-invalid');
					}
				});
				elms = frm.querySelectorAll('textarea[required]');
				elms.forEach(function (el) {
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

			var post = {},
				fields = new FormData(frm);

			for (var key of fields.keys()) {
				post[key] = fields.get(key);
			}

			fetch(frm.getAttribute('data-api'), {
				method: btn.getAttribute('data-action') == 'update' ? 'PUT' : 'POST',
				headers: headers,
				body: JSON.stringify(post)
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
				.catch(function (err) {
					alert(err);
				});
		});
	});

	document.querySelectorAll('.queue-pl-delete').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			if (confirm(this.getAttribute('data-confirm'))) {
				fetch(this.getAttribute('data-api'), {
					method: 'DELETE',
					headers: headers
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
					.catch(function (err) {
						alert(err);
					});
			}
		});
	});

	document.querySelectorAll('.queue-pl-edit').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			$(this.getAttribute('href')).dialog({
				modal: true,
				width: '550px'
			});
		});
	});

	// Create queue
	var qname = document.getElementById('queue-name');
	if (qname) {
		qname.addEventListener('keyup', function () {
			var val = this.value;

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9_-]+/g, '');

			this.value = val;
		});
	}

	var qclass = document.getElementById('queue-queueclass');
	if (qclass) {
		qclass.addEventListener('change', function () {
			var val = this.value;

			if (val == 'debug') {
				document.getElementById('queue-reservation').checked = true;
			}
		});
	}

	var qsubresource = document.getElementById('queue-subresourceid');
	if (qsubresource) {
		qsubresource.addEventListener('change', function () {
			var opt = this.options[this.selectedIndex];

			var nodecores = document.getElementById("SPAN_nodecores");
			var nodemem = document.getElementById("SPAN_nodemem");
			var cluster = document.getElementById("queue-cluster");

			document.getElementById('queue-clusterlabel').innerHTML = opt.getAttribute('data-clusterlabel');

			nodecores.innerHTML = opt.getAttribute('data-nodecores');
			nodemem.innerHTML = opt.getAttribute('data-nodemem');

			var nodememmin = document.getElementById('queue-nodememmin');
			nodememmin.value = opt.getAttribute('data-nodemem');

			var nodememmax = document.getElementById('queue-nodememmax');
			nodememmax.value = opt.getAttribute('data-nodemem');

			var nodecoresmin = document.getElementById('queue-nodecoresmin');
			nodecoresmin.value = opt.getAttribute('data-nodecores');

			var nodecoresmax = document.getElementById('queue-nodecoresmax');
			nodecoresmax.value = opt.getAttribute('data-nodecores');

			cluster.value = opt.getAttribute('data-cluster');
		});
	}

	// Clone the select to preserve all the optgroups
	var select = document.getElementById("queue-subresourceid");
	var sclone = select.cloneNode(true);
	sclone.setAttribute('id', select.getAttribute('id') + '-clone');

	var qscheduler = document.getElementById('queue-schedulerid');
	if (qscheduler) {
		qscheduler.addEventListener('change', function () {
			if (this.selectedIndex == 0) {
				return;
			}

			// Clear some values
			document.getElementById("SPAN_nodecores").innerHTML = '-';
			document.getElementById("SPAN_nodemem").innerHTML = '-';

			var sched = this,
				opt = sched.options[sched.selectedIndex];

			// Start processing
			sched.parentNode.className = sched.parentNode.className + ' loading';

			// Set max wall time
			document.getElementById("queue-maxwalltime").value = parseInt(opt.getAttribute('data-defaultmaxwalltime')) / 60 / 60;

			// Set policy
			var policies = document.getElementById("queue-schedulerpolicyid");

			for (var x = 0; x < policies.options.length; x++) {
				if (policies.options[x].value == opt.getAttribute('data-schedulerpolicyid')) {//results['defaultpolicy']['id']) {
					policies.options[x].selected = "true";
				} else {
					policies.options[x].selected = "";
				}
			}

			// Get the optgroup for the selected resource
			select.querySelectorAll("optgroup").forEach(function (el) {
				el.remove();
			});
			select.append(sclone.querySelector("optgroup[data-resourceid='" + opt.getAttribute('data-resourceid') + "']").cloneNode(true));

			// Finished processing
			sched.parentNode.className = sched.parentNode.className.replace(' loading', '');
		});
	}
});
