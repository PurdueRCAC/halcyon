/* global TomSelect */ // vendor/tom-select/js/tom-select.complete.min.js
/* global ForMe */ // orders.js
/* global CancelMou */ // orders.js
/* global UpdateOrderTotal */ // orders.js
/* global OpenUserSearch */ // orders.js

var headers = {
	'Content-Type': 'application/json'
};

// Force update of totals in case browswer is caching values
document.addEventListener('DOMContentLoaded', function () {
	headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	// ---- Cart page

	let cont = document.getElementById('continue');
	if (cont) {
		cont.addEventListener('click', function (e) {
			e.preventDefault();
			ForMe();
		});
	}

	let cancel = document.getElementById('cancel');
	if (cancel) {
		cancel.addEventListener('click', function (e) {
			e.preventDefault();
			CancelMou();
		});
	}

	UpdateOrderTotal();

	let suser = document.getElementById('search_user');
	if (suser) {
		let sel = new TomSelect(suser, {
			maxItems: 1,
			valueField: 'id',
			labelField: 'name',
			searchField: ['name', 'username', 'email'],
			plugins: {
				clear_button: {
					title: 'Remove selected',
				}
			},
			persist: false,
			// Fetch remote data
			load: function (query, callback) {
				var url = suser.getAttribute('data-api') + '?order=name&order_dir=asc&search=' + encodeURIComponent(query);

				fetch(url, {
					method: 'GET',
					headers: headers
				})
					.then(response => response.json())
					.then(json => {
						for (var i = 0; i < json.data.length; i++) {
							if (!json.data[i].id) {
								json.data[i].id = json.data[i].username;
							}
						}
						callback(json.data);
					}).catch(() => {
						callback();
					});
			},
			// Custom rendering functions for options and items
			render: {
				// Option list when searching
				option: function (item, escape) {
					if (item.name.match(/\([a-z0-9]+\)$/)) {
						item.username = item.name.replace(/([^(]+\()/, '').replace(/\)$/, '');
						item.name = item.name.replace(/\s(\([a-z0-9]+\))$/, '');
					}
					return `<div data-id="${escape(item.id)}">${escape(item.name)} <span class="text-muted">(${escape(item.username)})</span></div>`;
				},
				// Selected items
				item: function (item, escape) {
					if (item.name.match(/\([a-z0-9-]+\)$/)) {
						if (isNaN(item.id)) {
							item.id = item.username;
						}
						item.username = item.name.replace(/([^(]+\()/, '').replace(/\)$/, '');
						item.name = item.name.replace(/\s(\([a-z0-9-]+\))$/, '');
					}
					return `<div data-id="${escape(item.id)}">${escape(item.name)}&nbsp;<span class="text-muted">(${escape(item.username)})</span></div>`;
				}
			}
		});
		sel.on('item_add', function () {
			document.getElementById(suser.id + '-ts-control').value = '';
		});
	}

	let formeyes = document.getElementById('formeyes');
	if (formeyes) {
		formeyes.addEventListener('click', function () {
			OpenUserSearch();
		});
	}
	let formno = document.getElementById('formno');
	if (formno) {
		formno.addEventListener('click', function () {
			OpenUserSearch();
		});
	}

	document.querySelectorAll('.quantity-input').forEach(function (el) {
		el.addEventListener('change', function () {
			UpdateOrderTotal(this);
		});
	});

	document.querySelectorAll('.total-input').forEach(function (el) {
		el.addEventListener('change', function () {
			UpdateOrderTotal(this, true);
		});
	});

	document.querySelectorAll('.form-block-radio').forEach(function (el) {
		el.addEventListener('click', function () {
			this.querySelectorAll('input[type="radio"]').forEach(function (ip){
				//if (!ip.checked) {
					ip.checked = true;
				//}
			})

			var c = this.querySelectorAll('input[type="checkbox"]');
			if (c.length) {
				c.forEach(function (bx) {
					if (!bx.checked) {
						bx.checked = true;
					} else {
						bx.checked = false;
					}
				});
				this.classList.toggle('checked');
			} else {
				document.querySelectorAll('.form-block').forEach(function (cbx) {
					cbx.classList.remove('checked');
				});
				this.classList.add('checked');
			}
		});
	});
	document.querySelectorAll('.form-block-check input[type="checkbox"]').forEach(function (el) {
		el.addEventListener('change', function () {
			if (this.checked) {
				this.closest('.form-block').classList.add('checked');
			} else {
				this.closest('.form-block').classList.remove('checked');
			}
		});
	});

	document.querySelectorAll('.btn-cart-remove').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;

			fetch(btn.getAttribute('data-api'), {
				method: 'DELETE',
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
			.then(function (results) {
				document.getElementById(btn.getAttribute('data-item').replace('#', '')).remove();

				document.getElementById('ordertotal').innerHTML = results.total;

				// Disable the 'continue' button if the cart is empty
				if (document.querySelectorAll('.cart-item').length <= 0) {
					document.getElementById('continue').disabled = true;
				}
			})
			.catch(function (err) {
				alert(err);
			});
		});
	});

	// ---- Products page

	// Add event listener for filters
	var filters = document.getElementsByClassName('filter-submit');
	for (var i = 0; i < filters.length; i++) {
		filters[i].addEventListener('change', function () {
			this.form.submit();
		});
	}

	// Enable/disable button when quantity changes
	document.querySelectorAll('.quantity-input').forEach(function (el) {
		el.addEventListener('change', function () {
			if (this.value > 0) {
				this.closest('tr').querySelectorAll('.btn-secondary').forEach(function (btn) {
					btn.disabled = false;
				});
			} else {
				this.closest('tr').querySelectorAll('.btn-secondary').forEach(function (btn) {
					btn.disabled = true;
				});
			}
		});
	});

	// Confirm deletion
	document.querySelectorAll('.btn-delete').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			if (confirm(this.getAttribute('data-confirm'))) {
				return true;
			}
			return false;
		});
	});

	// Update something in the cart
	document.querySelectorAll('.btn-cart-update').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this,
				qty = btn.closest('tr').querySelector('.quantity-input').value;

			if (!qty) {
				return;
			}

			btn.classList.add('processing');

			fetch(btn.getAttribute('data-api'), {
				method: 'PUT',
				headers: headers,
				body: JSON.stringify({
					quantity: qty
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
			.then(function (results) {
				updateCart(results);
				btn.classList.remove('processing');
			})
			.catch(function (err) {
				alert(err);
				btn.classList.remove('processing');
			});
		});
	});

	// Add to the cart
	document.querySelectorAll('.btn-cart-add').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this,
				qty = btn.closest('tr').querySelector('.quantity-input').value;

			if (!qty) {
				return;
			}

			btn.classList.add('processing');

			fetch(btn.getAttribute('data-api'), {
				method: 'POST',
				headers: headers,
				body: JSON.stringify({
					productid: btn.getAttribute('data-product'),
					quantity: qty
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
			.then(function (results) {
				updateCart(results);

				for (var i = 0; i < results.data.length; i++) {
					if (results.data[i].id == btn.getAttribute('data-product')) {
						btn.setAttribute('data-api', results.data[i].api);
						btn.classList.remove('btn-cart-add');
						btn.classList.add('btn-cart-update');
						btn.innerHTML = btn.getAttribute('data-text-update');
					}
				}

				btn.classList.remove('processing');

				document.getElementById(btn.getAttribute('data-product') + "_product").classList.add('selected');
			})
			.catch(function (err) {
				alert(err);
			});
		});
	});

	// Update the cart display
	function updateCart(response) {
		var cart = document.getElementById('cart');
		cart.querySelectorAll('.cart-item').forEach(function (el) {
			el.remove();
		});

		var t = cart.querySelector('.template');

		for (var i = 0; i < response.data.length; i++) {
			var tmpl = t.cloneNode(true);
			tmpl.classList.remove('hide');
			tmpl.classList.remove('template');
			tmpl.classList.add('cart-item');

			var content = tmpl.innerHTML
				.replace(/\{name\}/g, response.data[i].name)
				.replace(/\{price\}/g, response.data[i].price)
				.replace(/\{total\}/g, response.data[i].subtotal)
				.replace(/\{qty\}/g, response.data[i].qty);

			tmpl.innerHTML = content;

			cart.prepend(tmpl);
		}

		document.getElementById('order-total').innerHTML = response.total;
	}
});
