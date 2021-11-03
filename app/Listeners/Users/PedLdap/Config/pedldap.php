<?php
return [
    /*
    |--------------------------------------------------------------------------
    | PED LDAP credentials
    |--------------------------------------------------------------------------
    */
    'hosts'    => ['ped.purdue.edu'],
    //'port'     => 10636,
    //'use_ssl'  => true,
    'base_dn'  => 'ou=ped,dc=purdue,dc=edu',
    'username' => env('LDAP_PED_USERNAME', conf('ldap_ped', 'rdn', '')),
    'password' => env('LDAP_PED_PASSWORD', conf('ldap_ped', 'pass', '')),
];
