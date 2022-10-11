<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Client ID
    |--------------------------------------------------------------------------
    |
    | Your organization's CILogon App ID
    |
    */
    'client_id' => env('CILOGON_ID', '{cilogon-client-id}'),

    /*
    |--------------------------------------------------------------------------
    | Client Secret
    |--------------------------------------------------------------------------
    |
    | Client Secret provided when your organization is registered on CILogon
    |
    */
    'client_secret' => env('CILOGON_SECRET', '{cilogon-client-secret}'),

    /*
    |--------------------------------------------------------------------------
    | Server
    |--------------------------------------------------------------------------
    |
    | Typically, you would use the production server https://cilogon.org.
    | However, you can specify a 'server' parameter when creating the provider
    | to use the "test" server https://test.cilogon.org or "dev" server
    | https://dev.cilogon.org.
    |
    */
    'server' => env('CILOGON_SERVER', null),
];
