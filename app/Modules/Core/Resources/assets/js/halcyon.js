/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// Only define the Halcyon namespace if not defined
if (typeof(Halcyon) === 'undefined') {
	var Halcyon = {};
}

/**
 * Get root URL
 *
 * @return  string
 */
Halcyon.root = function ()
{
	var scripts = document.getElementsByTagName('script'),
		root = null,
		i;

	if (root === null) {
		root = '';

		for (i = 0; 1 <= scripts.length; i++)
		{
			if (scripts[i].src.toLowerCase().indexOf('/core') > -1) {
				root = scripts[i].src.substr(0, scripts[i].src.indexOf('/core'));
				break;
			}

			if (scripts[i].src.toLowerCase().indexOf('/app') > -1) {
				root = scripts[i].src.substr(0, scripts[i].src.indexOf('/app'));
				break;
			}
		}
	}

	return root;
}

/**
 * Session based api initialization
 *
 * @param   object  Callback function
 * @return  void
 */
Halcyon.initApi = function (callback)
{
	// Get session token for oauth calls
	$.ajax({
		url      : Halcyon.root() + '/api/developer/oauth/token',
		data     : 'grant_type=session',
		dataType : 'json',
		type     : 'POST',
		cache    : 'false',
		success  : function (data, textStatus, jqXHR) {
			var token = data.access_token;

			// Set defaults for ajax calls
			$.ajaxSetup({
				headers : {
					'Authorization' : 'Bearer ' + token
				}
			});

			if ($.type(callback) === 'function') {
				callback();
			}

			// Check again when the token expires
			setTimeout(Halcyon.initApi, data.expires_in*1000);
		}
	});
};
