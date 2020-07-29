<?php

return [
	'fieldsets' => [
		'basic' => [
			'label' => 'widgets::widgets.options',
			'fields' => [
				'show_here' => [
					'type' => 'radio',
					'default' => 1,
					'label' => 'widget.breadcrumbs::config.show here',
					'description' => 'widget.breadcrumbs::config.show here description',
					'options' => [
						0 => 'global.no',
						1 => 'global.yes',
					],
				],
				'show_home' => [
					'type' => 'radio',
					'default' => 1,
					'label' => 'widget.breadcrumbs::config.show home',
					'description' => 'widget.breadcrumbs::config.show home description',
					'options' => [
						0 => 'global.no',
						1 => 'global.yes',
					],
				],
				'home_text' => [
					'type' => 'text',
					'default' => '',
					'label' => 'widget.breadcrumbs::config.home text',
					'description' => 'widget.breadcrumbs::config.home text description',
				],
				'show_last' => [
					'type' => 'radio',
					'default' => 1,
					'label' => 'widget.breadcrumbs::config.show last',
					'description' => 'widget.breadcrumbs::config.show last description',
					'options' => [
						0 => 'global.no',
						1 => 'global.yes',
					],
				],
			],
		],
	],
];
