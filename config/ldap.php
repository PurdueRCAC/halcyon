<?php
return [

	/*
	|--------------------------------------------------------------------------
	| Listener Stubs
	|--------------------------------------------------------------------------
	|
	| Default module stubs.
	|
	
	'default' => [
		// Mandatory Configuration Options
		'hosts'            => ['corp-dc1.corp.acme.org', 'corp-dc2.corp.acme.org'],
		'base_dn'          => 'dc=corp,dc=acme,dc=org',
		'username'         => 'admin',
		'password'         => 'password',

		// Optional Configuration Options
		'schema'           => Adldap\Schemas\ActiveDirectory::class,
		'account_prefix'   => 'ACME-',
		'account_suffix'   => '@acme.org',
		'port'             => 389,
		'follow_referrals' => false,
		'use_ssl'          => false,
		'use_tls'          => false,
		'version'          => 3,
		'timeout'          => 5,

		// Custom LDAP Options
		'custom_options'   => [
			// See: http://php.net/ldap_set_option
			LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_HARD
		]
	],*/

	'rcac' => [
		'hosts'            => ['animus.rcac.purdue.edu'],
		'base_dn'          => 'ou=People,dc=rcac,dc=purdue,dc=edu',
		'use_ssl'          => true,
		'username'         => '',
		'password'         => '',
	],

	'rcac_group' => [
		'hosts'            => ['animus.rcac.purdue.edu'],
		'base_dn'          => 'ou=Group,dc=rcac,dc=purdue,dc=edu',
		'use_ssl'          => true,
		'username'         => '',
		'password'         => '',
	],

	'ped' => [
		'hosts'            => ['ped.purdue.edu'],
		'base_dn'          => 'ou=ped,dc=purdue,dc=edu',
		'username'         => '',
		'password'         => '',
	],

	'dbm' => [
		'hosts'            => ['webservices.itns.purdue.edu:10636'],
		'base_dn'          => 'cn=users,cn=careeraccount,dc=purdue,dc=edu',
		'username'         => 'uid=rcacservice,cn=nonperson,cn=administrators,dc=purdue,dc=edu',
		'password'         => 'VopIanCigh',
	],
];
