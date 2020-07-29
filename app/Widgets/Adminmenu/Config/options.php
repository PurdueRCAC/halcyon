<?php

return [
	'fieldsets' => [
		'basic' => [
			'label' => 'widgets::widgets.options',
			'fields' => [
				'class' => [
					'type' => 'text',
					'default' => '',
					'label' => 'widgets::widgets.class',
					'description' => 'widgets::widgets.class description',
				],
				'show_help' => [
					'type' => 'radio',
					'default' => 1,
					'label' => 'widget.breadcrumbs::config.show help',
					'description' => 'widget.breadcrumbs::config.show help description',
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
