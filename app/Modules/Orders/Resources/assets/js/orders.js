
/* global $ */ // jquery.js
/* global ROOT_URL */ // common.js
/* global WSPostURL */ // common.js
/* global WSPutURL */ // common.js
/* global WSDeleteURL */ // common.js

/**
 * Format number as currency
 *
 * @param   {number}  num
 * @return  {string}
 */
function FormatNumber(num) {
	var neg = "";
	if (num < 0) {
		num = -num;
		neg = "-";
	}

	if (num > 99) {
		num = num.toString();
		var dollars = num.substr(0, num.length - 2);
		var p = 1;
		var end = dollars.length;

		if (dollars.lastIndexOf(".") != -1) {
			end = dollars.lastIndexOf(".");
		}
		for (var t=dollars;t>999;t=t/1000) {
			dollars = dollars.substr(0,end-p*3) + "," + dollars.substr(end-p*3,dollars.length);
			p++;
		}

		var cents = num.substr(num.length - 2, 2);
		num = dollars + "." + cents;
	} else if (num > 9 && num < 100) {
		num = num.toString();
		num = "0." + num;
	} else if (num > 0) {
		num = num.toString();
		num = "0.0" + num;
	} else {
		num = "0.00";
	}

	return neg + num;
}

/**
 * Format text from markup to HTML
 *
 * @param   {string}  text
 * @return  {string}
 */
function FormatText(text) {
	// nl2br
	text = text.replace(/\n/g, "<br>", text);

	// bold
	text = text.replace(/(^|\W|_)\*(\S.*?)\*(\W|$|_)/g, "$1<strong>$2</strong>$3");

	// italics
	text = text.replace(/(^|\W)_(\S.*?)_(\W|$)/g, "$1<em>$2</em>$3");

	return text;
}

/**
 * Unformat text from hTML to markup
 *
 * @param   {string}  text
 * @return  {string}
 */
function UnformatText(text) {
	// br2nl
	text = text.replace(/<br>/g, "\n");
	text = text.replace(/<\/p><p>/g, "\n\n");
	text = text.replace(/<p>/g, "");
	text = text.replace(/<\/p>/g, "");

	// bold
	text = text.replace(/<span style\s*=\s*\"font-weight:bold;\">(.*?)<\/span>/g, "*$1*");
	text = text.replace(/<strong>(.*?)<\/strong>/g, "*$1*");

	// italics
	text = text.replace(/<span style\s*=\s*\"font-style:italic;\">(.*?)<\/span>/g, "_$1_");
	text = text.replace(/<em>(.*?)<\/em>/g, "_$1_");

	return text;
}

/**
 * Update order total
 *
 * @param   {object}  input
 * @param   {bool}    override
 * @return  {void}
 */
function UpdateOrderTotal(input, override) {
	if (typeof(override) == 'undefined') {
		override = false;
	}

	/* If the number of items has a decimal, round it to an integer. If it's not valid, show "ERR" in the input. */
	if (input != 'undefined') {
		var regex = /^[0-9]+$/;
		var result = regex.exec(input.value);
		if (!result) {
			// Check if we were given a fractional number.
			regex = /^[0-9]*[.][0-9]+$/;
			result = regex.exec(input.value);
			if (!result) {
				$(input).val("ERR");
			} else {
				$(input).val(parseInt(Math.round(input.value)));
			}
		}
	}

	var x;

	// reset totals
	//var spans = document.getElementsByTagName("span");
	var spans = document.getElementsByClassName("category-total");
	for (x=0;x<spans.length;x++) {
		//if (spans[x].id.match("total$")) {
			spans[x].innerHTML = "0.00";
		//}
	}

	// Ring it up
	var ordertotal = document.getElementById("ordertotal");
	//var inputs = document.getElementsByTagName("input");
	var inputs = document.getElementsByClassName("quantity-input");

	for (x=0;x<inputs.length;x++) {
		//if (inputs[x].id.match("_quantity$")) {
			var product = inputs[x].getAttribute('data-id');//.id;
			//product = product.substr(0,product.lastIndexOf("_"));

			var quantity = inputs[x].value; //document.getElementById(product +"_quantity").value;
			var quantity_in = document.getElementById(product +"_quantity");
			// Sanity check
			if (!quantity.match(/^[0-9]+$/)) {
				quantity = 0;
			}
			var price = document.getElementById(product + "_price").value;
			var category = document.getElementById(product + "_category").value;

			var t = document.getElementById(product + "_linetotal");

			/*if (parseInt(quantity) > 0) {
				$('#' + product + "_product").addClass('selected');
			} else {
				$('#' + product + "_product").removeClass('selected');
			}*/

			if (!override) {
				if (t.tagName == "INPUT") {
					if (quantity_in == input) {
						t.value = FormatNumber(parseInt(price) * parseInt(quantity)).replace(/[,]/g,"");
					}
				} else {
					t.innerHTML = FormatNumber(parseInt(price) * parseInt(quantity));
				}
			} else {
				if (t.value != "0.00") {
					t.value = t.value.replace(/[\$,]/g, "");
					if (t.value.match(/^[0-9]+$/)) {
						t.value = t.value + ".00";
					}
					// strip leading zeros
					t.value = t.value.replace(/^0+/, "");
					if (!t.value.match(/^[0-9]*\.[0-9]{2}$/)) {
						t.value = "0";
					}
					t.value = t.value.replace(/[,\.]/g, "");
					t.value = FormatNumber(t.value).replace(/[,]/g,"");
				}
			}
			//var cattotal = document.getElementById(category + "_total");
			//var cattotal = document.getElementById("total");
			if (override || t.tagName == "INPUT") {
				//cattotal.innerHTML = FormatNumber(parseInt(cattotal.innerHTML.replace(/[,\.]/g, "")) + parseInt(t.value.replace(/[,\.]/g, "")));
				ordertotal.innerHTML = FormatNumber(parseInt(ordertotal.innerHTML.replace(/[,\.]/g, "")) + parseInt(t.value.replace(/[,\.]/g, "")));
			} else {
				//cattotal.innerHTML = FormatNumber(parseInt(cattotal.innerHTML.replace(/[,\.]/g, "")) + parseInt(price) * parseInt(quantity));
				ordertotal.innerHTML = FormatNumber(parseInt(ordertotal.innerHTML.replace(/[,\.]/g, "")) + parseInt(price) * parseInt(quantity));
			}
		//}
	}
}

/**
 * event handler for button click on edit/save button
 *
 * @param   {string}  field
 * @param   {string}  item
 * @return  {void}
 */
function EditProperty(field, item) {
	var img = document.getElementById("IMG_" + item + "_" + field);
	var cancelimg = document.getElementById("CANCEL_" + item + "_" + field);
	var span = document.getElementById("SPAN_" + item + "_" + field);
	var input = document.getElementById("INPUT_" + item + "_" + field);
	if (img.className.match(/pencil/) || img.className.match(/exclamation/)) {
		// turn to edit mode
		img.className = "fa fa-save";
		img.title = "Click to save changes.";
		cancelimg.style.display = "inline";
		span.style.display = "none";
		if (input.tagName == "TEXTAREA") {
			input.style.display = "block";
		} else {
			input.style.display = "inline";
		}
		if (input.tagName != "SELECT" && input.tagName != "TEXTAREA") {
			input.value = span.innerHTML;

			if (field.match(/price/)) {
				input.value = span.innerHTML.replace(/,/g, "");
			}
		}
		//input.style.marginBottom = "-5px";
	} else {
		// turn to save mode
		img.className = "fa fa-spinner";
		img.title = "Click to edit field";
		span.style.display = "inline";
		input.style.display = "none";

		if (input.tagName == "SELECT") {
			input = input.options[input.selectedIndex];
		}

		// don't send a post if it isn't changing.
		var oldval = span.innerHTML;
		var val = input.value;

		if (input.tagName == "INPUT" && input.type == "checkbox") {
			if (span.innerHTML == "Yes") {
				oldval = 1;
			} else {
				oldval = 0;
			}
			if (input.checked == true) {
				val = 1;
			} else {
				val = 0;
			}
		}
		if (input.tagName == "TEXTAREA") {
			oldval = oldval.replace(/<br>/g, '');
		}
		if (input.tagName == "OPTION") {
			val = input.innerHTML;
		}
		if (oldval == val) {
			img.className = "fa fa-pencil";
			document.getElementById("CANCEL_" + item + "_" + field).style.display = "none";
			return;
		} 

		if (field.match(/price/)) {
			val = val.replace(/[\$, ]/g, "");
			// strip leading zeros
			if (val.match(/^-?[0-9]+$/)) {
				val = val + ".00";
			}
			if (!val.match(/^-?[0-9]*\.[0-9][0-9]$/)) {
				img.className = "fa fa-exclamation-triangle";
				return;
			}
			val = val.replace(/\./g, "");
			val = val.replace(/^0+/, "");
			input.value = val;
		}

		if (field.match(/mou/)) {
			// Check if value is a URL
			// @link  https://gist.github.com/dperini/729294
			var pattern = new RegExp(
				"^" +
					// protocol identifier (optional)
					// short syntax // still required
					"(?:(?:(?:https?|ftp):)?\\/\\/)" +
					// user:pass BasicAuth (optional)
					"(?:\\S+(?::\\S*)?@)?" +
					"(?:" +
						// IP address exclusion
						// private & local networks
						"(?!(?:10|127)(?:\\.\\d{1,3}){3})" +
						"(?!(?:169\\.254|192\\.168)(?:\\.\\d{1,3}){2})" +
						"(?!172\\.(?:1[6-9]|2\\d|3[0-1])(?:\\.\\d{1,3}){2})" +
						// IP address dotted notation octets
						// excludes loopback network 0.0.0.0
						// excludes reserved space >= 224.0.0.0
						// excludes network & broadcast addresses
						// (first & last IP address of each class)
						"(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])" +
						"(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}" +
						"(?:\\.(?:[1-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))" +
					"|" +
						// host & domain names, may end with dot
						// can be replaced by a shortest alternative
						// (?![-_])(?:[-\\w\\u00a1-\\uffff]{0,63}[^-_]\\.)+
						"(?:" +
							"(?:" +
								"[a-z0-9\\u00a1-\\uffff]" +
								"[a-z0-9\\u00a1-\\uffff_-]{0,62}" +
							")?" +
							"[a-z0-9\\u00a1-\\uffff]\\." +
						")+" +
						// TLD identifier name, may end with dot
						"(?:[a-z\\u00a1-\\uffff]{2,}\\.?)" +
					")" +
					// port number (optional)
					"(?::\\d{2,5})?" +
					// resource path (optional)
					"(?:[/?#]\\S*)?" +
				"$", "i"
			);
			//var pattern = /^(?:\w+:)?\/\/([^\s\.]+\.\S{2}|localhost[\:?\d]*)\S*$/;
			if (!pattern.test(val)) {
				img.className = "fa fa-exclamation-triangle";
				return;
			}
		}

		if (input.tagName == "OPTION") {
			val = input.value;
		}
		var post = {};
		post[field] = val;

		//post = JSON.stringify(post);
		//console.log(post);

		$.ajax({
			url: document.getElementById('order').getAttribute('data-api'),
			type: 'put',
			data: post,
			//dataType: 'json',
			async: false,
			success: function(response) {
				//Halcyon.message('success', 'Item added');

				EditedProperty(response.data, item + "_" + field);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				//alert(thrownError);
				console.log(ajaxOptions);
				console.log(xhr);
				EditedProperty({id:0}, item + "_" + field);
				//Halcyon.message('danger', xhr.responseJSON.message);
				//Halcyon.message('danger', btn.getAttribute('data-error'));
			}
		});
		//WSPostURL(item, post, EditedProperty, item + "_" + field);
	}
}

/**
 * Cancel edit property
 *
 * @param   {string}  field
 * @param   {string}  item
 * @return  {void}
 */
function CancelEditProperty(field, item) {
	var span = document.getElementById("SPAN_" + item + "_" + field);
	var input = document.getElementById("INPUT_" + item + "_" + field);
	var img = document.getElementById("IMG_" + item + "_" + field);
	var cancelimg = document.getElementById("CANCEL_" + item + "_" + field);
	img.title = "Click to edit field";
	span.style.display = "inline";
	input.style.display = "none";
	img.className = "fa fa-pencil";
	cancelimg.style.display = "none";
}

/**
 * handler for queue post
 *
 * @param   {object}  xml
 * @param   {string}  field
 * @return  {void}
 */
function EditedProperty(xml, field) {
	var span = document.getElementById("SPAN_" + field);
	var input = document.getElementById("INPUT_" + field);
	var img = document.getElementById("IMG_" + field);
	var cancelimg = document.getElementById("CANCEL_" + field);

	//if (xml.status != 200) {
	if (xml.id) {
		img.className = "fa fa-exclamation-triangle";
		if (xml.status == 409) {
			img.title = "Value is in use. Enter another value and try again.";
		} else if (xml.status == 415) {
			img.title = "Value is in an incorrect format. Enter another value and try again.";
		} else {
			img.title = "An error has occurred. Please try again.";
		}
	} else {
		if (input.tagName == "SELECT") {
			span.innerHTML = input.options[input.selectedIndex].innerHTML;
		} else if (input.tagName == "INPUT" && input.type == "checkbox") {
			if (input.checked == true) {
				span.innerHTML = "Yes";
			} else {
				span.innerHTML = "No";
			}
		} else {
			if (input.tagName == "TEXTAREA") {
				span.innerHTML = FormatText(input.value);
			} else {
				if (field.match(/price/)) {
					span.innerHTML = FormatNumber(input.value.replace(/\./, ""));
				} else {
					span.innerHTML = input.value;
				}
			}
		}
		img.className = "fa fa-pencil";
		span.display = "inline";
		cancelimg.style.display = "none";
	}
}

/**
 * Add a new product
 *
 * @param   {string}  category
 * @return  {void}
 */
function AddNewProduct(category) {
	// Insert blank new entry and refresh page
	var post = {};
	post['category'] = category;
	post['name'] = "New Product";
	post['description'] = "Enter description for new product. This item will not be displayed until it is marked public.";
	post['unit'] = "unit";
	post['unitprice'] = "100";
	post['public'] = "0";

	post = JSON.stringify(post);

	WSPostURL(ROOT_URL + "orders/products", post, function(xml) {
		if (xml.status != 200) {
			// Error handling
			alert("An error occurred.");
		} else {
			window.location.reload(true);
		}
	});
}

/**
 * Add a new category
 *
 * @return  {void}
 */
function AddNewCategory() {
	// Insert blank new entry and refresh page
	var post = {};
	post['parentcategory'] = ROOT_URL + "orders/categories/1";
	post['name'] = "New Category";
	post['description'] = "Enter description for new category. This category will not be displayed until at least one public product is created.";

	post = JSON.stringify(post);
	WSPostURL(ROOT_URL + "ordercategory", post, function(xml) {
		if (xml.status != 200) {
			// Error handling
			alert("An error occurred.");
		} else {
			window.location.reload(true);
		}
	});
}

/**
 * Delete a product
 *
 * @param   {string}  product
 * @return  {void}
 */
function DeleteProduct(product) {
	if (confirm("Are you sure you want to delete '" + document.getElementById("SPAN_" + product + "_name").innerHTML + "'?")) {
		WSDeleteURL(product, function(xml) {
			if (xml.status != 200) {
				// Error handling
				alert("An error occurred.");
			} else {
				window.location.reload();
			}
		});
	}
}

/**
 * Delete a category
 *
 * @param   {string}  category
 * @return  {void}
 */
function DeleteCategory(category) {
	if (confirm("Are you sure you want to delete '" + document.getElementById("SPAN_" + category + "_name").innerHTML + "' and all its products?")) {
		WSDeleteURL(category, function(xml) {
			if (xml.status != 200) {
				// Error handling
				alert("An error occurred.");
			} else {
				window.location.reload();
			}
		});
	}
}

/**
 * Sequence items
 *
 * @param   {string}  item
 * @param   {string}  change
 * @return  {void}
 */
function Sequence(item, change) {
	var post = {};
	post['sequence'] = change;
	post = JSON.stringify(post);

	WSPutURL(item, post, Sequenced, change);
}

/**
 * Callback after sequencing
 *
 * @param   {object}  xml
 * @param   {string}  change
 * @return  {void}
 */
function Sequenced(xml, change) {
	if (xml.status != 200) {
		// Error handling
		if (xml.status == 409) {
			alert("Unable to move any further.");
		} else {
			alert("An error occurred.");
		}
	} else {
		var results = JSON.parse(xml.responseText);
		var row = document.getElementById("ROW_" + results['id']);
		var swapped = document.getElementById("ROW_" + results['swapped']);

		if (change == "-1") {
			row.parentNode.insertBefore(row, swapped);
		} else {
			row.parentNode.insertBefore(swapped, row);
		}

		// Turn butts off and on
		// Turn everything on first
		document.getElementById("UP_" + results['id']).style.visibility = "visible";
		document.getElementById("DOWN_" + results['id']).style.visibility = "visible";
		if (results['sequence'] == results['minseq']) {
			// Disable up button
			document.getElementById("UP_" + results['id']).style.visibility = "hidden";
		}
		if (results['sequence'] == results['maxseq']) {
			// Disable down button
			document.getElementById("DOWN_" + results['id']).style.visibility = "hidden";
		}
		var swap_seq = results['swapped_sequence'];
		// Turn everything on first
		document.getElementById("UP_" + results['swapped']).style.visibility = "visible";
		document.getElementById("DOWN_" + results['swapped']).style.visibility = "visible";
		if (swap_seq == results['minseq']) {
			// Disable up button
			document.getElementById("UP_" + results['swapped']).style.visibility = "hidden";
		}
		if (swap_seq == results['maxseq']) {
			// Disable down button
			document.getElementById("DOWN_" + results['swapped']).style.visibility = "hidden";
		}
	}
}

/**
 * For me
 *
 * @return  {void}
 */
function ForMe() {
	if (document.getElementById("cancel").style.display == "none") {
		var inputs = document.getElementsByTagName("input");
		var count = 0;
		var x;
		for (x=0;x<inputs.length;x++) {
			if (inputs[x].id.match("_quantity$")) {
				var quantity = inputs[x].value;
				if (quantity.match(/^[0-9]+$/) && quantity > 0) {
					count++;
				} else {
					inputs[x].value = "0";
				}
			}
		}

		if (count == 0) {
			return;
		}

		for (x=0;x<inputs.length;x++) {
			if (inputs[x].id.match("_quantity$")) {
				var quantity = inputs[x];

				var product = inputs[x].id;
				product = product.substr(0,product.lastIndexOf("_"));
				var price = document.getElementById(product +"_price").value;

				// disable input
				quantity.disabled = true;

				// fill in 0 if it's not set
				if (quantity.value == "") {
					quantity.value = "0";
				} 
				// If it's a free item, you only get one.
				if (quantity.value > 1 && price == 0) {
					quantity.value = "1";
				}
			}
		}
		document.getElementById("cancel").style.display = "inline";
		$( "#forme" ).toggle( "blind", {'direction': 'up'} );
		//document.getElementById("forme_error").style.display = "none";

		document.getElementById("formeyes").checked = false;
		document.getElementById("formeno").checked = false;
	} else {
		if (document.getElementById("search_user").value.match(/^.*?\(([a-z0-9]+)\)$/) || document.getElementById("formeno").checked == true) {
			//document.getElementById("forme_search_error").style.display = "none";
			MouAgree();
		} else {
			if (document.getElementById("formeyes").checked == true) {
				$("#usersearch").effect( "highlight", {'duration': 1000} );
				//document.getElementById("forme_search_error").style.display = "inline";
			} else {
				$("#forme").effect( "highlight", {'duration': 1000} );
				//document.getElementById("forme_error").style.display = "inline";
			}
		}
	}
}

/**
 * Validate For Me
 *
 * @return  {void}
 */
function ValidateForMe() {
	if (document.getElementById("search_user").value.match(/^.*?\(([a-z0-9]+)\)$/)) {
		//document.getElementById("forme_search_error").style.display = "none";
	}
}

/**
 * Callback after updating account info
 *
 * @return  {void}
 */
function OpenUserSearch() {
	//document.getElementById("forme_error").style.display = "none";
	if (document.getElementById("formeyes").checked == true) {
		if (document.getElementById("usersearch").style.display == "none") {
			$( "#usersearch" ).toggle( "blind", {'direction': 'up'} );
		}
	} else {
		if (document.getElementById("usersearch").style.display != "none") {
			$( "#usersearch" ).toggle( "blind", {'direction': 'up'} );
		}
	}
}

/**
 * MOU Agree
 *
 * @return  {void}
 */
function MouAgree() {
	//var inputs = document.getElementsByTagName("input");
	//var count = 0;
	var checked = 0;
	var restrict = false;
	var x;

	var count = 0;
	var inputs = document.getElementsByClassName("quantity-input");

	for (x=0; x<inputs.length; x++) {
		//if (inputs[x].id.match("_quantity$")) {
			var quantity = inputs[x].value;
			var product  = inputs[x].getAttribute('data-id'); //id;

			//product = product.substr(0, product.lastIndexOf("_"));

			// did we select this?
			var mou = document.getElementById(product + "_mou");
			if (quantity > 0) {
				if (mou) {
					mou.style.display = "block";
					count++;
				}
				// Check to see if have restrict question
				if (document.getElementById(product + "_restrict") != null) {
					restrict = true;
				}
			} else {
				if (mou) {
					mou.style.display = "none";
				}
			}
		//}
	}

	if (!restrict) {
		// Change button text
		var btn = document.getElementById('continue');
		var btnval = btn.value;
		btn.value = btn.getAttribute('data-submit-txt');
		btn.setAttribute('data-submit-txt', btnval);
	}

	var opened = false;
	if (count > 0 && document.getElementById("mouagree").style.display == "none") {
		$( "#mouagree" ).toggle( "blind", {'direction': 'up'} );
		opened = true;
	} else if (document.getElementById("mouagree").style.display == "none") {
		if (restrict) {
			RestrictAgree();
		} else {
			TotalOrder();
		}
		return;
	}

	inputs = document.getElementsByClassName('mou-agree');
	for (x=0;x<inputs.length;x++) {
		var product = inputs[x].getAttribute('data-id');
		var mou = document.getElementById(product + "_mou");
		if (mou.style.display != 'none') {

			// Check checkbox
			var box = inputs[x];
			if (box.checked == true) {
				checked++;
			}
		}
	}

	if (count != checked) {
		if (opened == false) {
			$( "#mouagree" ).effect( "highlight", {'duration': 1000} );
		}
		return;
	} else {
		if (restrict) {
			RestrictAgree();
		} else {
			TotalOrder();
		}
		return;
	}
}

/**
 * Restrict Agree
 *
 * @return  {void}
 */
function RestrictAgree() {
	var count = 0;
	//var checked = 0;

	// Change button text
	var btn = document.getElementById('continue');
	var btnval = btn.value;
	btn.value = btn.getAttribute('data-submit-txt');
	btn.setAttribute('data-submit-txt', btnval);

	var inputs = document.getElementsByClassName("restrict-agree");
	for (var x=0;x<inputs.length;x++) {
		var product = inputs[x].getAttribute('data-id');

		// did we select this?
		var quantity = document.getElementById(product + "_quantity").value;

		var restrict = document.getElementById(product + "_restrict");
		if (quantity > 0) {
			restrict.style.display = "block";
			count++;
		} else {
			restrict.style.display = "none";
		}
	}

	var opened = false;

	if (count > 0 && document.getElementById("restrictagree").style.display == "none") {
		$( "#restrictagree" ).toggle( "blind", {'direction': 'up'} );
		opened = true;
	} else if (document.getElementById("restrictagree").style.display != "none") {
		TotalOrder();
		return;
	}

	var fail = false;
	if (fail || opened) {
		return;
	} else {
		TotalOrder();
		return;
	}
}

/**
 * Cancel MOU
 *
 * @return  {void}
 */
function CancelMou() {
	//$( "#forme" ).toggle( "blind", {'direction': 'up'} );

	$(".cancellable").toggle( "blind", {'direction': 'up'} );

	/*if (document.getElementById("usersearch").style.display != "none") {
		$( "#usersearch" ).toggle( "blind", {'direction': 'up'} );
	}
	if (document.getElementById("mouagree").style.display != "none") {
		$( "#mouagree" ).toggle( "blind", {'direction': 'down'} );
	}
	if (document.getElementById("restrictagree").style.display != "none") {
		$( "#restrictagree" ).toggle( "blind", {'direction': 'down'} );
	}*/
	document.getElementById("cancel").style.display = "none";
	$( "#continue" ).val("Continue");

	var inputs = document.getElementsByClassName("quantity-input");
	for (var x=0;x<inputs.length;x++) {
		//if (inputs[x].id.match("_quantity$")) {
			//var quantity = inputs[x];

			// disable input
			inputs[x].disabled = false;
		//}
	}
}

/**
 * Total order
 *
 * @return  {void}
 */
function TotalOrder() {
	// Ring it up
	var order = {};
	var items = Array();

	var name;
	if (name = document.getElementById("search_user").value.match(/^.*?\(([a-z0-9]+)\)$/)) {
		order['userid'] = name[1];
	} else {
		order['userid'] = document.getElementById("userid").value;
	}

	var count = 0;
	var x;
	var inputs = document.getElementsByClassName("quantity-input");
	for (x=0;x<inputs.length;x++) {
		var product = inputs[x].getAttribute('data-id');
		var quantity = document.getElementById(product +"_quantity").value;

		var linetotal = document.getElementById(product + "_linetotal");
		if (linetotal.tagName == "INPUT") {
			linetotal = linetotal.value.replace(/[,\.]/g, "");
		} else {
			linetotal = linetotal.innerHTML.replace(/[,\.]/g, "");
		}

		// Sanity check
		if (!quantity.match(/[0-9]+/) || !linetotal.match(/[0-9]+/)) {
			quantity = 0;
			linetotal = 0;
		}

		if (quantity > 0) {
			//JSON.stringify(
			items[count] = {
				'product': product,
				'price': linetotal,
				'quantity': quantity
			};
			count++;
		}
	}

	var notes = "";
	var yescount = 0;
	var inputs = document.getElementsByClassName("restrict-agree");
	for (x=0;x<inputs.length;x++) {
		var product = inputs[x].getAttribute('data-id');
		var restrict = document.getElementById(product + "_restrict");

		if (restrict.style.display != 'none') {
			// Check checkbox
			var box = inputs[x];

			if (box.checked == true) {
				notes = notes + document.getElementById(product + "_productname").innerHTML + ":\n\r";
				//notes = notes + "IRB Data: YES\n\r"
				notes = notes + box.parentNode.getElementsByTagName('label')[0].innerHTML + ": YES\n\r";
				yescount++;

				if (box.getAttribute('data-dialog')) {
					$(box.getAttribute('data-dialog')).dialog('open');
					return;
				}
			}
		}
	}

	if (yescount == 0) {
		notes = notes + "No restricted data categories were selected.\n\r";
	}
	order['items'] = items; //JSON.stringify(items);
	order['staffnotes'] = notes; //JSON.stringify(notes);
	order['usernotes'] = document.getElementById('usernotes').value;
	
	//var post = '{"userid": "' + order['user'] + '", "items": ' + order['items'] + ', "staffnotes": ' + order['staffnotes'] + '}';
	//console.log(post);
	var btn = document.getElementById('continue');
	//return;
//console.log(order); return;
	$.ajax({
		url: btn.getAttribute('data-api'),
		type: 'post',
		data: {
			userid: order['user'],
			items: order['items'],
			usernotes: order['usernotes'],
			staffnotes: order['staffnotes']
		},
		dataType: 'json',
		async: false,
		success: function(response) {
			//Halcyon.message('success', 'Item added');

			// Don't really need to do anything here, we are just ensuring the selected user has a database entry
			window.location = response.url;
		},
		error: function(xhr, ajaxOptions, thrownError) {
			console.log(xhr);
			alert("There was an error processing your order. Please wait a few minutes and try again or contact help.");
			//Halcyon.message('danger', xhr.responseJSON.message);
			//Halcyon.message('danger', btn.getAttribute('data-error'));
		}
	});

	/*WSPostURL(ROOT_URL + "order", post, function(xml) {
		if (xml.status == 200) {
			var results = JSON.parse(xml.responseText);
			window.location = "/order/" + results['id'].split("/")[3];
		} else {
			alert("There was an error processing your order. Please wait a few minutes and try again or contact rcac-help@purdue.edu for help.");
		}
	});*/
}

/**
 * Add a new account row
 *
 * @return  {void}
 */
function AddNewAccountRow() {
	var row = document.getElementById("account_new_row");
	//var row2 = document.getElementById("account_new_row2");
	//var prompt_row = document.getElementById("account_new_row_prompt");
	var new_row = row.cloneNode(true);
	//var new_row2 = row2.cloneNode(true);

	var new_box = new_row.getElementsByTagName("input")[0];
	//var new_box2 = new_row.getElementsByTagName("input")[1];

	var i = row.parentNode.getElementsByTagName("tr").length;
	new_row.id = "account_new_row" + i;
	var rm = new_row.getElementsByClassName("account-remove")[0];
	rm.setAttribute('href', '#' + new_row.id);

	//new_row2.id = "";

	new_box.value = "";
	//new_box2.value = "";

	var autocompleteOrderPruchaseAccount = function(url) {
		return function(request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
				response($.map(data.data, function (el) {
					return {
						label: (el.purchasewbse ? el.purchasewbse : el.purchaseio),
						id: (el.purchasewbse ? el.purchasewbse : el.purchaseio)
					};
				}));
			});
		};
	};

	$( new_box ).autocomplete({
		source: autocompleteOrderPruchaseAccount(ROOT_URL + "orders/accounts/?api_token=" + $('meta[name="api-token"]').attr('content') + "&fund=%s"),
		dataName: 'data',
		height: 150,
		delay: 100,
		minLength: 0,
		prefix: 'fund:',
		filter: /^[a-zA-Z]?[0-9\.]*$/i,
		noResultsText: '',
		autoText: false
	});

	/*$( new_box2 ).autocomplete({
		source: autocompleteOrderPruchaseAccount(ROOT_URL + "orderpurchaseaccount/cc:%s"),
		dataName: 'accounts',
		height: 150,
		delay: 100,
		minLength: 0,
		prefix: 'cc:',
		filter: /^[a-zA-Z]?[0-9\.]*$/i,
		noResultsText: '',
		autoText: false
	});*/

	new_row.classList.remove('hide');
	row.parentNode.insertBefore(new_row, row);
	//row.parentNode.insertBefore(new_row2, prompt_row);
	//new_row.style.display = "table-row";
	//new_row2.style.display = "table-row";

	AccountApproverSearch();
}

function AccountApproverSearch() {
	var users = $(".form-users");
	if (users.length) {
		users.each(function (i, user) {
			user = $(user);
			/*var cl = user.clone()
				.attr('type', 'hidden')
				.val(user.val().replace(/([^:]+):/, ''));
			user
				.attr('name', user.attr('id') + i)
				.attr('id', user.attr('id') + i)
				.val(user.val().replace(/(:\d+)$/, ''))
				.after(cl);*/
			user.autocomplete({
				minLength: 2,
				source: function (request, response) {
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
					user.attr('data-id', ui.item.id);
					//cl.val(ui.item.id); // save selected id to input
					return false;
				}
			});
		});
	}
}

function AddNewProductRow() {
	var row = document.getElementById("item_new_row");

	var new_row = row.cloneNode(true);

	var new_box = new_row.getElementsByTagName("input")[0];

	var i = row.parentNode.getElementsByTagName("tr").length;
	new_row.id = "item_new_row" + i;

	var rm = new_row.getElementsByClassName("item-remove")[0];
	rm.setAttribute('href', '#' + new_row.id);

	//new_row2.id = "";

	new_box.value = 0;
	//new_box2.value = "";

	new_row.classList.remove('hide');
	row.parentNode.insertBefore(new_row, row);

	$(new_row).find('.searchable-select').select2();

	//ProductSearch();
}

/**
 * Update balance
 *
 * @param   {bool}  quick
 * @return  {void}
 */
function UpdateBalance(quick) {
	if (typeof(quick) == 'undefined') {
		quick = false;
	}

	// Get all amounts
	var amounts = $('[name=account_amount]');
	var accounts = $('[name=account]');
	//var errors = $('.account_error');
	var balance = document.getElementById("balance");
	//var justifications = $('[name=justification]');
	var total = document.getElementById("ordertotal").innerHTML.replace(/[,\.]/g, "");

	var error_count = 0;
	var ok = true;

	for (var x=0;x<amounts.length;x++) {
		ok = true;
		if (accounts[x].value != "") {
			// Check account number for WBSE
			account = accounts[x].value;
			// If we are starting with letter, assume we are inputting WBSE
			if (account.match(/^[A-Za-z]/)) {
				// If we have something random in between ,error
				if (account.match(/^[A-Za-z].*[^\d\.].*/)) {
					ok = false;
				} else {
					// So far so good
					// Yank out first period, and replace it
					var account = account.replace(/\./g, '');
					// Use original length (last char is . messes up)
					if (accounts[x].value.length > 1) {
						// Put first period
						account = account.substring(0, 1) + "." + account.substring(1, account.length)
					}
					// Use original length (last char is . messes up)
					if (accounts[x].value.length > 10) {
						// Put second period
						account = account.substring(0, 10) + "." + account.substring(10, account.length)
					}
					if (accounts[x].value.length > 13) {
						// Put second period
						account = account.substring(0, 13) + "." + account.substring(13, account.length)
					}
					accounts[x].value = account
				}
			} else {
				// We are an IO
				if (!account.match(/^\d{10}$/)) {
					ok = false;
				}
			}

			if (ok) {
				//errors[x].style.display = "none";
				accounts[x].classList.remove('is-invalid');
			} else {
				accounts[x].classList.add('is-invalid');
				//errors[x].style.display = "inline";
				error_count++;
			}
		}

		var amt = amounts[x].value;
		amt = amt.replace(/[\$,]/g, "");
		if (amt.match(/^-?[0-9]+$/)) {
			amt = amt + ".00";
		}
		// strip leading zeros
		amt = amt.replace(/^0+/, "");
		if (amt.match(/^-?[0-9]*\.[0-9]{2}$/)) {
			amt = amt.replace(/[,\.]/g, "");
			amt = amt.replace(/^0+/, "");
			if (!quick) {
				amounts[x].value = FormatNumber(amt).replace(/[,]/g, "");
			}
			total -= amt;
			amounts[x].classList.remove('is-invalid');
		} else if (amt == "") {// && ok) {
			//errors[x].style.display = "none";
			amounts[x].classList.remove('is-invalid');
		} else {
			// Turn on "error" icon
			//errors[x].style.display = "inline";
			amounts[x].classList.add('is-invalid');
			error_count++;
		}
	}

	if (!quick || total == 0) {
		balance.innerHTML = FormatNumber(total);
	}

	if (total != 0) {
		document.getElementById("balance_error").style.display = "inline";
	} else {
		document.getElementById("balance_error").style.display = "none";
	}

	if (total == 0 && error_count == 0) {
		// Enable buttons
		document.getElementById("save_accounts").disabled = false;
		document.getElementById("save_quantities").disabled = false;
	} else {
		// Disable buttons if any errors
		document.getElementById("save_accounts").disabled = true;
		if (document.getElementById("save_quantities").value != "Edit Quantities") {
			document.getElementById("save_quantities").disabled = true;
		}
	}
}

/**
 * Divide balance
 *
 * @return  {void}
 */
function DivideBalance() {
	// Get elements
	var accounts = $('[name=account]');
	//var orders = $('[name=purchaseorder]');
	var amounts = $('[name=account_amount]');

	var ways = 0;
	var x;
	for (x=0;x<accounts.length-1;x++) {
		ways++;
	}

	var balance = document.getElementById("ordertotal").innerHTML.replace(/[,\.]/g, "");
	var total = balance;

	var count = 0;
	for (x=0;x<amounts.length-1;x++) {
		var amt = Math.floor(total / ways);
		amounts[x].value = FormatNumber(amt).replace(/,/g, "");
		balance -= amt;
		count++;

		if (count == ways) {
			amounts[x].value = FormatNumber(amt + balance).replace(/,/g, "");
		}
	}

	UpdateBalance();
}

/**
 * Save quantities
 *
 * @return  {void}
 */
function SaveQuantities() {
	var quantityinputs = $('[name=quantity]');
	var periodsinputs = $('[name=periods]');
	var originalquantity = $('[name=original_quantity]');
	var originalperiods = $('[name=original_periods]');
	var originalprice = $('[name=original_total]');
	var priceinputs = $('[name=linetotal]');
	var items = $('[name=item]');
	var num_changes = 0;

	var post, id;

	for (var x=0;x<originalquantity.length;x++) {
		post = {}
		id = items[x].getAttribute('data-api');//value;

		if (originalquantity[x].value != quantityinputs[x].value && quantityinputs[x].value.match(/^[0-9]+$/)) {
			post['quantity'] = quantityinputs[x].value;
		}
		if (originalperiods[x].value != periodsinputs[x].value && periodsinputs[x].value.match(/^[0-9]+$/)) {
			post['timeperiodcount'] = periodsinputs[x].value;
		}
		if (originalprice.length == quantityinputs.length
		 && originalprice[x].value.replace(/[,\.]/g, "") != priceinputs[x].value.replace(/[,\.]/g, "")
		 && priceinputs[x].value.replace(/[,\.]/g, "").match(/^[0-9]+$/)) {
			post['price'] = priceinputs[x].value.replace(/[,\.]/g, "");
		}
		//console.log(post);
		post = JSON.stringify(post);

		if (post != "{}") {
			pendingupdates++;
			num_changes++;
			//console.log(id);
			WSPutURL(id, post, UpdatedAccountInfo);
		}
	}

	// Check for deleted items
	for (x = 0; x < deleteitems.length; x++) {
		pendingupdates++;
		num_changes++;
		//console.log(deleteitems[x]);
		WSDeleteURL(deleteitems[x]);//, UpdatedAccountInfo);
	}

	// Check for new items
	var order = document.getElementById('order');
	var products = $('.item-product');

	for (x = 0; x < products.length; x++) {
		if (!products[x].value || products[x].value == '0') {
			continue;
		}

		var container = $($(products[x]).closest('tr'));
		var quantity = container.find('.item-quantity')[0];

		if (!quantity.value) {
			continue;
		}

		var total = container.find('.item-total')[0],
			periods = container.find('.item-periods')[0],
			opt = $(products[x]).find('option:selected');

		var post = {
			'orderid': order.value,
			'orderproductid': opt.attr('value'),
			'quantity': quantity.value,
			'price': total.value.replace(/[,\.]/g, ""),
			'origunitprice': opt.attr('data-price').replace(/[,\.]/g, ""),
			'recurringtimeperiodid': opt.attr('data-recurringtimeperiodid'),
			'timeperiodcount': periods.value
		};

		pendingupdates++;
		num_changes++;

		//console.log(post);
		post = JSON.stringify(post);

		WSPostURL(iteminputs[x].getAttribute('data-api'), post);
	}

	if (num_changes == 0) {
		CancelEditAccounts();
	}
}

/**
 * Save accounts
 *
 * @return  {void}
 */
function SaveAccounts() {
	var accounts = $('[name=account]');
	var amounts = $('[name=account_amount]');
	var justifications = $('[name=justification]');
	var approverinputs = $('[name=approver]');
	//var account_errors = $('[name=amount_error]');
	var total = document.getElementById("ordertotal").innerHTML.replace(/[,\.]/g, "");
	var order = document.getElementById("order").value;
	var posts = Array();
	var count = 0;
	var errors = 0;

	for (var x=0;x<amounts.length-1;x++) {
		if (accounts[x].value != "" && amounts[x].value.match(/^-?[0-9]+\.[0-9]{2}$/)) {
			var account = accounts[x].value;
			var amt = amounts[x].value;
			amt = amt.replace(/[,\.]/g, "");

			// Determine if we are WBSE or IO
			if (account.match(/^[A-Za-z]\.\d{8}\.\d{2}\.\d{3}$/)) {
				// WBSE - f.90000000.02.001
				// Normalize letter
				account = account.charAt(0).toLowerCase() + account.substr(1);
				// Strip periods
				account = account.replace(/\./g,'');
				posts[count] = {
					'purchasewbse': account,
					'amount': amt,
					'orderid': order,
					'budgetjustification': justifications[x].value
				};
			} else if (account.match(/^\d{10}$/)) {
				//IO
				posts[count] = {
					'purchaseio': account,
					'amount': amt,
					'orderid': order,
					'budgetjustification': justifications[x].value
				};
			} else {
				// We should not be here!
				alert("Format error");
				return;
			}
			if (approverinputs[x].value) {
				posts[count]['approveruserid'] = approverinputs[x].value;
			}
			total -= amt;
			count++;
		}

		var row_errors = 0;
		if (accounts[x].value == "") {
			row_errors++;
			//account_errors[x].style.display = "inline";
			accounts[x].classList.add('is-invalid');
		}
		if (!amounts[x].value.match(/^-?[0-9]+\.[0-9]{2}$/)) {
			row_errors++;
			accounts[x].classList.add('is-invalid');
		}

		errors += row_errors;

		if (row_errors > 0) {
			$(accounts[x].parentNode.parentNode.parentNode).effect("highlight", {'duration': 1000});
		}
	}

	if (total == 0 && errors == 0) {
		var post = '{"accounts": ' + JSON.stringify(posts) + '}';
		//console.log(ROOT_URL + "orders/" + order);
		console.log(post);
		return;
		WSPutURL(ROOT_URL + "orders/" + order, post, function(xml) {
			if (xml.status == 200) {
				window.scrollTo(0, 0);
				window.location.reload();
			} else {
				alert("An error occurred while saving accounts.");
			}
		});
	}
}

/**
 * Callback after saving an account
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function SavedAccounts(xml) {
	if (xml.status == 200) {
		window.scrollTo(0, 0);
		window.location.reload();
	} else {
		alert("An error occurred while saving accounts.");
	}
}*/

/**
 * Cancel an order
 *
 * @return  {void}
 */
function CancelOrder(button) {
	var url = document.getElementById("order").getAttribute('data-api');

	if (confirm(button.getAttribute('data-confirm'))) {
		WSDeleteURL(url, CanceledOrder);
	}
}

/**
 * Callback after cancelling an order
 *
 * @param   {object}  xml
 * @return  {void}
 */
function CanceledOrder(xml) {
	if (xml.status < 400) {
		window.location.reload();// = "/orders/";
	} else {
		alert("An error occurred while canceling order.");
	}
}

/**
 * Cancel an order
 *
 * @return  {void}
 */
function RestoreOrder(button) {
	var url = document.getElementById("order").getAttribute('data-api');

	var post = JSON.stringify({ "restore": 1 });

	WSPutURL(url, post, function (xml) {
		if (xml.status == 200) {
			window.location.reload();
		} else {
			alert("An error occurred while restoring order.");
		}
	});
}

/**
 * Reset an account
 *
 * @param   {string}  id
 * @param   {string}  button
 * @return  {void}
 */
function ResetAccount(url, button) {
	var post = JSON.stringify({ "reset": 1 });

	WSPutURL(url, post, function (xml) {
		if (xml.status == 200) {
			window.location.reload();
		} else {
			alert("An error occurred while resetting account.");
		}
	});
}

/**
 * Approve an account
 *
 * @param   {string}  id
 * @param   {string}  button
 * @return  {void}
 */
function ApproveAccount(url, button) {
	var post = JSON.stringify({"approved": 1});

	/*WSPutURL(url, post, function(xml, button) {
		if (xml.status == 200) {*/
			var id = button.getAttribute('data-id');

			button.classList.add('hide');
			button.disabled = true;

			document.getElementById("status_" + id).innerHTML = button.getAttribute('data-txt');
			//document.getElementById("button_" + id).style.visibility = "hidden";

			document.getElementById("button_" + id + "_deny").classList.add('hide');
			document.getElementById("button_" + id + "_deny").disabled = true;

			document.getElementById("button_" + id + "_reset").classList.remove('hide');
			document.getElementById("button_" + id + "_reset").disabled = false;

			document.getElementById("button_" + id + "_remind").classList.add('hide');
			document.getElementById("button_" + id + "_remind").disabled = true;

			var accountstatus = $('[name=accountid]');
			for (var x = 0; x < accountstatus.length; x++) {
				if (accountstatus[x].id == id) {
					accountstatus[x].value = "PENDING_COLLECTION";
				}
			}
		/*} else {
			alert("An error occurred while approving account.");
		}
	}, button);*/
}

/**
 * Remind account
 *
 * @param   {string}  url
 * @param   {string}  button
 * @return  {void}
 */
function RemindAccount(url, button) {
	var post = JSON.stringify({ "notice": 3 });

	WSPutURL(url, post, function (xml, button) {
		if (xml.status == 200) {
			var id = button.getAttribute('data-id');

			document.getElementById("status_" + id).innerHTML = button.getAttribute('data-txt');
			document.getElementById("button_" + id).classList.add('hide');
		} else {
			alert("An error occurred while approving account.");
		}
	}, button);
}

/**
 * Remind order
 *
 * @param   {string}  id
 * @return  {void}
 */
function RemindOrder(url, button) {
	var post = JSON.stringify({ "notice": 1 });

	WSPutURL(url, post, function(xml, button) {
		if (xml.status == 200) {
			document.getElementById("remindorderspan").innerHTML = button.getAttribute('data-txt');
			//document.getElementById("remindorder").style.display = "none";
			button.classList.add('hide');
			button.disabled = true;
		} else {
			alert("An error occurred while approving account.");
		}
	}, button);
}

/**
 * Deny account
 *
 * @param   {string}  url
 * @param   {string}  button
 * @return  {void}
 */
function DenyAccount(url, button) {
	var post = JSON.stringify({ "denied": 1 });

	/*WSPutURL(url, post, function (xml, button) {
		if (xml.status == 200) {*/
			var id = button.getAttribute('data-id');

			button.classList.add('hide');
			button.disabled = true;

			document.getElementById("status_" + id).innerHTML = button.getAttribute('data-txt');
			//document.getElementById("button_" + id).classList.add('hide');

			document.getElementById("button_" + id + "_approve").classList.add('hide');
			document.getElementById("button_" + id + "_approve").disabled = true;

			document.getElementById("button_" + id + "_remind").classList.add('hide');
			document.getElementById("button_" + id + "_remind").disabled = true;

			document.getElementById("button_" + id + "_reset").classList.remove('hide');
			document.getElementById("button_" + id + "_reset").disabled = false;

			var accountstatus = $('[name=accountid]');
			for (var x = 0; x < accountstatus.length; x++) {
				if (accountstatus[x].id == id) {
					accountstatus[x].value = "DENIED";
				}
			}
		/*} else {
			alert("An error occurred while denying account.");
		}
	}, button);*/
}

/**
 * Collect account
 *
 * @param   {string}  url
 * @param   {string}  button
 * @return  {void}
 */
function CollectAccount(url, button) {
	var id = button.getAttribute('data-id');

	var docid = document.getElementById("docid_" + id).value;
	var docdate = document.getElementById("docdate_" + id).value;

	if (docid != "" && docdate.match(/\d{4}-\d{2}-\d{2}/)) {
		var post = JSON.stringify({"paid": 1, "docid": docid, "docdate": docdate});

		WSPutURL(url, post, function(xml, button) {
			if (xml.status == 200) {
				id = button.getAttribute('data-id');

				document.getElementById("status_" + id).innerHTML = button.getAttribute('data-txt');
				document.getElementById("button_" + id).classList.add('hide');
			} else {
				alert("An error occurred while collecting account.");
			}
		}, button);
	}
}

/**
 * Copy doc
 *
 * @param   {object}  input
 * @return  {void}
 */
function CopyDoc(input) {
	var docs = $('[name=docid]');
	for (var x=0;x<docs.length;x++) {
		if (docs[x].value == "") {
			docs[x].value = input.value;
		}
	}
}

/**
 * Copy doc date
 *
 * @param   {object}  input
 * @return  {void}
 */
function CopyDocDate(input) {
	var docs = $('[name=docdate]');
	for (var x=0;x<docs.length;x++) {
		if (docs[x].value == "") {
			docs[x].value = input.value;
		}
	}
}

/**
 * Fulfill item
 *
 * @param   {string}  url
 * @param   {string}  button
 * @return  {void}
 */
function FulfillItem(url, button) {
	var post = JSON.stringify({"fulfilled": 1});

	WSPutURL(url, post, function(xml, button) {
		if (xml.status == 200) {
			var id = button.getAttribute('data-id');

			document.getElementById("status_" + id).innerHTML = button.getAttribute('data-txt');
			document.getElementById("button_" + id).classList.add('hide');//style.visibility = "hidden";
			document.getElementById("button_" + id).disabled = true;

			var itemstatus = $('[name=itemid]');
			for (var x = 0; x < itemstatus.length; x++) {
				if (itemstatus[x].id == id) {
					itemstatus[x].value = "FULFILLED";
				}
			}
		} else {
			alert("An error occurred while fulfilling item.");
		}
	}, button);
}

var deleteaccounts = Array();
var deleteitems = Array();
var pendingupdates = 0;

/**
 * Edit accounts
 *
 * @return  {void}
 */
function EditAccounts() {
	var b = document.getElementById("save_accounts");
	var c = document.getElementById("cancel_accounts");
	var new_row = document.getElementById("account_new_row");
	var x;

	if (b.innerHTML == b.getAttribute('data-save-txt')) {
		//verify first
		var accountstatus = $('[name=accountid]');
		var accountinputs = $('[name=account]');
		//var costcenterinputs = $('[name=costcenter]');
		//var orderinputs = $('[name=purchaseorder]');
		var justificationinputs = $('[name=justification]');
		var approverinputs = $('[name=approver]');
		var amountinputs = $('[name=account_amount]');
		//var account_errors = $('[name=amount_error]');

		// Check amounts have values
		var errors = 0;
		var row_errors = 0;
		for (x=0;x<accountinputs.length-1;x++) {
			row_errors = 0;
			if (accountinputs[x].value == "") {
				row_errors++;
				//account_errors[x].style.display = "inline";
				accountinputs[x].classList.add('is-invalid');
			}
			if (!amountinputs[x].value.match(/^-?[0-9]+\.[0-9]{2}$/)) {
				row_errors++;
				accountinputs[x].classList.add('is-invalid');
			}
			errors += row_errors;

			if (row_errors > 0) {
				$(accountinputs[x].parentNode.parentNode.parentNode).effect("highlight", {'duration': 1000});
			}
		}

		if (errors > 0) {
			return;
		}

		var items = $('[name=item]');
		var num_changes = 0;

		// Check accounts for edits
		var spans = $('.account_span');
		for (x=0;x<spans.length;x++) {
			// Check the old value (in HTML) against the input
			if (spans[x].innerHTML != accountinputs[x].value) {
				var id = accountstatus[x].getAttribute('data-api');
				account = accountinputs[x].value;
				if (account.match(/^[A-Za-z]\.\d{8}\.\d{2}\.\d{3}$/)) {
					// WBSE - f.90000000.02.001
					// Normalize letter
					account = account.charAt(0).toLowerCase() + account.substr(1);
					// Strip periods
					account = account.replace(/\./g,'');
					var post = {'purchasewbse': account};
				} else if (account.match(/^\d{10}$/)) {
					var post = {'purchaseio': account};
				} else {
					// Really shouldn't be here
					alert("Format error");
					return;
				}
				pendingupdates++;
				num_changes++;
				post = JSON.stringify(post);
				WSPutURL(id, post, UpdatedAccountInfo);
			}
		}

		// Check budget justifications
		var spans = $('.justification_span');
		for (x=0;x<spans.length;x++) {
			// Check the old value (in HTML) against the input
			if (spans[x].innerHTML != justificationinputs[x].value
			 && spans[x].innerHTML != "null"
			 && justificationinputs[x].value != "null") {
				var id = accountstatus[x].getAttribute('data-api');
				var post = {'justification': justificationinputs[x].value}; //JSON.stringify({'justification': justificationinputs[x].value});
				post = JSON.stringify(post);
				pendingupdates++;
				num_changes++;
				WSPutURL(id, post, UpdatedAccountInfo);
			}
		}

		// Check approvers
		var spans = $('.approver_span');
		for (x=0;x<approverinputs.length;x++) {
			if (typeof (accountstatus[x]) == 'undefined') {
				continue;
			}
			//if ((spans[x].getElementsByTagName("a").length == 0 && approverinputs[x].value.match(/.*?\(([a-z0-9]+)\)/))
			// || (spans[x].getElementsByTagName("a").length != 0 && spans[x].getElementsByTagName("a")[0].innerHTML != approverinputs[x].value && approverinputs[x].value.match(/.*?\(([a-z0-9]+)\)/))) {
			if (spans[x].getAttribute('data-approverid') != approverinputs[x].getAttribute('data-id')) {
				var id = accountstatus[x].getAttribute('data-api');
				//var name = approverinputs[x].value.match(/.*?\(([a-z0-9]+)\)/);
				//	name = name[1];
				var post = { 'approveruserid': approverinputs[x].getAttribute('data-id') }//(approverinputs[x].value ? approverinputs[x].value : 0)};
				console.log(approverinputs[x]);
				if (accountstatus[x].value == "PENDING_COLLECTION") {
					post['approved'] = "0";
				}
				pendingupdates++;
				num_changes++;
				//console.log(post);
				post = JSON.stringify(post);
				WSPutURL(id, post, UpdatedAccountInfo);
			}
		}

		// Check if amounts changed
		var spans = $('.account_amount_span');
		for (x=0;x<spans.length;x++) {
			var amount = amountinputs[x].value;
			if (spans[x].innerHTML.replace(/[,.]/g, "") != amount.replace(/[,.]/g, "")) {
				if (amountinputs[x].value.match(/^-?[0-9]+\.[0-9]{2}$/)) {
					var id = accountstatus[x].getAttribute('data-api');
					var post = {'amount': amount.replace(/[,.]/g, "")};

					if (accountstatus[x].value == "PENDING_COLLECTION") {
						if (spans[x].innerHTML.replace(/[,.]/g, "") < amount.replace(/[,.]/g, "")) {
							post['approved'] = "0";
						}
					}
					pendingupdates++;
					num_changes++;
					//console.log(post);
					post = JSON.stringify(post);
					WSPutURL(id, post, UpdatedAccountInfo);
				}
			}
		}

		/*var quantityinputs = $('[name=quantity]');
		var originalquantity = $('[name=original_quantity]');
		var periodsinputs = $('[name=periods]');
		var originalperiods = $('[name=original_periods]');
		var originalprice = $('[name=original_total]');
		var priceinputs = $('[name=linetotal]');
		for (x=0;x<originalquantity.length;x++) {
			var post = {}
			var id = items[x].value;
			if (originalquantity[x].value != quantityinputs[x].value && quantityinputs[x].value.match(/^[0-9]+$/)) {
				post['quantity'] = quantityinputs[x].value;
			}
			if (originalperiods[x].value != periodsinputs[x].value && periodsinputs[x].value.match(/^[0-9]+$/)) {
				post['timeperiodcount'] = periodsinputs[x].value;
			}
			if (originalprice.length == quantityinputs.length && originalprice[x].value.replace(/[,\.]/g, "") != priceinputs[x].value.replace(/[,\.]/g, "").replace(/^0+/, "0") && priceinputs[x].value.replace(/[,\.]/g, "").replace(/^0+/, "0").match(/^[0-9]+$/)) {
				post['price'] = priceinputs[x].value.replace(/[,\.]/g, "");
			}

			//post = JSON.stringify(post);
			//if (post != "{}") {
				pendingupdates++;
				num_changes++;
				WSPutURL(id, post, UpdatedAccountInfo);
			//}
		}*/

		// Check for deleted accounts
		for (x=0;x<deleteaccounts.length;x++) {
			pendingupdates++;
			num_changes++;
			//console.log(deleteaccounts[x]);
			WSDeleteURL(deleteaccounts[x], UpdatedAccountInfo);
		}

		// Check for new accounts
		for (x=0;x<accountinputs.length;x++) {
			if (typeof(accountstatus[x]) == 'undefined') {
				var amount = amountinputs[x].value;
				if (accountinputs[x].value != "" && amount.match(/^-?[0-9]+\.[0-9]{2}$/)) {
					var account = accountinputs[x].value

					var post = {
						'orderid': document.getElementById("order").value,
						'amount': amount.replace(/[,.]/g, ""),
						'budgetjustification': justificationinputs[x].value
					};

					if (account.match(/^[A-Za-z]\.\d{8}\.\d{2}\.\d{3}$/)) {
						// WBSE - f.90000000.02.001
						// Normalize letter
						account = account.charAt(0).toLowerCase() + account.substr(1);
						// Strip periods
						account = account.replace(/\./g,'');

						post['purchasewbse'] = account;
					} else if (account.match(/^\d{10}$/)) {
						post['purchaseio'] = account;
					} else {
						// Really shouldn't be here
						alert("Format error");
						return;
					}

					if (approverinputs[x].getAttribute('data-id')) {
						post['approveruserid'] = approverinputs[x].getAttribute('data-id');
					}

					pendingupdates++;
					num_changes++;

					//console.log(post);
					post = JSON.stringify(post);

					WSPostURL(accountinputs[x].getAttribute('data-api'), post, UpdatedAccountInfo);
				}
			}
		}

		if (num_changes == 0) {
			CancelEditAccounts();
		}
	} else {
		// Change the "edit" button into a "save" button
		b.innerHTML = b.getAttribute('data-save-txt');
		b.classList.remove('btn-secondary');
		b.classList.add('btn-success');

		// Show the cancel button
		$(c).removeClass('hide');

		//if (new_row != null) {
			//var prompt_row = document.getElementById("account_new_row_prompt");
			//prompt_row.style.display = "table-row";
		//}
		$('.account-edit-hide').addClass('hide');
		$('.account-edit-show').removeClass('hide');

		var accountstatus = $('[name=accountid]');

		// enable delete buttons
		/*var buttons = $('[name=editremove]');
		for (x=0;x<buttons.length;x++) {
			buttons[x].style.display = "inline";
		}*/

		// disable any approve/deny buttons
		$('[name=adbutton]').prop('disabled', true);
		/*buttons = $('[name=adbutton]');
		for (x=0;x<buttons.length;x++) {
			buttons[x].style.display = "none";
		}*/

		// disable any remind buttons
		$('[name=remind]').prop('disabled', true);
		/*buttons = $('[name=remind]');
		for (x=0;x<buttons.length;x++) {
			buttons[x].style.display = "none";
		}
		// enable account number box
		var inputs = $('[name=account]');
		var spans = $('.account_span');
		for (x=0;x<accountstatus.length;x++) {
			if (accountstatus[x].value == "PENDING_ASSIGNMENT"
			|| accountstatus[x].value == "PENDING_APPROVAL"
			|| accountstatus[x].value == "PENDING_COLLECTION") {
				inputs[x].style.display = "inline";
				spans[x].style.display = "none";
			}
		}

		// enable amount box
		inputs = $('[name=account_amount]');
		spans = $('.account_amount_span');
		for (x=0;x<accountstatus.length;x++) {
			if (accountstatus[x].value == "PENDING_ASSIGNMENT"
			|| accountstatus[x].value == "PENDING_APPROVAL"
			|| accountstatus[x].value == "PENDING_COLLECTION") {
				inputs[x].style.display = "inline";
				spans[x].style.display = "none";
			}
		}

		// enable approver box
		inputs = $('[name=approver]');
		spans = $('.approver_span');
		for (x=0;x<inputs.length;x++) {
			if (accountstatus[x].value == "PENDING_ASSIGNMENT"
			|| accountstatus[x].value == "PENDING_APPROVAL"
			|| accountstatus[x].value == "PENDING_COLLECTION") {
				inputs[x].style.display = "inline";
				spans[x].style.display = "none";
			}
		}

		// enable justification box
		inputs = $('[name=justification]');
		spans = $('.justification_span');
		for (x=0;x<spans.length;x++) {
			if (accountstatus[x].value == "PENDING_ASSIGNMENT"
			|| accountstatus[x].value == "PENDING_APPROVAL"
			|| accountstatus[x].value == "PENDING_COLLECTION") {
				inputs[x].style.display = "inline";
				spans[x].style.display = "none";
			}
		}*/
	}
}

/**
 * Cancel editing of accounts
 *
 * @return  {void}
 */
function CancelEditAccounts() {
	window.location.reload(true);
}

/**
 * Remove account while editing
 *
 * @param   {string}  id
 * @param   {object}  e
 * @return  {void}
 */
function EditRemoveAccount(btn, e) {
	//var table = e.parentNode.parentNode.parentNode.parentNode;
	var row = $(btn.attr('href'));//table.getElementsByTagName("tr");
	if (btn.attr('data-api')) {
		deleteaccounts.push(btn.attr('data-api'));
		//console.log(btn.attr('data-api'));
		//WSDeleteURL(btn.attr('data-api'));
	}
	row.remove();

	UpdateBalance();
}

/**
 * Remove account while editing
 *
 * @param   {string}  id
 * @param   {object}  e
 * @return  {void}
 */
function EditRemoveProduct(btn, e) {
	var row = $(btn.attr('href'));//table.getElementsByTagName("tr");
	if (btn.attr('data-api')) {
		deleteitems.push(btn.attr('data-api'));
		//console.log(btn.attr('data-api'));
		//WSDeleteURL(btn.attr('data-api'));
	}
	row.remove();

	UpdateTotal();
}

/**
 * Error tally
 *
 * @var  {number}
 */
var numerrorboxes = 0;

/**
 * Callback after updating account info
 *
 * @param   {object}  xml
 * @return  {void}
 */
function UpdatedAccountInfo(xml) {
	pendingupdates--;

	if (xml.status < 400) {
		if (pendingupdates == 0) {
			window.location.reload(true);
		}
	} else {
		if (numerrorboxes == 0) {
			alert("An error occurred while updating account. Please reload page and try again or contact rcac-help@purdue.edu.");
			numerrorboxes++;
		}
	}
}

/**
 * Update total
 *
 * @param   {bool}  tot_override
 * @return  {void}
 */
function UpdateTotal(tot_override) {
	if (typeof(tot_override) == 'undefined') {
		tot_override = false;
	}
	var inputs = $('[name=quantity]');
	var periods = $('[name=periods]');
	var prices = $('[name=price]');
	var spans = $('[name=itemtotal]')
	var qspans = $('.quantity_span');
	var periods_spans = $('.periods_span');
	var totalinputs = $('[name=linetotal]');
	var x;

	// sannity checks
	for (x=0;x<totalinputs.length;x++) {
		totalinputs[x].value = totalinputs[x].value.replace(/[\$,]/g, "");
		if (totalinputs[x].value.match(/^[0-9]+$/)) {
			totalinputs[x].value = totalinputs[x].value + ".00";
		}
		totalinputs[x].value = totalinputs[x].value.replace(/[,\.]/g, "").replace(/^0+/, "");
		totalinputs[x].value = FormatNumber(totalinputs[x].value).replace(/[,]/g,"");
		if (!totalinputs[x].value.replace(/[,\.]/g, "").match(/^[0-9]+$/)) {
			return;
		}
		if (!inputs[x].value.match(/^[0-9]+$/)) {
			return;
		}
		if (!periods[x].value.match(/^[0-9]+$/)) {
			return;
		}
	}

	var total = 0;
	for (x=0;x<inputs.length;x++) {
		if (prices[x].innerHTML.replace(/[,\.]/g,"") * qspans[x].innerHTML * periods_spans[x].innerHTML == spans[x].innerHTML.replace(/[,\.]/g,"") && !tot_override) {
			spans[x].innerHTML = FormatNumber(prices[x].innerHTML.replace(/[,\.]/g,"") * inputs[x].value * periods[x].value);
			total += prices[x].innerHTML.replace(/[,\.]/g,"") * inputs[x].value * periods[x].value;
			if (totalinputs.length == inputs.length) {
				totalinputs[x].value = FormatNumber(prices[x].innerHTML.replace(/[,\.]/g, "") * inputs[x].value * periods[x].value); //.replace(/,/g,"");
			}
		} else {
			if (totalinputs.length == inputs.length) {
				total += parseInt(totalinputs[x].value.replace(/[,\.]/g,""));
				spans[x].innerHTML = totalinputs[x].value.replace(/,/g,"");
			} else {
				total += parseInt(spans[x].innerHTML.replace(/[,\.]/g,""));
			}
		}
		qspans[x].innerHTML = inputs[x].value;
		periods_spans[x].innerHTML = periods[x].value;
	}

	document.getElementById("ordertotal").innerHTML = FormatNumber(total);

	var allow = true;
	if ($('[name=accountid]').length == 0) {
		// No accounts saved yet... check to see if anything is entered
		var accounts = $('[name=account]');
		var costcenters = $('[name=costcenter]');
		var orders = $('[name=purchaseorder]');
		var account_amounts = $('[name=account_amount]');

		for (x=0;x<accounts.length;x++) {
			if (accounts[x].value != "") {
				allow = false;
			}
		}
		for (x=0;x<costcenters.length;x++) {
			if (x<costcenters[x].value != "") {
				allow = false;
			}
		}
		for (x=0;x<orders.length;x++) {
			if (x<orders[x].value != "") {
				allow = false;
			}
		}
		for (x=0;x<account_amounts.length;x++) {
			if (x<account_amounts[x].value != "") {
				allow = false;
			}
		}
	} else {
		allow = false;
	}

	if (!allow) {
		UpdateBalance();
	} else {
		document.getElementById("balance").innerHTML = FormatNumber(total);
		document.getElementById("save_quantities").disabled = false;
	}
}

/**
 * Edit quantities
 *
 * @return  {void}
 */
function EditQuantities() {
	var b = document.getElementById("save_quantities");
	//var c = document.getElementById("cancel_quantities");
	var b2 = document.getElementById("save_accounts");
	//var c2 = document.getElementById("cancel_accounts");
	//var itemstatus = $('[name=itemid]');
	var x;
	var inputs = $('[name=quantity]');

	if (b.getAttribute('data-state') != 'active') {
		/*if (b2 && b2.innerHTML == "Edit Accounts") {
			EditAccounts();
		}*/
		b.setAttribute('data-state', 'active');
		b.innerHTML = b.getAttribute('data-active');
		b.classList.remove('btn-secondary');
		b.classList.add('btn-success');
		//c.style.display = "inline";

		$('.item-edit-hide').addClass('hide');
		$('.item-edit-show').removeClass('hide');

		/*var spans = $('.quantity_span');
		var totals = $('[name=itemtotal]');
		var price = $('[name=price]');
		var periods = $('[name=periods]');
		var periodspans = $('.periods_span');
		var totalinputs = $('[name=linetotal]');

		for (x=0;x<inputs.length;x++) {
			if (inputs[x].value * price[x].innerHTML.replace(/[,\.]/g, "") * periods[x].value == totals[x].innerHTML.replace(/[,\.]/g, "")
			|| totalinputs.length == inputs.length) {
				inputs[x].style.display = "inline";
				spans[x].style.display = "none";
				periods[x].style.display = "inline";
				periodspans[x].style.display = "none";
			}
			if (totalinputs.length == inputs.length) {
				totalinputs[x].style.display = "inline";
				totals[x].style.display = "none";
			}
		}*/
	} else {
		// See how many total items are in the order.
		var totalItems = 0;
		for (x = 0; x < inputs.length; x++) {
			totalItems += inputs[x].value;
		}

		// Alert the user that this will delete their order.
		if (totalItems == 0) {
			$('#error1').dialog({
				modal: true,
				width: 500,
				buttons: {
					"Cancel order": function() {
						var order = document.getElementById("order").value;
						WSDeleteURL(order, CanceledOrder);
					},
					"Exit": function() {
						$(this).dialog("close");
						CancelEditAccounts();
					}
				}
			});
			$('#error1').dialog('open');
			return;
		}

		if ($('[name=accountid]').length > 0) {
			EditAccounts();
		} else {
			SaveQuantities();
		}
	}
}

/**
 * Save order user
 *
 * @return  {void}
 */
function SaveOrderUser() {
	var button = document.getElementById("user_save");

	if (button.className.match(/pencil/)) {
		button.className = "fa fa-save";
		document.getElementById("search_user").parentNode.classList.remove('hide');//parentNode.style.display = "inline";
		document.getElementById("edit_user").classList.add('hide');//style.display = "none";
	} else {
		var id = document.getElementById("order").getAttribute('data-api'); //value;
		var name = document.getElementById("search_user").value;
		//console.log(name);
		//var nm = name.match(/^.*?\(([a-z0-9]+)\)$/);
		if (name) {
			//if (nm[0] != document.getElementById("edit_user").innerHTML) {
			if (name != document.getElementById("edit_user").getAttribute('data-userid')) {
				//name = nm[1];

				//var post = JSON.stringify({'userid': name});
				pendingupdates++;
				//WSPutURL(id, post, UpdatedAccountInfo);

				$.ajax({
					url: id,
					type: 'put',
					data: {
						'userid' : name
					},
					dataType: 'json',
					async: false,
					success: function(response) {
						pendingupdates--;
						if (pendingupdates == 0) {
							window.location.reload(true);
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						console.log(xhr);
						if (numerrorboxes == 0) {
							alert("An error occurred while updating account. Please reload page and try again or contact rcac-help@purdue.edu.");
							numerrorboxes++;
						}
					}
				});
			} else {
				document.getElementById("search_user").parentNode.classList.add('hide');
				document.getElementById("edit_user").classList.remove('hide');
				button.className = "fa fa-pencil";
			}
		}
	}
}

/**
 * Save order group
 *
 * @return  {void}
 */
function SaveOrderGroup() {
	var button = document.getElementById("group_save");

	if (button.className.match(/pencil/)) {
		button.className = "fa fa-save";
		document.getElementById("search_group").style.display = "inline";
		document.getElementById("edit_group").style.display = "none";
	} else {
		var url = document.getElementById("order").getAttribute('data-api');
		var id = document.getElementById("search_group").getAttribute('data-groupid');

		if (document.getElementById("edit_group").getAttribute('data-groupid') != id) {
			var post = JSON.stringify({'groupid': id});
			pendingupdates++;

			WSPutURL(url, post, UpdatedAccountInfo);
		} else {
			document.getElementById("search_group").style.display = "none";
			document.getElementById("edit_group").style.display = "inline";
			button.className = "fa fa-pencil";
		}
	}
}

/**
 * Renew
 *
 * @param   {number}  sequence
 * @return  {void}
 */
function Renew(url, sequence) {
	var post = {};
	post['orderitemsequence'] = sequence;
	post = JSON.stringify(post);

	WSPostURL(url, post, function(xml) {
		if (xml.status == 200) {
			var results =  JSON.parse(xml.responseText);
			window.location = "/orders/" + results['id'];
		} else {
			alert("An error occurred while renewing. Please wait a few minutes and try again. If error continues, please contact rcac-help@purdue.edu.");
		}
	});
}

/**
 * Filter list
 *
 * @param   {string}  page
 * @param   {string}  field
 * @return  {void}
 */
function Filter(page, field) {
	var filter = document.getElementById("filter" + field);
	var value = "";

	if (filter.tagName == "SELECT") {
		for (var x=0;x<filter.options.length;x++) {
			if (filter.options[x].selected == true) {
				value = filter.options[x].value;
				break;
			}
		}
	} else if (filter.tagName == "INPUT") {
		value = filter.value;
	}

	var url = window.location.href.match(/\?.*/);
	if (field == "id") {
		url = value;
	} else if (url != null) {
		url = url[0];
		if (url.match("page=")){
			var t = new RegExp("page=[^&]*");
			url = url.replace(t,"page=1");
		}
		if (url.match("" + field + "=")) {
			var r = new RegExp("(" + field + "=)[^&]*");
			url = url.replace(r, "\$1" + value);
		} else {
			url = url + "&" + field + "=" + value;
		}
	} else {
		url = "?" + field + "=" + value;
	}
	url = "/orders/" + page + "/" + url;

	window.location = url;
}

/**
 * Search event handler
 *
 * @param   {object}  event
 * @param   {object}  ui
 * @return  {void}
 */
function SearchEventHandler(event, ui) {
	var id = ui['item']['id'];
	//var name = ui['item']['name'];
	//var username = ui['item']['usernames'][0]['name'];
	var username = ui['item']['username'];

	if (typeof(id) == 'undefined') {
		var post = JSON.stringify({ "name" : username });

		$.ajax({
			url: $('#search_user').data('api-create'),
			type: 'post',
			data: {
				'name' : ui['item']['name'],
				'username' : ui['item']['username']
			},
			dataType: 'json',
			async: false,
			success: function(response) {
				//Halcyon.message('success', 'Item added');

				// Don't really need to do anything here, we are just ensuring the selected user has a database entry
			},
			error: function(xhr, ajaxOptions, thrownError) {
				//console.log(xhr);
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});

		/*WSPostURL(ROOT_URL + "userusername", post, function(xml) {
			if (xml.status == 200) {
				// Don't really need to do anything here, we are just ensuring the selected user has a database entry
			} else {
				// handle errors
				// Its probably OK. The WS should pick it up.
			}
		});*/
	}
}

/**
 * Prepares the page to be printed.
 *
 * @return  {void}
 */
function PrintOrder() {
	/* Hide text boxes if they're empty. */
	$(".ordernotes").each(function( index ) {
		if ($(this).text() == "") {
			$(this).hide();
			switch(index) {
				case 0:
					$('.orderheader:contains("Customer Order Notes")').hide();
					break;
				case 1:
					$('.orderheader:contains("Internal Notes")').hide();
					break;
			}
		}
	});

	window.print();

	/* Bring back the elements we hid */
	$(".ordernotes").each(function( index ) {
		$(this).show();
		switch(index) {
			case 0:
				$('.orderheader:contains("Customer Order Notes")').show();
				break;
			case 1:
				$('.orderheader:contains("Internal Notes")').show();
				break;
		}
	});
}