<?php

return [
	'fieldsets' => [
		'pages' => [
			'label' => 'pages::options.pages',
			'description' => 'pages::options.pages description',
			'fields' => [
				'show_title' => [
					'type' => 'radio',
					'default' => 1,
					'label' => 'pages::options.show title',
					'description' => 'pages::options.show title description',
					'options' => [
						0 => 'global.hide',
						1 => 'global.show',
					],
				],
				'show_author' => [
					'type' => 'radio',
					'default' => 0,
					'label' => 'pages::options.show author',
					'description' => 'pages::options.show author description',
					'options' => [
						0 => 'global.hide',
						1 => 'global.show',
					],
				],
				'show_create_date' => [
					'type' => 'radio',
					'default' => 0,
					'label' => 'pages::options.show create date',
					'description' => 'pages::options.show create date description',
					'options' => [
						0 => 'global.hide',
						1 => 'global.show',
					],
				],
				'show_modify_date' => [
					'type' => 'radio',
					'default' => 0,
					'label' => 'pages::options.show modify date',
					'description' => 'pages::options.show modify date description',
					'options' => [
						0 => 'global.hide',
						1 => 'global.show',
					],
				],
			],
		],
		'permissions' => [
			'label' => 'access.PERMISSIONS_LABEL',
			'description' => 'access.PERMISSIONS_DESC',
			'fields' => [
				'rules' => [
					'type' => 'rules',
					'label' => 'access.PERMISSIONS_LABEL',
					'class' => 'inputbox',
					'validate' => '',
					'filter' => 'rules',
					'module' => 'themes',
					'section' => 'module',
				],
			],
		],
	],
];
