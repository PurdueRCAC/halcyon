<?php
return [
	'listener name' => 'Content - External links',
	'listener desc' => 'Apply specific actions or `rel` attributes to external links in content.',
	'params' => [
		'MODE_LABEL' => 'Change external links',
		'MODE_DESC' => 'Follow mode for external links',
		'MODE_SETIF' => 'Set "No Follow", if no `rel` attribute is specified',
		'MODE_FORCE' => 'Force "No Follow"',
		'MODE_STRIP' => 'Strip "No Follow"',
		'MODE_NOCHANGE' => 'Do not change',
		'TARGET_LABEL' => 'Change link target',
		'TARGET_DESC' => 'Add or change the link\'s `target` attribute',
		'TARGET_IFSET' => 'Open in new window (_blank) if no `target` attribute specified',
		'TARGET_BLANK' => 'Open in new window (_blank)',
		'TARGET_PARENT' => 'Open in parent window / frame (_parent)',
		'TARGET_NOCHANGE' => 'Do not change',
		'CSS_LABEL' => 'Ignore with CSS Classes',
		'CSS_DESC' => 'Links with one or mroe of the specified classes will be ignored. Comma separated values.',
	],
];
