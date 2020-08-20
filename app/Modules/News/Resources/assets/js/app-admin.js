/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

// Add a request interceptor
window.axios.interceptors.request.use(function (config) {
	// assume your access token is stored in local storage 
	// (it should really be somewhere more secure but I digress for simplicity)
	let token = document.head.querySelector('meta[name="api-token"]').getAttribute('content');//localStorage.getItem('access_token')
	if (token) {
		config.headers['Authorization'] = `Bearer ${token}`
	}
	return config;
},
function (error) {
	// Do something with request error
	return Promise.reject(error);
});

window.Vue = require('vue');

let baseurl = document.head.querySelector('meta[name="base-url"]');

if (baseurl) {
	Vue.prototype.ROOT_URL = baseurl.content;
} else {
	Vue.prototype.ROOT_URL = '';
}


/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

//Vue.component('example-component', require('./components/ExampleComponent.vue').default);
/*import moment from 'moment';

Vue.filter('formatDate', function(value) {
	if (value) {
		return moment(String(value)).format('MM/DD/YYYY hh:mm');
	}
});*/

import Admin from './components/Admin.vue';

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
	el: '#app',
	components: {
		Admin
	},
	render: h => h(Admin)
});
