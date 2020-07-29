<?php

return [
	'fieldsets' => [
		'basic' => [
			'label' => 'listeners::listeners.options',
			'fields' => [
				'user' => [
					'type' => 'text',
					'default' => '',
					'label' => 'listener.groups.github::github.username',
					'description' => 'listener.groups.github::github.username desc',
				],
				'pass' => [
					'type' => 'password',
					'default' => '',
					'label' => 'listener.groups.github::github.password',
					'description' => 'listener.groups.github::github.password desc',
				],
				'url' => [
					'type' => 'text',
					'default' => '',
					'label' => 'listener.groups.github::github.url',
					'description' => 'listener.groups.github::github.url desc',
				],
				'api_key' => [
					'type' => 'text',
					'default' => '',
					'label' => 'listener.groups.github::github.api key',
					'description' => 'listener.groups.github::github.api key desc',
				],
			],
		],
	],
];
