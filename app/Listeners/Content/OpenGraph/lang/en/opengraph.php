<?php
return [
	'listener name' => 'Content - Open Graph',
	'listener desc' => 'Adding Open Graph meta information to the site. The Open Graph protocol enables any web page to become a rich object in a social graph.<br /><a href="http://ogp.me">http://ogp.me</a>',

	//'CATEGORY_OPTIONS' => 'Category (Blog) Options',
	//'DISPLAY_CATEGORY_LABEL' => 'Display (Category)',
	//'DISPLAY_CATEGORY_DESC' => 'Run the plugin in Category View',
	//'DISPLAY_FEATURED_LABEL' => 'Display (Featured)',
	//'DISPLAY_FEATURED_DESC' => 'Run the plugin in Featured View',

	// Required Properties
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
	// Miscellaneous Settings
	'app id label' => 'Application ID',
	'app id desc' => 'Set Facebook Application ID',
	'other properties label' => 'Other Properties',
	'other properties desc' => 'Set other properties. Separate each property value with semicolon (;). E.g. og:audio:title=Some Audio;og:audio:artist=SomeArtist',
	// Fieldsets
	'article options' => 'Article Options',
	'featured options' => 'Featured Options',
	'common options' => 'Common Options',
];
