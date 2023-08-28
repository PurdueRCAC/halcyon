/* global Vue */

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
/*
window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add a request interceptor
window.axios.interceptors.request.use(
	function (config) {
		const token = document.head.querySelector('meta[name="csrf-token"]');

		if (token) {
			config.headers['X-CSRF-TOKEN'] = token.content;
		} else {
			console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
		}

		const userApiToken = document.head.querySelector('meta[name="api-token"]');

		if (userApiToken) {
			config.headers['Authorization'] = `Bearer ${userApiToken.content}`;
		} else {
			console.error('User API token not found in a meta tag.');
		}

		return config;
	},
	function (error) {
		// Do something with request error
		return Promise.reject(error);
	}
);
*/
const currentLocale = document.querySelector('html').getAttribute('lang');

if (currentLocale) {
	window.Halcyon.currentLocale = currentLocale;
} else {
	console.error('Current locale token not found in a meta tag.');
}

// Let's get the main Vue object
/*window.Vue = require('vue');*/

const baseurl = document.head.querySelector('meta[name="base-url"]');

window.ROOT_URL = '';
if (baseurl) {
	window.ROOT_URL = baseurl.content;
}
window.fetch_headers = {
	'Content-Type': 'application/json',
	'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
};

// Import the app
import { createApp } from 'vue';
import CrmReports from './components/site/CrmReports.vue';
//import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

/*new Vue({
	el: '#contactreports',
	render: h => h(CrmReports)
});*/
const app = createApp(CrmReports); //.component('font-awesome-icon', FontAwesomeIcon);

app.mount('#contactreports');
