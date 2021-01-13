
/**
 * New queue group
 *
 * @param   {string}  subresource
 * @return  {void}
 */
function NewQueueGroup(subresource) {
	var group = document.getElementById("group");
	var queue = document.getElementById("queue");
	var opt = document.createElement("option");

	queue.options.length = 0;
	opt.innerHTML = "- Select Queue -";
	queue.appendChild(opt);

	if (group.selectedIndex > 0) {
		group = group.options[group.selectedIndex].value;

		queue.disabled = false;

		WSGetURL(group, NewQueuePopulateQueue, subresource);
	} else {
		queue.disabled = true;
	}
}

/**
 * New queue populate queue
 *
 * @param   {object}  xml
 * @param   {string}  subresource
 * @return  {void}
 */
function NewQueuePopulateQueue(xml, subresource) {
	if (xml.status == 200) {
		var group = JSON.parse(xml.responseText);
		var queue = document.getElementById("queue");
		var dest_queue = document.getElementById("queueid").value;

		queue.options.length = 0;
		var count = 0;
		var x, opt;
		for (x in group['queues']) {
			if (group['queues'][x]['subresource']['id'] == subresource || group['queues'][x]['name'].match(/^(rcac|workq|debug)/)) {
				if (group['queues'][x]['id'] != dest_queue) {
					count++;
				}
			}
		}
		if (count > 1) {
			opt = document.createElement("option");
			opt.innerHTML = "(Select Queue)";
			queue.appendChild(opt);
		}

		for (x in group['queues']) {
			if (group['queues'][x]['subresource']['id'] == subresource || group['queues'][x]['name'].match(/^(rcac|workq|debug)/)) {
				if (group['queues'][x]['id'] != dest_queue) {
					opt = document.createElement("option");
					opt.innerHTML = group['queues'][x]['name'] + " (" + group['queues'][x]['subresource']['name'] + ")";
					opt.value = group['queues'][x]['id'];

					queue.appendChild(opt);
				}
			}
		}
	} else {
		SetError(ERRORS['unknown'], null);
	}
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function() {

	var elms = document.querySelectorAll('input[required]');

	for (i = 0; i < elms.length; i++) {
		elms[i].addEventListener('change', function(e) {
			if (this.classList.contains('is-invalid')) {
				this.classList.remove('is-invalid');
			}
			/*if (!this.value || !this.validity.valid) {
				this.classList.add('is-invalid');
			}*/
		});
		elms[i].addEventListener('blur', function (e) {
			if (!this.value || !this.validity.valid) {
				this.classList.add('is-invalid');
			}
		});
	}

	/*var autocompleteUsers = function(url) {
		return function(request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
				response($.map(data.data, function (el) {
					return {
						label: el.name + ' (' + el.username + ')',
						name: el.name,
						id: el.id,
					};
				}));
			});
		};
	};

	var autocompleteGroups = function(url) {
		return function(request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
				response($.map(data.data, function (el) {
					return {
						label: el.name,
						name: el.name,
						id: el.id,
					};
				}));
			});
		};
	};

	var newsuser = $(".form-users");
	if (newsuser.length) {
		newsuser.tagsInput({
			placeholder: 'Select user...',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteUsers(newsuser.attr('data-uri')),
				dataName: 'data',
				height: 150,
				delay: 100,
				minLength: 1
			},
			limit: 1
		});
	}

	var newsuser = $(".form-groups");
	if (newsuser.length) {
		newsuser.tagsInput({
			placeholder: 'Select group...',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteGroups(newsuser.attr('data-uri')),
				dataName: 'data',
				height: 150,
				delay: 100,
				minLength: 1
			},
			limit: 1
		});
	}*/

	var groups = $(".form-groups");
	if (groups.length) {
		groups.each(function(i, group){
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
				source: function( request, response ) {
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
	}

	$('#field-name').on('keyup', function (e) {
		var val = $(this).val();

		val = val.toLowerCase()
			.replace(/\s+/g, '_')
			.replace(/[^a-z0-9_]+/g, '');

		$(this).val(val);
	});

	$('#field-queueclass').on('change', function (e) {
		var val = $(this).val();

		if (val == 'debug') {
			$('#field-reservation').prop('checked', true);
		}
	});

	$('#field-subresourceid').on('change', function (event) {
		//ChangeNodeType();
		//document.getElementById("SPAN_nodecores").innerHTML = '';
		//document.getElementById("SPAN_nodemem").innerHTML = '';

		var opt = this.options[this.selectedIndex];

		var nodecores = document.getElementById("SPAN_nodecores");
		var nodemem = document.getElementById("SPAN_nodemem");
		var cluster = document.getElementById("field-cluster");

		//if (opt.getAttribute('data-nodecores') != "") {
			nodecores.innerHTML = opt.getAttribute('data-nodecores');
		/*} else {
			nodecores.innerHTML = "--";
		}
		if (opt.getAttribute('nodemem') != "") {*/
			nodemem.innerHTML = opt.getAttribute('data-nodemem');
		/*} else {
			nodecores.innerHTML = "--";
		}*/

		var nodememmin = document.getElementById('field-nodememmin');
		//if (!nodememmin.value) {
			nodememmin.value = opt.getAttribute('data-nodemem');
		//}

		var nodememmax = document.getElementById('field-nodememmax');
		//if (!nodememmax.value) {
			nodememmax.value = opt.getAttribute('data-nodemem');
		//}

		var nodecoresmin = document.getElementById('field-nodecoresmin');
		//if (!nodecoresmin.value) {
			nodecoresmin.value = opt.getAttribute('data-nodecores');
		//}

		var nodecoresmax = document.getElementById('field-nodecoresmax');
		//if (!nodecoresmax.value) {
			nodecoresmax.value = opt.getAttribute('data-nodecores');
		//}

		cluster.value = opt.getAttribute('data-cluster');
	});

	/*$('#SELECT_queuetype').on('change', function (event) {
		UpdateQueueType();
	});*/

	// Clone the select to preserve all the optgroups
	var select = document.getElementById("field-subresourceid");
	var sclone = $(select).clone().attr('id', $(select).attr('id') + '-clone');

	$('#field-schedulerid').on('change', function (event) {
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
		document.getElementById("field-maxwalltime").value = parseInt(opt.getAttribute('data-defaultmaxwalltime')) / 60 / 60;

		// Set policy
		var policies = document.getElementById("field-schedulerpolicyid");

		for (var x=0;x<policies.options.length;x++) {
			if (policies.options[x].value == opt.getAttribute('data-schedulerpolicyid')) {//results['defaultpolicy']['id']) {
				policies.options[x].selected = "true";
			} else { 
				policies.options[x].selected = "";
			}
		}

		// Get the optgroup for the selected resource
		$(select).find("optgroup").remove();
		$(select).append(sclone.find("optgroup[data-resourceid='" + opt.getAttribute('data-resourceid') + "']").clone());

		// Finished processing
		sched.parentNode.className = sched.parentNode.className.replace(' loading', '');

		/*var select = document.getElementById("field-subresourceid");
		var optgroups = select.getElementsByTagName('optgroup');
		for (var i = 0; i < optgroups.length; i++) {
			if (optgroups[i].getAttribute('data-resourceid') != opt.getAttribute('data-resourceid')) {
				optgroups[i].remove();
			}
		}

		$.getJSON(opt.getAttribute('data-resourceid') + '?api_token=' + $('meta[name="api-token"]').attr('content'), function(results) {
			sched.parentNode.className = sched.parentNode.className.replace(' loading', '');

			var select = document.getElementById("field-subresourceid");
				select.innerHTML = '';

			var opt = document.createElement("option");
				opt.innerHTML = "(Select Node Type)";

			select.appendChild(opt);

			for (var x in results['subresources']) {
				opt = document.createElement("option");
				opt.value = results['subresources'][x]['id'];
				opt.innerHTML = results['subresources'][x]['name'];

				select.appendChild(opt);
			}
		});*/
	});

	$('.dialog-btn').on('click', function(e){
		e.preventDefault();

		/*$(".dialog").dialog({
			modal: true,
			width: '550px'
		});*/
		$($(this).attr('href')).dialog({
			modal: true,
			width: '550px'
		});
	});

	$('.nodes').on('keyup', function (e) {
		var nodecores = $(this).data('nodes');

		var cores = document.getElementById(this.getAttribute('data-cores-field'));
		var nodes = this.value.replace(/(^\s+|\s+$)/g, "");

		if (nodes.match(RegExp("^[\-]?[0-9]+$"))) {
			cores.value = (nodes * nodecores);
		} else {
			cores.value = "";
		}
	});

	$('.cores').on('keyup', function (e) {
		var nodecores = $(this).data('cores');

		if (nodecores == 0) {
			return;
		}

		var cores = this.value.replace(/(^\s+|\s+$)/g, "");
		var nodes = document.getElementById(this.getAttribute('data-nodes-field'));

		if (cores.match(RegExp("^[\-]?[0-9]+$"))) {
			nodes.value = (cores / nodecores);
		} else {
			nodes.value = "";
		}
	});

	var groups = $(".form-group-queues");
	if (groups.length) {
		groups.each(function (i, group) {
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

					var queue = $('#' + group.data('update'));
					var dest_queue = document.getElementById("field-id").value;
					console.log(group.data('queue-api') + '?groupid=' + ui.item.id + '&subresource=' + group.data('subresource'));

					$.ajax({
						url: group.data('queue-api'),
						type: 'get',
						data: {
							'group': ui.item.id,
							'subresource': group.data('subresource')
						},
						dataType: 'json',
						async: false,
						success: function (data) {
							if (data.data.length > 0) {
								queue.prop('disabled', false);
								queue.empty();//options.length = 0;

								opt = document.createElement("option");
								opt.innerHTML = "- Select Queue -";
								queue.append(opt);

								var x, opt;
								for (x in data.data) {
									//if (data.data[x]['name'].match(/^(rcac|workq|debug)/)) {
										if (data.data[x]['id'] != dest_queue) {
											opt = document.createElement("option");
											opt.innerHTML = data.data[x]['name'] + " (" + data.data[x]['subresource']['name'] + ")";
											opt.value = data.data[x]['id'];

											//queue.appendChild(opt);
											queue.append(opt);
										}
									//}
								}
							}
						},
						error: function (xhr, reason, thrownError) {
							if (xhr.responseJSON) {
								Halcyon.message('danger', xhr.responseJSON.message);
							} else {
								Halcyon.message('danger', 'Failed to retrieve queues.');
							}
							console.log(xhr.responseText);
						}
					});
					return false;
				}
			});
		});
	}

	$('.dialog-submit').on('click', function(e){
		e.preventDefault();

		var btn = this,
			frm = $(this).closest('form');

		$.ajax({
			url: frm.data('api'),
			type: 'post',
			data: frm.serialize(),
			dataType: 'json',
			async: false,
			success: function (data) {
				Halcyon.message('success', btn.getAttribute('data-success'));
				window.location.reload(true);
			},
			error: function (xhr, reason, thrownError) {
				if (xhr.responseJSON) {
					Halcyon.message('danger', xhr.responseJSON.message);
				} else {
					Halcyon.message('danger', 'Failed to create item.');
				}
				console.log(xhr.responseText);
			}
		});
	});

	$('.delete').on('click', function(e){
		e.preventDefault();

		var btn = this;

		if (confirm(btn.getAttribute('data-confirm'))) {
			$.ajax({
				url: btn.getAttribute('data-api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function (data) {
					Halcyon.message('success', btn.getAttribute('data-success'));
					window.location.reload(true);
				},
				error: function (xhr, reason, thrownError) {
					if (xhr.responseJSON) {
						Halcyon.message('danger', xhr.responseJSON.message);
					} else {
						Halcyon.message('danger', 'Failed to delete item.');
					}
					console.log(xhr.responseText);
				}
			});
		}
	});

	$('#field-aclusersenabled').on('change', function (e) {
		$('#field-aclgroups').parent().toggleClass('hide');
	});

	/*$('#create_queue_btn').on('click', function (event) {
		event.preventDefault();
		CreateQueue(this);
	});*/
});
