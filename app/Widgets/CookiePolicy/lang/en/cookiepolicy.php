<?php
return [
	'widget name' => 'e-Privacy Directive (cookie policy)',
	'widget desc' => 'This shows a notice about the site\'s e-Privacy directive (aka cookie policy)',
	// Misc.
	'close' => 'Close',
	'default message' => 'The :name website uses cookies. By continuing to browse the site, you are agreeing to our use of cookies. <a href="/legal/privacy">Find out more &raquo;</a>',
	// Params
	'params' => [
		'id' => 'Widget ID',
		'id desc' => 'An ID to be applied to the css of the widget container, this allows individual widget styling',
		'message' => 'Message',
		'message desc' => 'The message to be displayed.',
		'duration' => 'Duration',
		'duration desc' => 'The time, in days, that confirmation of the e-privacy policy lasts.',
	],
];
