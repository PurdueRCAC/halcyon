<?php
return [
    /*
    |--------------------------------------------------------------------------
    | AuthPrimary LDAP credentials
    |--------------------------------------------------------------------------
    */
    'hosts'    => ['authprimary.rcac.purdue.edu'],
    'use_ssl'  => false,
    'base_dn'  => env('LDAP_AUTHPRIMARY_BASEDN', conf('ldap_amie', 'basedn', 'dc=rcac,dc=purdue,dc=edu')),
    'username' => env('LDAP_AUTHPRIMARY_USERNAME', conf('ldap_amie', 'rdn', 'cn=halcyon,dc=rcac,dc=purdue,dc=edu')),
    'password' => env('LDAP_AUTHPRIMARY_PASSWORD', conf('ldap_amie', 'pass', '')),
];
