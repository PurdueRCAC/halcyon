<?php

return [
	'fieldsets' => [
		'basic' => [
			'fields' => [
				'showmode' => [
					'type'    => 'list',
					'default' => 0,
					'label'   => 'pages::options.show value',
					'desc'    => 'pages::options.show value desc',
					'options' => [
						0 => 'widgets::whosonline.param show number',
						1 => 'widgets::whosonline.param show name',
						2 => 'widgets::whosonline.param show both'
					],
				],
			],
		],
		'advanced' => [
			'fields' => [
				'moduleclass_sfx' => [
					'type'    => 'text'
					'label'   => 'widgets::whosonline.param display limit',
					'desc'    => 'widgets::whosonline.param display limit desc',
					'default' => ''
				],
				'cache' => [
					'type'    => 'list'
					'label'   => 'widgets::whosonline.param display limit',
					'desc'    => 'widgets::whosonline.param display limit desc',
					'default' => ''
				],
			],
		],
		'admin' => [
			'fields' => [
				'display_limit' => [
					'type' => 'text'
					'label' => 'widgets::whosonline.param display limit',
					'desc' => 'widgets::whosonline.param display limit desc',
					'default' => 25
				],
			],
		],
	],
];
