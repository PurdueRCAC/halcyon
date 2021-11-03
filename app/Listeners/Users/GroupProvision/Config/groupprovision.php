<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */
    'url' => env('GROUP_PROVISION_URL', conf('groupprovision', 'url', '')),
    'url_dev' => env('GROUP_PROVISION_URL_DEV', conf('groupprovision', 'url_dev', '')),

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    */
    'user' => env('GROUP_PROVISION_USER', conf('groupprovision', 'user', '')),
    'password' => env('GROUP_PROVISION_PASSWORD', conf('groupprovision', 'password', '')),
];
