<?php
return [
    'dev' => env('UNITIME_DEV', false),

    /*
    |--------------------------------------------------------------------------
    | Production credentials
    |--------------------------------------------------------------------------
    */
    'user' => env('UNITIME_USER', conf('unitime', 'user', '')),
    'password' => env('UNITIME_PASSWORD', conf('unitime', 'password', '')),

    /*
    |--------------------------------------------------------------------------
    | Dev credentials
    |--------------------------------------------------------------------------
    */
    'user_dev' => env('UNITIME_USER_DEV', conf('unitime', 'user_dev', '')),
    'password_dev' => env('UNITIME_PASSWORD_DEV', conf('unitime', 'password_dev', '')),
];
