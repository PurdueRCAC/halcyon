<?php
return [
	'listener name' => 'Content - Twitter',
	'listener desc' => 'Add metadata for Twitter to the document.',

	// Required Properties
	'username label' => 'Twitter Username',
	'username desc' => 'Provide the Twitter username that content should be associated with.',
	'title label' => 'Title',
	'title desc' => 'If empty, article title will be set. It is recommended to leave this parameter empty.',
	'type label' => 'Type',
	'type desc' => 'Set type',
	'image label' => 'Image',
	'image desc' => 'Set image. If image will be not set here, plugin will try to find the image in article content. If article does not contain any image, plugin will try to search /files/opengraph/ folder for image which has the same name as article ID has (e.g. article ID=1 ==> 1.jpg). See documentation to understand this behaviour.',
	'url label' => 'Url',
	'url desc' => 'If empty, article url will be set. It is recommended to leave this parameter empty',
	// Recommended Properties
	'site name label' => 'Site Name',
	'site name desc' => 'Set site name - human-readable name of your site. If empty, site name from global configuration will be set. It is recommended to leave this parameter empty.',
	'description label' => 'Site Description',
	'description desc' => 'Set site description. If empty, site description from article options will be set, if the description of article will be empty, global configuration will be set. Site Meta Description parameter will be used.',
	// Fieldsets
	'article options' => 'Article Options',
	'featured options' => 'Featured Options',
	'common options' => 'Common Options',
];
