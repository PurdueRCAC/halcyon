<?php

return [
	'name' => 'Users',
	'profiles' => [
		'org' => [
			'type' => 'textarea',
			'label' => '',
			'desc' => '',
			'required' => false,
		],
		'orcid' => [
			'type' => 'text',
			'label' => '',
			'desc' => '',
			'required' => false,
		],
	],
	'redirect_route_after_login' => 'home',
	'redirect_route_after_logout' => 'home',
	'create_on_login' => 1,
	'create_on_search' => 1,
];
