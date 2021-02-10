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
		'username'         => env('LDAP_RCAC_USERNAME', conf('ldap_rcac', 'rdn', '')),
		'password'         => env('LDAP_RCAC_PASSWORD', conf('ldap_rcac', 'pass', '')),
	],

	'rcac_group' => [
		'hosts'            => ['animus.rcac.purdue.edu'],
		'base_dn'          => 'ou=Group,dc=rcac,dc=purdue,dc=edu',
		'use_ssl'          => true,
		'username'         => env('LDAP_RCACGROUP_USERNAME', conf('ldap_rcac_group', 'rdn', '')),
		'password'         => env('LDAP_RCACGROUP_PASSWORD', conf('ldap_rcac_group', 'pass', '')),
	],

	'ped' => [
		'hosts'            => ['ped.purdue.edu'],
		'base_dn'          => 'ou=ped,dc=purdue,dc=edu',
		'username'         => env('LDAP_PED_USERNAME', conf('ldap_ped', 'rdn', '')),
		'password'         => env('LDAP_PED_PASSWORD', conf('ldap_ped', 'pass', '')),
	],

	'dbm' => [
		'hosts'            => ['webservices.itns.purdue.edu'],
		'port'             => 10636,
		'use_ssl'          => true,
		'base_dn'          => env('LDAP_DBM_BASEDN', conf('ldap_dbm', 'basedn', '')),
		'username'         => env('LDAP_DBM_USERNAME', conf('ldap_dbm', 'rdn', '')),
		'password'         => env('LDAP_DBM_PASSWORD', conf('ldap_dbm', 'pass', '')),
	],
];
