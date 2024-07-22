<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limit
    |--------------------------------------------------------------------------
    |
    | Configure the number of hits per minute for registered and anonymous
    | users. Anonymous are rate limited by IP address.
    |
    | Set the limit to 0 to disable rate limiting.
    |
    */

    'rate_limit' => [
        'registered' => 1000,
        'anonymous'  => 360,
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to apply to all API routes.
    |
    */

    'middleware' => [
        'api',
    ],

];
