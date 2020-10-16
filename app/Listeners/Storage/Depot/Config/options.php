<?php

return [
	'fieldsets' => [
		'basic' => [
			'label' => 'listeners::listeners.options',
			'fields' => [
				'user' => [
					'type' => 'text',
					'default' => '',
					'label' => 'listener.storage.depot::depot.username',
					'description' => 'listener.storage.depot::depot.username desc',
				],
				'pass' => [
					'type' => 'password',
					'default' => '',
					'label' => 'listener.storage.depot::depot.password',
					'description' => 'listener.storage.depot::depot.password desc',
				],
				'url' => [
					'type' => 'text',
					'default' => '',
					'label' => 'listener.storage.depot::depot.url',
					'description' => 'listener.storage.depot::depot.url desc',
				],
				'api_key' => [
					'type' => 'text',
					'default' => '',
					'label' => 'listener.storage.depot::depot.api key',
					'description' => 'listener.storage.depot::depot.api key desc',
				],
			],
		],
	],
];
