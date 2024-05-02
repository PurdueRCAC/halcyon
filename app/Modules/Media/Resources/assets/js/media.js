/* global Halcyon */
/* global Dropzone */ // dropzone.min.js

if (typeof Dropzone !== 'undefined') {
	Dropzone.autoDiscover = false;
}

/**
 * Show loading spinner
 *
 * @return {void}
 */
function showSpinner() {
	document.querySelectorAll('.spinner').forEach(function (item) {
		item.classList.remove('d-none');
	});
}

/**
 * Hide loading spinner
 *
 * @return {void}
 */
function hideSpinner() {
	document.querySelectorAll('.spinner').forEach(function (item) {
		item.classList.add('d-none');
	});
}

/**
 * Log data if debugging is enabled
 *
 * @param {mixed} msg
 * @return {void}
 */
function log(msg) {
	if (Halcyon.config.app.debug) {
		window.console && console.log(msg);
	}
}

/**
 * Build the URL
 *
 * @param {string} base
 * @param {string} layout
 * @param {string} folder
 * @param {string} page
 * @return {void}
 */
function mediaUrl(base, layout, folder, page) {
	base += (base.indexOf('?') == -1 ? '?' : '&');
	return base + 'layout=' + layout + '&page=' + page + (folder ? '&folder=' + folder : '');
}

let headers = {
	'Content-Type': 'application/json'
};

document.addEventListener('DOMContentLoaded', function () {
	headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	var contents = document.getElementById('media-items'),
		layout = document.getElementById('layout'),
		folder = document.getElementById('folder'),
		page = document.getElementById('page');

	if (!contents) {
		return;
	}

	var isModal = (contents.getAttribute('data-tmpl') == 'component');

	var views = document.querySelectorAll('.media-files-view');
	views.forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			views.forEach(function (view) {
				view.classList.remove('active');
			});

			document.querySelectorAll('.media-files').forEach(function (item) {
				item.classList.remove('active');
			});

			this.classList.add('active');

			var view = this.getAttribute('data-view');
			document.getElementById('media-' + view).classList.add('active');

			layout.value = view;

			var url = window.location.protocol + '//' + window.location.host + window.location.pathname + '?layout=' + view + '&folder=' + folder.value;
			var dataurl = history.state
				? history.state.dataurl.replace(/(layout=[^&]+)/, '') + 'layout=' + view
				: contents.getAttribute('data-list') + '?layout=' + view;
			window.history.pushState({
				dataurl: dataurl,
				folder: folder.value,
				layout: view
			}, '', url);
		});
	});

	document.querySelector('#toolbar-folder-new a').addEventListener('click', function (e) {
		e.preventDefault();

		var title = prompt(this.getAttribute('data-prompt'));
		if (title) {
			var href = this.getAttribute('data-api');
			log({ 'path': folder.value, 'name': title });

			showSpinner();

			fetch(href, {
				method: 'POST',
				headers: headers,
				body: JSON.stringify({
					'path': folder.value,
					'name': title
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
			.then(function (response) {
				log(response);

				fetch(mediaUrl(contents.getAttribute('data-list'), layout.value, folder.value, page.value), {
					method: 'GET',
					headers: headers
				})
				.then(function (response) {
					if (response.ok) {
						return response.text();
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
					log(data);

					contents.innerHTML = data;

					hideSpinner();
				});
			})
			.catch(function (err) {
				alert(err);
			});
		}
	});

	document.querySelectorAll('.media-breadcrumbs-block').forEach(function (el) {
		el.addEventListener('click', function (e) {
			if (!e.target.matches('.media-breadcrumbs')) {
				return;
			}
			e.preventDefault();

			folder.value = e.target.getAttribute('data-folder');

			log('Calling: ' + mediaUrl(e.target.getAttribute('href'), layout.value, '', page.value));

			var fldr = e.target.getAttribute('data-folder');

			breadcrumbs(
				fldr,
				contents.getAttribute('data-list') + '?layout=' + layout.value + '&page=' + page.value + '&folder='
			);

			showSpinner();

			var dataurl = e.target.getAttribute('href');

			fetch(dataurl, {
				method: 'GET',
				headers: headers
			})
			.then(function (response) {
				if (response.ok) {
					return response.text();
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
				contents.innerHTML = data;

				window.history.pushState({
					dataurl: dataurl,
					folder: fldr,
					layout: layout.value
				}, '', dataurl.replace('/files', ''));

				hideSpinner();
			})
			.catch(function (err) {
				alert(err);
			});
		});
	});

	contents
		.addEventListener('click', function (e) {
			console.log(e.target);
			if (e.target.matches('.folder-item')) {
				e.preventDefault();

				var fldr = this.getAttribute('data-folder');
				folder.value = fldr;

				breadcrumbs(
					fldr,
					contents.getAttribute('data-list') + '?layout=' + layout.value + '&page=' + page.value + '&folder='
				);

				showSpinner();

				var url = this.getAttribute('href'),
					dataurl = this.getAttribute('data-href');

				log('Calling: ' + mediaUrl(dataurl, layout.value, '', page.value));

				fetch(mediaUrl(dataurl, layout.value, '', page.value), {
					method: 'GET',
					headers: headers
				})
				.then(function (response) {
					if (response.ok) {
						return response.text();
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
					contents.innerHTML = data;

					window.history.pushState({
						dataurl: dataurl,
						folder: fldr,
						layout: layout.value
					}, '', url);

					hideSpinner();
				})
				.catch(function (err) {
					alert(err);
				});
			}
			else if (e.target.matches('.doc-item')) {
				if (isModal) {
					e.preventDefault();

					// Get the image tag field information
					var url = this.getAttribute('href');

					if (url == '') {
						return false;
					}

					if (document.getElementById('e_name')) {
						var alt = this.getAttribute('title');
						var tag = '<img src="' + url + '" ';

						// Set alt attribute
						if (alt != '') {
							tag += 'alt="' + alt + '" ';
						} else {
							tag += 'alt="" ';
						}

						tag += '/>';

						window.parent.insertEditorText(tag, document.getElementById('e_name').value);
					}

					if (document.getElementById('fieldid')) {
						var id = document.getElementById('fieldid').value;
						window.parent.document.getElementById(id).value = url;
					}

					return false;
				}
			}
			else if (e.target.matches('.media-options-btn')) {
				e.preventDefault();

				var item = e.target.closest('.media-item');

				if (!item.classList.contains('ui-activated')) {
					document.querySelectorAll('.media-item').forEach(function (el) {
						el.classList.remove('ui-activated');
					});
				}
				item.classList.toggle('ui-activated');
			}
			else if (e.target.matches('.media-opt-download')) {
				document.querySelectorAll('.media-item').forEach(function (el) {
					el.classList.remove('ui-activated');
				});
			}
			else if (e.target.matches('.media-opt-rename')) {
				e.preventDefault();

				var title = prompt(e.target.getAttribute('data-prompt'), e.target.getAttribute('data-name'));
				if (title) {
					var href = e.target.getAttribute('data-api');

					var data = {
						'before': e.target.getAttribute('data-path') + '/' + e.target.getAttribute('data-name'),
						'after': e.target.getAttribute('data-path') + '/' + title
					};

					log(data);

					showSpinner();

					fetch(mediaUrl(href, layout.value, '', page.value), {
						method: 'PUT',
						headers: headers,
						body: JSON.stringify(data)
					})
					.then(function (response) {
						if (!response.ok) {
							return response.json().then(function (data) {
								var msg = data.message;
								if (typeof msg === 'object') {
									msg = Object.values(msg).join('<br />');
								}
								throw msg;
							});
						}

						fetch(mediaUrl(contents.getAttribute('data-list'), layout.value, folder.value, page.value), {
							method: 'GET',
							headers: headers
						})
						.then(function (response) {
							if (response.ok) {
								return response.text();
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
							contents.innerHTML = data;

							hideSpinner();
						});
					})
					.catch(function (err) {
						alert(err);
					});
				}
			}
			else if (e.target.matches('.media-opt-move')) {
				e.preventDefault();

				var name = e.target.getAttribute('data-name');
				var before = e.target.getAttribute('data-path') + '/' + name;
				var href = e.target.getAttribute('data-api');

				before = '/' + before.replace(/^\.+/gm, '').replace(/^\/+/gm, '');

				document.getElementById('mover').addEventListener('submit', function (e) {
					e.preventDefault();

					var after = document.getElementById('move-destination').value + '/' + name;

					if (before == after) {
						alert('Cannot move to self.');
					}

					var data = {
						'before': before,
						'after': after
					};

					log(data);

					showSpinner();

					fetch(href, {
						method: 'PUT',
						headers: headers,
						body: JSON.stringify(data)
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
						fetch(mediaUrl(contents.getAttribute('data-list'), layout.value, folder.value, page.value), {
							method: 'GET',
							headers: headers
						})
							.then(function (response) {
								if (response.ok) {
									return response.text();
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
								log(data);

								contents.innerHTML = data;

								hideSpinner();

								if (bootstrap && bootstrap.Modal.VERSION > '5') {
									bootstrap.Modal.getInstance(document.getElementById('media-move')).hide();
								} else {
									$('#media-move').modal('hide');
								}
							});
					})
					.catch(function (err) {
						alert(err);
					});
				});
			}
			else if (e.target.matches('.media-opt-delete')) {
				e.preventDefault();

				var conf = confirm(contents.getAttribute('data-confirm'));
				if (!conf) {
					return;
				}

				var href = e.target.getAttribute('data-api');
				log('Deleting: ' + href);

				showSpinner();

				fetch(href, {
					method: 'DELETE',
					headers: headers
				})
				.then(function (response) {
					if (!response.ok) {
						return response.json().then(function (data) {
							var msg = data.message;
							if (typeof msg === 'object') {
								msg = Object.values(msg).join('<br />');
							}
							throw msg;
						});
					}

					fetch(mediaUrl(contents.getAttribute('data-list'), layout.value, folder.value, page.value), {
						method: 'GET',
						headers: headers
					})
					.then(function (response) {
						if (response.ok) {
							return response.text();
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
						log(data);

						contents.innerHTML = data;

						hideSpinner();
					});
				})
				.catch(function (err) {
					alert(err);
				});
			}
		});

	function breadcrumbs(path, href) {
		var trail = path.split('/'),
			crumbs = '',
			fld = '';

		for (var i = 0; i < trail.length; i++) {
			if (trail[i] == '') {
				continue;
			}

			href += '/' + trail[i];
			fld += '/' + trail[i];

			crumbs += '<span class="fa fa-chevron-right dir-separator">/</span>';
			crumbs += '<a href="' + href + '" data-folder="' + fld + '" class="media-breadcrumbs folder has-next-button" id="path_' + trail[i] + '">' + trail[i] + '</a>';
		}

		document.getElementById('media-breadcrumbs').innerHTML = crumbs;
	}

	document.getElementById('media-tree_tree').querySelectorAll('a').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			folder.value = this.getAttribute('data-folder');

			var fldr = this.getAttribute('data-folder');

			breadcrumbs(
				fldr,
				contents.getAttribute('data-list') + '?layout=' + layout.value + '&folder='
			);

			showSpinner();

			var url = this.getAttribute('href'),
				dataurl = this.getAttribute('data-href');

			log('Calling: ' + dataurl + '&layout=' + layout.value + '&page=' + page.value);

			fetch(dataurl + '&layout=' + layout.value + '&page=' + page.value, {
				method: 'GET',
				headers: headers
			})
			.then(function (response) {
				if (response.ok) {
					return response.text();
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
				log(data);

				contents.innerHTML = data;
				window.history.pushState({ dataurl: dataurl, folder: fldr, layout: layout.value }, '', url);

				hideSpinner();
			})
			.catch(function (err) {
				alert(err);
			});
		});
	});

	window.addEventListener("popstate", function () {
		if (history.state) {
			showSpinner();

			fetch(mediaUrl(history.state.dataurl, history.state.layout, '', page.value), {
				method: 'GET',
				headers: headers
			})
			.then(function (response) {
				if (response.ok) {
					return response.text();
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
				log(data);

				contents.innerHTML = data;

				breadcrumbs(
					history.state.folder,
					contents.getAttribute('data-list') + '?layout=' + layout.value + '&page=' + page.value + '&folder='
				);

				hideSpinner();
			})
			.catch(function (err) {
				alert(err);
			});
		}
	});

	new Dropzone('.dropzone', {
		init: function () {
			this.on("sending", function (file, xhr, formData) {
				formData.append("path", folder.value);
			});
		},
		queuecomplete: function () {
			fetch(mediaUrl(contents.getAttribute('data-list'), document.getElementById('layout').value, document.getElementById('folder').value, document.getElementById('page').value), {
				method: 'GET',
				headers: headers
			})
			.then(function (response) {
				if (response.ok) {
					return response.text();
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
				log(data);

				contents.innerHTML = data;

				Dropzone.forElement('.dropzone').removeAllFiles();

				if (bootstrap && bootstrap.Modal.VERSION > '5') {
					bootstrap.Modal.getInstance(document.getElementById('media-upload')).hide();
				} else {
					$('#media-upload').modal('hide');
				}
			})
			.catch(function (err) {
				alert(err);
			});
		},
		error: function (errorMessage) {
			alert(errorMessage);
		}
	});
});
