<?php
return [
	'module name' => 'Group Manager',
	'module sections' => 'Module sections',
	'groups' => 'Groups',
	'group' => 'Group',
	'id' => 'ID',
	'name' => 'Name',
	'description' => 'Description',
	'username' => 'Username',
	'members' => 'Members',
	'slug' => 'Slug',
	'create group' => 'Create Group',
	'created' => 'Created',
	'creator' => 'Creator',
	'modifier' => 'Modifier',
	'modified' => 'Modified',
	'type' => 'Type',
	'department' => 'Department',
	'active allocation' => 'Active Allocation',
	'create default unix groups' => 'Create Default Unix Groups',
	'default unix groups desc' => 'This will create default Unix groups; A base group, <code>apps</code>, and <code>data</code> group will be created. These will prefixed by the base name chosen. Once these are created, the groups and base name cannot be easily changed.',
	'create base unix group only' => 'Create Base Unix Group Only',
	'base unix group' => 'Base Unix group',
	'unix group base name' => 'Unix group base name',
	'unix group' => 'Unix group',
	'unix id' => 'Unix ID',
	'history edited' => ':user edited the page @ :datetime',
	'overview' => 'Overview',
	'user account removed' => 'User account was removed.',
	'membership type' => 'Membership Type',
	'select membership type' => '- All Membership Types -',
	'added' => 'Added',
	'joined' => 'Joined',
	'all states' => '- All States -',
	'last visit' => 'Last Visit',
	'unix groups' => 'Unix Groups',
	'all departments' => '- All Departments -',
	'all fields of science' => '- All Fields of Science -',
	'select department' => '- Select Department -',
	'select field of science' => '- Field of Science -',
	'fields of science' => 'Fields of Science',
	'field of science' => 'Field of Science',
	'departments' => 'Departments',
	'my groups' => 'Groups',
	'parent' => 'Parent',
	'unix group base name hint' => 'Alpha-numeric & dashes, max 10 characters',
	'unix group id' => 'Unix group system ID',
	'short name' => 'Short name',
	'confirm delete' => 'Are you sure you want to remove this?',
	'motd' => 'Notices',
	'past motd' => 'Past Notices',
	'set notice' => 'Set Notice',
	'past notices' => 'Past Notices',
	'from' => 'From',
	'until' => 'Until',
	'message' => 'Message',
	'back' => 'Back',
	'export' => 'Export',
	'import' => 'Import',
	'memberships updated' => 'Memberships updated!',
	'group has pending requests' => ':group has pending membership requests.',
	'history' => [
		'title' => 'History',
		'date' => 'Date',
		'time' => 'Time',
		'actor' => 'Actor',
		'manager' => 'Manager',
		'user' => 'User',
		'action taken' => 'Action taken',
		'none' => 'No activity found.',
		'description' => 'Any actions taken by managers of this group are listed below. There may be a short delay in actions showing up in the log.',
		'error' => 'An error occurred while performing this action. Action may not have completed.',
	],
	'mail' => [
		'ownerauthorized' => 'Manager Approved',
		'ownerremoved' => 'Manager Removed',
		'user request submitted' => 'New access request to group\'s resources',
	],
	'group member' => 'group member',
	'error' => [
		'group name already exists' => 'Group ":name" already exists.',
		'name is too long' => 'Name exceeds allowable max length.',
		'name is incorrectly formatted' => 'Name is incorrectly formatted.',
		'unixgroup name already exists' => 'Unix group ":name" already exists.',
		'unixgroup invalid format' => 'Field `unixgroup` not in valid format.',
	],
	'cascade managers' => 'Automatically add managers to associated items',
	'cascade managers desc' => 'When checked, this means that any time someone is added to the group as a manager or promoted to manager, they will automatically become a member of any associated unix groups, compute queues, etc.',
	'prefix unixgroup' => 'Enforce custom unix groups are prefixed',
	'prefix unixgroup desc' => 'Enforce custom unix groups are prefixed with the base unix group.',
	'user will be added as a manager' => ':user will be added as a manager.',
	'add to group' => 'Add to group',
	'choose group' => 'Choose group',
	'groups have pending requests' => 'The following groups have pending membership requests',
	'what is this page' => 'What is this page?',
	'what this page is' => 'If you are a manager or member of a group, you will find it listed here. You will also find groups listed where you are a member of at least one of its resource queues or unix groups.',
	'pending membership requests' => 'Pending membership requests',
	'enter motd' => 'Enter the notice your group will see at login',
	'delete notice' => 'Delete Notice',
];
