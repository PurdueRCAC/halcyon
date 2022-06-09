<?php
return [
	'module name' => 'Group Manager',
	'module sections' => 'Module sections',
	'groups' => 'Groups',
	'id' => 'ID',
	'name' => 'Name',
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
	'unix group base name' => 'Unix group base name',
	'unix group' => 'Unix group',
	'unix id' => 'Unix ID',
	'history edited' => ':user edited the page @ :datetime',
	'history' => 'History',
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
];
