/**
 * @package    halcyon
 * @copyright  Copyright 2019 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

Halcyon.submitbutton = function(task) {
	var frm = document.getElementById('item-form');

	if (frm) {
		$(document).trigger('editorSave');

		if (task == 'cancel' || document.formvalidator.isValid(frm)) {
			Halcyon.submitform(task, frm);
		} else {
			alert(frm.getAttribute('data-invalid-msg'));
		}
	}
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function() {
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

	var users = $(".form-users");
	if (users.length) {
		users.each(function(i, user){
			user = $(user);
			var cl = user.clone()
				.attr('type', 'hidden')
				.val(user.val().replace(/([^:]+):/, ''));
			user
				.attr('name', user.attr('id') + i)
				.attr('id', user.attr('id') + i)
				.val(user.val().replace(/(:\d+)$/, ''))
				.after(cl);
			user.autocomplete({
				minLength: 2,
				source: function( request, response ) {
					return $.getJSON(user.attr('data-uri').replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
						response($.map(data.data, function (el) {
							return {
								label: el.name + ' (' + el.username + ')',
								name: el.name,
								id: el.id,
							};
						}));
					});
				},
				select: function (event, ui) {
					event.preventDefault();
					// Set selection
					user.val(ui.item.label); // display the selected text
					cl.val(ui.item.id); // save selected id to input
					return false;
				}
			});
		});
	}

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

	$('#field-name').on('keyup', function (e){
		var val = $(this).val();

		val = val.toLowerCase()
			.replace(/\s+/g, '-')
			.replace(/[^a-z0-9\-_]+/g, '');

		$(this).val(val);
	});

	var data = JSON.parse($('#tree-data').html());

	$("#tree").fancytree({
		activate: function(event, data) {
			var node = data.node;
			var did = node.data.id; //node.key.split("/");
			//did = did[3];
			$("#" + did + "_dialog").dialog({
				modal: true,
				width: '550px',
				position: { my: "left top", at: "left top", of: $( "#tree" ) },
				close: function(event) {
					var node = $("#tree").fancytree("getActiveNode").setActive(false);
				}
			});
			$( "#" + did + "_dialog" ).dialog('open');
			$( '#selected_dir' ).attr('value', node.data.parentdir);
			$( '#selected_dir_unixgroup' ).attr('value', node.data.parentunixgroup);
			$( '#new_dir_path' ).html(node.data.path + "/");
			$( '#new_dir_quota_available' ).html(node.data.parentquota);
			$( '#new_dir_quota_available2' ).html(node.data.parentquota);
		},
		persist: true,
		extensions: ["table"],
		table: {
			indentation: 20,      // indent 20px per node level
			nodeColumnIdx: 0,     // render the node title into the 2nd column
			checkboxColumnIdx: 0  // render the checkboxes into the 1st column
		},
		source: data,
		renderColumns: function(event, data) {
			var node = data.node,
			$tdList = $(node.tr).find(">td");

			if ( node.data.quota == '0 B') {
				$tdList.eq(1).text("-");
			} else {
				$tdList.eq(1).text(node.data.quota);
				$tdList.eq(1).attr("id",node.key + "_quota_td");
				if ( node.data.quotaproblem == "1" ) {
					if ( node.data.quota != "-" ) {
						$tdList.eq(1).addClass('quotaProblem');
						$tdList.eq(1).html(node.data.quota + '<img style="float:right;" class="img editicon" alt="Storage space is over-allocated. Quotas reduced until allocation balanced." src="/include/images/error.png" />');
					}
				}
			}
			$tdList.eq(1).addClass('quota');

			if (typeof (node.data.futurequota) != 'undefined') {
				$tdList.eq(2).html(node.data.futurequota);
			}
			$tdList.eq(2).addClass('quota');
			// (index #2 is rendered by fancytree)
		}
	});
});