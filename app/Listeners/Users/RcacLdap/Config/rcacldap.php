<?php
return [
    /*
    |--------------------------------------------------------------------------
    | RCAC LDAP credentials
    |--------------------------------------------------------------------------
    */

    'rcac' => [
        'hosts'    => ['centralservices.rcac.purdue.edu'],
        //'port'     => 10636,
        'base_dn'  => 'ou=People,dc=rcac,dc=purdue,dc=edu',
        'use_ssl'  => true,
        'username' => env('LDAP_RCAC_USERNAME', conf('ldap_rcac', 'rdn', '')),
        'password' => env('LDAP_RCAC_PASSWORD', conf('ldap_rcac', 'pass', '')),
    ],

    'rcac_group' => [
        'hosts'    => ['centralservices.rcac.purdue.edu'],
        //'port'     => 10636,
        'base_dn'  => 'ou=Group,dc=rcac,dc=purdue,dc=edu',
        'use_ssl'  => true,
        'username' => env('LDAP_RCACGROUP_USERNAME', conf('ldap_rcac_group', 'rdn', '')),
        'password' => env('LDAP_RCACGROUP_PASSWORD', conf('ldap_rcac_group', 'pass', '')),
    ],
];
