<?php
return [
	'module name' => 'Resource Manager',
	'resources' => 'Resources',
	'subresources' => 'Subresources',
	'types' => 'Types',
	'batchsystems' => 'Batch Systems',
	'type resources' => ':type Resources',
	'type retired resources' => 'Retired :type Resources',
	'retired' => 'Retired Resources',

	'errors' => [
		'type has resources' => 'Type has :count resources. They must be re-assigned before this type can be removed.',
		'batchsystem has resources' => 'Batchsystem has :count resources. They must be re-assigned before this batchsystem can be removed.',
	],
	'start scheduling' => 'Enable scheduling',
	'stop scheduling' => 'Disable scheduling',
	'messages' => [
		'queues stopped' => 'Queue scheduling stopped on :count subresources',
		'queues started' => 'Queue scheduling started on :count subresources',
	],
];
