<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */
    'url' => env('ROLE_PROVISION_URL', conf('roleprovision', 'url', '')),
    'url_dev' => env('ROLE_PROVISION_URL_DEV', conf('roleprovision', 'url_dev', '')),

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    */
    'user' => env('ROLE_PROVISION_USER', conf('roleprovision', 'user', '')),
    'password' => env('ROLE_PROVISION_PASSWORD', conf('roleprovision', 'password', '')),
];
