<?php
return [
    /*
    |--------------------------------------------------------------------------
    | DBM LDAP credentials
    |--------------------------------------------------------------------------
    */
    'hosts'    => ['webservices.itns.purdue.edu'],
    'port'     => 10636,
    'use_ssl'  => true,
    'base_dn'  => env('LDAP_DBM_BASEDN', conf('ldap_dbm', 'basedn', '')),
    'username' => env('LDAP_DBM_USERNAME', conf('ldap_dbm', 'rdn', '')),
    'password' => env('LDAP_DBM_PASSWORD', conf('ldap_dbm', 'pass', '')),
];
