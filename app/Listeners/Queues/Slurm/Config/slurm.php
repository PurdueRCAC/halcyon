<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Slurm REST API credentials
    |--------------------------------------------------------------------------
    | NOTE: Domain _should_ be determined by scheduler hostname. This is a
    | temporary fix.
    */
    'domain' => 'http://somedomain',
    'port' => 6820,
    'version' => 'v0.0.37',
    'username_header' => 'X-SLURM-USER-NAME',
    'username' => 'slurm',
    'token_header' => 'X-SLURM-USER-TOKEN',
    'token' => env('SLURM_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Admin usernames
    |--------------------------------------------------------------------------
    | A list of usernames that should have admin SLURM access
    */
    'admin_users' => ['root'],

    /*
    |--------------------------------------------------------------------------
    | Admin groups
    |--------------------------------------------------------------------------
    | A list of admin groups
    */
    'admin_groups' => [],

    /*
    |--------------------------------------------------------------------------
    | Default account name
    |--------------------------------------------------------------------------
    */
    'default_account' => 'standby',

    /*
    |--------------------------------------------------------------------------
    | Default account name for interactive accounts
    |--------------------------------------------------------------------------
    */
    'interactive_account' => 'interactive',
];
