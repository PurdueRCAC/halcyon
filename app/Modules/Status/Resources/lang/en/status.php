<?php
return [
	'module name' => 'Status',
	'configuration' => 'Configuration',

	// Fields
	'status' => 'Status',
	'name' => 'Name',
	'state' => [
		'impaired' => 'One or more services may be experiencing issues',
		'down' => 'One or more services are down',
		'maintenance' => 'Service is undergoing maintenance',
		'offline' => 'Service is offline',
		'operational' => 'All services operational',
	],
	'has announcements' => 'Announcements available',
	'option' => [
		'automatic' => 'Automatic',
		'operational' => 'Operational',
		'impaired' => 'Experiencing issues',
		'down' => 'Down',
		'maintenance' => 'Maintenance',
		'offline' => 'Offline',
	],
	'starts' => 'Starts',
	'ends' => 'Ends',
	'no upcoming items' => 'No upcoming items.',
];
